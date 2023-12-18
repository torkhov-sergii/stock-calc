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
        $interdayValueChangeSum = $this->graphService->getInterdayValueChangeSum($graphData);
        $expectedValue = $this->graphService->getExpectedValue($graphData);

        return view('stock.company', [
            'symbol' => $symbol,
            'interdayValueChangeSum' => $interdayValueChangeSum, 
            'expectedValue' => $expectedValue,
        ]);
    }
}
