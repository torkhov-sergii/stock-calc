<?php

namespace App\Services;

use App\Models\Companies;
use App\Models\TimeSeries;
use MathPHP\Statistics\Average;

class GraphService
{
    private $EMAperiod = 20;

    public function __construct()
    {
    }

    public function getEMA($graphData): array
    {
        $EMA = Average::exponentialMovingAverage($graphData['y'], $this->EMAperiod);
        //$y2 = Average::simpleMovingAverage($y, 5);
        //$y4 = Average::cumulativeMovingAverage($y, 5);

        //$daylyStdDev = $interdayValueChangeSum / count($interdayValueChange);

        return $EMA;
    }

    public function getInterdayValueChangeSum($graphData): float
    {
        $interdayValueChange = $this->getInterdayValueChange($graphData);

        $interdayValueChangeSum = array_sum($interdayValueChange);

        return $interdayValueChangeSum;
    }

    public function getInterdayValueChange($graphData): array
    {
        $interdayValueChange = [];

        $EMA = $this->getEMA($graphData);

        foreach ($EMA as $key => $value) {
            $interdayValueChange[] = abs($graphData['y'][$key] - $value);
        }

        return $interdayValueChange;
    }

    public function getGraphData($symbol): array
    {
        $x = [];
        $y = [];

        $from = Companies::GRAPH_DATE_RANGE['from'];
        $to = Companies::GRAPH_DATE_RANGE['to'];

        $timeframes = TimeSeries::query()
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
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
