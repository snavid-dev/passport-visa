<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$reports = array(
    array('url' => 'reports/balance_sheet',    'icon' => 'fa-scale-balanced',     'title' => 'ترازنامه',          'desc' => 'مانده همه حساب‌ها در سه ارز'),
    array('url' => 'reports/account_statement','icon' => 'fa-file-invoice',        'title' => 'صورتحساب حساب',     'desc' => 'دفتر کل یک حساب در بازه زمانی'),
    array('url' => 'reports/cash_position',     'icon' => 'fa-sack-dollar',         'title' => 'وضعیت نقدینگی',     'desc' => 'موجودی صندوق‌ها در هر ارز'),
    array('url' => 'reports/income_expense',    'icon' => 'fa-arrow-right-arrow-left','title' => 'درآمد و مصارف',   'desc' => 'مقایسه درآمد و مصرف در هر ارز'),
    array('url' => 'reports/tasks',             'icon' => 'fa-list-check',          'title' => 'گزارش وظایف',       'desc' => 'تعداد و مبالغ وظایف'),
    array('url' => 'reports/profit',            'icon' => 'fa-chart-line',          'title' => 'گزارش سود',         'desc' => 'سود (فیس منهای هزینه، هم‌ارز)'),
    array('url' => 'reports/outstanding',       'icon' => 'fa-triangle-exclamation','title' => 'مانده‌های معوق',    'desc' => 'بدهی‌های پرداخت‌نشده هر وظیفه'),
    array('url' => 'reports/client',            'icon' => 'fa-user-tie',            'title' => 'گزارش مشتریان',     'desc' => 'صورتحساب و پرداخت هر مشتری'),
    array('url' => 'reports/vendor',            'icon' => 'fa-building',            'title' => 'گزارش فروشندگان',   'desc' => 'هزینه و پرداخت هر فروشنده'),
    array('url' => 'reports/volume',            'icon' => 'fa-passport',            'title' => 'حجم پاسپورت',       'desc' => 'تعداد بر اساس نوع ویزا'),
);
?>

<div class="mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0">گزارشات</h2>
    <p class="text-secondary mb-0 small">ده گزارش مالی و عملیاتی</p>
</div>

<div class="row g-3">
    <?php foreach ($reports as $rep): ?>
        <div class="col-12 col-sm-6 col-lg-4" data-aos="fade-up">
            <a href="<?= base_url($rep['url']) ?>" class="text-decoration-none">
                <div class="glass-card p-4 h-100 d-flex align-items-start gap-3">
                    <div class="stat-card__icon" style="background:linear-gradient(135deg,var(--accent),var(--accent-light));flex:0 0 auto">
                        <i class="fa-solid <?= $rep['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= $rep['title'] ?></div>
                        <div class="text-secondary small"><?= $rep['desc'] ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
