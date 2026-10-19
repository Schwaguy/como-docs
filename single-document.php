<?php
defined('ABSPATH') || exit;

get_header();

$doc_id = get_queried_object_id();

$doc = array(
    'id'          => $doc_id,
    'post-date'   => get_the_date('', $doc_id),
    'image'       => get_the_post_thumbnail(
        $doc_id,
        'full',
        array('class' => 'team-photo')
    ),
    'title'       => get_the_title($doc_id),
    'author'      => get_post_meta($doc_id, 'comodoc-author', true),
    'publication' => get_post_meta($doc_id, 'comodoc-publication', true),
    'event'       => get_post_meta($doc_id, 'comodoc-event', true),
    'abstract'    => get_post_meta($doc_id, 'comodoc-abstract', true),
    'volume'      => get_post_meta($doc_id, 'comodoc-volume', true),
    'number'      => get_post_meta($doc_id, 'comodoc-number', true),
    'page-start'  => get_post_meta($doc_id, 'comodoc-page-start', true),
    'page-end'    => get_post_meta($doc_id, 'comodoc-page-end', true),
    'date'        => get_post_meta($doc_id, 'comodoc-date', true),
    'doi'         => get_post_meta($doc_id, 'comodoc-doi', true),
    'file'        => get_post_meta($doc_id, 'comodoc-file', true),
    'file-id'     => absint(get_post_meta($doc_id, 'comodoc-file-id', true)),
    'file-2'      => get_post_meta($doc_id, 'comodoc-file-2', true),
    'file-id-2'   => absint(get_post_meta($doc_id, 'comodoc-file-id-2', true)),
    'link'        => get_post_meta($doc_id, 'comodoc-link', true),
);

/**
 * Normalize text metadata before escaping it for HTML output.
 * Escape at the point of output, not when storing the data.
 */
foreach (
    array(
        'author',
        'publication',
        'event',
        'abstract',
        'volume',
        'number',
        'page-start',
        'page-end',
        'date',
        'doi',
        'title',
    ) as $key
) {
    $doc[$key] = is_scalar($doc[$key])
        ? trim((string) $doc[$key])
        : '';
}

/**
 * Validate the DOI before constructing a resolver URL.
 * Accept either a bare DOI or a doi.org URL.
 */
$doi = preg_replace(
    '~^https?://(?:dx\.)?doi\.org/~i',
    '',
    $doc['doi']
);

$doi = preg_replace('~^doi:\s*~i', '', $doi);
$doi = trim($doi);

$doi_is_valid = (
    $doi !== ''
    && (bool) preg_match(
        '~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i',
        $doi
    )
);

$doi_url = '';

if ($doi_is_valid) {
    // Encode each path segment while preserving DOI separators.
    $encoded_doi = implode(
        '/',
        array_map('rawurlencode', explode('/', $doi))
    );

    $doi_url = esc_url('https://doi.org/' . $encoded_doi);
}

/**
 * Only use URLs belonging to actual WordPress attachments.
 */
$file_url = '';
$file_title = '';

if (
    $doc['file-id']
    && get_post_type($doc['file-id']) === 'attachment'
) {
    $attachment_url = wp_get_attachment_url($doc['file-id']);

    if ($attachment_url) {
        $file_url = esc_url($attachment_url);
        $file_title = get_the_title($doc['file-id']);
    }
}

/**
 * Sanitize external links for output.
 * esc_url() restricts unsafe URL schemes.
 */
$external_link = '';

if (is_string($doc['link']) && trim($doc['link']) !== '') {
    $external_link = esc_url(trim($doc['link']));
}

/**
 * Build the publication citation.
 * Escape all metadata before inserting it into HTML.
 */
$publication = '';

$publication .= '<div itemscope itemtype="https://schema.org/ScholarlyArticle"';
$publication .= ' class="news-item show-on-scroll fadeInUp-on-scroll no-repeat">';

if ($doc['author'] !== '') {
    $publication .= '<span class="author" itemprop="author">'
        . esc_html($doc['author'])
        . '</span>. ';
}

if ($doc['title'] !== '') {
    $publication .= '<span class="title" itemprop="name">'
        . esc_html($doc['title'])
        . '</span>. ';
}

if ($doc['event'] !== '') {
    $publication .= '<span class="event" itemprop="event">'
        . esc_html($doc['event']);

    if ($doc['date'] !== '') {
        $publication .= ' ' . esc_html($doc['date']);
    }

    $publication .= '</span>. ';
}

if ($doc['publication'] !== '') {
    $publication .= '<span itemscope itemtype="https://schema.org/Periodical">';
    $publication .= '<span class="pubName" itemprop="name">'
        . esc_html($doc['publication'])
        . '</span>';
    $publication .= '</span> ';

    if ($doc['date'] !== '') {
        $publication .= esc_html($doc['date']) . ' ';
    }
}

if ($doc['abstract'] !== '') {
    $publication .= '<span class="abstract" itemprop="abstract">'
        . esc_html($doc['abstract'])
        . '</span>. ';
}

if ($doc['volume'] !== '') {
    $publication .= '<span itemprop="isPartOf" itemscope ';
    $publication .= 'itemtype="https://schema.org/PublicationVolume">';

    $publication .= '<span class="volume" itemprop="volumeNumber">'
        . esc_html($doc['volume'])
        . '</span>';

    if ($doc['page-start'] !== '') {
        $publication .= ' <span class="pages" itemprop="pageStart">'
            . esc_html($doc['page-start'])
            . '</span>';

        if ($doc['page-end'] !== '') {
            $publication .= '-<span class="pages" itemprop="pageEnd">'
                . esc_html($doc['page-end'])
                . '</span>';
        }

        $publication .= '. ';
    }

    $publication .= '</span>';
}

if ($doi_url !== '') {
    $publication .= ' DOI: <a itemprop="sameAs" href="' . $doi_url . '"';
    $publication .= ' target="_blank" rel="noopener noreferrer"';
    $publication .= ' title="' . esc_attr($doc['title']) . '">';
    $publication .= esc_html($doi);
    $publication .= '</a>. ';
}

$publication .= '</div>';

if ($external_link !== '') {
    $publication .= '<p><a class="read-more" href="'
        . $external_link
        . '" target="_blank" rel="noopener noreferrer" itemprop="url">'
        . esc_html__('Read More', 'como-strap')
        . ' &gt;</a></p>';
}

if ($file_url !== '') {
    $publication .= '<p><a class="read-more" href="'
        . $file_url
        . '" target="_blank" rel="noopener noreferrer" itemprop="contentUrl">'
        . esc_html($file_title ?: __('Download document', 'como-strap'))
        . '</a></p>';
}
?>

<div id="content-wrap">
    <section class="page-section">
        <div class="container main content">
            <div class="row justify-content-center page-content">
                <div class="col-12 col-md-12 col-lg-11 col-xl-10 content-wrap">

                    <?php if (have_posts()) : ?>
                        <?php while (have_posts()) : the_post(); ?>

                            <div class="content show-on-scroll fadeIn-on-scroll no-repeat">
                                <?php
                                /*
                                 * Retain the site's content filters.
                                 * The publication HTML above is constructed
                                 * from escaped metadata and validated URLs.
                                 */
                                echo apply_filters('the_content', $publication);
                                ?>
                            </div>

                        <?php endwhile; ?>
                    <?php else : ?>
                        <?php get_template_part('template-parts/content-none'); ?>
                    <?php endif; ?>

                    <div class="post-edit">
                        <?php edit_post_link(__('Edit', 'como-strap')); ?>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>