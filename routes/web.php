<?php

use App\Helpers\Utils;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\ResponseController;
use App\Http\Controllers\RegistrantController;
use App\Models\Admin\Country;
use App\Models\Admin\Event;
use App\Models\Admin\EventFees;
use App\Models\RegistrantStage;
use Illuminate\Support\Facades\Route;

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

Route::get('/registrant_login', function () {
    return view('login');
})->name('registrant_login');

Route::controller(RegistrantController::class)->group(function () {
    Route::get('/registration', 'register')->name('registrant.registration');
    Route::post('/registration', 'store')->name('registrant.store');
    Route::post('/registration_confirm', 'individualRegistrationConfirm')->name('registrant.confirm');
    Route::post('/registration_update', 'individualRegistrationUpdate')->name('registrant.update');
    Route::post('/registration_batch', 'batchRegistrationStage')->name('registrant.batch');
    Route::get('/registrant_download', 'exportRegistrationStage')->name('registrant_download');

    Route::post('/registrant_login', 'registrationLogin')->name('registrant_login');
    Route::get('/registrant_page', 'individualLogin')->name('registrant_page');
    Route::get('/registrant_page_batch', 'batchLogin')->name('registrant_page_batch');

    Route::post('/make_payment', 'registrantMakePayment')->name('make_payment');

    Route::get('/registrant/batch/confirmation/{id}', 'batchRegistrationConfirm');
    Route::post('/batch_confirm', 'batchRegistrationConfirmation')->name('batch.confirm');
    Route::post('/batch_payment', 'batchPayment')->name('batch_payment');


    Route::post('/registrant_logout', 'registrantLogout')->name('registrant_logout');
});

Route::get('/download.file/{file_path}', [DownloadController::class, 'downloadFile'])->name('registrant.download.file');

Route::controller(ResponseController::class)->group(function () {
    Route::get('/forms/{slug}', 'showForm')->name('forms.public');
    Route::post('/forms/{slug}', 'storeResponse')->name('forms.submit');
});

Route::get('/remove_registrant_from_batch/{id}', function ($id){
    return RegistrantController::destroy($id);
});

