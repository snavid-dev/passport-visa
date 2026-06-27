<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $heading; ?></title>
<style type="text/css">
  body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px;
    font-family:'Vazirmatn',-apple-system,'Segoe UI',Tahoma,sans-serif; direction:rtl;
    background:linear-gradient(135deg,#f0f4ff,#e8ecf8 50%,#f4f0ff); color:#1e1b4b; }
  .card { width:min(100%,460px); background:rgba(255,255,255,.8); backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.6); border-radius:24px; padding:36px 32px; text-align:center;
    box-shadow:0 8px 32px rgba(31,38,135,.12); }
  .icon { width:64px;height:64px;margin:0 auto 18px;border-radius:18px;display:grid;place-items:center;
    background:linear-gradient(135deg,#f43f5e,#fb7185); color:#fff;font-size:30px; }
  h1 { font-size:20px;margin:0 0 10px; }
  p,div { color:#6b7280;line-height:1.8;font-size:14px; }
  a { display:inline-block;margin-top:20px;padding:10px 22px;border-radius:14px;text-decoration:none;color:#fff;
    background:linear-gradient(135deg,#4f46e5,#818cf8); font-weight:600; }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">!</div>
    <h1><?php echo $heading; ?></h1>
    <div><?php echo $message; ?></div>
    <a href="<?php echo function_exists('base_url') ? base_url() : '/'; ?>">بازگشت به خانه</a>
  </div>
</body>
</html>
