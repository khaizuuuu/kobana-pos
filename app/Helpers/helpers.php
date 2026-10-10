<?php

use Carbon\Carbon;

if (!function_exists('format_rupiah')) {
    /**
     * Format number to Indonesian Rupiah currency format.
     */
    function format_rupiah(int|float|null $amount): string
    {
        if ($amount === null) {
            return 'Rp 0';
        }
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (!function_exists('format_tanggal')) {
    /**
     * Format date in Indonesian locale.
     */
    function format_tanggal(mixed $date, string $format = 'd M Y'): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->locale('id')->translatedFormat($format);
    }
}

if (!function_exists('nama_hari')) {
    /**
     * Get Indonesian day name from date.
     */
    function nama_hari(mixed $date): string
    {
        if (!$date) {
            return '-';
        }
        return Carbon::parse($date)->locale('id')->translatedFormat('l');
    }
}

if (!function_exists('format_jam')) {
    /**
     * Format time string to H:i.
     */
    function format_jam(mixed $time): string
    {
        if (!$time) {
            return '-';
        }
        return Carbon::parse($time)->format('H:i');
    }
}
