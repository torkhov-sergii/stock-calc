<?php

namespace App\Strategy;

use Illuminate\Database\Eloquent\Collection;

class Strategy_2 implements StrategyInterface
{
    public function __construct()
    {
        $this->strategyDescription = 'что считаем?';
    }

    public function getAction($timeframes, $key, $timeframe) {
        $action = '';
        $message = '';

        $dynamic = [
            '1' => $this->getChangeByDays($timeframes, $key, -1) / $timeframes[$key]['close'] * 100,
            '7' => $this->getChangeByDays($timeframes, $key, -7) / $timeframes[$key]['close'] * 100,
            '30' => $this->getChangeByDays($timeframes, $key, -30) / $timeframes[$key]['close'] * 100,
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

    private function getChangeByDays($timeframes, $key, $changesByDays)
    {
        $change = 0;

        if(isset($timeframes[$key-$changesByDays])) {
            $change = $timeframes[$key]['close'] - $timeframes[$key-$changesByDays]['close'];
        }

        return $change;
    }
}
