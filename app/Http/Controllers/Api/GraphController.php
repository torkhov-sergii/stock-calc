<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Companies;
use App\Models\TimeSeries;
use Illuminate\Http\Request;
use MathPHP\Statistics\Average;

class GraphController extends Controller
{
    public function getCompanyGraph(Request $request, $symbol)
    {
        $x = [];
        $y = [];
        $y2 = [];

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

        $y = [1,2,4,8,10,9,8,1,10,10,10,10,10,0,1,1,2,4,8,10,9,8,1,10,10,10,10,10,0,1];

        $yExpnentiaMovingAverage = Average::exponentialMovingAverage($y, 5);
        //$y2 = Average::simpleMovingAverage($y, 5);
        //$y4 = Average::cumulativeMovingAverage($y, 5);

        return response()->json([
            'x' => $x,
            'y' => $y,
            'yExpnentiaMovingAverage' => $yExpnentiaMovingAverage,
        ]); 
    }
}
