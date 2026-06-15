<?php

namespace App\Providers;

use App\Models\Profile;
use App\Observers\ProfileObserver;
use App\Models\BukuInduk;
use App\Observers\BukuIndukObserver;
use App\Models\MuridTrial;
use App\Observers\MuridTrialObserver;
use App\Models\PenerimaanProduk;
use App\Observers\PenerimaanProdukObserver;
use Carbon\Carbon;


// TAMBAHAN BARIS INI (hanya 2 baris ini yang ditambah)
use App\Models\Student;                         // Tambah ini
use App\Observers\StudentHumasObserver;         // Tambah ini
use App\Observers\ProfileImbalanObserver;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

// Google Sheets
use Google\Client as GoogleClient;
use Google\Service\Sheets as GoogleSheets;
use Google\Service\Drive as GoogleDrive;
use Revolution\Google\Sheets\Facades\Sheets;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // ===============================
    // SET LOCALE TANGGAL INDONESIA
    // ===============================
    setlocale(LC_TIME, 'id_ID.UTF-8');
    Carbon::setLocale('id');
        
        // Pastikan Laravel tahu domain & skema yang benar (termasuk saat pakai ngrok)
        if (!$this->app->runningInConsole()) {
            $req = request();

            $host = $req->getHost() ?? '';
            $isNgrokHost = str_contains($host, 'ngrok');
            $isForwardedHttps = ($req->server('HTTP_X_FORWARDED_PROTO') ?? '') === 'https';
            $isAlreadyHttps = $req->isSecure();

            if ($isNgrokHost || $isForwardedHttps || $isAlreadyHttps) {
                // Paksa HTTPS dan root URL agar sesuai domain yang aktif (ngrok, hosting, dll)
                $root = $req->getSchemeAndHttpHost();
                URL::forceScheme('https');
                URL::forceRootUrl($root);

                Log::info('AppServiceProvider: HTTPS mode enabled', [
                    'root' => $root,
                    'host' => $host,
                    'scheme' => $req->getScheme(),
                ]);
            }
        }

        // Daftarkan observer Profile
        Profile::observe(ProfileObserver::class);

        // TAMBAHKAN BARIS INI — INI YANG BIKIN SEMUA OTOMATIS!
        MuridTrial::observe(MuridTrialObserver::class);

        // BARIS BARU INI YANG KAMU INGINKAN (otomatis bikin Humas dari Student)
        Student::observe(StudentHumasObserver::class);

        PenerimaanProduk::observe(PenerimaanProdukObserver::class);

        // Gunakan Bootstrap 5 untuk pagination
        Paginator::useBootstrapFive();

        // Tambahkan ini bersama observer lain yang sudah ada
Profile::observe(ProfileImbalanObserver::class);

BukuInduk::observe(BukuIndukObserver::class);

        // Google Sheets Integration (optional)
        $keyFile = env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION');
        if ($keyFile && file_exists($keyFile)) {
            try {
                $client = new GoogleClient();
                $client->setApplicationName(env('GOOGLE_APPLICATION_NAME', 'Laravel Google Sheet'));
                $client->setAuthConfig($keyFile);
                $client->setScopes([
                    GoogleSheets::SPREADSHEETS,
                    GoogleDrive::DRIVE_READONLY,
                ]);

                Sheets::setService(new GoogleSheets($client));
                Log::info('Google Sheets connected successfully');
            } catch (\Throwable $e) {
                Log::error('Google Sheets binding failed', [
                    'error' => $e->getMessage(),
                    'path' => $keyFile,
                ]);
            }
        } else {
            Log::warning('Google service account JSON not found', ['path' => $keyFile]);
        }
    }
    
}