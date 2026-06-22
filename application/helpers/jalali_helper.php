<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jalali (Shamsi) date helper.
 *
 * Storage is ALWAYS Gregorian (DATE/DATETIME in MySQL). These helpers
 * convert at the display/input boundary only.
 *
 * Conversion algorithm: the well-known jdf algorithm by Roozbeh Pournader
 * & Mohammad Toossi (public domain).
 *
 * Digits are kept Western (per the project brief).
 */

if (! function_exists('gregorian_to_jalali')) {
    /**
     * @param int $gy @param int $gm @param int $gd
     * @return array [jy, jm, jd]
     */
    function gregorian_to_jalali($gy, $gm, $gd)
    {
        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
        $gy = (int) $gy; $gm = (int) $gm; $gd = (int) $gd;

        if ($gy > 1600) { $jy = 979; $gy -= 1600; }
        else            { $jy = 0;   $gy -= 621;  }

        $gy2  = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy) + ((int) (($gy2 + 3) / 4)) - ((int) (($gy2 + 99) / 100))
              + ((int) (($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];

        $jy  += 33 * ((int) ($days / 12053));
        $days %= 12053;
        $jy  += 4 * ((int) ($days / 1461));
        $days %= 1461;

        if ($days > 365) {
            $jy  += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + (int) ($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int) (($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return array($jy, $jm, $jd);
    }
}

if (! function_exists('jalali_to_gregorian')) {
    /**
     * @param int $jy @param int $jm @param int $jd
     * @return array [gy, gm, gd]
     */
    function jalali_to_gregorian($jy, $jm, $jd)
    {
        $jy = (int) $jy; $jm = (int) $jm; $jd = (int) $jd;

        if ($jy > 979) { $gy = 1600; $jy -= 979; }
        else           { $gy = 621; }

        $days = (365 * $jy) + (((int) ($jy / 33)) * 8) + ((int) ((($jy % 33) + 3) / 4))
              + 78 + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy   += 400 * ((int) ($days / 146097));
        $days %= 146097;

        if ($days > 36524) {
            $gy   += 100 * ((int) (--$days / 36524));
            $days %= 36524;
            if ($days >= 365) { $days++; }
        }

        $gy   += 4 * ((int) ($days / 1461));
        $days %= 1461;

        if ($days > 365) {
            $gy  += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }

        $gd    = $days + 1;
        $leap  = ((($gy % 4 === 0) && ($gy % 100 !== 0)) || ($gy % 400 === 0));
        $sal_a = array(0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }

        return array($gy, $gm, $gd);
    }
}

if (! function_exists('jalali_month_names')) {
    function jalali_month_names()
    {
        return array(
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر',     5 => 'مرداد',    6 => 'شهریور',
            7 => 'مهر',     8 => 'آبان',     9 => 'آذر',
            10 => 'دی',    11 => 'بهمن',    12 => 'اسفند',
        );
    }
}

if (! function_exists('jalali_weekday_names')) {
    // Keyed by PHP date('w'): 0=Sunday .. 6=Saturday
    function jalali_weekday_names()
    {
        return array(
            0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه',
            4 => 'پنجشنبه', 5 => 'جمعه', 6 => 'شنبه',
        );
    }
}

if (! function_exists('jalali_date')) {
    /**
     * Format a Gregorian date/datetime (or timestamp) as a Jalali string.
     *
     * Supported format tokens:
     *   Y year | m 2-digit month | n month | d 2-digit day | j day
     *   F month name | l weekday name | H hour | i minute | s second
     *
     * @param  string|int|null $gregorian  'Y-m-d' / 'Y-m-d H:i:s' / timestamp
     * @param  string          $format
     * @param  string          $empty      returned when input is empty/invalid
     * @return string
     */
    function jalali_date($gregorian, $format = 'Y/m/d', $empty = '—')
    {
        if (empty($gregorian) || $gregorian === '0000-00-00' || $gregorian === '0000-00-00 00:00:00') {
            return $empty;
        }

        $ts = is_numeric($gregorian) ? (int) $gregorian : strtotime($gregorian);
        if ($ts === FALSE || $ts === -1) {
            return $empty;
        }

        $gy = (int) date('Y', $ts);
        $gm = (int) date('n', $ts);
        $gd = (int) date('j', $ts);

        list($jy, $jm, $jd) = gregorian_to_jalali($gy, $gm, $gd);

        $months   = jalali_month_names();
        $weekdays = jalali_weekday_names();
        $w        = (int) date('w', $ts);

        $replacements = array(
            'Y' => $jy,
            'm' => sprintf('%02d', $jm),
            'n' => $jm,
            'd' => sprintf('%02d', $jd),
            'j' => $jd,
            'F' => isset($months[$jm]) ? $months[$jm] : $jm,
            'l' => isset($weekdays[$w]) ? $weekdays[$w] : '',
            'H' => date('H', $ts),
            'i' => date('i', $ts),
            's' => date('s', $ts),
        );

        // Token-by-token replacement that respects literal characters.
        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $ch = $format[$i];
            $out .= array_key_exists($ch, $replacements) ? $replacements[$ch] : $ch;
        }

        return $out;
    }
}

if (! function_exists('to_jalali')) {
    /**
     * Alias of jalali_date() — display a Gregorian value as Shamsi.
     */
    function to_jalali($gregorian, $format = 'Y/m/d', $empty = '—')
    {
        return jalali_date($gregorian, $format, $empty);
    }
}

if (! function_exists('from_jalali')) {
    /**
     * Convert a Jalali date string (YYYY/MM/DD or YYYY-MM-DD) to a
     * Gregorian 'Y-m-d' string suitable for DB storage.
     *
     * @param  string      $jalali
     * @return string|null  'Y-m-d' or NULL when unparseable/empty
     */
    function from_jalali($jalali)
    {
        if (empty($jalali)) {
            return NULL;
        }

        // Normalise Persian/Arabic digits to Western, separators to '/'.
        $jalali = persian_to_western_digits($jalali);
        $jalali = str_replace(array('-', '.'), '/', trim($jalali));
        $parts  = explode('/', $jalali);

        if (count($parts) !== 3) {
            return NULL;
        }

        $jy = (int) $parts[0];
        $jm = (int) $parts[1];
        $jd = (int) $parts[2];

        if ($jy < 1 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return NULL;
        }

        list($gy, $gm, $gd) = jalali_to_gregorian($jy, $jm, $jd);
        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }
}

if (! function_exists('persian_to_western_digits')) {
    /**
     * Convert Persian (۰-۹) and Arabic (٠-٩) digits to Western (0-9).
     */
    function persian_to_western_digits($str)
    {
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $arabic  = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        return str_replace(array_merge($persian, $arabic), array_merge($western, $western), (string) $str);
    }
}

if (! function_exists('jalali_today')) {
    /**
     * Today's date as a Jalali string (default Y/m/d).
     */
    function jalali_today($format = 'Y/m/d')
    {
        return jalali_date(date('Y-m-d'), $format);
    }
}
