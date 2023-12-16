<?php

namespace App\Services;

use App\Models\TimeSeries;
use MathPHP\Statistics\Average;

class GraphService
{
    private $EMAperiod = 20;

    public function __construct()
    {
    }

    public function getEMA($symbol): array
    {
        $y = $this->getGraphData($symbol)['y'];

        $EMA = Average::exponentialMovingAverage($y, $this->EMAperiod);
        //$y2 = Average::simpleMovingAverage($y, 5);
        //$y4 = Average::cumulativeMovingAverage($y, 5);

        //$daylyStdDev = $interdayValueChangeSum / count($interdayValueChange);

        return $EMA;
    }

    public function getInterdayValueChangeSum($symbol): float
    {
        $interdayValueChange = [];

        $y = $this->getGraphData($symbol)['y'];

        $EMA = $this->getEMA($symbol);

        foreach ($EMA as $key => $value) {
            $interdayValueChange[] = abs($y[$key] - $value);
        }

        $interdayValueChangeSum = array_sum($interdayValueChange);

        return $interdayValueChangeSum;
    }

    public function getInterdayValueChange($symbol): array
    {
        $interdayValueChange = [];

        $y = $this->getGraphData($symbol)['y'];

        $EMA = $this->getEMA($symbol);

        foreach ($EMA as $key => $value) {
            $interdayValueChange[] = abs($y[$key] - $value);
        }

        return $interdayValueChange;
    }

    public function getGraphData($symbol): array
    {
        $x = [];
        $y = [];

        $timeframes = TimeSeries::query()
            ->where('symbol', $symbol)
            //->whereBetween('date', [$from, $to])
            ->orderBy('date')
            //->limit(200)
            ->get();

        foreach ($timeframes as $timeframe) {
            $x[] = $timeframe['date'];
            $y[] = $timeframe['adjusted_close'];
        }

        //$y = [1,2,4,8,15,9,8,1,10,10,10,10,10,0,1];

        return [
            'x' => $x,
            'y' => $y,
        ];
    }
}
