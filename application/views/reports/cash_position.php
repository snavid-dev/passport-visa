<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">وضعیت نقدینگی</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($currencies as $code => $label): ?>
        <div class="col-12 col-md-4" data-aos="fade-up">
            <div class="glass-card stat-card">
                <div class="stat-card__label">موجودی نقدی — <?= html_escape($label) ?></div>
                <div class="stat-card__value num <?= bc_compare($totals[$code], '0') < 0 ? 'text-danger' : '' ?>"><?= format_money($totals[$code], NULL, FALSE) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>صندوق</th><?php foreach ($currencies as $l): ?><th class="text-start"><?= html_escape($l) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="<?= 1 + count($currencies) ?>" class="text-center text-muted py-3">صندوقی تعریف نشده</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= html_escape($r->name) ?></td>
                    <?php foreach ($currencies as $code => $l): $f = 'bal_' . $code; ?>
                        <td class="num text-start"><?= format_money($r->$f, NULL, FALSE) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
