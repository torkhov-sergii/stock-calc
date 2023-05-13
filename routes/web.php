<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\HomeController;


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

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::group([
    'prefix' => 'stock',
], function () {
    Route::get('all/{symbol}', [StockController::class, 'all']);
    Route::get('show/{symbol}', [StockController::class, 'show']);
});

Route::group([
    'prefix' => 'import',
], function () {
    Route::get('import_companies_from_xls', [StockController::class, 'importCompaniesFromXls']);
    Route::get('import_by_symbol', [ImportController::class, 'importBySymbol']);
    Route::get('import_from_companies', [ImportController::class, 'importCompanies']);
    Route::get('update_from_companies', [ImportController::class, 'updateCompanies']);
});

Route::group([
    'prefix' => 'period',
], function () {
    Route::get('generate', [PeriodController::class, 'generate']);
});
