<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$status_badge = array('open' => 'badge-info', 'completed' => 'badge-success', 'cancelled' => 'badge-error');

/** Render a per-currency outstanding block: due (only in the cost currency) vs paid. */
function outstanding_block($due_amount, $due_currency, $paid_by_currency, $currencies)
{
    foreach ($currencies as $code => $label) {
        $due  = ($code === $due_currency) ? $due_amount : '0.00';
        $paid = isset($paid_by_currency[$code]) ? $paid_by_currency[$code] : '0.00';
        // Skip currencies with nothing on either side.
        if (bc_compare($due, '0') <= 0 && bc_compare($paid, '0') <= 0) { continue; }
        $out    = bc_subtract($due, $paid);
        $status = payment_status_key($due, $paid);
        $badge  = $status === 'paid' ? 'badge-success' : ($status === 'partial' ? 'badge-warning' : 'badge-error');
        echo '<div class="d-flex justify-content-between align-items-center py-1 border-bottom">';
        echo '<span class="small text-secondary">' . html_escape($label) . '</span>';
        echo '<span class="num">' . format_money($paid, NULL, FALSE) . ' / ' . format_money($due, NULL, FALSE) . '</span>';
        echo '<span class="badge-soft ' . $badge . '">' . payment_status_label($status);
        if (bc_compare($out, '0') > 0) { echo ' · باقی ' . format_money($out, NULL, FALSE); }
        echo '</span></div>';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">وظیفه #<?= (int) $task->id ?>
            <span class="badge-soft <?= isset($status_badge[$task->status]) ? $status_badge[$task->status] : 'badge-info' ?>"><?= html_escape($statuses[$task->status]) ?></span>
        </h2>
        <p class="text-secondary mb-0 small"><?= jalali_date($task->date, 'l، j F Y') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('tasks/edit/' . (int) $task->id) ?>" class="btn btn-glass"><i class="fa-solid fa-pen ms-1"></i> ویرایش</a>
        <a href="<?= base_url('tasks') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> بازگشت</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Info -->
    <div class="col-12 col-lg-6" data-aos="fade-up">
        <div class="glass-card p-4 h-100">
            <h3 class="h6 mb-3">اطلاعات</h3>
            <div class="row small g-2">
                <div class="col-6"><span class="text-secondary">مشتری:</span> <?= html_escape($task->client_name ?: '—') ?></div>
                <div class="col-6"><span class="text-secondary">فروشنده:</span> <?= html_escape($task->vendor_name ?: '—') ?></div>
                <div class="col-6"><span class="text-secondary">خدمت:</span> <?= html_escape($task->service_name ?: '—') ?></div>
                <div class="col-6"><span class="text-secondary">نوع ویزا:</span> <?= html_escape($task->visa_type ?: '—') ?></div>
                <div class="col-6"><span class="text-secondary">مقصد:</span> <?= html_escape($task->destination) ?></div>
                <div class="col-12"><span class="text-secondary">یادداشت:</span> <?= html_escape($task->note ?: '—') ?></div>
            </div>
        </div>
    </div>
    <!-- Financial summary -->
    <div class="col-12 col-lg-6" data-aos="fade-up">
        <div class="glass-card p-4 h-100">
            <h3 class="h6 mb-3">وضعیت مالی (هر ارز مستقل)</h3>
            <div class="mb-2">
                <div class="small fw-bold mb-1">مشتری — فیس: <span class="num"><?= format_money($task->fee_amount, $task->fee_currency) ?></span></div>
                <?php outstanding_block($task->fee_amount, $task->fee_currency, $client_paid, $currencies); ?>
            </div>
            <div class="mt-3">
                <div class="small fw-bold mb-1">فروشنده — هزینه: <span class="num"><?= format_money($task->vendor_cost_amount, $task->vendor_cost_currency) ?></span></div>
                <?php outstanding_block($task->vendor_cost_amount, $task->vendor_cost_currency, $vendor_paid, $currencies); ?>
            </div>
        </div>
    </div>
</div>

<!-- Passports -->
<div class="glass-card glass-table-wrap mb-3" data-aos="fade-up">
    <div class="p-3 pb-0"><h3 class="h6 mb-0"><i class="fa-solid fa-passport text-primary ms-1"></i> پاسپورت‌ها (<?= count($passports) ?>)</h3></div>
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>#</th><th>نام خانوادگی</th><th>نام</th><th>شماره</th><th>تولد</th><th>انقضا</th><th>جنسیت</th><th>اسکن</th></tr></thead>
        <tbody>
            <?php if (empty($passports)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">پاسپورتی ثبت نشده است</td></tr>
            <?php else: foreach ($passports as $i => $p): ?>
                <tr>
                    <td class="num"><?= $i + 1 ?></td>
                    <td><?= html_escape($p->surname ?: '—') ?></td>
                    <td><?= html_escape($p->given_name ?: '—') ?></td>
                    <td class="num"><?= html_escape($p->passport_no ?: '—') ?></td>
                    <td class="num"><?= $p->dob ? jalali_date($p->dob) : '—' ?></td>
                    <td class="num"><?= $p->expiry_date ? jalali_date($p->expiry_date) : '—' ?></td>
                    <td><?= $p->gender === 'male' ? 'مرد' : ($p->gender === 'female' ? 'زن' : '—') ?></td>
                    <td>
                        <?php if ($p->scan_path): ?>
                            <a href="<?= base_url('scan/' . (int) $task->id . '/' . basename($p->scan_path)) ?>" target="_blank" class="btn btn-sm btn-glass"><i class="fa-solid fa-file-arrow-down"></i></a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="row g-3 mb-5">
    <?php
    $payment_blocks = array(
        array('side' => 'client', 'title' => 'پرداخت‌های مشتری', 'icon' => 'fa-arrow-down', 'rows' => $client_payments,
              'add' => 'tasks/add_client_payment/' . (int) $task->id, 'del' => 'tasks/delete_client_payment/'),
        array('side' => 'vendor', 'title' => 'پرداخت‌های فروشنده', 'icon' => 'fa-arrow-up', 'rows' => $vendor_payments,
              'add' => 'tasks/add_vendor_payment/' . (int) $task->id, 'del' => 'tasks/delete_vendor_payment/'),
    );
    foreach ($payment_blocks as $blk): ?>
        <div class="col-12 col-lg-6" data-aos="fade-up">
            <div class="glass-card p-4 h-100">
                <h3 class="h6 mb-3"><i class="fa-solid <?= $blk['icon'] ?> text-primary ms-1"></i> <?= $blk['title'] ?></h3>

                <form class="row g-2 mb-3 payment-form" data-action="<?= base_url($blk['add']) ?>">
                    <div class="col-4"><input type="number" step="0.01" min="0" class="form-control form-control-sm num" name="amount" placeholder="مبلغ" required></div>
                    <div class="col-3">
                        <select class="form-select form-select-sm" name="currency" required>
                            <?php foreach ($currencies as $code => $label): ?><option value="<?= $code ?>"><?= $label ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-3"><input type="text" class="form-control form-control-sm num" name="date" data-jdp data-jdp-only-date placeholder="تاریخ" value="<?= jalali_today() ?>" required></div>
                    <div class="col-2 d-grid"><button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i></button></div>
                    <div class="col-12"><input type="text" class="form-control form-control-sm" name="note" placeholder="یادداشت (اختیاری)"></div>
                    <div class="col-12 payment-error form-error" style="display:none"></div>
                </form>

                <div class="table-responsive">
                    <table class="table glass-table align-middle mb-0" style="width:100%">
                        <thead><tr><th>تاریخ</th><th>مبلغ</th><th>یادداشت</th><th></th></tr></thead>
                        <tbody>
                            <?php if (empty($blk['rows'])): ?>
                                <tr><td colspan="4" class="text-center text-muted small py-2">پرداختی ثبت نشده</td></tr>
                            <?php else: foreach ($blk['rows'] as $pay): ?>
                                <tr>
                                    <td class="num"><?= jalali_date($pay->date) ?></td>
                                    <td class="num"><?= format_money($pay->amount, $pay->currency) ?></td>
                                    <td class="small text-secondary"><?= html_escape($pay->note ?: '—') ?></td>
                                    <td class="text-start">
                                        <button type="button" class="btn btn-sm btn-glass text-danger delete-payment"
                                                data-action="<?= base_url($blk['del'] . (int) $pay->id) ?>" title="حذف">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
