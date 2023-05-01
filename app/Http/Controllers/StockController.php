<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Companies;
use App\Models\Period;
use App\Models\TimeSeries;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct()
    {
        $strategyNumber = 3;
        $strategyClass = 'App\Strategy\Strategy_' . $strategyNumber;
        $this->stockService = new StockService(new $strategyClass());
    }

    public function import(Request $request, $symbol = '')
    {
        //https://www.alphavantage.co/documentation/

        if (!$symbol) {
            return abort(403, 'Add symbol');
        }

        if($symbol == 'auto') {
            $company = Companies::query()
                ->where('fetch_date', null)
                ->first();

            $symbol = $company->symbol;
        }

        $outputsize = 'compact';
//        $outputsize = 'full';
        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=' . $symbol . '&apikey=' . env('ALPHAVANTAGE_API') . '&outputsize=' . $outputsize);

        $data = json_decode($json, true);

        $timeframes = $data['Time Series (Daily)'];

        if (!$timeframes) {
            return abort(403, 'Symbol not found');
        }

        $count = 0;

        foreach ($timeframes as $date => $series) {
            $open = $series['1. open'];
            $high = $series['2. high'];
            $low = $series['3. low'];
            $close = $series['4. close'];
            $adjusted_close = $series['5. adjusted close'];
            $volume = $series['6. volume'];

            if ($close) {
                $stock = TimeSeries::firstOrCreate([
                    'symbol' => $symbol,
                    'date' => $date,
                ], [
                    'symbol' => $symbol,
                    'date' => $date,
                    'open' => $open,
                    'high' => $high,
                    'low' => $low,
                    'close' => $close,
                    'adjusted_close' => $adjusted_close,
                    'volume' => $volume,
                ]);

                if ($stock->wasRecentlyCreated) $count++;
            }
        }

        $company->update([
            'fetch_date' => Carbon::now()
        ]);

        return response('Added: ' . $count);
    }

    public function import_companies()
    {
        // https://stockanalysis.com/stocks/screener/

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

    public function show(Request $request, $symbol): string
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $periodId = $request->get('period');

        if (!(($from && $to) || $periodId)) {
            return abort(403, 'Set FROM & TO Date as get params');
        }

        if($periodId) {
            $period = Period::find($periodId);

            $from = $period->from;
            $to = $period->to;
        }

        $timeframes = $this->stockService->stockCalc($symbol, $from, $to);

        return view('stock.show', [
            'periodId' => $periodId,
            'symbol' => $symbol,
            'from' => $from,
            'to' => $to,
            'initialAmount' => $this->stockService->getInitalAmount(),
            'finalAmount' => $this->stockService->getFinalAmount(),
            'timeSeries' => $timeframes,
        ]);
    }

    public function all(Request $request, $symbol)
    {
        // Начало данных с года
        $stockDateFrom = TimeSeries::query()
            ->where('symbol', $symbol)
            ->orderBy('id', 'desc')
            ->first()['date'];

        // Периоды для данных для года
        $periods = Period::query()
            ->whereYear('min', $stockDateFrom)
            //->limit(1)
            ->get();

        foreach ($periods as $period) {
            $this->stockService->amount = 1000;
            $from = $period['from'];
            $to = $period['to'];

//            $from = '2014-01-01';
//            $to = '2014-01-10';

            $timeframes = $this->stockService->stockCalc($symbol, $from, $to);

            $stockPriceFrom = $timeframes->first()['close'];
            $stockPriceTo = $timeframes->last()['close'];

            $holdAmount = $this->stockService->getInitalAmount() / $stockPriceFrom * $stockPriceTo;

            $periodDays = Carbon::parse($from)->diffInDays($to);
            $changePerYear =  ceil((100 / $this->stockService->getInitalAmount() * $holdAmount - 100) / (1 / (365 / $periodDays)));

            $periodResults[] = [
                'id' => $period->id,
                'from' => $from,
                'to' => $to,
                'periodDays' => $periodDays,
                'stockPriceFrom' => $stockPriceFrom,
                'stockPriceTo' => $stockPriceTo,
                'initialAmount' => $this->stockService->getInitalAmount(),
                'changePerYear' => $changePerYear,
                'holdAmount' => $holdAmount,
                'finalAmount' => $this->stockService->getFinalAmount(),
            ];
        }

        $averageHoldAmount = Helpers::averageArrayKey($periodResults,  'holdAmount');
        $averageFinalAmount = Helpers::averageArrayKey($periodResults,  'finalAmount');

        return view('stock.all', [
            'symbol' => $symbol,
            'periodResults' => $periodResults,
            'averageFinalAmount' => $averageFinalAmount,
            'averageHoldAmount' => $averageHoldAmount,
        ]);
    }

}
