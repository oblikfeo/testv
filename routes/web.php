<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\LandingTrafficController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupportController;
use App\Http\Middleware\AdminAuth;
use App\Services\IndexNowService;
use App\Services\LandingTrafficService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request, LandingTrafficService $traffic) {
    $visitorCount = $traffic->recordHomeVisit($request);

    return view('welcome', compact('visitorCount'));
})->name('home');

Route::get('/landing/traffic-stats', [LandingTrafficController::class, 'stats'])
    ->middleware('throttle:120,1')
    ->name('landing.traffic-stats');

Route::post('/landing/traffic-modal', [LandingTrafficController::class, 'recordModalOpen'])
    ->middleware('throttle:60,1')
    ->name('landing.traffic-modal');

Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/offer', 'offer')->name('offer');
Route::view('/personal-data', 'personal-data')->name('personal-data');

// ---- SEO: robots.txt, sitemap.xml, IndexNow ключ-файл ----

Route::get('/robots.txt', function () {
    $sitemap = route('sitemap');
    $body = <<<TXT
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /cabinet
Disallow: /cabinet/
Disallow: /keys
Disallow: /profile
Disallow: /dashboard
Disallow: /sub/
Disallow: /payment/

User-agent: Yandex
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /cabinet
Disallow: /cabinet/
Disallow: /keys
Disallow: /profile
Disallow: /dashboard
Disallow: /sub/
Disallow: /payment/
Clean-param: utm_source&utm_medium&utm_campaign&utm_term&utm_content&fbclid&gclid /

Sitemap: {$sitemap}
TXT;

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'),          'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => route('privacy'),       'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => route('offer'),         'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => route('personal-data'), 'priority' => '0.3', 'changefreq' => 'yearly'],
    ];

    $today = now()->toDateString();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $u) {
        $xml .= "    <url>\n";
        $xml .= '        <loc>'.htmlspecialchars($u['loc'], ENT_XML1)."</loc>\n";
        $xml .= '        <lastmod>'.$today."</lastmod>\n";
        $xml .= '        <changefreq>'.$u['changefreq']."</changefreq>\n";
        $xml .= '        <priority>'.$u['priority']."</priority>\n";
        $xml .= "    </url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

// IndexNow подтверждение владения сайтом: файл /<key>.txt с тем же ключом внутри.
Route::get('/{key}.txt', function (string $key, IndexNowService $indexNow) {
    if (! hash_equals($indexNow->key(), $key)) {
        abort(404);
    }

    return response($key, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->where('key', '[A-Za-z0-9\-]{8,128}')->name('indexnow.key');

Route::get('/dashboard', function () {
    return redirect()->route('cabinet.subscription');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/cabinet', [CabinetController::class, 'subscription'])->name('cabinet.subscription');
    Route::redirect('/cabinet/devices', '/cabinet')->name('cabinet.devices');
    Route::get('/cabinet/trial', [CabinetController::class, 'trial'])->name('cabinet.trial');
    Route::get('/cabinet/profile', [CabinetController::class, 'profile'])->name('cabinet.profile');
    Route::get('/cabinet/security', [CabinetController::class, 'security'])->name('cabinet.security');
    Route::get('/cabinet/history', [CabinetController::class, 'history'])->name('cabinet.history');
    Route::post('/cabinet/trial-feedback', [CabinetController::class, 'submitTrialFeedback'])->name('cabinet.trial-feedback.submit');

    // Profile update must be available even before email verification,
    // otherwise users cannot change placeholder emails created via Telegram login.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/cabinet/trial', [CabinetController::class, 'createTrial'])->name('cabinet.trial.create');

    Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
    Route::get('/payment/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
});

// Subscription endpoint (public, no auth required)
Route::get('/sub/{subId}', [SubscriptionController::class, 'show'])->name('subscription.show');

// YooKassa webhook (public)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::middleware(['auth'])->prefix('cabinet/support')->name('cabinet.support.')->group(function () {
    Route::get('/', [SupportController::class, 'index'])->name('index');
    Route::post('/', [SupportController::class, 'store'])->name('store');
    Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/close', [SupportController::class, 'close'])->name('close');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(AdminAuth::class)->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::get('/trial-feedback', [AdminController::class, 'trialFeedback'])->name('admin.trial-feedback');

        Route::get('/support', [AdminSupportController::class, 'index'])->name('admin.support.index');
        Route::get('/support/{ticket}', [AdminSupportController::class, 'show'])->name('admin.support.show');
        Route::post('/support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('admin.support.reply');
        Route::post('/support/{ticket}/close', [AdminSupportController::class, 'close'])->name('admin.support.close');
        Route::post('/support/{ticket}/reopen', [AdminSupportController::class, 'reopen'])->name('admin.support.reopen');
    });
});

require __DIR__.'/auth.php';
