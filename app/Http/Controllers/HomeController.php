<?php

namespace App\Http\Controllers;

use App\Models\Companies;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $companies = Companies::query()
            ->whereNotNull('fetch_date')
            //->where('id', '<=', 3)
            ->get();

        return view('home', [
            'companies' => $companies
        ]);
    }
}
