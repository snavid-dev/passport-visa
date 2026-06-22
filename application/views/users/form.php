<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($user);
$v = function ($field, $current = '') use ($user, $is_edit) {
    return set_value($field, $is_edit && isset($user->$field) ? $user->$field : $current);
};
$current_role   = $is_edit ? (int) $user->role_id : (int) set_value('role_id');
$current_active = $is_edit ? (int) $user->active : (int) set_value('active', 1);
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0"><?= $is_edit ? 'ویرایش کاربر' : 'افزودن کاربر' ?></h2>
    <a href="<?= base_url('users') ?>" class="btn btn-glass">
        <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت
    </a>
</div>

<?= form_open($form_action, array('id' => 'user-form', 'autocomplete' => 'off')) ?>
<div class="glass-card p-4" data-aos="fade-up">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label" for="name">نام و نام خانوادگی</label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= html_escape($v('name')) ?>" required autofocus>
            <?= form_error('name') ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="username">نام کاربری</label>
            <input type="text" class="form-control num" id="username" name="username"
                   value="<?= html_escape($v('username')) ?>" required>
            <?= form_error('username') ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="password">
                رمز عبور
                <?php if ($is_edit): ?><small class="text-muted">(برای عدم تغییر خالی بگذارید)</small><?php endif; ?>
            </label>
            <input type="password" class="form-control" id="password" name="password"
                   autocomplete="new-password" <?= $is_edit ? '' : 'required' ?>>
            <?= form_error('password') ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="role_id">نقش</label>
            <select class="form-select select2" id="role_id" name="role_id"
                    data-placeholder="انتخاب نقش" required>
                <option value=""></option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r->id ?>" <?= $current_role === (int) $r->id ? 'selected' : '' ?>>
                        <?= html_escape($r->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= form_error('role_id') ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="email">ایمیل</label>
            <input type="email" class="form-control num" id="email" name="email"
                   value="<?= html_escape($v('email')) ?>">
            <?= form_error('email') ?>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="phone">شماره تماس</label>
            <input type="text" class="form-control num" id="phone" name="phone"
                   value="<?= html_escape($v('phone')) ?>">
            <?= form_error('phone') ?>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                       <?= $current_active === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="active">حساب فعال است</label>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2" data-aos="fade-up">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk ms-1"></i> ذخیره
    </button>
    <a href="<?= base_url('users') ?>" class="btn btn-glass">انصراف</a>
</div>
<?= form_close() ?>
