<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">کاربران</h2>
        <p class="text-secondary mb-0 small">مدیریت کاربران سیستم</p>
    </div>
    <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> افزودن کاربر
    </a>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>نام</th>
                <th>نام کاربری</th>
                <th>نقش</th>
                <th>تماس</th>
                <th>وضعیت</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td class="num"><?= (int) $u->id ?></td>
                    <td><?= html_escape($u->name) ?></td>
                    <td><span class="num"><?= html_escape($u->username) ?></span></td>
                    <td><?= html_escape($u->role_name ?: '—') ?></td>
                    <td class="small text-secondary">
                        <?php if ($u->email): ?><div><?= html_escape($u->email) ?></div><?php endif; ?>
                        <?php if ($u->phone): ?><div class="num"><?= html_escape($u->phone) ?></div><?php endif; ?>
                        <?php if (! $u->email && ! $u->phone): ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int) $u->active === 1): ?>
                            <span class="badge-soft badge-success">فعال</span>
                        <?php else: ?>
                            <span class="badge-soft badge-error">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <a href="<?= base_url('users/edit/' . (int) $u->id) ?>"
                           class="btn btn-sm btn-glass" title="ویرایش">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <?php if ((int) $u->id !== (int) $current_user->id): ?>
                            <?= form_open('users/delete/' . (int) $u->id, array(
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('آیا از حذف این کاربر مطمئن هستید؟');"
                            )) ?>
                                <button type="submit" class="btn btn-sm btn-glass text-danger" title="حذف">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <button class="btn btn-sm btn-glass text-muted" disabled title="حساب شما">
                                <i class="fa-solid fa-user-check"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
