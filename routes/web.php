<?php

use App\Http\Controllers\Apps\PermissionManagementController;
use App\Http\Controllers\Apps\RoleManagementController;
use App\Http\Controllers\Apps\UserManagementController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Apps\TeamManagementController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberFollowUpController;
use App\Http\Controllers\TransactionAssignController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::name('user-management.')->group(function () {
        Route::resource('/user-management/users', UserManagementController::class);
        Route::prefix('/user-management/users')->name('users.')->group(function () {
            Route::get('{user}/members-data', [UserManagementController::class, 'membersData'])->name('members.data');
            Route::get('{user}/logs-data', [UserManagementController::class, 'logsData'])->name('logs.data');
            Route::post('{user}/restore', [UserManagementController::class, 'restore'])->name('restore');
            //            Route::get('{user}/transactions-data', [UserManagementController::class, 'transactionsData'])->name('transactions.data');
            // Route::get('{user}/logs-data', [UserManagementController::class, 'logsData'])->name('logs.data');
        });
        Route::resource('/user-management/roles', RoleManagementController::class);
        Route::resource('/user-management/permissions', PermissionManagementController::class);
        Route::resource('/user-management/teams', TeamManagementController::class);
        Route::get('/user-management/teams/{team}/available-marketings', [TeamManagementController::class, 'getAvailableMarketings']);
    });

    Route::name('settings.')->group(function () {
        Route::get('/config/ips', [ConfigController::class, 'index'])->name('config.ips.index');
        Route::post('/config/ips', [ConfigController::class, 'update'])->name('config.ips.update');
    });

    Route::resource("transactions", TransactionController::class);
    Route::post("transactions/import", [TransactionController::class, 'import'])->name('transactions.import');
    Route::post("transactions/{id}/follow-up", [TransactionController::class, 'followUpMember'])->name('transactions.follow-up');
    Route::get('transactions/export/{type}', [\App\Http\Controllers\TransactionController::class, 'export'])->name('transactions.export');
    Route::post('transactions/{id}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

    Route::resource("transactions-assign", TransactionAssignController::class);


    Route::resource('members/followup', MemberFollowUpController::class);
    Route::resource("members", MemberController::class);
    Route::post('/members/import', [MemberController::class, 'import'])->name('members.import');
    Route::post('/members/{id}/restore', [MemberController::class, 'restore'])->name('members.restore');
    Route::get('/members/{member}/transactions/data', [MemberController::class, 'transactionsData'])->name('members.transactions.data');
    Route::get('/members/{member}/followups/data', [MemberController::class, 'followupsData'])->name('members.followups.data');


});

Route::get('/error', function () {
    abort(500);
});

Route::get('/auth/redirect/{provider}', [SocialiteController::class, 'redirect']);

require __DIR__ . '/auth.php';
