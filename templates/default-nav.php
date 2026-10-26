<?php
defined('ABSPATH') || exit;

$per_page = isset($a['per_page']) ? absint($a['per_page']) : 20;
$per_page = max(1, min(100, $per_page));

$text_loading = isset($a['text-loading']) && is_scalar($a['text-loading'])
    ? (string) $a['text-loading']
    : 'Loading';

$text_results = isset($a['text-results']) && is_scalar($a['text-results'])
    ? (string) $a['text-results']
    : 'Results';

$list_template = isset($a['list-template']) && is_scalar($a['list-template'])
    ? sanitize_file_name((string) $a['list-template'])
    : '';

$pagination_template = isset($a['pagination-template']) && is_scalar($a['pagination-template'])
    ? sanitize_file_name((string) $a['pagination-template'])
    : '';

$active_term = isset($a['active']) ? absint($a['active']) : 0;
?>

<div
    id="container-comodocs-async"
    data-paged="<?php echo esc_attr((string) $per_page); ?>"
    data-loading="<?php echo esc_attr($text_loading); ?>"
    data-results="<?php echo esc_attr($text_results); ?>"
    data-list-template="<?php echo esc_attr($list_template); ?>"
    data-pagination-template="<?php echo esc_attr($pagination_template); ?>"
    class="sc-ajax-filter"
>
    <ul class="nav-filter">
        <li
            data-loading="<?php echo esc_attr($text_loading . ' All'); ?>"
            data-results="<?php echo esc_attr($text_results); ?>"
            class="active"
        >
            <a
                href="#"
                data-filter="all-terms"
                data-term="all-terms"
                data-page="1"
                class="active"
                aria-selected="true"
            >All</a>
        </li>

        <?php if (isset($terms) && is_iterable($terms)) : ?>
            <?php foreach ($terms as $term) : ?>
                <?php
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $term_link = get_term_link($term, $term->taxonomy);

                if (is_wp_error($term_link)) {
                    $term_link = '#';
                }

                $term_name = $term->name;
                $term_slug = $term->slug;
                $term_taxonomy = $term->taxonomy;
                $is_active = ((int) $term->term_id === $active_term);
                ?>

                <li
                    <?php if ($is_active) : ?>class="active"<?php endif; ?>
                    data-loading="<?php echo esc_attr($text_loading . ' ' . $term_name); ?>"
                    data-results="<?php echo esc_attr($text_results . ' ' . $term_name); ?>"
                >
                    <a
                        href="<?php echo esc_url($term_link); ?>"
                        data-filter="<?php echo esc_attr($term_taxonomy); ?>"
                        data-term="<?php echo esc_attr($term_slug); ?>"
                        data-page="1"
                        data-title="<?php echo esc_attr($term_name); ?>"
                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                        <?php if ($is_active) : ?>class="active"<?php endif; ?>
                    ><?php echo esc_html($term_name); ?></a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <p class="status" aria-live="polite"></p>
    <div class="content"></div>
</div>