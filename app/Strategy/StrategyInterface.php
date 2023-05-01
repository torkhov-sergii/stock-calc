<?php

namespace App\Strategy;

interface StrategyInterface
{
    public function getAction($timeframes, $key, $timeframe);
}
