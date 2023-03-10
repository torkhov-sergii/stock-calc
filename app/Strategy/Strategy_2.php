<?php

namespace App\Strategy;

use Illuminate\Database\Eloquent\Collection;

class Strategy_2 implements StrategyInterface
{
    //покупать ???
    //продавать ???
    public function getAction($timeSeries, $key, $day) {
        $action = '';
        $message = '';

        $dynamic = [
            '1' => $this->getChangeByDays($timeSeries, $key, 1) / $timeSeries[$key]['close'] * 100,
            '7' => $this->getChangeByDays($timeSeries, $key, 7) / $timeSeries[$key]['close'] * 100,
            '30' => $this->getChangeByDays($timeSeries, $key, 30) / $timeSeries[$key]['close'] * 100,
        ];

        foreach ($dynamic as $key => $val) {
            $message .= '<br>'.$key.': '.round($val, 2);
        }

        // BUY
        if ($dynamic['1'] > 0) {
            $action = 'buy';
        }

        // SELL
        if ($dynamic['7'] < 0) {
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
