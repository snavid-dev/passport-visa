<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$types = ACCOUNT_TYPES;
$grouped = array();
foreach ($rows as $r) { $grouped[$r->type][] = $r; }
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">گزارش ترازنامه</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>
<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>حساب</th><?php foreach ($currencies as $l): ?><th class="text-start"><?= html_escape($l) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
            <?php foreach ($types as $type => $tlabel): if (empty($grouped[$type])) continue; ?>
                <tr><td colspan="<?= 1 + count($currencies) ?>" class="fw-bold text-secondary" style="background:rgba(79,70,229,0.05)"><?= html_escape($tlabel) ?></td></tr>
                <?php foreach ($grouped[$type] as $r): ?>
                    <tr>
                        <td><?= html_escape($r->name) ?></td>
                        <?php foreach ($currencies as $code => $l): $f = 'bal_' . $code; ?>
                            <td class="num text-start <?= bc_compare($r->$f, '0') < 0 ? 'text-danger' : '' ?>"><?= format_money($r->$f, NULL, FALSE) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot><tr class="fw-bold" style="border-top:2px solid var(--accent-light)"><td>جمع کل</td>
            <?php foreach ($currencies as $code => $l): ?><td class="num text-start"><?= format_money($totals[$code], NULL, FALSE) ?></td><?php endforeach; ?>
        </tr></tfoot>
    </table>
</div>
