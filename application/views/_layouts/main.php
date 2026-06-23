<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF (read by assets/js/main.js for AJAX) -->
    <meta name="csrf-name"   content="<?= html_escape($this->security->get_csrf_token_name()) ?>">
    <meta name="csrf-cookie" content="csrf_cookie">
    <meta name="csrf-hash"   content="<?= html_escape($this->security->get_csrf_hash()) ?>">

    <title><?= isset($page_title) ? html_escape($page_title) . ' — ' : '' ?>سیستم پردازش ویزای ایران</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS (CDN + SRI) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
          integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha384-PPIZEGYM1v8zp5Py7UjFb79S58UeqCL9pYVnVPURKEqvioPROaVAJKKLzvH2rDnI" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css"
          integrity="sha384-/rJKQnzOkEo+daG0jMjU1IwwY9unxt1NBw3Ef2fmOJ3PW/TfAg2KXVoWwMZQZtw9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
          integrity="sha384-OXVF05DQEe311p6ohU11NwlnX08FzMCsyoXzGOaL+83dKAb3qS17yZJxESl8YrJQ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
          integrity="sha384-IrMr0LFnIMa9H6HhC5VVqVuWNEIwspnRLKQc0SUyPj4Cy4s02DiWDZEoJOo5WNK6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css"
          integrity="sha384-ulbpmnIhSyJCRH6yLG2zh9HtW6LEZ39ZjgO1uEDyYCbISb5WXC9J9IXbQl4JA1m/" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs5@2.4.2/css/buttons.bootstrap5.min.css"
          integrity="sha384-pZ0WBkZvjXVs+cznlKxzQDazHsf8DuyL4APQE+07UDMZa9yuEGDV7rSQDJ9r1TMh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker@0.9.9/dist/jalalidatepicker.min.css"
          integrity="sha384-bIPIyn7CFYB7JsdrgqPYWBmBe/xRP4Pr+ifh2QXUadBxxzWVXwiL2dXISkNXwcaD" crossorigin="anonymous">

    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/glass-tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= isset($extra_css) ? $extra_css : '' ?>
</head>
<body>
<div class="app-shell">

    <?php $this->load->view('_partials/sidebar'); ?>
    <div class="sidebar-backdrop"></div>

    <div class="app-main">
        <?php $this->load->view('_partials/topbar'); ?>

        <main class="app-content">
            <?php $this->load->view('_partials/alerts'); ?>
            <?php $this->load->view($content_view); ?>
        </main>
    </div>
</div>

<!-- Vendor JS (CDN + SRI) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"
        integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"
        integrity="sha384-g4NTh/Iv5PPU4xPyhEWqPcwtNXOvdaDI8LLnyYfyNZOjKJeYQyjzQ9X5275eBjpt" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"
        integrity="sha384-n1AULnKdMJlK1oQCLNDL9qZsDgXtH6jRYFCpBtWFc+a9Yve0KSoMn575rk755NJZ" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
        integrity="sha384-d3UHjPdzJkZuk5H3qKYMLRyWLAQBJbby2yr2Q58hXXtAGF8RSNO9jpLDlKKPv5v3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"
        integrity="sha384-EA0TH5FL18lz8e2FL4CeUXiVOMJ3ppD6fZo3QkEgERDV68Qa6ok9LFz0xQe2NLNv" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"
        integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/dataTables.buttons.min.js"
        integrity="sha384-57StXIwAbLcC8X3+3CfIRofQ1D7rMggjdwLqpqUhBcl7m8JdVqKH6/9F0Sn9B2j5" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs5@2.4.2/js/buttons.bootstrap5.min.js"
        integrity="sha384-8Fm9OFhJ1epvcmDiJTZ2SFHHZoCp/xJ8Ld7wG7/aUwGni32fG7LIhsNTFfEtUaSv" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@majidh1/jalalidatepicker@0.9.9/dist/jalalidatepicker.min.js"
        integrity="sha384-2WXbgtBGqyoPm87f8XnLFs7M8S1qgCgWxxCDKq1E2UtWl1gP2qaKqLbLSQu0d6ge" crossorigin="anonymous"></script>

<!-- App JS -->
<script src="<?= base_url('assets/js/select2-init.js') ?>"></script>
<script src="<?= base_url('assets/js/datatables-init.js') ?>"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?= isset($extra_js) ? $extra_js : '' ?>
</body>
</html>
