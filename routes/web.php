<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\ProposalController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/profile', 'ProfileController@index')->name('profile');
Route::put('/profile', 'ProfileController@update')->name('profile.update');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::resource('clients', ClientController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('partners', PartnerController::class);

Route::resource('vehicles', VehicleController::class);
Route::get('vehicles/{vehicle}/profit', 'VehicleController@profit')->name('vehicles.profit');

Route::resource('expenses', ExpenseController::class);

Route::resource('sales', SaleController::class);

Route::get('brands', [VehicleBrandController::class, 'index'])->name('brands.index');

Route::resource('proposals', ProposalController::class);

Route::get('proposals/{id}/download-pdf', [ProposalController::class, 'downloadPdf'])->name('proposals.downloadPdf');
Route::post('/proposals/{proposal}/duplicate', [ProposalController::class, 'duplicate'])->name('proposals.duplicate');
