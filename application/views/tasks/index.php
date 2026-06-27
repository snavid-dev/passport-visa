<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$status_badge = array('open' => 'badge-info', 'completed' => 'badge-success', 'cancelled' => 'badge-error');
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">وظایف</h2>
        <p class="text-secondary mb-0 small">مدیریت بسته‌های پاسپورت و پردازش ویزا (<?= (int) $total ?> مورد)</p>
    </div>
    <a href="<?= base_url('tasks/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> افزودن وظیفه
    </a>
</div>

<!-- Filters -->
<div class="glass-card p-3 mb-3" data-aos="fade-up">
    <?= form_open('tasks', array('method' => 'get', 'class' => 'row g-2 align-items-end')) ?>
        <div class="col-6 col-md-3">
            <label class="form-label small">جستجو</label>
            <input type="text" class="form-control form-control-sm" name="search" value="<?= html_escape($filters['search']) ?>" placeholder="مشتری، فروشنده، نوع ویزا">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">وضعیت</label>
            <select class="form-select form-select-sm" name="status">
                <option value="">همه</option>
                <?php foreach ($statuses as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">مشتری</label>
            <select class="form-select form-select-sm select2" name="client_id" data-placeholder="همه">
                <option value="">همه</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= (int) $c->id ?>" <?= (string) $filters['client_id'] === (string) $c->id ? 'selected' : '' ?>><?= html_escape($c->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">از تاریخ</label>
            <input type="text" class="form-control form-control-sm num" name="date_from" data-jdp data-jdp-only-date value="<?= html_escape($this->input->get('date_from')) ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">تا تاریخ</label>
            <input type="text" class="form-control form-control-sm num" name="date_to" data-jdp data-jdp-only-date value="<?= html_escape($this->input->get('date_to')) ?>">
        </div>
        <div class="col-6 col-md-1 d-grid">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i></button>
        </div>
    <?= form_close() ?>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>تاریخ</th>
                <th>مشتری</th>
                <th>فروشنده</th>
                <th>پاسپورت‌ها</th>
                <th>فیس</th>
                <th>وضعیت</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tasks)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">وظیفه‌ای یافت نشد</td></tr>
            <?php else: foreach ($tasks as $t): ?>
                <tr>
                    <td class="num"><?= (int) $t->id ?></td>
                    <td class="num"><?= jalali_date($t->date) ?></td>
                    <td><?= html_escape($t->client_name ?: '—') ?></td>
                    <td><?= html_escape($t->vendor_name ?: '—') ?></td>
                    <td><span class="badge-soft badge-info num"><?= (int) $t->passport_count ?></span></td>
                    <td class="num"><?= format_money($t->fee_amount, $t->fee_currency) ?></td>
                    <td><span class="badge-soft <?= isset($status_badge[$t->status]) ? $status_badge[$t->status] : 'badge-info' ?>"><?= html_escape($statuses[$t->status]) ?></span></td>
                    <td class="text-start text-nowrap">
                        <a href="<?= base_url('tasks/view/' . (int) $t->id) ?>" class="btn btn-sm btn-glass" title="مشاهده"><i class="fa-solid fa-eye"></i></a>
                        <a href="<?= base_url('tasks/edit/' . (int) $t->id) ?>" class="btn btn-sm btn-glass" title="ویرایش"><i class="fa-solid fa-pen"></i></a>
                        <?= form_open('tasks/delete/' . (int) $t->id, array('class' => 'd-inline', 'onsubmit' => "return confirm('حذف وظیفه و تمام پاسپورت‌ها و پرداخت‌های آن؟ ثبت‌های دفتر کل برگردانده می‌شوند.');")) ?>
                            <button type="submit" class="btn btn-sm btn-glass text-danger" title="حذف"><i class="fa-solid fa-trash"></i></button>
                        <?= form_close() ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php $this->load->view('_partials/pagination'); ?>
</div>
