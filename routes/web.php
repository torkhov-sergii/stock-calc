<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PeriodController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('import_companies', [StockController::class, 'import_companies']);

Route::group([
    'prefix' => 'stock',
], function () {
    Route::get('all/{symbol}', [StockController::class, 'all']);
    Route::get('show/{symbol}', [StockController::class, 'show']);
    Route::get('import/{symbol?}', [StockController::class, 'import']);
});

Route::group([
    'prefix' => 'period',
], function () {
    Route::get('generate', [PeriodController::class, 'generate']);
});
