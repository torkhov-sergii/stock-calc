<?php

namespace App\Http\Controllers;

use App\Data\ExampleStock;
use App\Helpers\Helpers;
use App\Helpers\TableHelper;
use App\Models\Period;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockController extends Controller
{
    private $apikey = 'RYBN57DFVBOWIE5B';
    private $amount = 1000;
    private $initialAmount;
    private $finalAmount;
    private $stockPortfolio = [];

    public function __construct()
    {
        $this->initialAmount = $this->amount;
    }

    public function import(Request $request, $symbol = '')
    {
        if (!$symbol) {
            return abort(403, 'Add symbol');
        }

        $outputsize = 'compact';
//        $outputsize = 'full';
        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=' . $symbol . '&apikey=' . $this->apikey . '&outputsize=' . $outputsize);
        //$json = ExampleStock::tsla();

        $data = json_decode($json, true);

        $timeSeries = $data['Time Series (Daily)'];

        if (!$timeSeries) {
            return abort(403, 'Symbol not found');
        }

        $count = 0;

        foreach ($timeSeries as $date => $series) {
            $close = $series['5. adjusted close'];

            if ($close) {
                $stock = Stock::firstOrCreate([
                    'symbol' => $symbol,
                    'date' => $date,
                ], [
                    'symbol' => $symbol,
                    'date' => $date,
                    'close' => $close,
                ]);

                if ($stock->wasRecentlyCreated) $count++;
            }
        }

        return response('Added: ' . $count);
    }

    public function show(Request $request, $symbol): string
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $periodId = $request->get('period');

        if (!(($from && $to) || $periodId)) {
            return abort(403, 'Set FROM & TO Date as get params');
        }

        if($periodId) {
            $period = Period::find($periodId);

            $from = $period->from;
            $to = $period->to;
        }

        $timeSeries = $this->stockCalc($symbol, $from, $to);

        return view('stock.show', [
            'periodId' => $periodId,
            'symbol' => $symbol,
            'from' => $from,
            'to' => $to,
            'initialAmount' => $this->initialAmount,
            'finalAmount' => $this->finalAmount,
            'timeSeries' => $timeSeries,
        ]);
    }

    public function all(Request $request, $symbol)
    {
        // Начало данных с года
        $stockDateFrom = Stock::query()
            ->where('symbol', $symbol)
            ->orderBy('id', 'desc')
            ->first()['date'];

        // Периоды для данных для года
        $periods = Period::query()
            ->whereYear('min', $stockDateFrom)
            //->limit(1)
            ->get();

        foreach ($periods as $period) {
            $this->amount = 1000;
            $this->initialAmount = $this->amount;
            $from = $period['from'];
            $to = $period['to'];

//            $from = '2014-01-01';
//            $to = '2014-01-10';

            $timeSeries = $this->stockCalc($symbol, $from, $to);

            $stockPriceFrom = $timeSeries->first()['close'];
            $stockPriceTo = $timeSeries->last()['close'];

            $holdAmount = $this->initialAmount / $stockPriceFrom * $stockPriceTo;

            $periodDays = Carbon::parse($from)->diffInDays($to);
//            $holdInterestRate =  ceil(($holdAmount - ($this->initialAmount)) / 1000 * 100 / (1 / (365 / $periodDays)));
            $changePerYear =  ceil((100 / $this->initialAmount * $holdAmount - 100) / (1 / (365 / $periodDays)));

            $periodResults[] = [
                'id' => $period->id,
                'from' => $from,
                'to' => $to,
                'periodDays' => $periodDays,
                'stockPriceFrom' => $stockPriceFrom,
                'stockPriceTo' => $stockPriceTo,
                'initialAmount' => $this->initialAmount,
                'changePerYear' => $changePerYear,
                'holdAmount' => $holdAmount,
                'finalAmount' => $this->finalAmount,
            ];
        }

        $averageHoldAmount = Helpers::averageArrayKey($periodResults,  'holdAmount');
        $averageFinalAmount = Helpers::averageArrayKey($periodResults,  'finalAmount');

        return view('stock.all', [
            'symbol' => $symbol,
            'periodResults' => $periodResults,
            'averageFinalAmount' => $averageFinalAmount,
            'averageHoldAmount' => $averageHoldAmount,
        ]);
    }

    private function buy($day): bool
    {
//        dump('buy');

        $deal['operation'] = 'buy';
        $deal['price'] = $day['close'];
        $deal['count'] = $this->amount / $day['close'];
        $this->stockPortfolio[] = $deal;

        $this->amount = round($this->amount - $deal['count'] * $deal['price']);

        //dump([$deal, $this->amount]);

        return true;
    }

    private function sell($day): bool
    {
        $last = last($this->stockPortfolio);

        if(isset($last['operation']) && $last['operation'] == 'buy') {
//            dump('sell', $this->stockPortfolio);

            $this->amount = abs(last($this->stockPortfolio)['count']) * $day['close'];

            $deal['operation'] = 'sell';
            $deal['price'] = $day['close'];
            $deal['count'] = last($this->stockPortfolio)['count'];
            $this->stockPortfolio[] = $deal;

            return true;
        }

        return false;
    }

    private function stockCalc($symbol, $from, $to) {
        $change = 0;

        $timeSeries = Stock::query()
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            //->limit(20)
            ->get();

//        dd($timeSeries);

        foreach ($timeSeries as $key => $day) {
            if(isset($timeSeries[$key-1])) {
                $prevDay = $timeSeries[$key-1];
            }

            // Today's price changes
            if (isset($prevDay)) {
                $change = $day['close'] - $prevDay['close'];

                $timeSeries[$key]['change'] = $change;
            }

            // BUY
            if ($change > 0 && $this->amount) {
                if ($this->buy($day)) {
                    $timeSeries[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            // SELL
            if ($change < 0) {
                if ($this->sell($day)) {
                    $timeSeries[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            if ($this->amount) {
                $timeSeries[$key]['amount'] = $this->amount;
            } else {
                $timeSeries[$key]['amount'] = last($this->stockPortfolio)['count'] * $day['close'];
            }
        }

        $this->sell($day);

        $this->finalAmount = $this->amount;

        return $timeSeries;
    }
}
