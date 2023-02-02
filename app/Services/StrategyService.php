<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class StrategyService
{
    //покупать если со вчера идет вверх
    //продавать если со вчера идет вниз
    public function strategy1($timeSeries, $key, $day) {
        $action = '';
        $message = '';

        $change = $this->getChangeByDays($timeSeries, $key, $day, 1);

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

    //покупать
    //продавать
    public function strategy2($timeSeries, $key, $day) {
        $action = '';
        $message = '';

        $change = $this->getChangeByDays($timeSeries, $key, $day, 2);

        $dynamic = [
          1 => round($this->getChangeByDays($timeSeries, $key, $day, 1), 2),
          7 => round($this->getChangeByDays($timeSeries, $key, $day, 7), 2),
          30 => round($this->getChangeByDays($timeSeries, $key, $day, 7), 2),
        ];

        foreach ($dynamic as $key => $val) {
            $message .= '<br>'.$key.': '.$val;
        }

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

    private function getChangeByDays($timeSeries, $key, $day, $changesByDays)
    {
        $change = 0;

        if(isset($timeSeries[$key-$changesByDays])) {
            $change = $day['close'] - $timeSeries[$key-$changesByDays]['close'];
        }

        return $change;
    }
}
