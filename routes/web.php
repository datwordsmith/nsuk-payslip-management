<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\t\DashboardController;

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
    return view('auth.login');
})->name('auth.login');

Route::get('/superadmin', function () {
    return view('auth.superadmin');
});

Auth::routes();

Route::get('/register', function () {
    return redirect()->route('auth.login');
});

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('t')->middleware(['auth', 'isAdmin'])->group(function (){
    Route::get('/payslips', App\Http\Livewire\Admin\FileUpload\Index::class)->name('payslips');
    Route::get('/staff', App\Http\Livewire\Admin\Staff\Index::class)->name('staff');
    Route::get('/dispatch_payslips', App\Http\Livewire\Admin\PayslipDispatch\Index::class)->name('dispatch');
});
