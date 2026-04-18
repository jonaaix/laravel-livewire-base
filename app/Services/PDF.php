<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\PdfBuilder;

class PDF
{
    /**
     * Get printer instance
     */
    public static function getPrinter(): PdfBuilder
    {
        return \Spatie\LaravelPdf\Support\pdf()->withBrowsershot(function (Browsershot $browsershot) {
            $browsershot
                ->setOption('executablePath', '/usr/bin/chromium-browser')
                ->setOption('args', ['--no-sandbox', '--disable-gpu'])
                ->timeout(60)
                ->protocolTimeout(60);
        });
    }
}
