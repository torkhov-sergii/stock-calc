<?php

namespace App\Strategy;

interface StrategyInterface
{
    public function getAction($timeSeries, $key, $day);
}
