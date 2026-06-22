<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pagination partial — renders CI3 Pagination library output.
 * Expects $pagination_links (string) produced by $this->pagination->create_links().
 */
if (! empty($pagination_links)): ?>
<nav class="d-flex justify-content-center mt-3" aria-label="صفحه‌بندی">
    <div class="app-pagination">
        <?= $pagination_links ?>
    </div>
</nav>
<?php endif; ?>
