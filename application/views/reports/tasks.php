<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">گزارش وظایف</h2>
    <a href="<?= base_url('reports') ?>" class="btn btn-glass"><i class="fa-solid fa-arrow-right ms-1"></i> گزارشات</a>
</div>

<?php $this->load->view('_partials/date_filter', array('date_from' => $date_from, 'date_to' => $date_to)); ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3" data-aos="fade-up">
        <div class="glass-card stat-card"><div class="stat-card__label">کل وظایف</div><div class="stat-card__value num"><?= (int) $summary['total_tasks'] ?></div></div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up">
        <div class="glass-card stat-card"><div class="stat-card__label">کل پاسپورت‌ها</div><div class="stat-card__value num"><?= (int) $summary['passports'] ?></div></div>
    </div>
    <?php foreach ($statuses as $k => $label): ?>
        <div class="col-6 col-md-2" data-aos="fade-up">
            <div class="glass-card stat-card"><div class="stat-card__label"><?= $label ?></div><div class="stat-card__value num"><?= isset($summary['by_status'][$k]) ? (int) $summary['by_status'][$k] : 0 ?></div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6" data-aos="fade-up">
        <div class="glass-card p-4">
            <h3 class="h6 mb-3">جمع فیس مشتری (هر ارز)</h3>
            <?php foreach ($currencies as $code => $label): ?>
                <div class="d-flex justify-content-between border-bottom py-1"><span class="text-secondary small"><?= $label ?></span><span class="num"><?= format_money(isset($summary['fees'][$code]) ? $summary['fees'][$code] : '0', NULL, FALSE) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-12 col-md-6" data-aos="fade-up">
        <div class="glass-card p-4">
            <h3 class="h6 mb-3">جمع هزینه فروشنده (هر ارز)</h3>
            <?php foreach ($currencies as $code => $label): ?>
                <div class="d-flex justify-content-between border-bottom py-1"><span class="text-secondary small"><?= $label ?></span><span class="num"><?= format_money(isset($summary['costs'][$code]) ? $summary['costs'][$code] : '0', NULL, FALSE) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
