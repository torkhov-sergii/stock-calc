<?php

namespace App\Strategy;

use Illuminate\Database\Eloquent\Collection;

class Strategy_1 extends Strategy implements StrategyInterface
{
    public function __construct()
    {
        $this->strategyDescription = 'покупать если со вчера идет вверх, продавать если со вчера идет вниз';
    }

    public function getAction($timeframes, $key, $timeframe): array {
        $action = '';
        $message = '';

        $change = $this->getChangeByDays($timeframes, $key, -1);

        // BUY
        if ($change > 0) {
            $action = 'buy';
        }

        // SELL
        if ($change < 0) {
            $action = 'sell';
        }

        return [
            'action' => $action,
            'message' => $message,
        ];
    }
}
