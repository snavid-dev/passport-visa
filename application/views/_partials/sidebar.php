<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Active-link detection by controller class name.
$active = strtolower($this->router->fetch_class());

/**
 * Render one nav link if the user holds the permission (NULL = always shown).
 */
$nav = function ($controller, $url, $icon, $label, $perm = NULL) use ($active) {
    if ($perm !== NULL && ! $this->permission->has($perm)) {
        return;
    }
    $is = ($active === $controller) ? ' active' : '';
    echo '<a class="nav-link' . $is . '" href="' . base_url($url) . '">'
       . '<i class="fa-solid ' . $icon . '"></i><span>' . $label . '</span></a>';
};
?>
<aside class="app-sidebar">
    <div class="app-brand">
        <div class="app-brand__logo"><i class="fa-solid fa-passport"></i></div>
        <div>
            <div class="app-brand__title">پردازش ویزای ایران</div>
            <div class="app-brand__subtitle">سیستم حسابداری</div>
        </div>
    </div>

    <nav>
        <?php $nav('dashboard', 'dashboard', 'fa-gauge-high', 'داشبورد'); ?>

        <div class="nav-section-title">عملیات</div>
        <?php
        $nav('tasks',    'tasks',    'fa-list-check',     'وظایف',    'manage_tasks');
        $nav('accounts', 'accounts', 'fa-users',          'حساب‌ها',  'manage_accounts');
        $nav('services', 'services', 'fa-briefcase',      'خدمات',    'manage_services');
        $nav('receipts', 'receipts', 'fa-receipt',        'رسیدها',   'manage_receipts');
        ?>

        <div class="nav-section-title">مالی</div>
        <?php
        $nav('balance_sheet', 'balance_sheet', 'fa-scale-balanced', 'ترازنامه', 'view_balance_sheet');
        $nav('reports',       'reports',       'fa-chart-line',     'گزارشات',  'view_reports');
        ?>

        <?php if ($this->permission->has_any(array('manage_users', 'manage_roles'))): ?>
            <div class="nav-section-title">مدیریت</div>
            <?php
            $nav('users', 'users', 'fa-user-gear',      'کاربران', 'manage_users');
            $nav('roles', 'roles', 'fa-shield-halved',  'نقش‌ها',  'manage_roles');
            ?>
        <?php endif; ?>
    </nav>
</aside>
