<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$u = isset($current_user) ? $current_user : NULL;
$initial = ($u && ! empty($u->name)) ? mb_substr($u->name, 0, 1, 'UTF-8') : '?';
?>
<header class="app-topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="topbar-toggle" data-sidebar-toggle aria-label="منو">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="app-topbar__title"><?= isset($page_title) ? html_escape($page_title) : 'داشبورد' ?></h1>
    </div>

    <div class="app-topbar__actions">
        <?php if ($u): ?>
            <div class="user-chip">
                <div class="user-chip__avatar"><?= html_escape($initial) ?></div>
                <div class="d-none d-sm-block">
                    <div class="user-chip__name"><?= html_escape($u->name) ?></div>
                    <div class="user-chip__role"><?= html_escape(isset($u->role_name) ? $u->role_name : '') ?></div>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="btn btn-glass btn-sm" title="خروج">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        <?php endif; ?>
    </div>
</header>
