<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    private $count = 5;

    public function generate()
    {
        Period::truncate();

        for ($i = 0; $i < 40; $i++) {
            for ($j = 0; $j < $this->count; $j++) {
                $min = Carbon::now()->startOfYear()->addYear(-$i);

                $maxDays = $min->diffInDays();
                $periodLength = rand(1,$maxDays / 2);
                $from = $min->copy()->addDay(rand(1,$maxDays));
                $to = $from->copy()->addDay($periodLength);

                if ($to->isFuture()) {
                    $to = Carbon::now()->startOfDay();
                }

                $period = Period::create([
                   'from' => $from,
                   'to' => $to,
                   'min' => $min,
                ]);

                //dump([$from->toDateTimeString(), $to->toDateTimeString()]);
            }
        }

        return redirect()->back()->withErrors(['msg' => 'Generated '. $this->count . ' periods']);;
    }
}
