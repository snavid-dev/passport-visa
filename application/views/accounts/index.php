<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$type_badge = array(
    'client'     => 'badge-info',
    'vendor'     => 'badge-warning',
    'expense'    => 'badge-error',
    'income'     => 'badge-success',
    'individual' => 'badge-info',
    'cash'       => 'badge-success',
);
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">حساب‌ها</h2>
        <p class="text-secondary mb-0 small">مدیریت حساب‌های مالی (مشتری، فروشنده، مصرف، درآمد، شخص، صندوق)</p>
    </div>
    <button type="button" class="btn btn-primary" data-crud-create>
        <i class="fa-solid fa-plus ms-1"></i> افزودن حساب
    </button>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>نام حساب</th>
                <th>نوع</th>
                <th>تلفن</th>
                <th>وضعیت</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $a): ?>
                <tr>
                    <td class="num"><?= (int) $a->id ?></td>
                    <td><?= html_escape($a->name) ?></td>
                    <td>
                        <span class="badge-soft <?= isset($type_badge[$a->type]) ? $type_badge[$a->type] : 'badge-info' ?>">
                            <?= html_escape(isset($account_types[$a->type]) ? $account_types[$a->type] : $a->type) ?>
                        </span>
                    </td>
                    <td class="num text-secondary"><?= $a->phone ? html_escape($a->phone) : '—' ?></td>
                    <td>
                        <?php if ((int) $a->active === 1): ?>
                            <span class="badge-soft badge-success">فعال</span>
                        <?php else: ?>
                            <span class="badge-soft badge-error">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <button type="button" class="btn btn-sm btn-glass" data-crud-edit data-id="<?= (int) $a->id ?>" title="ویرایش">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <?php if ($a->type !== 'cash'): ?>
                            <?= form_open('accounts/delete/' . (int) $a->id, array(
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('آیا از حذف این حساب مطمئن هستید؟');"
                            )) ?>
                                <button type="submit" class="btn btn-sm btn-glass text-danger" title="حذف">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            <?= form_close() ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create / Edit modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <form data-crud-form
                  data-create-url="<?= base_url('accounts/store') ?>"
                  data-update-url-base="<?= base_url('accounts/update') ?>"
                  data-get-url-base="<?= base_url('accounts/get') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title" data-crud-title data-create-text="افزودن حساب" data-edit-text="ویرایش حساب">افزودن حساب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="acc-name">نام حساب</label>
                        <input type="text" class="form-control" id="acc-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="acc-type">نوع حساب</label>
                        <select class="form-select select2" id="acc-type" name="type" data-placeholder="انتخاب نوع" required>
                            <option value=""></option>
                            <?php foreach ($account_types as $key => $label): ?>
                                <option value="<?= html_escape($key) ?>"><?= html_escape($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="acc-phone">تلفن</label>
                        <input type="text" class="form-control num" id="acc-phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="acc-note">یادداشت</label>
                        <input type="text" class="form-control" id="acc-note" name="note">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="acc-active" name="active" value="1" checked>
                        <label class="form-check-label" for="acc-active">حساب فعال است</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-glass" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk ms-1"></i> ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
