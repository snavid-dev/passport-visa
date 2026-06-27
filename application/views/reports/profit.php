<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">گزارش سود</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right me-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to)); ?>

<div class="glass-card glass-table-wrap mb-3" data-aos="fade-up">
    <div class="p-3 pb-0"><h3 class="h6 mb-0">سود به تفکیک ارز (فقط وظایفی که فیس و هزینه هم‌ارز هستند)</h3></div>
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>ارز</th><th class="text-start">جمع فیس</th><th class="text-start">جمع هزینه</th><th class="text-start">سود</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">داده‌ای نیست</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= html_escape($currencies[$r->cur]) ?></td>
                    <td class="num text-start"><?= format_money($r->fee, NULL, FALSE) ?></td>
                    <td class="num text-start"><?= format_money($r->cost, NULL, FALSE) ?></td>
                    <td class="num text-start fw-bold <?= bc_compare($r->profit, '0') < 0 ? 'text-danger' : 'text-success' ?>"><?= format_money($r->profit, NULL, FALSE) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <div class="p-3 pb-0"><h3 class="h6 mb-0">سود هر وظیفه</h3></div>
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>#</th><th>تاریخ</th><th>مشتری</th><th>فیس</th><th>هزینه</th><th class="text-start">سود</th></tr></thead>
        <tbody>
            <?php if (empty($tasks)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">داده‌ای نیست</td></tr>
            <?php else: foreach ($tasks as $t): ?>
                <tr>
                    <td class="num"><?= (int) $t->id ?></td>
                    <td class="num"><?= jalali_date($t->date) ?></td>
                    <td><?= html_escape($t->client_name ?: '—') ?></td>
                    <td class="num"><?= format_money($t->fee_amount, $t->fee_currency) ?></td>
                    <td class="num"><?= format_money($t->vendor_cost_amount, $t->fee_currency) ?></td>
                    <td class="num text-start fw-bold <?= bc_compare($t->profit, '0') < 0 ? 'text-danger' : 'text-success' ?>"><?= format_money($t->profit, $t->fee_currency) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
