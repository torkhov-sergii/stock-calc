<?php

namespace App\Strategy;

use App\Services\StockService;
use App\Strategy\StrategyService;
use Illuminate\Database\Eloquent\Collection;

class Strategy_4 extends Strategy implements StrategyInterface
{
    public $strategyDescription;

    public function __construct()
    {
        $this->strategyDescription = 'продавать по стоплосу в процентах. продавать по стоплосу в процентах прибыли';
    }

    // что считаем?
    public function getAction($timeframes, $key, $timeframe, $stockPortfolio) {
        $action = '';
        $message = '';
        $stopLossPercentage = 1;
        $stopProfitPercentage = 20;

        $change = $this->getChangeByDays($timeframes, $key, -1);

        $message = null;

//        dd($stockPortfolio->totalAmount);

//        dump($change);

        // BUY
        if ($change < -5) {
            $action = 'buy';
        }

        $startDealAmount = $stockPortfolio->count * $stockPortfolio->price;
        $stopLossAmount = $startDealAmount - $startDealAmount * $stopLossPercentage / 100;
        $stopProfitAmount = $startDealAmount + $startDealAmount * $stopProfitPercentage / 100;

//        $message = $stockPortfolio->totalAmount.'<br>'.$startDealAmount.'<br>'.$stopLossAmount;

        // SELL
        if (
            $startDealAmount && $stockPortfolio->totalAmount < $stopLossAmount
            || $startDealAmount && $stockPortfolio->totalAmount > $stopProfitAmount
        ) {
            $action = 'sell';

            if ($stockPortfolio->totalAmount < $stopLossAmount) $message = 'looses';
            if ($stockPortfolio->totalAmount > $stopProfitAmount) $message = 'profit';
        }



        return [
            'action' => $action,
            'message' => $message,
        ];
    }
}
