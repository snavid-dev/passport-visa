<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="auth-card glass-card glass-panel--strong">
    <div class="auth-card__logo"><i class="fa-solid fa-passport"></i></div>
    <h1 class="auth-card__title">سیستم پردازش ویزای ایران</h1>
    <p class="auth-card__subtitle">برای ادامه وارد حساب کاربری خود شوید</p>

    <?= form_open('login', array('autocomplete' => 'off')) ?>
        <div class="mb-3">
            <label class="form-label" for="username">نام کاربری</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent"><i class="fa-solid fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= set_value('username') ?>" required autofocus>
            </div>
            <?= form_error('username') ?>
        </div>

        <div class="mb-4">
            <label class="form-label" for="password">رمز عبور</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <?= form_error('password') ?>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="fa-solid fa-arrow-left-to-bracket ms-1"></i>
            ورود
        </button>
    <?= form_close() ?>

    <div class="app-alert app-alert--info mt-4 small" style="position:static">
        <i class="fa-solid fa-circle-info"></i>
        <span class="flex-grow-1">
            اطلاعات ورود پیش‌فرض —
            نام کاربری: <strong class="num">admin</strong> ·
            رمز عبور: <strong class="num">admin@1234</strong>
        </span>
    </div>
</div>
