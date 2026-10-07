<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OperateController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\ScaleController;
use App\Http\Controllers\SolutionController;
use Illuminate\Support\Facades\Route;

// ── Public ───────────────────────────────────────────────────────────────
Route::get('/', [PublicController::class, 'landing'])->name('landing');
Route::get('/how-it-works', [PublicController::class, 'howItWorks'])->name('how-it-works');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'email'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->name('password.update');
});

// Demo environment (presentation helper; disable with TUMBUH_DEMO_MODE=false)
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/', [DemoController::class, 'index'])->name('index');
    Route::post('/login/{key}', [DemoController::class, 'login'])->name('login');
    Route::post('/reset/{key}', [DemoController::class, 'reset'])->name('reset');
    Route::middleware(['auth', 'onboarded'])->group(function () {
        Route::post('/action/{action}', [DemoController::class, 'action'])->name('action');
        Route::post('/tier', [DemoController::class, 'tier'])->name('tier');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/verify', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/verify', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:10,1');
    Route::post('/verify/resend', [OtpController::class, 'resend'])->name('otp.resend');

    Route::middleware('verified.otp')->group(function () {
        Route::get('/onboarding/{step?}', [OnboardingController::class, 'show'])->whereNumber('step')->name('onboarding.show');
        Route::post('/onboarding/{step}', [OnboardingController::class, 'store'])->whereNumber('step')->name('onboarding.store');
        Route::get('/goals', [OnboardingController::class, 'goals'])->name('goals.edit');
        Route::post('/goals', [OnboardingController::class, 'storeGoals'])->name('goals.store');
    });

    // ── Platform ─────────────────────────────────────────────────────────
    Route::middleware(['verified.otp', 'onboarded'])->group(function () {
        Route::get('/overview', [OverviewController::class, 'index'])->name('overview');
        Route::get('/app/how-it-works', [PublicController::class, 'howItWorks'])->name('app.how-it-works');

        // Growth Diagnostic
        Route::prefix('diagnostic')->name('diagnostic.')->group(function () {
            Route::get('/', [DiagnosticController::class, 'start'])->name('start');
            Route::post('/new', [DiagnosticController::class, 'newCycle'])->name('new');
            Route::get('/q/{index}', [DiagnosticController::class, 'question'])->whereNumber('index')->name('question');
            Route::post('/q/{index}', [DiagnosticController::class, 'answer'])->whereNumber('index')->name('answer');
            Route::post('/autosave', [DiagnosticController::class, 'autosave'])->name('autosave');
            Route::get('/review', [DiagnosticController::class, 'review'])->name('review');
            Route::post('/submit', [DiagnosticController::class, 'submit'])->name('submit');
            Route::get('/baseline', [DiagnosticController::class, 'baseline'])->name('baseline');
            Route::post('/baseline', [DiagnosticController::class, 'confirmBaseline'])->name('baseline.confirm');
        });

        // My Growth
        Route::get('/growth', [GrowthController::class, 'index'])->name('growth.index');
        Route::get('/growth/score', [GrowthController::class, 'score'])->name('growth.score');
        Route::get('/growth/ledger', [GrowthController::class, 'ledger'])->name('growth.ledger');
        Route::get('/growth/before-after', [GrowthController::class, 'beforeAfter'])->middleware('tier:before_after')->name('growth.before-after');
        Route::get('/growth/review', [GrowthController::class, 'review'])->name('growth.review');

        Route::get('/assistant', [AssistantController::class, 'show'])->name('assistant');
        Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');

        // Missions
        Route::get('/plan', [MissionController::class, 'plan'])->name('plan.show');
        Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
        Route::get('/missions/{mission}', [MissionController::class, 'show'])->name('missions.show');
        Route::post('/missions/{mission}/check-in', [MissionController::class, 'checkIn'])->name('missions.check-in');

        // Solutions & activation
        Route::get('/solutions', [SolutionController::class, 'index'])->name('solutions.index');
        Route::get('/solutions/activation', [SolutionController::class, 'activation'])->name('solutions.activation');
        Route::get('/solutions/{solution}', [SolutionController::class, 'show'])->name('solutions.show');
        Route::post('/solutions/{solution}/advance', [SolutionController::class, 'advance'])->name('solutions.advance');

        Route::middleware('tier:campaigns')->group(function () {
            Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
            Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
            Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
            Route::post('/campaigns/{campaign}/optimize', [CampaignController::class, 'optimize'])->name('campaigns.optimize');
        });

        // Operate: Business Operating Dashboard, Growth Finance, Growth Inventory
        Route::get('/operate', [OperateController::class, 'index'])->name('operate.index');

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::get('/income/create', [FinanceController::class, 'createIncome'])->name('income.create');
            Route::post('/income', [FinanceController::class, 'storeIncome'])->name('income.store');
            Route::get('/expense/create', [FinanceController::class, 'createExpense'])->name('expense.create');
            Route::post('/expense', [FinanceController::class, 'storeExpense'])->name('expense.store');
            Route::middleware('tier:finance.ocr')->group(function () {
                Route::get('/scan', [FinanceController::class, 'scan'])->name('scan');
                Route::post('/scan', [FinanceController::class, 'upload'])->name('scan.upload');
                Route::get('/scan/{receipt}', [FinanceController::class, 'reviewReceipt'])->name('scan.review');
                Route::post('/scan/{receipt}', [FinanceController::class, 'confirmReceipt'])->name('scan.confirm');
            });
            Route::get('/report', [FinanceController::class, 'report'])->middleware('tier:finance.pnl')->name('report');
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/products/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/products', [InventoryController::class, 'store'])->name('store');
            Route::get('/products/{product}', [InventoryController::class, 'show'])->name('show');
            Route::post('/products/{product}/stock-in', [InventoryController::class, 'stockIn'])->name('stock-in');
            Route::post('/products/{product}/sale', [InventoryController::class, 'sale'])->name('sale');
            Route::post('/products/{product}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        });

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        // Rewards
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::get('/rewards/tiers', [RewardController::class, 'tiers'])->name('rewards.tiers');
        Route::post('/rewards/{businessReward}/claim', [RewardController::class, 'claim'])->name('rewards.claim');

        // Scale
        Route::prefix('scale')->name('scale.')->group(function () {
            Route::get('/', [ScaleController::class, 'index'])->name('index');
            Route::get('/passport', [ScaleController::class, 'passport'])->name('passport');
            Route::get('/readiness', [ScaleController::class, 'readiness'])->middleware('tier:expansion.basic')->name('readiness');
            Route::post('/readiness', [ScaleController::class, 'assess'])->middleware('tier:expansion.basic')->name('readiness.assess');
            Route::get('/sitesense', [ScaleController::class, 'siteSense'])->middleware('tier:sitesense')->name('sitesense');
            Route::post('/sitesense', [ScaleController::class, 'runSiteSense'])->middleware('tier:sitesense')->name('sitesense.run');
            Route::get('/msight', [ScaleController::class, 'mSight'])->middleware('tier:msight')->name('msight');
            Route::post('/msight', [ScaleController::class, 'runMSight'])->middleware('tier:msight')->name('msight.run');
            Route::get('/dce', [ScaleController::class, 'dce'])->name('dce');
            Route::post('/dce', [ScaleController::class, 'advanceDce'])->middleware('tier:dce.apply')->name('dce.advance');
        });

        // Profile & governance
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal');
        Route::put('/profile/business', [ProfileController::class, 'updateBusiness'])->name('profile.business');
        Route::put('/profile/consents', [ProfileController::class, 'updateConsents'])->name('profile.consents');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/sessions/logout-others', [ProfileController::class, 'logoutOthers'])->name('profile.logout-others');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });
});
