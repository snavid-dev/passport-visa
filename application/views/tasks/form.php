<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($task);
$val = function ($field, $default = '') use ($task, $is_edit) {
    if ($is_edit && isset($task->$field)) { return $task->$field; }
    return set_value($field, $default);
};
$cur_sel = function ($field, $current) {
    return (set_value($field, $current) === $current) ? 'selected' : '';
};

/**
 * Render a single passport row. $i is the row index; $p is a passport object
 * or NULL (template / blank row).
 */
function render_passport_row($i, $p = NULL)
{
    $g = function ($f) use ($p) { return $p && isset($p->$f) && $p->$f !== NULL ? html_escape($p->$f) : ''; };
    $jd = function ($f) use ($p) { return $p && ! empty($p->$f) ? jalali_date($p->$f) : ''; };
    $id    = $p ? (int) $p->id : '';
    $scan  = $p && ! empty($p->scan_path) ? $p->scan_path : '';
    ob_start(); ?>
    <div class="passport-row glass-panel--subtle p-3 mb-2" data-row>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small"><i class="fa-solid fa-passport me-1"></i> پاسپورت</span>
            <button type="button" class="btn btn-sm btn-glass text-danger remove-passport-row" title="حذف"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <input type="hidden" name="passport_rows[<?= $i ?>][id]" value="<?= $id ?>">
        <input type="hidden" name="passport_rows[<?= $i ?>][existing_scan]" value="<?= html_escape($scan) ?>">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <label class="form-label small">نام خانوادگی</label>
                <input type="text" class="form-control form-control-sm" name="passport_rows[<?= $i ?>][surname]" value="<?= $g('surname') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">نام</label>
                <input type="text" class="form-control form-control-sm" name="passport_rows[<?= $i ?>][given_name]" value="<?= $g('given_name') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">شماره پاسپورت</label>
                <input type="text" class="form-control form-control-sm num" name="passport_rows[<?= $i ?>][passport_no]" value="<?= $g('passport_no') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">جنسیت</label>
                <select class="form-select form-select-sm row-select" name="passport_rows[<?= $i ?>][gender]">
                    <option value="">—</option>
                    <option value="male" <?= ($p && $p->gender === 'male') ? 'selected' : '' ?>>مرد</option>
                    <option value="female" <?= ($p && $p->gender === 'female') ? 'selected' : '' ?>>زن</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">تاریخ تولد</label>
                <input type="text" class="form-control form-control-sm num" name="passport_rows[<?= $i ?>][dob]" data-jdp data-jdp-only-date value="<?= $jd('dob') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">محل تولد</label>
                <input type="text" class="form-control form-control-sm" name="passport_rows[<?= $i ?>][place_of_birth]" value="<?= $g('place_of_birth') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">تاریخ صدور</label>
                <input type="text" class="form-control form-control-sm num" name="passport_rows[<?= $i ?>][issue_date]" data-jdp data-jdp-only-date value="<?= $jd('issue_date') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">تاریخ انقضا</label>
                <input type="text" class="form-control form-control-sm num" name="passport_rows[<?= $i ?>][expiry_date]" data-jdp data-jdp-only-date value="<?= $jd('expiry_date') ?>">
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label small">اسکن پاسپورت (JPG/PNG/PDF، حداکثر ۵MB)</label>
                <input type="file" class="form-control form-control-sm" name="scan_<?= $i ?>" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <?php if ($scan): ?>
                    <a href="<?= base_url('scan/' . (int) $p->task_id . '/' . basename($scan)) ?>" target="_blank" class="btn btn-sm btn-glass w-100">
                        <i class="fa-solid fa-file-arrow-down me-1"></i> مشاهده اسکن فعلی
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0"><?= $is_edit ? 'ویرایش وظیفه' : 'افزودن وظیفه' ?></h2>
    <a href="<?= base_url('tasks') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right me-1"></i> بازگشت</a>
</div>

<?php if ($this->session->flashdata('error')): ?>
<?php endif; ?>

<?= form_open_multipart($form_action, array('id' => 'task-form')) ?>

<!-- Header -->
<div class="glass-card p-4 mb-3" data-aos="fade-up">
    <h3 class="h6 mb-3"><i class="fa-solid fa-circle-info text-primary me-1"></i> اطلاعات وظیفه</h3>
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label class="form-label">مشتری</label>
            <select class="form-select select2" name="client_id" data-placeholder="انتخاب مشتری" required>
                <option value=""></option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= (int) $c->id ?>" <?= (string) $val('client_id') === (string) $c->id ? 'selected' : '' ?>><?= html_escape($c->name) ?></option>
                <?php endforeach; ?>
            </select>
            <?= form_error('client_id') ?>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">فروشنده</label>
            <select class="form-select select2" name="vendor_id" data-placeholder="انتخاب فروشنده">
                <option value=""></option>
                <?php foreach ($vendors as $v): ?>
                    <option value="<?= (int) $v->id ?>" <?= (string) $val('vendor_id') === (string) $v->id ? 'selected' : '' ?>><?= html_escape($v->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">خدمت</label>
            <select class="form-select select2" id="service-select" name="service_id" data-placeholder="انتخاب خدمت">
                <option value=""></option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= (int) $s->id ?>"
                            data-fee="<?= html_escape($s->default_fee) ?>"
                            data-currency="<?= html_escape($s->default_currency) ?>"
                            data-visa="<?= html_escape($s->visa_type) ?>"
                            <?= (string) $val('service_id') === (string) $s->id ? 'selected' : '' ?>>
                        <?= html_escape($s->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">نوع ویزا</label>
            <input type="text" class="form-control" id="visa-type" name="visa_type" value="<?= html_escape($val('visa_type')) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">مقصد</label>
            <input type="text" class="form-control" name="destination" value="<?= html_escape($val('destination', 'Iran')) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">تاریخ</label>
            <input type="text" class="form-control num" name="date" data-jdp data-jdp-only-date
                   value="<?= $is_edit ? jalali_date($task->date) : html_escape(set_value('date', jalali_today())) ?>" required>
            <?= form_error('date') ?>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">وضعیت</label>
            <select class="form-select select2" name="status" required>
                <?php foreach ($statuses as $k => $label): ?>
                    <option value="<?= $k ?>" <?= (string) $val('status', 'open') === (string) $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Financials -->
<div class="glass-card p-4 mb-3" data-aos="fade-up">
    <h3 class="h6 mb-3"><i class="fa-solid fa-money-bill-wave text-primary me-1"></i> مالی</h3>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <label class="form-label">مبلغ فیس (مشتری)</label>
            <input type="number" step="0.01" min="0" class="form-control num" id="fee-amount" name="fee_amount" value="<?= html_escape($val('fee_amount', '0')) ?>" required>
            <?= form_error('fee_amount') ?>
            <div class="form-text small">پیش‌فرض: فیس خدمت × تعداد پاسپورت (قابل ویرایش)</div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">ارز فیس</label>
            <select class="form-select select2" id="fee-currency" name="fee_currency" required>
                <?php foreach ($currencies as $code => $label): ?>
                    <option value="<?= $code ?>" <?= $cur_sel('fee_currency', $code) ?: ((string) $val('fee_currency') === (string) $code ? 'selected' : '') ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">هزینه فروشنده</label>
            <input type="number" step="0.01" min="0" class="form-control num" name="vendor_cost_amount" value="<?= html_escape($val('vendor_cost_amount', '0')) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">ارز هزینه فروشنده</label>
            <select class="form-select select2" name="vendor_cost_currency">
                <?php foreach ($currencies as $code => $label): ?>
                    <option value="<?= $code ?>" <?= (string) $val('vendor_cost_currency', 'AFN') === (string) $code ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">یادداشت</label>
            <input type="text" class="form-control" name="note" value="<?= html_escape($val('note')) ?>">
        </div>
    </div>
</div>

<!-- Passports -->
<div class="glass-card p-4 mb-3" data-aos="fade-up">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 mb-0"><i class="fa-solid fa-passport text-primary me-1"></i> پاسپورت‌ها (<span id="passport-count"><?= count($passports) ?></span>)</h3>
        <button type="button" class="btn btn-sm btn-primary" id="add-passport-row"><i class="fa-solid fa-plus me-1"></i> افزودن پاسپورت</button>
    </div>
    <div id="passport-rows-container">
        <?php
        if (! empty($passports)) {
            foreach ($passports as $i => $p) {
                echo render_passport_row($i, $p);
            }
        }
        ?>
    </div>
    <p class="text-muted small mb-0" id="no-passport-hint" style="<?= empty($passports) ? '' : 'display:none' ?>">
        هنوز پاسپورتی اضافه نشده است. روی «افزودن پاسپورت» کلیک کنید.
    </p>
</div>

<template id="passport-row-template">
    <?= render_passport_row(0, NULL) ?>
</template>

<div class="d-flex gap-2 mb-5" data-aos="fade-up">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> ذخیره وظیفه</button>
    <a href="<?= base_url('tasks') ?>" class="btn btn-glass">انصراف</a>
</div>

<?= form_close() ?>
