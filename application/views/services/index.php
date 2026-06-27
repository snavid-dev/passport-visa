<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">خدمات</h2>
        <p class="text-secondary mb-0 small">انواع خدمات ویزا و هزینه پیش‌فرض هر پاسپورت</p>
    </div>
    <button type="button" class="btn btn-primary" data-crud-create>
        <i class="fa-solid fa-plus me-1"></i> افزودن خدمت
    </button>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>نام خدمت</th>
                <th>نوع ویزا</th>
                <th>هزینه پیش‌فرض</th>
                <th>وضعیت</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td class="num"><?= (int) $s->id ?></td>
                    <td><?= html_escape($s->name) ?></td>
                    <td class="text-secondary"><?= $s->visa_type ? html_escape($s->visa_type) : '—' ?></td>
                    <td class="num"><?= format_money($s->default_fee, $s->default_currency) ?></td>
                    <td>
                        <?php if ((int) $s->active === 1): ?>
                            <span class="badge-soft badge-success">فعال</span>
                        <?php else: ?>
                            <span class="badge-soft badge-error">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <button type="button" class="btn btn-sm btn-glass" data-crud-edit data-id="<?= (int) $s->id ?>" title="ویرایش">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <?= form_open('services/delete/' . (int) $s->id, array(
                            'class'    => 'd-inline',
                            'onsubmit' => "return confirm('آیا از حذف این خدمت مطمئن هستید؟');"
                        )) ?>
                            <button type="submit" class="btn btn-sm btn-glass text-danger" title="حذف">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        <?= form_close() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create / Edit modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <form data-crud-form
                  data-create-url="<?= base_url('services/store') ?>"
                  data-update-url-base="<?= base_url('services/update') ?>"
                  data-get-url-base="<?= base_url('services/get') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title" data-crud-title data-create-text="افزودن خدمت" data-edit-text="ویرایش خدمت">افزودن خدمت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="svc-name">نام خدمت</label>
                        <input type="text" class="form-control" id="svc-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="svc-visa">نوع ویزا</label>
                        <input type="text" class="form-control" id="svc-visa" name="visa_type" placeholder="مثلاً توریستی، زیارتی">
                    </div>
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label" for="svc-fee">هزینه پیش‌فرض</label>
                            <input type="number" step="0.01" min="0" class="form-control num" id="svc-fee" name="default_fee" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label" for="svc-cur">ارز</label>
                            <select class="form-select select2" id="svc-cur" name="default_currency" required>
                                <?php foreach ($currencies as $code => $label): ?>
                                    <option value="<?= html_escape($code) ?>"><?= html_escape($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="svc-active" name="active" value="1" checked>
                        <label class="form-check-label" for="svc-active">خدمت فعال است</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-glass" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> ذخیره
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
