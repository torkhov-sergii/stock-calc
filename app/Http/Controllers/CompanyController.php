<?php

namespace App\Http\Controllers;

use App\Services\GraphService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private GraphService $graphService)
    {
    }

    public function company(Request $request, $symbol)
    {
        $graphData = $this->graphService->getGraphData($symbol);
        $getInterdayValueChange = $this->graphService->getInterdayValueChange($graphData);
        $getInterdayValueChangeSum = $this->graphService->getInterdayValueChangeSum($graphData);
        $getExpectedValue = $this->graphService->getExpectedValue($graphData);

        return view('stock.company', [
            'symbol' => $symbol,
            'interdayValueChange' => $getInterdayValueChange, 
            'interdayValueChangeSum' => $getInterdayValueChangeSum, 
            'expectedValue' => $getExpectedValue,
        ]);
    }
}
