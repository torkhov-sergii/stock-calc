<?php

namespace App\Strategy;

use Illuminate\Database\Eloquent\Collection;

class Strategy_1 implements StrategyInterface
{
    //покупать если со вчера идет вверх
    //продавать если со вчера идет вниз
    public function getAction($timeSeries, $key, $day) {
        $action = '';
        $message = '';

        $change = $this->getChangeByDays($timeSeries, $key, 1);

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

    private function getChangeByDays($timeSeries, $key, $changesByDays)
    {
        $change = 0;

        if(isset($timeSeries[$key-$changesByDays])) {
            $change = $timeSeries[$key]['close'] - $timeSeries[$key-$changesByDays]['close'];
        }

        return $change;
    }
}
