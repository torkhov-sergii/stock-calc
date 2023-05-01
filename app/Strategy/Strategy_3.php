<?php

namespace App\Strategy;

use App\Services\StockService;
use App\Strategy\StrategyService;
use Illuminate\Database\Eloquent\Collection;

class Strategy_3 extends Strategy implements StrategyInterface
{
    /*
    private StrategyService $strategyService;

    public function __construct(StrategyService $strategyService)
    {
        $this->strategyService = $strategyService;
    }
    */

    // посчитать возможный макс (если быть провидцем)
    public function getAction($timeframes, $key, $timeframe) {
        $action = '';
        $message = '';

        $change = $this->getChangeByDays($timeframes, $key, 1);

        // BUY
        if ($change < 0) {
            $action = 'buy';
        }

        // SELL
        if ($change > 0) {
            $action = 'sell';
        }

        return [
            'action' => $action,
            'message' => $message,
        ];
    }
}
