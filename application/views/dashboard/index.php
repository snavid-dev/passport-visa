<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="mb-4" data-aos="fade-up">
    <h2 class="mb-1">سلام، <?= html_escape($current_user->name) ?> 👋</h2>
    <p class="text-secondary mb-0">به سیستم پردازش ویزای ایران خوش آمدید — امروز <?= jalali_today('l، j F Y') ?></p>
</div>

<?php if (! empty($cards)): ?>
<div class="row g-3 mb-4">
    <?php foreach ($cards as $c): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= base_url($c['link']) ?>" class="text-decoration-none">
                <div class="glass-card stat-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-card__label"><?= $c['label'] ?></div>
                            <div class="stat-card__value num"><?= $c['value'] ?></div>
                        </div>
                        <div class="stat-card__icon" style="background: <?= $c['grad'] ?>;">
                            <i class="fa-solid <?= $c['icon'] ?>"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="glass-card p-4" data-aos="fade-up">
    <h3 class="h5 mb-2"><i class="fa-solid fa-rocket text-primary ms-1"></i> سیستم آماده است</h3>
    <p class="text-secondary mb-0">
        زیرساخت پایه (احراز هویت، نقش‌ها و دسترسی‌ها، طرح شیشه‌ای و انیمیشن‌ها) با موفقیت راه‌اندازی شد.
        ماژول‌های وظایف، حساب‌ها، خدمات، رسیدها و گزارشات در مراحل بعدی اضافه می‌شوند.
    </p>
</div>
