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
        $getInterdayValueChange = $this->graphService->getInterdayValueChange($symbol);
        $getInterdayValueChangeSum = $this->graphService->getInterdayValueChangeSum($symbol);

        return view('stock.company', [
            'symbol' => $symbol,
            'interdayValueChange' => $getInterdayValueChange, 
            'interdayValueChangeSum' => $getInterdayValueChangeSum, 
        ]);
    }
}
