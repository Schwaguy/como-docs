<?php
defined('ABSPATH') || exit;

$post_id = get_the_ID();

if (!$post_id) {
    return;
}

/**
 * Read document metadata safely.
 * WordPress post meta may return strings, arrays, or other values.
 */
$meta = array();

$meta_keys = array(
    'comodoc-author',
    'comodoc-event',
    'comodoc-date',
    'comodoc-publication',
    'comodoc-abstract',
    'comodoc-volume',
    'comodoc-page-start',
    'comodoc-page-end',
    'comodoc-doi',
    'comodoc-link',
    'comodoc-file-id',
);

foreach ($meta_keys as $key) {
    $value = get_post_meta($post_id, $key, false);
    $value = isset($value[0]) && is_scalar($value[0])
        ? trim((string) $value[0])
        : '';

    $meta[$key] = $value;
}

/**
 * Validate DOI before creating its resolver URL.
 */
$doi = preg_replace(
    '~^https?://(?:dx\.)?doi\.org/~i',
    '',
    $meta['comodoc-doi']
);

$doi = preg_replace('~^doi:\s*~i', '', trim($doi));

$doi_valid = (
    $doi !== ''
    && (bool) preg_match(
        '~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i',
        $doi
    )
);

$doi_url = '';

if ($doi_valid) {
    $encoded_doi = implode(
        '/',
        array_map('rawurlencode', explode('/', $doi))
    );

    $doi_url = esc_url('https://doi.org/' . $encoded_doi);
}

/**
 * Validate the external read-more URL.
 */
$external_url = esc_url($meta['comodoc-link']);

/**
 * Only link to a real WordPress attachment.
 */
$attachment_url = '';
$file_id = absint($meta['comodoc-file-id']);

if ($file_id && get_post_type($file_id) === 'attachment') {
    $url = wp_get_attachment_url($file_id);

    if ($url) {
        $attachment_url = esc_url($url);
    }
}

$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
?>

<article
    itemscope
    itemtype="https://schema.org/ScholarlyArticle"
    class="loop-item news-item animated fadeIn"
>
    <header>
        <h2 class="entry-title">
            <a href="<?php echo esc_url($permalink); ?>">
                <span class="title" itemprop="name">
                    <?php echo esc_html($title); ?>
                </span>
            </a>
        </h2>
    </header>

    <div class="entry-summary">
        <p>
            <?php if ($meta['comodoc-author'] !== '') : ?>
                <span class="author" itemprop="author"><?php
                    echo esc_html($meta['comodoc-author']);
                ?></span>.
            <?php endif; ?>

            <?php if ($meta['comodoc-event'] !== '') : ?>
                <span class="event" itemprop="event"><?php
                    echo esc_html($meta['comodoc-event']);

                    if ($meta['comodoc-date'] !== '') {
                        echo ' ' . esc_html($meta['comodoc-date']);
                    }
                ?></span>.
            <?php endif; ?>

            <?php if ($meta['comodoc-publication'] !== '') : ?>
                <span itemscope itemtype="https://schema.org/Periodical">
                    <span class="pubName" itemprop="name"><?php
                        echo esc_html($meta['comodoc-publication']);
                    ?></span>
                </span>
                <?php
                if ($meta['comodoc-date'] !== '') {
                    echo ' ' . esc_html($meta['comodoc-date']);
                }
                ?>
            <?php endif; ?>

            <?php if ($meta['comodoc-abstract'] !== '') : ?>
                <span class="abstract" itemprop="abstract"><?php
                    echo esc_html($meta['comodoc-abstract']);
                ?></span>.
            <?php endif; ?>

            <?php if ($meta['comodoc-volume'] !== '') : ?>
                <span
                    itemprop="isPartOf"
                    itemscope
                    itemtype="https://schema.org/PublicationVolume"
                >
                    <span class="volume" itemprop="volumeNumber"><?php
                        echo esc_html($meta['comodoc-volume']);
                    ?></span>

                    <?php if ($meta['comodoc-page-start'] !== '') : ?>
                        <span class="pages" itemprop="pageStart"><?php
                            echo esc_html($meta['comodoc-page-start']);
                        ?></span>

                        <?php if ($meta['comodoc-page-end'] !== '') : ?>
                            -<span class="pages" itemprop="pageEnd"><?php
                                echo esc_html($meta['comodoc-page-end']);
                            ?></span>
                        <?php endif; ?>

                        .
                    <?php endif; ?>
                </span>
            <?php endif; ?>

            <?php if ($doi_url !== '') : ?>
                DOI:
                <a
                    itemprop="sameAs"
                    href="<?php echo $doi_url; ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="<?php echo esc_attr($title); ?>"
                ><?php echo esc_html($doi); ?></a>.
            <?php endif; ?>
        </p>

        <p>
            <?php if ($external_url !== '') : ?>
                <a
                    class="read-more"
                    href="<?php echo $external_url; ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    itemprop="url"
                >Read More &gt;</a>
            <?php endif; ?>

            <?php if ($attachment_url !== '') : ?>
                <a
                    class="read-more"
                    href="<?php echo $attachment_url; ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    itemprop="contentUrl"
                >Read More &gt;</a>
            <?php endif; ?>
        </p>
    </div>
</article>