<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$account_types = ACCOUNT_TYPES;
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <div>
        <h2 class="h4 mb-0">رسیدها</h2>
        <p class="text-secondary mb-0 small">ثبت دستی بدهکار / بستانکار روی حساب‌ها (دفتر کل)</p>
    </div>
    <button type="button" class="btn btn-primary" data-crud-create>
        <i class="fa-solid fa-plus ms-1"></i> ثبت رسید
    </button>
</div>

<div class="glass-card glass-table-wrap" data-aos="fade-up">
    <table class="table glass-table datatable align-middle" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>تاریخ</th>
                <th>حساب</th>
                <th>بدهکار</th>
                <th>بستانکار</th>
                <th>یادداشت</th>
                <th class="text-start">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receipts as $r): ?>
                <tr>
                    <td class="num"><?= (int) $r->id ?></td>
                    <td class="num"><?= jalali_date($r->date) ?></td>
                    <td>
                        <?= html_escape($r->account_name ?: '—') ?>
                        <?php if ($r->account_type): ?>
                            <span class="badge-soft badge-info"><?= html_escape(isset($account_types[$r->account_type]) ? $account_types[$r->account_type] : $r->account_type) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="num text-success">
                        <?= bc_compare($r->debit, '0') > 0 ? format_money($r->debit, $r->currency) : '—' ?>
                    </td>
                    <td class="num text-danger">
                        <?= bc_compare($r->credit, '0') > 0 ? format_money($r->credit, $r->currency) : '—' ?>
                    </td>
                    <td class="text-secondary small"><?= $r->note ? html_escape($r->note) : '—' ?></td>
                    <td class="text-start">
                        <button type="button" class="btn btn-sm btn-glass" data-crud-edit data-id="<?= (int) $r->id ?>" title="ویرایش">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <?= form_open('receipts/delete/' . (int) $r->id, array(
                            'class'    => 'd-inline',
                            'onsubmit' => "return confirm('آیا از حذف این رسید مطمئن هستید؟ ثبت دفتر کل برگردانده می‌شود.');"
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
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <form data-crud-form
                  data-create-url="<?= base_url('receipts/store') ?>"
                  data-update-url-base="<?= base_url('receipts/update') ?>"
                  data-get-url-base="<?= base_url('receipts/get') ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title" data-crud-title data-create-text="ثبت رسید" data-edit-text="ویرایش رسید">ثبت رسید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="rc-account">حساب</label>
                        <select class="form-select select2" id="rc-account" name="account_id" data-placeholder="انتخاب حساب" required>
                            <option value=""></option>
                            <?php foreach ($accounts as $a): ?>
                                <option value="<?= (int) $a->id ?>"><?= html_escape($a->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="rc-direction">نوع</label>
                            <select class="form-select select2" id="rc-direction" name="direction" required>
                                <option value="debit">بدهکار (واریز)</option>
                                <option value="credit">بستانکار (برداشت)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rc-currency">ارز</label>
                            <select class="form-select select2" id="rc-currency" name="currency" required>
                                <?php foreach ($currencies as $code => $label): ?>
                                    <option value="<?= html_escape($code) ?>"><?= html_escape($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-0">
                        <div class="col-6">
                            <label class="form-label" for="rc-amount">مبلغ</label>
                            <input type="number" step="0.01" min="0" class="form-control num" id="rc-amount" name="amount" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rc-date">تاریخ</label>
                            <input type="text" class="form-control num" id="rc-date" name="date"
                                   data-jdp data-jdp-only-date placeholder="۱۴۰۴/۰۱/۰۱" required>
                        </div>
                    </div>
                    <div class="mb-1 mt-3">
                        <label class="form-label" for="rc-note">یادداشت</label>
                        <input type="text" class="form-control" id="rc-note" name="note">
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
