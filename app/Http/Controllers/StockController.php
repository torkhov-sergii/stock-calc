<?php

namespace App\Http\Controllers;

use App\Data\ExampleStock;
use App\Helpers\TableHelper;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    private $apikey = 'RYBN57DFVBOWIE5B';
    private $amount = 1000;
    private $initalAmount;
    private $finalAmount;
    private $stockPortfolio = [];

    public function __construct()
    {
        $this->initalAmount = $this->amount;
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

    public function show($symbol): string
    {
        $change = 0;

        $timeSeries = Stock::where('symbol', $symbol)->orderBy('date')->limit(20)->get();

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
//        $timeSeries[$key]['stock'] = $this->stock;

        $this->finalAmount = $this->amount;

        return view('stock.show', [
            'initialAmount' => $this->initalAmount,
            'finalAmount' => $this->finalAmount,
            'timeSeries' => $timeSeries,
        ]);
    }

    private function buy($day): bool
    {
//        dump('buy');

        $deal['operation'] = 'buy';
        $deal['price'] = $day['close'];
        $deal['count'] = $this->amount / $day['close'];
        $this->stockPortfolio[] = $deal;

        $this->amount = $this->amount - $deal['count'] * $deal['price'];

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
}
