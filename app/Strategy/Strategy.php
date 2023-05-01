<?php

namespace App\Strategy;

class Strategy
{
    public function getChangeByDays($timeframes, $key, $changesByDays)
    {
        $change = 0;

        if(isset($timeframes[$key+$changesByDays])) {
            $change = $timeframes[$key]['close'] - $timeframes[$key+$changesByDays]['close'];
        }

        return $change;
    }
}
