<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">گزارش فروشندگان</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to)); ?>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>فروشنده</th><th>ارز</th><th class="text-start">هزینه</th><th class="text-start">پرداخت‌شده</th><th class="text-start">باقی‌مانده</th></tr></thead>
        <tbody>
            <?php if (empty($map)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">داده‌ای نیست</td></tr>
            <?php else: foreach ($map as $acc): $first = TRUE; ?>
                <?php foreach ($acc['cur'] as $cur => $d): $rem = bc_subtract($d['billed'], $d['paid']); ?>
                    <tr>
                        <td><?= $first ? html_escape($acc['name']) : '' ?></td>
                        <td><?= html_escape($currencies[$cur]) ?></td>
                        <td class="num text-start"><?= format_money($d['billed'], NULL, FALSE) ?></td>
                        <td class="num text-start text-success"><?= format_money($d['paid'], NULL, FALSE) ?></td>
                        <td class="num text-start fw-bold <?= bc_compare($rem, '0') > 0 ? 'text-danger' : '' ?>"><?= format_money($rem, NULL, FALSE) ?></td>
                    </tr>
                <?php $first = FALSE; endforeach; ?>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
