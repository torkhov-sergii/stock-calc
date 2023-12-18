<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Companies;
use App\Models\TimeSeries;
use App\Services\GraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MathPHP\Statistics\Average;

class GraphController extends Controller
{
    public function __construct(private GraphService $graphService)
    {
    }

    public function getCompanyGraph(Request $request, $symbol)
    {
        $graphData = $this->graphService->getGraphData($symbol);
        $graphDataXY = $this->convertToXYArray($graphData);

        $ema = $this->graphService->getEma($graphData);
        $emaXY = $this->convertToXYArray($ema);

        $crossings = $this->graphService->getCrossings($graphData);
        $crossingsXY = $this->convertToXYArray($crossings);

        $getMaxDeviationBetweenEmaAndGraphDataAfterEachCrossing = $this->graphService->getMaxDeviationBetweenEmaAndGraphDataAfterEachCrossing($graphData);
        $maxDeviationXY = $this->convertToXYArray($getMaxDeviationBetweenEmaAndGraphDataAfterEachCrossing);
        
        return response()->json([
            'graphData' => $graphDataXY,
            'ema' => $emaXY,
            'crossings' => $crossingsXY,
            'maxDeviation' => $maxDeviationXY,
        ]);
    }

    private function convertToXYArray(array $data): array
    {
        $dataX = array_keys($data);
        $dataY = array_values($data);

        return [
            'x' => $dataX,
            'y' => $dataY,
        ];
    }
}
