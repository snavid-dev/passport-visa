<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Persian overrides for CodeIgniter's form validation messages.
 *
 * The app's default config language is 'english', so CI loads validation
 * strings from the 'english' language path; placing this file under
 * application/language/english/ overrides the system defaults globally,
 * keeping the entire UI Persian-only.
 *
 * %s placeholders: first = field label, second = rule parameter.
 */

$lang['form_validation_required']          = 'فیلد {field} الزامی است.';
$lang['form_validation_isset']             = 'فیلد {field} باید مقدار داشته باشد.';
$lang['form_validation_valid_email']       = 'فیلد {field} باید یک ایمیل معتبر باشد.';
$lang['form_validation_valid_emails']      = 'مقادیر فیلد {field} باید ایمیل‌های معتبر باشند.';
$lang['form_validation_valid_url']         = 'فیلد {field} باید یک آدرس معتبر باشد.';
$lang['form_validation_valid_ip']          = 'فیلد {field} باید یک IP معتبر باشد.';
$lang['form_validation_min_length']        = 'فیلد {field} باید حداقل {param} کاراکتر باشد.';
$lang['form_validation_max_length']        = 'فیلد {field} باید حداکثر {param} کاراکتر باشد.';
$lang['form_validation_exact_length']      = 'فیلد {field} باید دقیقاً {param} کاراکتر باشد.';
$lang['form_validation_alpha']             = 'فیلد {field} فقط می‌تواند شامل حروف باشد.';
$lang['form_validation_alpha_numeric']     = 'فیلد {field} فقط می‌تواند شامل حروف و اعداد باشد.';
$lang['form_validation_alpha_numeric_spaces'] = 'فیلد {field} فقط می‌تواند شامل حروف، اعداد و فاصله باشد.';
$lang['form_validation_alpha_dash']        = 'فیلد {field} فقط می‌تواند شامل حروف، اعداد، خط تیره و زیرخط باشد.';
$lang['form_validation_numeric']           = 'فیلد {field} باید عددی باشد.';
$lang['form_validation_is_numeric']        = 'فیلد {field} باید فقط شامل اعداد باشد.';
$lang['form_validation_integer']           = 'فیلد {field} باید یک عدد صحیح باشد.';
$lang['form_validation_regex_match']       = 'قالب فیلد {field} صحیح نیست.';
$lang['form_validation_matches']           = 'فیلد {field} با فیلد {param} مطابقت ندارد.';
$lang['form_validation_differs']           = 'فیلد {field} باید با فیلد {param} متفاوت باشد.';
$lang['form_validation_is_unique']         = 'مقدار فیلد {field} باید یکتا باشد.';
$lang['form_validation_is_natural']        = 'فیلد {field} باید فقط شامل اعداد باشد.';
$lang['form_validation_is_natural_no_zero']= 'فیلد {field} باید عددی بزرگ‌تر از صفر باشد.';
$lang['form_validation_decimal']           = 'فیلد {field} باید یک عدد اعشاری باشد.';
$lang['form_validation_less_than']         = 'فیلد {field} باید کوچک‌تر از {param} باشد.';
$lang['form_validation_less_than_equal_to']= 'فیلد {field} باید کوچک‌تر یا مساوی {param} باشد.';
$lang['form_validation_greater_than']      = 'فیلد {field} باید بزرگ‌تر از {param} باشد.';
$lang['form_validation_greater_than_equal_to'] = 'فیلد {field} باید بزرگ‌تر یا مساوی {param} باشد.';
$lang['form_validation_in_list']           = 'فیلد {field} باید یکی از مقادیر مجاز باشد: {param}.';
