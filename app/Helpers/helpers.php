<?php

declare(strict_types=1);

use Carbon\Carbon;

if (! function_exists('format_date_modern')) {
    /**
     * Tarih bilgisini modern formatta gösterir.
     * Yakın tarihler için göreceli (Bugün, Dün), diğerleri için Türkçe format.
     *
     * @param  \DateTimeInterface|string|null  $date
     * @return string
     */
    function format_date_modern($date): string
    {
        if ($date === null) {
            return '-';
        }

        $carbon = $date instanceof \DateTimeInterface
            ? Carbon::parse($date)
            : Carbon::parse((string) $date);

        $carbon->locale('tr');
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        return match (true) {
            $carbon->toDateString() === $today => 'Bugün ' . $carbon->format('H:i'),
            $carbon->toDateString() === $yesterday => 'Dün ' . $carbon->format('H:i'),
            $carbon->isCurrentYear() => $carbon->translatedFormat('j F') . ' ' . $carbon->format('H:i'),
            default => $carbon->translatedFormat('d.m.Y') . ' ' . $carbon->format('H:i'),
        };
    }
}
