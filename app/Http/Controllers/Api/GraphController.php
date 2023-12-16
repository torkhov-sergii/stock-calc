<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Companies;
use App\Models\TimeSeries;
use App\Services\GraphService;
use Illuminate\Http\Request;
use MathPHP\Statistics\Average;

class GraphController extends Controller
{
    public function __construct(private GraphService $graphService)
    {
    }

    public function getCompanyGraph(Request $request, $symbol)
    {
        $EMA = $this->graphService->getEMA($symbol);
        $graphData = $this->graphService->getGraphData($symbol);

        return response()->json([
            'x' => $graphData['x'],
            'y' => $graphData['y'],
            'yEMA' => $EMA,
        ]); 
    }
}
