<?php

namespace App\Services;

use App\Models\Stock;
use App\Strategy\Strategy_1;
use App\Strategy\Strategy_2;
use Illuminate\Database\Eloquent\Collection;

class StockService
{
    public $amount = 1000;
    private $initialAmount;
    private $finalAmount;
    private $stockPortfolio = [];

    public function __construct()
    {
        $this->initialAmount = $this->amount;
    }

    public function getInitalAmount(): int
    {
        return $this->initialAmount;
    }

    public function getFinalAmount(): int
    {
        return $this->finalAmount;
    }

    public function buy($price, $message): bool
    {
        $deal['operation'] = 'buy';
        $deal['price'] = $price;
        $deal['count'] = $this->amount / $price;
        $deal['message'] = $message;
        $this->stockPortfolio[] = $deal;

        $this->amount = round($this->amount - $deal['count'] * $deal['price']);

        //dump([$deal, $this->amount]);

        return true;
    }

    public function sell($price, $message): bool
    {
        $last = last($this->stockPortfolio);

        if(isset($last['operation']) && $last['operation'] == 'buy') {
            $this->amount = abs(last($this->stockPortfolio)['count']) * $price;

            $deal['operation'] = 'sell';
            $deal['price'] = $price;
            $deal['count'] = last($this->stockPortfolio)['count'];
            $deal['message'] = $message;
            $this->stockPortfolio[] = $deal;

            return true;
        }

        return false;
    }

    public function stockCalc($symbol, $from, $to)
    {
        $timeSeries = Stock::query()
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            //->limit(20)
            ->get();

        foreach ($timeSeries as $key => $day) {
            if(isset($timeSeries[$key-1])) {
                $prevDay = $timeSeries[$key-1];
            }

            // Today's price changes
            if (isset($prevDay)) {
                $change = $day['close'] - $prevDay['close'];

                $timeSeries[$key]['change'] = $change;
            }

            $strategyClass = new Strategy_1();
//            $strategyClass = new Strategy_2();

            $strategy = $strategyClass->getAction($timeSeries, $key, $day);

            // BUY
            if ($strategy['action'] === 'buy' && $this->amount) {
                if ($this->buy($day['close'], $strategy['message'])) {
                    $timeSeries[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            // SELL
            if ($strategy['action'] === 'sell') {
                if ($this->sell($day['close'], $strategy['message'])) {
                    $timeSeries[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            if ($this->amount) {
                $timeSeries[$key]['amount'] = $this->amount;
            } else {
                $timeSeries[$key]['amount'] = last($this->stockPortfolio)['count'] * $day['close'];
            }
        }

        $this->sell($day['close'], 'sell remain');

        $this->finalAmount = $this->amount;

        return $timeSeries;
    }
}
