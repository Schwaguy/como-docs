<?php
defined('ABSPATH') || exit;
?>

<ul class="pagination">
    <?php if (isset($paginate) && is_iterable($paginate)) : ?>
        <?php foreach ($paginate as $page) : ?>
            <li><?php echo esc_html((string) $page); ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>