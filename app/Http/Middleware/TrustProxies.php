<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TrustProxies extends Middleware
{
    /**
     * Percayakan semua proxy (termasuk ngrok).
     *
     * Untuk production bisa diganti pakai IP proxy kamu.
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * Header yang dipakai untuk deteksi HTTPS/host dari proxy.
     *
     * Kita build value secara defensif supaya tidak tergantung pada
     * satu konstanta yang mungkin tidak ada di semua versi Symfony.
     *
     * @var int
     */
    protected $headers;

    public function __construct()
    {
        // Jika Symfony menyediakan HEADER_X_FORWARDED_ALL gunakan itu
        if (defined('Symfony\\Component\\HttpFoundation\\Request::HEADER_X_FORWARDED_ALL')) {
            $this->headers = SymfonyRequest::HEADER_X_FORWARDED_ALL;
            return;
        }

        // Jika tidak, gabungkan konstanta yang tersedia
        $flags = 0;
        $candidates = [
            'HEADER_X_FORWARDED_FOR',
            'HEADER_X_FORWARDED_HOST',
            'HEADER_X_FORWARDED_PROTO',
            'HEADER_X_FORWARDED_PORT',
            // alternatif: HEADER_FORWARDED (RFC 7239)
            'HEADER_FORWARDED',
        ];

        foreach ($candidates as $constName) {
            $full = "Symfony\\Component\\HttpFoundation\\Request::$constName";
            if (defined($full)) {
                $flags |= constant($full);
            }
        }

        // Jika tetap 0 (sangat tidak mungkin), fallback ke HEADER_X_FORWARDED_FOR
        if ($flags === 0 && defined('Symfony\\Component\\HttpFoundation\\Request::HEADER_X_FORWARDED_FOR')) {
            $flags = SymfonyRequest::HEADER_X_FORWARDED_FOR;
        }

        $this->headers = $flags ?: 0;
    }
}
