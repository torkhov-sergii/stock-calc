<?php

namespace App\Services;

use App\Models\Companies;
use App\Models\TimeSeries;
use Illuminate\Support\Collection;
use MathPHP\Statistics\Average;
use PhpParser\ErrorHandler\Collecting;

class GraphService
{
    private $EMAperiod = 20;

    public function __construct()
    {
    }

    public function getGraphData(string $symbol): array
    {
        $from = Companies::GRAPH_DATE_RANGE['from'];
        $to = Companies::GRAPH_DATE_RANGE['to'];

        $timeframes = TimeSeries::query()
            ->select(['date', 'adjusted_close'])
            ->where('symbol', $symbol)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->limit(40)
            ->get()
            ->keyBy('date');

        $timeframesArray = $timeframes->pluck('adjusted_close', 'date')->toArray();

        //$y = [1,2,5,6,6,6,6,10,20,2,-1,1,1,1,1,1,20,40,22,23,24,25,26,27,5,6,7,6,5,4,5];
        //$x = range(1, count($y));

        return $timeframesArray;
    }

    public function getEma(array $graphData): array
    {
        $graphDataX = array_keys($graphData);
        $graphDataY = array_values($graphData); 

        $EmaY = Average::exponentialMovingAverage($graphDataY, $this->EMAperiod);

        $EmaArray = array_combine($graphDataX, $EmaY);

        //$y2 = Average::simpleMovingAverage($y, 5);
        //$y4 = Average::cumulativeMovingAverage($y, 5);

        return $EmaArray;
    }

    public function getInterdayValueChangeSum(array $graphData): float
    {
        $interdayValueChange = $this->getInterdayValueChange($graphData);

        $interdayValueChangeSum = array_sum($interdayValueChange);

        return $interdayValueChangeSum;
    }

    public function getInterdayValueChange(array $graphData): array
    {
        $interdayValueChange = [];

        $ema = $this->getEma($graphData);

        foreach ($graphData as $key => $value) {
            $interdayValueChange[$key] = abs($graphData[$key] - $ema[$key]);
        }

        return $interdayValueChange;
    }

    public function getExpectedValue(array $graphData): int
    {
        $graphDataY = array_values($graphData); 

        $fromValue = $graphDataY[0];
        $toValue = $graphDataY[count($graphDataY) - 1];

        $expectedValue = $toValue + ($toValue - $fromValue);

        return $expectedValue;
    }
    
    public function getCrossings(array $graphData): array
    {
        $ema = $this->getEma($graphData);
        $emaX = array_keys($ema);
        $emaY = array_values($ema);

        $graphDataX = array_keys($graphData);
        $graphDataY = array_values($graphData); 

        foreach ($graphDataX as $key => $value) {
            if (isset($graphDataX[$key + 1])) {
                $thisValue = $graphDataY[$key];
                $nextValue = $graphDataY[$key + 1];
    
                if ($thisValue > $emaY[$key] && $nextValue < $emaY[$key + 1]) {
                    $crossingPoints[$value] = $thisValue;
                } elseif ($thisValue < $emaY[$key] && $nextValue > $emaY[$key + 1]) {
                    $crossingPoints[$value] = $thisValue;
                }
            }
     
        }

        return $crossingPoints;
    }

    // Максимальное отклонение графика от EMA после каждого пересечения
    public function getMaxDeviationBetweenEmaAndGraphDataAfterEachCrossing(array $graphData)
    {
        $maxDeviations = [];

        $crossings = $this->getCrossings($graphData);
        $crossingsX = array_keys($crossings);

        $ema = $this->getEma($graphData);

        $graphDataX = array_keys($graphData);

        foreach ($crossingsX as $key => $value) {
            $offset = array_search($value, $graphDataX);

            if (isset($crossingsX[$key + 1])) {
                $nextCrossingXValue = $crossingsX[$key + 1];

                $length = array_search($nextCrossingXValue, $graphDataX) - $offset;
    
                $graphDataSlice = array_slice($graphData, $offset, $length);
    
                $maxDeviation = 0;
    
                foreach ($graphDataSlice as $key => $value) {
                    $deviation = abs($value - $ema[$key]);
    
                    if ($deviation > $maxDeviation) {
                        $maxDeviationGraphDate = $key;

                        if ($ema[$key] > $value) {
                            $maxDeviationGpraphValue = $ema[$key] - $deviation;
                        } else {
                            $maxDeviationGpraphValue = $ema[$key] + $deviation;
                        }

                        $maxDeviation = $deviation;
                    }

                }
    
                $maxDeviations[$maxDeviationGraphDate] = $maxDeviationGpraphValue;
            }
        }

        return $maxDeviations;
    }
}
