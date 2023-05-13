<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Companies;
use App\Models\Period;
use App\Models\TimeSeries;
use App\Services\ImportService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function importBySymbol(Request $request)
    {
        $symbol = $request->get('symbol');
        $output = $request->get('output');

        if (!$symbol) {
            return view('error')->with('error', 'Add $symbol');
        }

        if (!$output || !in_array($output, ['full', 'compact'])) {
            return view('error')->with('error', 'Add $output [full, compact]');
        }

        $added = $this->importService->importStockSeries($symbol, $output);

        return view('default', [
            'message' => 'Added: ' . $added
        ]);
    }

    public function importCompanies(Request $request)
    {
        $company = Companies::query()
            ->where('fetch_date', null)
            ->where('id', '<=', 5)
            ->first();

        if ($company) {
            $added = $this->importService->importStockSeries($company->symbol, 'full');

            $company->update([
                'fetch_date' => Carbon::now()
            ]);

            return view('default', [
                'message' => $company->symbol . ' added: ' . $added
            ]);
        }

        return view('default', [
            'message' => 'All already imported'
        ]);
    }

    public function updateCompanies(Request $request)
    {
        $company = Companies::query()
            ->whereNotNull('fetch_date')
            ->whereDate('fetch_date', '<', Carbon::now())
            ->orderBy('fetch_date')
            ->first();

        if ($company) {
            $added = $this->importService->importStockSeries($company->symbol, 'compact');

            $company->update([
                'fetch_date' => Carbon::now()
            ]);

            return view('default', [
                'message' => $company->symbol . ' added:' . $added
            ]);
        }

        return view('default', [
            'message' => 'Not fount to update. Everything is updated'
        ]);
    }

    // Импорт компаний из эксельки screener-stocks-companies-small.xlsx
    public function importCompaniesFromXls()
    {
        // https://stockanalysis.com/stocks/screener/

        exit;

        Companies::truncate();

        $collection = (new FastExcel)->import('../data/screener-stocks-companies-small.xlsx', function ($line) {
            return Companies::create([
                'symbol' => $line['Symbol'],
                'name' => $line['Company Name'],
                'industry' => $line['Industry'],
                'cap' => $line['Market Cap'],
            ]);
        });
    }
}
