<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\Admin\AccommodationBlockController;
use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AccommodationEpisodeController;
use App\Http\Controllers\Admin\AccommodationRoomController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AssignedRoomEpisodeController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\DropdownController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventFeesController;
use App\Http\Controllers\Admin\EventVenueController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\RegistrantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/dashboard', 'index')->middleware(['verified'])->name('dashboard');
        });

        Route::group(['middleware' => ['role:'.RolesEnum::SYSTEMADMIN->value]], function () {
            Route::controller(DropdownController::class)->group(function () {
                Route::get('/categories', 'indexCategory')->name('categories');
                Route::post('/category', 'storeCategory')->name('category');
                Route::put('/category', 'updateCategory')->name('category');

                Route::get('/dropdowns', 'index')->name('dropdowns');
                Route::post('/dropdown', 'store')->name('dropdown');
            });

            Route::controller(EventController::class)->group(function () {
                Route::get('/events', 'index')->name('events');
                Route::post('/event', 'store')->name('event');
                Route::put('/event', 'update')->name('event');
            });

            Route::controller(EventVenueController::class)->group(function () {
                Route::get('/venues', 'index')->name('venues');
                Route::post('/venue', 'store')->name('venue');
                Route::put('/venue', 'update')->name('venue');
            });

            Route::controller(AccommodationEpisodeController::class)->group(function () {
                Route::get('/accommodation_episodes', 'index')->name('accommodation_episodes');
                Route::post('/accommodation_episode', 'store')->name('accommodation_episode');
                Route::put('/accommodation_episode', 'update')->name('accommodation_episode');
            });

            Route::controller(AssignedRoomEpisodeController::class)->group(function () {
                Route::post('/add_roommate', 'addRoomMate')->name('add_roommate');
                Route::post('/transfer_roommate', 'transferRoomMate')->name('transfer_roommate');
            });

            Route::controller(EventFeesController::class)->group(function () {
                Route::post('/fees', 'store')->name('fees');
            });

            Route::controller(DownloadController::class)->group(function () {
                Route::get('/downloads', 'index')->name('downloads');
                Route::post('/download', 'store')->name('download');
                Route::put('/download', 'update')->name('download');
            });

            Route::controller(FormController::class)->group(function () {
                Route::get('/forms', 'index')->name('forms');
                Route::post('/forms', 'store')->name('forms.store');
                Route::put('/forms', 'update')->name('forms.store');
                Route::get('/forms/{form}/report', 'report')->name('forms.report');
                Route::get('/forms/{form}/export', 'export')->name('forms.export');
            });
        });

        Route::group(['middleware' => ['role:'.RolesEnum::SYSTEMADMIN->value.'|'.RolesEnum::ROOMALLOCATOR->value]], function () {
            Route::controller(RegistrantController::class)->group(function () {
                Route::get('/all_registrant', 'index')->name('all_registrant');
            });

            Route::controller(AccommodationController::class)->group(function () {
                Route::get('/accommodations/{id}', 'index')->name('accommodations');
                Route::post('/accommodation', 'store')->name('accommodation');
                Route::put('/accommodation_single', 'update')->name('accommodation_single');

                Route::get('allocate_room','allocateRoomsSingle')->name('allocate_room');
            });

            Route::controller(AccommodationBlockController::class)->group(function () {
                Route::post('/blocks', 'store')->name('blocks');
            });

            Route::controller(AccommodationRoomController::class)->group(function () {
                Route::post('/rooms', 'store')->name('rooms');
                Route::get('/room/{id}', 'show')->name('room');
                Route::post('/room/{id}', 'update')->name('room');
            });

            Route::get('/check_in/{registrant_id}', function ($registrant_id) {
                $check = \App\Models\Registrant::where('stage_id', $registrant_id)->first();
                if(is_null($check->check_in)){
                    $check->update([
                        'check_in' => now()->format('Y-m-d H:i:s'),
                        'check_in_by' => get_logged_in_user_id(),
                    ]);
                    return redirect()->back()->with('success', 'Registrant checked in successfully');
                } else {
                    $check->update([
                        'check_in' => null,
                        'check_in_by' => get_logged_in_user_id(),
                    ]);
                    return redirect()->back()->with('success', 'Registrant checked in cleared successfully');
                }
            })->name('check_in');

            Route::get('/check_out/{registrant_id}', function ($registrant_id) {
                $check = \App\Models\Registrant::where('stage_id', $registrant_id)->first();
                if(is_null($check->check_out)){
                    $check->update(['check_out' => now()->format('Y-m-d H:i:s')]);
                    return redirect()->back()->with('success', 'Registrant checked out successfully');
                } else {
                    $check->update(['check_out' => null]);
                    return redirect()->back()->with('success', 'Registrant checked out cleared successfully');
                }
            })->name('check_out');
        });

        Route::group(['middleware' => ['role:'.RolesEnum::SYSTEMADMIN->value.'|'.RolesEnum::FINANCE->value]], function () {
            Route::controller(FinanceController::class)->group(function () {
                Route::get('/payments', 'index')->name('payments');
                Route::post('/financial_clearance', 'financialClearance')->name('financial_clearance');
                Route::get('/financial_entries', 'financialEntryIndex')->name('financial_entries');
                Route::post('/financial_entry', 'financialEntry')->name('financial_entry');
                Route::put('/financial_entry', 'financialEntryUpdate')->name('financial_entry');
                Route::get('/financial_report', 'financialReport')->name('financial_report');
                Route::get('/print_financial_report/{report}', 'printFinancialReport')->name('print_financial_report');

//                Route::post('/registrant_payment', 'registrantPaymentStore')->name('registrant_payment_store');
            });
        });

    });
});
