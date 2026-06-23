<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">مانده‌های معوق</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead><tr><th>وظیفه</th><th>تاریخ</th><th>طرف</th><th>مبلغ کل</th><th>پرداخت‌شده</th><th>باقی‌مانده</th><th>ارز</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">مانده معوقی وجود ندارد 🎉</td></tr>
            <?php else: foreach ($rows as $r): $rem = bc_subtract($r->due, $r->paid); ?>
                <tr>
                    <td><a href="<?= base_url('tasks/view/' . (int) $r->id) ?>">#<?= (int) $r->id ?></a>
                        <span class="small text-secondary d-block"><?= html_escape($r->side === 'client' ? ($r->client_name ?: 'مشتری') : ($r->vendor_name ?: 'فروشنده')) ?></span></td>
                    <td class="num"><?= jalali_date($r->date) ?></td>
                    <td><span class="badge-soft <?= $r->side === 'client' ? 'badge-info' : 'badge-warning' ?>"><?= $r->side === 'client' ? 'مشتری' : 'فروشنده' ?></span></td>
                    <td class="num"><?= format_money($r->due, NULL, FALSE) ?></td>
                    <td class="num text-success"><?= format_money($r->paid, NULL, FALSE) ?></td>
                    <td class="num fw-bold text-danger"><?= format_money($rem, NULL, FALSE) ?></td>
                    <td><?= html_escape($currencies[$r->currency]) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
