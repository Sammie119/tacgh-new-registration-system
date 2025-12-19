<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::group(['middleware' => ['role:'.RolesEnum::SYSTEMADMIN->value]], function () {
            Route::controller(RegisteredUserController::class)->group(function () {
                Route::get('users', 'index')->name('users');
                Route::post('register', 'store')->name('register');
                Route::put('register', 'update')->name('register');
                Route::post('/user_roles', 'assignRolesToUser')->name('user_roles');
            });
        });

        Route::group(['middleware' => ['role:'.RolesEnum::SYSTEMDEVELOPER->value]], function () {
            Route::controller(PermissionController::class)->group(function () {
                Route::get('/permissions', 'index')->name('permissions');
                Route::post('/permission', 'store')->name('permission');
                Route::put('/permission', 'update')->name('permission');
//        Route::post('delete_permission', 'destroy');
            });

            Route::controller(RoleController::class)->group(function () {
                Route::get('/roles', 'index')->name('roles');
                Route::post('/role', 'store')->name('role');
                Route::put('/role', 'update')->name('role');
//        Route::post('delete_role', 'destroy');
                Route::put('/assign_permissions', 'assignPermission')->name('assign_permissions');
            });

            Route::controller(FinanceController::class)->group(function () {
                Route::post('/store_online_payment_correction', 'onlinePaymentCorrectionStore')->name('store_online_payment_correction');
                Route::post('/check_payment_confirmation', 'checkPaymentConfirmation')->name('check_payment_confirmation');
            });
        });

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('profile.edit');
            Route::post('/profile', 'update')->name('profile.update');
            Route::post('/password_update', 'passwordUpdate')->name('password.update');
        });

    });

    // Form
    Route::get('/execute_form/{service}/{type}/{id?}', [FormController::class, 'executeForm']);
});
