<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Money helper.
 *
 * ALL currency arithmetic uses bcmath with scale = 2. Never float math.
 * The three currencies (AFN, USD, TOMAN) are NEVER converted between each
 * other — every amount is bound to its own currency.
 */

if (! defined('MONEY_SCALE')) {
    define('MONEY_SCALE', 2);
}

if (! function_exists('bc_add')) {
    function bc_add($a, $b)
    {
        return bcadd(_bc_clean($a), _bc_clean($b), MONEY_SCALE);
    }
}

if (! function_exists('bc_subtract')) {
    function bc_subtract($a, $b)
    {
        return bcsub(_bc_clean($a), _bc_clean($b), MONEY_SCALE);
    }
}

if (! function_exists('bc_multiply')) {
    function bc_multiply($a, $b)
    {
        return bcmul(_bc_clean($a), _bc_clean($b), MONEY_SCALE);
    }
}

if (! function_exists('bc_divide')) {
    function bc_divide($a, $b)
    {
        $b = _bc_clean($b);
        if (bccomp($b, '0', MONEY_SCALE) === 0) {
            return '0.00';
        }
        return bcdiv(_bc_clean($a), $b, MONEY_SCALE);
    }
}

if (! function_exists('bc_compare')) {
    /** @return int -1, 0, or 1 */
    function bc_compare($a, $b)
    {
        return bccomp(_bc_clean($a), _bc_clean($b), MONEY_SCALE);
    }
}

if (! function_exists('_bc_clean')) {
    /**
     * Normalise an arbitrary numeric input to a bcmath-safe decimal string.
     * Strips thousands separators and any non-numeric noise.
     */
    function _bc_clean($value)
    {
        if ($value === NULL || $value === '') {
            return '0';
        }
        // Remove thousands separators and spaces; keep digits, dot, minus.
        $value = str_replace(array(',', ' ', '٬'), '', (string) $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        if ($value === '' || $value === '-' || $value === '.') {
            return '0';
        }
        return $value;
    }
}

if (! function_exists('currency_label')) {
    /**
     * Persian label for a currency code.
     */
    function currency_label($currency)
    {
        $map = CURRENCIES;
        $currency = strtoupper((string) $currency);
        return isset($map[$currency]) ? $map[$currency] : $currency;
    }
}

if (! function_exists('format_money')) {
    /**
     * Format an amount with thousands separators (Western digits), two
     * decimals, and the Persian currency label appended.
     *
     * @param  string|float|int $amount
     * @param  string|null      $currency  AFN | USD | TOMAN (label appended if given)
     * @param  bool             $with_label
     * @return string
     */
    function format_money($amount, $currency = NULL, $with_label = TRUE)
    {
        $amount    = _bc_clean($amount);
        $formatted = number_format((float) $amount, MONEY_SCALE, '.', ',');

        if ($currency !== NULL && $with_label) {
            return $formatted . ' ' . currency_label($currency);
        }
        return $formatted;
    }
}

if (! function_exists('payment_status_key')) {
    /**
     * Derive a per-currency payment status from a total due vs paid.
     *
     * @return string  'unpaid' | 'partial' | 'paid'
     */
    function payment_status_key($total_due, $total_paid)
    {
        if (bc_compare($total_due, '0') <= 0) {
            // Nothing owed → treat as settled.
            return 'paid';
        }
        if (bc_compare($total_paid, '0') <= 0) {
            return 'unpaid';
        }
        if (bc_compare($total_paid, $total_due) >= 0) {
            return 'paid';
        }
        return 'partial';
    }
}

if (! function_exists('payment_status_label')) {
    function payment_status_label($key)
    {
        $map = PAYMENT_STATUSES;
        return isset($map[$key]) ? $map[$key] : $key;
    }
}
