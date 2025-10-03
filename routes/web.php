<?php

use Illuminate\Support\Facades\Mail;
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



Route::get('/test-mail', function () {
    Mail::raw('This is a test email from Laravel using SMTP.', function ($message) {
        $message->to('emeka.daniels@gmail.com')
                ->subject('Test Email from Laravel');
    });
    return 'Test email sent! Check your inbox.';
});


Route::get('/test-notification', function () {
    $staff = \App\Models\Staff::where('email', 'emeka.daniels@gmail.com')->first();
    $file = \App\Models\FileUpload::where('staff_id', 'TT0001')->first();

    if ($staff && $file) {
        $staff->notify(new \App\Notifications\PayslipNotification($file, 8, 2025));
        return 'Notification sent!';
    }

    return 'No staff or file found';
});
