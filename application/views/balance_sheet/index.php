<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Group rows by account type.
$grouped = array();
foreach ($rows as $r) { $grouped[$r->type][] = $r; }
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">ترازنامه</h2>
        <p class="text-secondary mb-0 small">مانده هر حساب در سه ارز مستقل (بدون تبدیل)</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($currencies as $code => $label): $field = 'bal_' . $code; ?>
        <div class="col-12 col-md-4" data-aos="fade-up">
            <div class="glass-card stat-card">
                <div class="stat-card__label">جمع کل — <?= html_escape($label) ?></div>
                <div class="stat-card__value num"><?= format_money($totals[$code], NULL, FALSE) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead>
            <tr>
                <th>حساب</th>
                <?php foreach ($currencies as $label): ?><th class="text-start"><?= html_escape($label) ?></th><?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($account_types as $type => $type_label): ?>
                <?php if (empty($grouped[$type])) continue; ?>
                <tr class="table-group">
                    <td colspan="<?= 1 + count($currencies) ?>" class="fw-bold text-secondary" style="background:rgba(79,70,229,0.05)">
                        <?= html_escape($type_label) ?>
                    </td>
                </tr>
                <?php foreach ($grouped[$type] as $r): ?>
                    <tr>
                        <td><?= html_escape($r->name) ?></td>
                        <?php foreach ($currencies as $code => $label): $field = 'bal_' . $code; $val = $r->$field; ?>
                            <td class="num text-start <?= bc_compare($val, '0') < 0 ? 'text-danger' : '' ?>">
                                <?= format_money($val, NULL, FALSE) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="border-top:2px solid var(--accent-light)">
                <td>جمع کل</td>
                <?php foreach ($currencies as $code => $label): ?>
                    <td class="num text-start"><?= format_money($totals[$code], NULL, FALSE) ?></td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>
</div>
