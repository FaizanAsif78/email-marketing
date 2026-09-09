<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkMailController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExtendedUiController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\IconsController;
use App\Http\Controllers\LayoutsController;
use App\Http\Controllers\MailConfigurationController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', fn () => redirect()->route('dashboard.index'));


## Auth Routes
Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('auth.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware('permission:view dashboard');
});

Route::middleware(['auth:web', 'role:super-admin'])->prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:web', 'role:super-admin|admin'])->prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:web', 'role:admin'])->prefix('mail-configurations')->name('mail-configurations.')->group(function () {
    Route::get('/', [MailConfigurationController::class, 'index'])->name('index');
    Route::get('/create', [MailConfigurationController::class, 'create'])->name('create');
    Route::post('/', [MailConfigurationController::class, 'store'])->name('store');
    Route::get('/{mailConfiguration}/edit', [MailConfigurationController::class, 'edit'])->name('edit');
    Route::put('/{mailConfiguration}', [MailConfigurationController::class, 'update'])->name('update');
    Route::delete('/{mailConfiguration}', [MailConfigurationController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:web'])->prefix('email-templates')->name('email-templates.')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
    Route::get('/create', [EmailTemplateController::class, 'create'])->name('create');
    Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
    Route::get('/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
    Route::put('/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('update');
    Route::delete('/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:web'])->prefix('bulk-mail')->name('bulk-mail.')->group(function () {
    Route::get('/', [BulkMailController::class, 'index'])->name('index');
    Route::get('/create', [BulkMailController::class, 'create'])->name('create');
    Route::post('/parse', [BulkMailController::class, 'parseContacts'])->name('parse');
    Route::post('/test', [BulkMailController::class, 'sendTest'])->name('test');
    Route::post('/send', [BulkMailController::class, 'send'])->name('send');
});

// Route::prefix('layouts')->name('layouts.')->group(function () {
//     Route::get('/without-menu', [LayoutsController::class, 'withoutMenu'])->name('without-menu');
//     Route::get('/without-navbar', [LayoutsController::class, 'withoutNavbar'])->name('without-navbar');
//     Route::get('/container', [LayoutsController::class, 'container'])->name('container');
//     Route::get('/fluid', [LayoutsController::class, 'fluid'])->name('fluid');
//     Route::get('/blank', [LayoutsController::class, 'blank'])->name('blank');
// });

Route::middleware(['auth:web', 'permission:view account settings'])->prefix('dashboard/account-settings')->name('account-settings.')->group(function () {
    Route::get('/account', [AccountSettingsController::class, 'account'])->name('account');
    Route::put('/account', [AccountSettingsController::class, 'update'])->name('update');
});

Route::middleware(['auth:web', 'permission:view account settings'])->prefix('pages/account-settings')->name('account-settings.')->group(function () {
    Route::get('/notifications', [AccountSettingsController::class, 'notifications'])->name('notifications');
    Route::get('/connections', [AccountSettingsController::class, 'connections'])->name('connections');
});

// Route::prefix('pages/misc')->name('misc.')->group(function () {
//     Route::get('/error', [MiscController::class, 'error'])->name('error');
//     Route::get('/maintenance', [MiscController::class, 'maintenance'])->name('maintenance');
// });

// Route::get('/cards/basic', [CardController::class, 'index'])->name('cards.basic');

// Route::prefix('ui')->name('ui.')->group(function () {
//     Route::get('/accordion', [UiController::class, 'accordion'])->name('accordion');
//     Route::get('/alerts', [UiController::class, 'alerts'])->name('alerts');
//     Route::get('/badges', [UiController::class, 'badges'])->name('badges');
//     Route::get('/buttons', [UiController::class, 'buttons'])->name('buttons');
//     Route::get('/carousel', [UiController::class, 'carousel'])->name('carousel');
//     Route::get('/collapse', [UiController::class, 'collapse'])->name('collapse');
//     Route::get('/dropdowns', [UiController::class, 'dropdowns'])->name('dropdowns');
//     Route::get('/footer', [UiController::class, 'footer'])->name('footer');
//     Route::get('/list-groups', [UiController::class, 'listGroups'])->name('list-groups');
//     Route::get('/modals', [UiController::class, 'modals'])->name('modals');
//     Route::get('/navbar', [UiController::class, 'navbar'])->name('navbar');
//     Route::get('/offcanvas', [UiController::class, 'offcanvas'])->name('offcanvas');
//     Route::get('/pagination-breadcrumbs', [UiController::class, 'paginationBreadcrumbs'])->name('pagination-breadcrumbs');
//     Route::get('/progress', [UiController::class, 'progress'])->name('progress');
//     Route::get('/spinners', [UiController::class, 'spinners'])->name('spinners');
//     Route::get('/tabs-pills', [UiController::class, 'tabsPills'])->name('tabs-pills');
//     Route::get('/toasts', [UiController::class, 'toasts'])->name('toasts');
//     Route::get('/tooltips-popovers', [UiController::class, 'tooltipsPopovers'])->name('tooltips-popovers');
//     Route::get('/typography', [UiController::class, 'typography'])->name('typography');
// });

// Route::prefix('extended-ui')->name('extended-ui.')->group(function () {
//     Route::get('/perfect-scrollbar', [ExtendedUiController::class, 'perfectScrollbar'])->name('perfect-scrollbar');
//     Route::get('/text-divider', [ExtendedUiController::class, 'textDivider'])->name('text-divider');
// });

// Route::get('/icons/boxicons', [IconsController::class, 'index'])->name('icons.boxicons');

// Route::prefix('forms')->name('forms.')->group(function () {
//     Route::get('/basic-inputs', [FormController::class, 'basicInputs'])->name('basic-inputs');
//     Route::get('/input-groups', [FormController::class, 'inputGroups'])->name('input-groups');
//     Route::get('/layouts-vertical', [FormController::class, 'layoutsVertical'])->name('layouts-vertical');
//     Route::get('/layouts-horizontal', [FormController::class, 'layoutsHorizontal'])->name('layouts-horizontal');
// });

// Route::get('/tables/basic', [TableController::class, 'index'])->name('tables.basic');
