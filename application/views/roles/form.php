<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = ! empty($role);
$name_val = set_value('name', $is_edit ? $role->name : '');
?>

<div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
    <h2 class="h4 mb-0"><?= $is_edit ? 'ویرایش نقش' : 'افزودن نقش' ?></h2>
    <a href="<?= base_url('roles') ?>" class="btn btn-glass">
        <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت
    </a>
</div>

<?= form_open($form_action, array('id' => 'role-form')) ?>
<div class="row g-3">
    <div class="col-12 col-lg-4" data-aos="fade-up">
        <div class="glass-card p-4 h-100">
            <h3 class="h6 mb-3">اطلاعات نقش</h3>
            <div class="mb-3">
                <label class="form-label" for="name">نام نقش</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= html_escape($name_val) ?>" required autofocus>
                <?= form_error('name') ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8" data-aos="fade-up">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 mb-0">دسترسی‌ها</h3>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggle-all">
                    <label class="form-check-label small" for="toggle-all">انتخاب همه</label>
                </div>
            </div>
            <div class="row g-2">
                <?php foreach ($permissions as $p): ?>
                    <?php $checked = in_array((int) $p->id, $assigned, TRUE); ?>
                    <div class="col-12 col-sm-6">
                        <label class="d-flex align-items-center gap-2 p-2 rounded glass-panel--subtle"
                               style="cursor:pointer">
                            <input class="form-check-input perm-check m-0" type="checkbox"
                                   name="permissions[]" value="<?= (int) $p->id ?>"
                                   <?= $checked ? 'checked' : '' ?>>
                            <span><?= html_escape($p->label) ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2" data-aos="fade-up">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk ms-1"></i> ذخیره
    </button>
    <a href="<?= base_url('roles') ?>" class="btn btn-glass">انصراف</a>
</div>
<?= form_close() ?>

<script>
(function () {
    var toggle = document.getElementById('toggle-all');
    var checks = Array.prototype.slice.call(document.querySelectorAll('.perm-check'));
    function syncToggle() {
        toggle.checked = checks.length > 0 && checks.every(function (c) { return c.checked; });
    }
    if (toggle) {
        toggle.addEventListener('change', function () {
            checks.forEach(function (c) { c.checked = toggle.checked; });
        });
        checks.forEach(function (c) { c.addEventListener('change', syncToggle); });
        syncToggle();
    }
})();
</script>
