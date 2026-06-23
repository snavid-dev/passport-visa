<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upload helper — secure passport-scan handling.
 *
 * Validates by CONTENT (getimagesize for images, %PDF magic bytes for PDFs)
 * rather than trusting the extension or the browser-supplied MIME type — so
 * it is safe even though the fileinfo extension is unavailable here.
 * Images are recompressed via GD (max width UPLOAD_MAX_WIDTH, JPEG quality
 * UPLOAD_JPEG_QUALITY). Files are stored under uploads/passports/{task_id}/.
 */

if (! function_exists('passports_dir')) {
    function passports_dir($task_id)
    {
        return FCPATH . 'uploads/passports/' . (int) $task_id . '/';
    }
}

if (! function_exists('process_passport_scan')) {
    /**
     * @param  string $field    name of the $_FILES entry (e.g. "scan_0")
     * @param  int    $task_id
     * @return array  ['ok'=>bool, 'path'=>string|null, 'error'=>string|null]
     *                path is relative to uploads/passports/ ("{task_id}/{uuid}.ext")
     */
    function process_passport_scan($field, $task_id)
    {
        if (empty($_FILES[$field]) || ! isset($_FILES[$field]['error'])) {
            return array('ok' => TRUE, 'path' => NULL, 'error' => NULL); // no file = fine
        }
        $f = $_FILES[$field];

        if ($f['error'] === UPLOAD_ERR_NO_FILE) {
            return array('ok' => TRUE, 'path' => NULL, 'error' => NULL);
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return array('ok' => FALSE, 'path' => NULL, 'error' => 'خطا در بارگذاری فایل.');
        }
        if ($f['size'] > UPLOAD_MAX_BYTES) {
            return array('ok' => FALSE, 'path' => NULL, 'error' => 'حجم فایل بیش از حد مجاز است (حداکثر ۵ مگابایت).');
        }
        if (! is_uploaded_file($f['tmp_name'])) {
            return array('ok' => FALSE, 'path' => NULL, 'error' => 'فایل نامعتبر است.');
        }

        $dir = passports_dir($task_id);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, TRUE)) {
            return array('ok' => FALSE, 'path' => NULL, 'error' => 'عدم امکان ایجاد پوشه بارگذاری.');
        }

        $uuid = bin2hex(random_bytes(16));
        $info = @getimagesize($f['tmp_name']);

        // ---- Image (content-verified) ----
        if ($info !== FALSE && in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), TRUE)) {
            $dest_rel = (int) $task_id . '/' . $uuid . '.jpg';
            $dest_abs = $dir . $uuid . '.jpg';
            if (! _compress_image_to_jpeg($f['tmp_name'], $dest_abs, $info)) {
                return array('ok' => FALSE, 'path' => NULL, 'error' => 'پردازش تصویر ناموفق بود.');
            }
            return array('ok' => TRUE, 'path' => $dest_rel, 'error' => NULL);
        }

        // ---- PDF (magic bytes) ----
        $fh   = fopen($f['tmp_name'], 'rb');
        $head = $fh ? fread($fh, 5) : '';
        if ($fh) { fclose($fh); }
        if (strncmp($head, '%PDF-', 5) === 0) {
            $dest_rel = (int) $task_id . '/' . $uuid . '.pdf';
            if (! @move_uploaded_file($f['tmp_name'], $dir . $uuid . '.pdf')) {
                return array('ok' => FALSE, 'path' => NULL, 'error' => 'ذخیره فایل ناموفق بود.');
            }
            return array('ok' => TRUE, 'path' => $dest_rel, 'error' => NULL);
        }

        return array('ok' => FALSE, 'path' => NULL, 'error' => 'فقط تصویر (JPG/PNG) یا PDF مجاز است.');
    }
}

if (! function_exists('_compress_image_to_jpeg')) {
    /**
     * Resize (max UPLOAD_MAX_WIDTH wide) and re-encode as JPEG.
     *
     * @return bool
     */
    function _compress_image_to_jpeg($src_path, $dest_path, $info)
    {
        list($w, $h) = $info;
        $src = ($info[2] === IMAGETYPE_PNG) ? @imagecreatefrompng($src_path) : @imagecreatefromjpeg($src_path);
        if (! $src) {
            return FALSE;
        }

        $max = UPLOAD_MAX_WIDTH;
        if ($w > $max) {
            $nw = $max;
            $nh = (int) round($h * ($max / $w));
            $dst = imagecreatetruecolor($nw, $nh);
            // White background (flatten any PNG transparency).
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
        } else {
            $dst = imagecreatetruecolor($w, $h);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $w, $h, $white);
            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);
        }

        $ok = imagejpeg($dst, $dest_path, UPLOAD_JPEG_QUALITY);
        imagedestroy($dst);
        return $ok;
    }
}

if (! function_exists('delete_passport_scan')) {
    /** Remove a stored scan by its relative path. */
    function delete_passport_scan($relative_path)
    {
        if (empty($relative_path)) {
            return;
        }
        $abs = FCPATH . 'uploads/passports/' . ltrim($relative_path, '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
