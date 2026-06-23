<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$total_tasks = 0; $total_pass = 0;
foreach ($rows as $r) { $total_tasks += (int) $r->task_count; $total_pass += (int) $r->passport_count; }
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">حجم پاسپورت / نوع ویزا</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to)); ?>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table align-middle" style="width:100%">
        <thead><tr><th>نوع ویزا</th><th class="text-start">تعداد وظایف</th><th class="text-start">تعداد پاسپورت</th></tr></thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">داده‌ای نیست</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= html_escape($r->visa_type) ?></td>
                    <td class="num text-start"><?= (int) $r->task_count ?></td>
                    <td class="num text-start fw-bold"><?= (int) $r->passport_count ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr class="fw-bold" style="border-top:2px solid var(--accent-light)"><td>جمع کل</td><td class="num text-start"><?= $total_tasks ?></td><td class="num text-start"><?= $total_pass ?></td></tr></tfoot>
    </table>
</div>
