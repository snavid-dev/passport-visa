<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reusable date-range filter bar for reports.
 * Expects $date_from, $date_to (raw Jalali strings). Submits via GET to the
 * current report URL. Pass $extra_filter (HTML) for additional fields.
 */
?>
<div class="glass-card p-3 mb-3" data-aos="fade-up">
    <form method="get" action="<?= current_url() ?>" class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label small">از تاریخ</label>
            <input type="text" class="form-control form-control-sm num" name="date_from" data-jdp data-jdp-only-date value="<?= html_escape($date_from) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small">تا تاریخ</label>
            <input type="text" class="form-control form-control-sm num" name="date_to" data-jdp data-jdp-only-date value="<?= html_escape($date_to) ?>">
        </div>
        <?= isset($extra_filter) ? $extra_filter : '' ?>
        <div class="col-6 col-md-2 d-grid">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter me-1"></i> اعمال</button>
        </div>
    </form>
</div>
