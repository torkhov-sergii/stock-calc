<?php

namespace App\Services;

use App\Models\Stock;
use App\Strategy\StrategyInterface;

class StockService
{
    public int $amount = 1000;
    private StrategyInterface $strategy;
    private int $initialAmount;
    private int $finalAmount;
    private array $stockPortfolio = [];

    public function __construct(StrategyInterface $strategy)
    {
        $this->initialAmount = $this->amount;
        $this->strategy = $strategy;
    }

    public function getInitalAmount(): int
    {
        return $this->initialAmount;
    }

    public function getFinalAmount(): int
    {
        return $this->finalAmount;
    }

    private function buy($price, $message): bool
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

    private function sell($price, $message): bool
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
        $timeframes = Stock::query()
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            //->limit(20)
            ->get();

        foreach ($timeframes as $key => $timeframe) {
            if(isset($timeframes[$key-1])) {
                $prevDay = $timeframes[$key-1];
            }

            // Today's price changes
            if (isset($prevDay)) {
                $change = $timeframe['close'] - $prevDay['close'];

                $timeframes[$key]['change'] = $change;
            }

            $strategyClass = $this->strategy;

            $strategy = $strategyClass->getAction($timeframes, $key, $timeframe);

            // BUY
            if ($strategy['action'] === 'buy' && $this->amount) {
                if ($this->buy($timeframe['close'], $strategy['message'])) {
                    $timeframes[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            // SELL
            if ($strategy['action'] === 'sell') {
                if ($this->sell($timeframe['close'], $strategy['message'])) {
                    $timeframes[$key]['stockPortfolio'] = last($this->stockPortfolio);
                }
            }

            if ($this->amount) {
                $timeframes[$key]['amount'] = $this->amount;
            } else {
                $timeframes[$key]['amount'] = last($this->stockPortfolio)['count'] * $timeframe['close'];
            }
        }

        $this->sell($timeframes->last()['close'], 'sell remain');

        $this->finalAmount = $this->amount;

        return $timeframes;
    }
}
