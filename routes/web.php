<?php

use App\Http\Controllers\admin\AccountsCategoryController;
use App\Http\Controllers\admin\AccountsController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminTicketController;
use App\Http\Controllers\admin\AuthenticatedSessionController;
use App\Http\Controllers\admin\BonusSettingController;
use App\Http\Controllers\admin\DepositController;
use App\Http\Controllers\admin\DepositMethodController;
use App\Http\Controllers\admin\GeneralSettingsController;
use App\Http\Controllers\admin\KycController;
use App\Http\Controllers\admin\PlansController;
use App\Http\Controllers\admin\PoolDistributionController;
use App\Http\Controllers\admin\TransactionsController;
use App\Http\Controllers\admin\TransferSettingsController;
use App\Http\Controllers\admin\UsersController;
use App\Http\Controllers\admin\WithdrawController;
use App\Http\Controllers\admin\WithdrawSettingsController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('user.dashboard');
});

Route::get('admin/dashboard',[AdminDashboardController::class,'index'])->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::prefix('admin')->middleware('auth')->group(function () {

    //all user
    Route::get('users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::post('users/update', [UsersController::class, 'update'])->name('admin.users.update');
    Route::get('users/{id}', [UsersController::class, 'show'])->name('admin.users.show');
    Route::post('users/wallet-update', [UsersController::class, 'updateWallet'])->name('admin.users.wallet.update');


    // Plans
    Route::resource('all-plan', PlansController::class)->names([
        'index' => 'admin.plans.index',
        'create' => 'admin.plans.create',
        'store' => 'admin.plans.store',
        'show' => 'admin.plans.show',
        'edit' => 'admin.plans.edit',
        'update' => 'admin.plans.update',
        'destroy' => 'admin.plans.destroy'
    ]);

    // all investor
    Route::get('investors', [PlansController::class, 'allInvestment'])->name('admin.investment');

    // withdraw
    Route::resource('withdraw', WithdrawController::class)->names([
        'index' => 'admin.withdraw.index',
        'create' => 'admin.withdraw.create',
        'store' => 'admin.withdraw.store',
        'show' => 'admin.withdraw.show',
        'edit' => 'admin.withdraw.edit',
        'update' => 'admin.withdraw.update',
        'destroy' => 'admin.withdraw.destroy'
    ]);

    Route::resource('transactions', TransactionsController::class)->names([
        'index' => 'admin.transactions.index',
        'create' => 'admin.transactions.create',
        'store' => 'admin.transactions.store',
        'show' => 'admin.transactions.show',
        'edit' => 'admin.transactions.edit',
        'update' => 'admin.transactions.update',
        'destroy' => 'admin.transactions.destroy'
    ]);
    Route::resource('kyc', KycController::class)->names([
        'index' => 'admin.kyc.index',
        'create' => 'admin.kyc.create',
        'store' => 'admin.kyc.store',
        'show' => 'admin.kyc.show',
        'edit' => 'admin.kyc.edit',
        'update' => 'admin.kyc.update',
        'destroy' => 'admin.kyc.destroy'
    ]);
    Route::get('cron', [CronController::class, 'view'])->name('cron');


    Route::get('withdraw-settings', [WithdrawSettingsController::class, 'index'])->name('admin.withdraw.settings');
    Route::post('withdraw-settings', [WithdrawSettingsController::class, 'update'])->name('admin.withdraw.settings.update');


    Route::get('transfer-settings',[TransferSettingsController::class,'index'])->name('admin.transfer.settings');
    Route::post('transfer-settings',[TransferSettingsController::class,'update'])->name('admin.transfer.settings.update');


    //deposit
    Route::resource('deposit', DepositController::class)->names([
        'index' => 'admin.deposit.index',
        'create' => 'admin.deposit.create',
        'store' => 'admin.deposit.store',
        'show' => 'admin.deposit.show',
        'edit' => 'admin.deposit.edit',
        'update' => 'admin.deposit.update',
        'destroy' => 'admin.deposit.destroy'
    ]);

    Route::resource('deposit-methods', DepositMethodController::class)->names([
        'index' => 'admin.deposit_methods.index',
        'create' => 'admin.deposit_methods.create',
        'store' => 'admin.deposit_methods.store',
        'show' => 'admin.deposit_methods.show',
        'edit' => 'admin.deposit_methods.edit',
        'update' => 'admin.deposit_methods.update',
        'destroy' => 'admin.deposit_methods.destroy'
    ]);

    // Accounts Category
    Route::resource('accounts-category', AccountsCategoryController::class)->names([
        'index' => 'admin.accounts-category.index',
        'create' => 'admin.accounts-category.create',
        'store' => 'admin.accounts-category.store',
        'show' => 'admin.accounts-category.show',
        'edit' => 'admin.accounts-category.edit',
        'update' => 'admin.accounts-category.update',
        'destroy' => 'admin.accounts-category.destroy'
    ]);

    // Accounts
    Route::resource('accounts', AccountsController::class)->names([
        'index' => 'admin.accounts.index',
        'create' => 'admin.accounts.create',
        'store' => 'admin.accounts.store',
        'show' => 'admin.accounts.show',
        'edit' => 'admin.accounts.edit',
        'update' => 'admin.accounts.update',
        'destroy' => 'admin.accounts.destroy'
    ]);
    // export
    Route::get('accounts/export/{type}', [AccountsController::class, 'export'])
    ->name('admin.accounts.export');

    // Invoices
    Route::get('invoices', [PlansController::class, 'invoices'])->name('admin.invoices.index');

    // support ticket
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
    Route::post('tickets/{id}/reply', [AdminTicketController::class, 'reply'])->name('admin.tickets.reply');
    Route::post('tickets/{id}/close', [AdminTicketController::class, 'close'])->name('admin.tickets.close');


    // General Settings
    Route::get('general-settings', [GeneralSettingsController::class, 'index'])->name('admin.general.settings');
    Route::post('general-settings', [GeneralSettingsController::class, 'update'])->name('admin.general.settings.update');

    Route::get('settings', [GeneralSettingsController::class, 'settings'])->name('admin.settings');
    Route::post('settings', [GeneralSettingsController::class, 'updateSettings'])->name('admin.settings.update');

    //Activation Settings
    Route::get('activation-settings', [BonusSettingController::class, 'edit'])->name('admin.bonus-settings.edit');
    Route::post('activation-settings', [BonusSettingController::class, 'update'])->name('admin.bonus-settings.update');

    //profile settings

    Route::get('profile', [AuthenticatedSessionController::class, 'profileEdit'])->name('admin.profile.edit');
    Route::post('profile', [AuthenticatedSessionController::class, 'profileUpdate'])->name('admin.profile.update');

    //pool Distribution settings
    Route::get('distribute', [PoolDistributionController::class, 'index'])->name('admin.distribute.index');
    Route::post('distribute-rank', [PoolDistributionController::class, 'distributeRankPool'])->name('admin.distribute-rank');
    Route::post('distribute-club',[PoolDistributionController::class, 'distributeClubPool'])->name('admin.distribute-club');
    Route::post('distribute-shareholder',[PoolDistributionController::class, 'distributeShareholderPool'])->name('admin.distribute-shareholder');
    Route::post('distribute-director',[PoolDistributionController::class, 'distributeDirectorPool'])->name('admin.distribute-director');

});

Route::get('check',function(){
    return \Carbon\Carbon::now();
});

require __DIR__.'/auth.php';
require __DIR__.'/auths.php';
require __DIR__.'/user.php';
