<?php

if (!function_exists('settings')) {
    function settings()
    {
        $settings = cache()->remember('settings', 24 * 60, function () {
            return \Modules\Setting\Entities\Setting::firstOrFail();
        });

        return $settings;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($value, $format = true)
    {
        if (!$format) {
            return $value;
        }

        if ($value === null) {
            $value = 0;
        }

        $currency = settings()->currency;

        // IDR tanpa desimal, selain IDR pakai 2 desimal
        $decimalPlaces = $currency->code === 'IDR' ? 0 : 2;

        $formatted = number_format(
            (float) $value,
            $decimalPlaces,
            $currency->decimal_separator,
            $currency->thousand_separator
        );

        if (settings()->default_currency_position === 'prefix') {
            return $currency->symbol . ' ' . $formatted;
        }

        return $formatted . ' ' . $currency->symbol;
    }
}

if (!function_exists('unformat_idr')) {
    function unformat_idr($value)
    {
        if ($value === null) return null;

        // contoh: "Rp25.000.000,00" atau "25.000.000,00"
        $value = str_replace(['Rp', 'rp', ' ', "\u{A0}"], '', $value);

        // buang desimal (setelah koma)
        $value = explode(',', $value)[0];

        // hapus pemisah ribuan
        $value = str_replace('.', '', $value);

        return (int) $value; // 25000000
    }
}

if (!function_exists('make_reference_id')) {
    function make_reference_id($prefix, $number)
    {
        $padded_text = $prefix . '-' . str_pad($number, 5, 0, STR_PAD_LEFT);

        return $padded_text;
    }
}

if (!function_exists('array_merge_numeric_values')) {
    function array_merge_numeric_values()
    {
        $arrays = func_get_args();
        $merged = array();
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                if (!isset($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] += $value;
                }
            }
        }

        return $merged;
    }
}
