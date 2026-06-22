<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">نقش‌ها و دسترسی‌ها</h2>
        <p class="text-secondary mb-0 small">مدیریت نقش‌های کاربری و سطوح دسترسی</p>
    </div>
    <a href="<?= base_url('roles/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus ms-1"></i> افزودن نقش
    </a>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>نام نقش</th>
                <th>تعداد دسترسی‌ها</th>
                <th>تعداد کاربران</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $r): ?>
                <tr>
                    <td class="num"><?= (int) $r->id ?></td>
                    <td><?= html_escape($r->name) ?></td>
                    <td><span class="badge-soft badge-info num"><?= (int) $r->permission_count ?></span></td>
                    <td><span class="badge-soft badge-success num"><?= (int) $r->user_count ?></span></td>
                    <td class="text-start">
                        <a href="<?= base_url('roles/edit/' . (int) $r->id) ?>"
                           class="btn btn-sm btn-glass" title="ویرایش">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <?php if ((int) $r->user_count === 0): ?>
                            <?= form_open('roles/delete/' . (int) $r->id, array(
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('آیا از حذف این نقش مطمئن هستید؟');"
                            )) ?>
                                <button type="submit" class="btn btn-sm btn-glass text-danger" title="حذف">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <button class="btn btn-sm btn-glass text-muted" disabled
                                    title="نقش در حال استفاده است">
                                <i class="fa-solid fa-lock"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
