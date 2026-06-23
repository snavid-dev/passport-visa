<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$extra = '';
ob_start(); ?>
<div class="col-6 col-md-3">
    <label class="form-label small">حساب</label>
    <select class="form-select form-select-sm select2" name="account_id" data-placeholder="انتخاب حساب">
        <option value="">—</option>
        <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a->id ?>" <?= $account_id === (int) $a->id ? 'selected' : '' ?>><?= html_escape($a->name) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-2">
    <label class="form-label small">ارز</label>
    <select class="form-select form-select-sm" name="currency">
        <?php foreach ($currencies as $code => $label): ?>
            <option value="<?= $code ?>" <?= $currency === $code ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php $extra = ob_get_clean(); ?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">صورتحساب حساب</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to, 'extra_filter' => $extra)); ?>

<?php if ($account): ?>
    <div class="glass-card glass-table-wrap" data-aos="fade-up">
        <div class="p-3 pb-0">
            <h3 class="h6 mb-0"><?= html_escape($account->name) ?> — <?= html_escape($currencies[$currency]) ?></h3>
            <p class="small text-secondary mb-0">مانده اول دوره: <span class="num"><?= format_money($opening, NULL, FALSE) ?></span></p>
        </div>
        <table class="table glass-table align-middle" style="width:100%">
            <thead><tr><th>تاریخ</th><th>شرح</th><th>بدهکار</th><th>بستانکار</th><th class="text-start">مانده</th></tr></thead>
            <tbody>
                <?php $running = $opening; if (empty($rows)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">تراکنشی در این بازه نیست</td></tr>
                <?php else: foreach ($rows as $r): $running = bc_add(bc_subtract($running, $r->credit), $r->debit); ?>
                    <tr>
                        <td class="num"><?= jalali_date($r->date) ?></td>
                        <td class="small"><?= html_escape($r->note ?: $r->source) ?></td>
                        <td class="num text-success"><?= bc_compare($r->debit, '0') > 0 ? format_money($r->debit, NULL, FALSE) : '—' ?></td>
                        <td class="num text-danger"><?= bc_compare($r->credit, '0') > 0 ? format_money($r->credit, NULL, FALSE) : '—' ?></td>
                        <td class="num text-start fw-bold"><?= format_money($running, NULL, FALSE) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="glass-card p-4 text-center text-muted" data-aos="fade-up">یک حساب را برای مشاهده صورتحساب انتخاب کنید.</div>
<?php endif; ?>
