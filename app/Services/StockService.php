<?php

namespace App\Services;

use App\Models\TimeSeries;
use App\Strategy\StrategyInterface;

class StockService
{
    public int $amount = 1000;
    private StrategyInterface $strategy;
    private float $initialAmount;
    private float $finalAmount;
    private object|null $stockPortfolio;

    public function __construct(StrategyInterface $strategy)
    {
        $this->initialAmount = $this->amount;
        $this->strategy = $strategy;

        $this->stockPortfolio = new \stdClass();
        $this->stockPortfolio->currentAmount = $this->amount;
        $this->stockPortfolio->totalAmount = $this->amount;
        $this->stockPortfolio->count = 0;
    }

    public function getInitalAmount(): float
    {
        return $this->initialAmount;
    }

    public function getFinalAmount(): float
    {
        return $this->finalAmount;
    }

    private function buy(float $price)
    {
        $count = $this->stockPortfolio->currentAmount / $price;

        $this->stockPortfolio->operation = 'buy';
        $this->stockPortfolio->price = $price;
        $this->stockPortfolio->count = $count;
        $this->stockPortfolio->currentAmount = 0;
    }

    private function sell(float $price)
    {
        $amount = $this->stockPortfolio->count * $price;

        $this->stockPortfolio->operation = 'sell';
        $this->stockPortfolio->price = $price;
        $this->stockPortfolio->count = 0;
        $this->stockPortfolio->currentAmount = $amount;
    }

    public function stockCalc($symbol, $from, $to)
    {
        $timeframes = TimeSeries::query()
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            //->limit(20)
            ->get();

        foreach ($timeframes as $key => $timeframe) {
            // Today's price changes
            $timeframes[$key]->change = isset($timeframes[$key - 1]['close']) ? $timeframe['close'] - $timeframes[$key - 1]['close'] : null;

            $strategyClass = $this->strategy;

            $strategy = $strategyClass->getAction($timeframes, $key, $timeframe);

            $this->stockPortfolio->message = $strategy['message'];

            // BUY
            if ($strategy['action'] === 'buy' && $this->stockPortfolio->currentAmount) {
                $this->buy($timeframe['close']);

                $tempStockPortfolio = clone $this->stockPortfolio;
            }
            // SELL
            elseif ($strategy['action'] === 'sell' && $this->stockPortfolio->count) {
                $this->sell($timeframe['close']);

                $tempStockPortfolio = clone $this->stockPortfolio;
            } else {
                $tempStockPortfolio = clone $this->stockPortfolio;

                $tempStockPortfolio->operation = '';
                $tempStockPortfolio->price = null;
                $tempStockPortfolio->count = null;
            }

            if ($tempStockPortfolio->currentAmount) {
                $tempStockPortfolio->totalAmount = $this->stockPortfolio->totalAmount = $tempStockPortfolio->currentAmount;
            } else {
                $tempStockPortfolio->totalAmount = $this->stockPortfolio->totalAmount = $this->stockPortfolio->count * $timeframe['close'];
            }

            $timeframes[$key]->stockPortfolio = $tempStockPortfolio;
        }

        if ($timeframes->last()) {
            $this->sell($timeframes->last()['close']);
        }

        $this->finalAmount = $this->stockPortfolio->totalAmount;

        return $timeframes;
    }
}
