<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">درآمد و مصارف</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right me-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to)); ?>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>ارز</th><th class="text-start">درآمد</th><th class="text-start">مصارف</th><th class="text-start">خالص</th></tr></thead>
        <tbody>
            <?php foreach ($currencies as $code => $label): $d = $data[$code]; $net = bc_subtract($d['income'], $d['expense']); ?>
                <tr>
                    <td><?= html_escape($label) ?></td>
                    <td class="num text-start text-success"><?= format_money($d['income'], NULL, FALSE) ?></td>
                    <td class="num text-start text-danger"><?= format_money($d['expense'], NULL, FALSE) ?></td>
                    <td class="num text-start fw-bold <?= bc_compare($net, '0') < 0 ? 'text-danger' : '' ?>"><?= format_money($net, NULL, FALSE) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="small text-muted px-3 pb-2 mb-0">درآمد = بستانکار حساب‌های نوع «درآمد» · مصارف = بدهکار حساب‌های نوع «مصرف»</p>
</div>
