<?php

namespace App\Services;

use App\Models\Companies;
use App\Models\TimeSeries;
use App\Strategy\StrategyInterface;

class ImportService
{
    public function __construct()
    {
    }

    public function importStockSeries(string $symbol, string $output)
    {
        //https://www.alphavantage.co/documentation/

        $company = Companies::query()
            ->where('symbol', $symbol)
            ->first();

        if (!$company) {
            return view('error')->with('error', 'Company not found');
        }

        $symbol = mb_strtolower($symbol);

        //$outputsize = 'compact';
        //$outputsize = 'full';
        $outputsize = $output;
        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=' . $symbol . '&apikey=' . env('ALPHAVANTAGE_API') . '&outputsize=' . $outputsize);

        $data = json_decode($json, true);

        $timeframes = $data['Time Series (Daily)'];

        if (!$timeframes) {
            return view('error')->with('error', 'Symbol not found in alphavantage.co API');
        }

        $count = 0;

        foreach ($timeframes as $date => $series) {
            $open = $series['1. open'];
            $high = $series['2. high'];
            $low = $series['3. low'];
            $close = $series['4. close'];
            $adjusted_close = $series['5. adjusted close'];
            $volume = $series['6. volume'];
            $split_coefficient = $series['8. split coefficient'];

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
                    'split_coefficient' => $split_coefficient,
                ]);

                if ($stock->wasRecentlyCreated) $count++;
            }
        }

        return $count;
    }
}
