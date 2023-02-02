<?php

namespace App\Http\Controllers;

use App\Data\ExampleStock;
use App\Helpers\Helpers;
use App\Helpers\StrategyService;
use App\Helpers\TableHelper;
use App\Models\Period;
use App\Models\Stock;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockController extends Controller
{
    private $apikey = 'RYBN57DFVBOWIE5B';
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
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

        $timeSeries = $this->stockService->stockCalc($symbol, $from, $to);

        return view('stock.show', [
            'periodId' => $periodId,
            'symbol' => $symbol,
            'from' => $from,
            'to' => $to,
            'initialAmount' => $this->stockService->getInitalAmount(),
            'finalAmount' => $this->stockService->getFinalAmount(),
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
            $this->stockService->amount = 1000;
            $from = $period['from'];
            $to = $period['to'];

//            $from = '2014-01-01';
//            $to = '2014-01-10';

            $timeSeries = $this->stockService->stockCalc($symbol, $from, $to);

            $stockPriceFrom = $timeSeries->first()['close'];
            $stockPriceTo = $timeSeries->last()['close'];

            $holdAmount = $this->stockService->getInitalAmount() / $stockPriceFrom * $stockPriceTo;

            $periodDays = Carbon::parse($from)->diffInDays($to);
            $changePerYear =  ceil((100 / $this->stockService->getInitalAmount() * $holdAmount - 100) / (1 / (365 / $periodDays)));

            $periodResults[] = [
                'id' => $period->id,
                'from' => $from,
                'to' => $to,
                'periodDays' => $periodDays,
                'stockPriceFrom' => $stockPriceFrom,
                'stockPriceTo' => $stockPriceTo,
                'initialAmount' => $this->stockService->getInitalAmount(),
                'changePerYear' => $changePerYear,
                'holdAmount' => $holdAmount,
                'finalAmount' => $this->stockService->getFinalAmount(),
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

}
