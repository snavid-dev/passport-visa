<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$flash_types = array(
    'success' => array('icon' => 'fa-circle-check',        'mod' => 'success'),
    'error'   => array('icon' => 'fa-circle-exclamation',  'mod' => 'error'),
    'warning' => array('icon' => 'fa-triangle-exclamation','mod' => 'warning'),
    'info'    => array('icon' => 'fa-circle-info',          'mod' => 'info'),
);

$messages = array();
foreach ($flash_types as $type => $meta) {
    $msg = $this->session->flashdata($type);
    if (! empty($msg)) {
        $messages[] = array('text' => $msg, 'meta' => $meta);
    }
}

if (! empty($messages)): ?>
<div class="alert-stack">
    <?php foreach ($messages as $m): ?>
        <div class="app-alert app-alert--<?= $m['meta']['mod'] ?>" data-autodismiss role="alert">
            <i class="fa-solid <?= $m['meta']['icon'] ?>"></i>
            <span class="flex-grow-1"><?= html_escape($m['text']) ?></span>
            <button type="button" class="btn-close btn-sm" data-dismiss-alert aria-label="بستن"></button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
