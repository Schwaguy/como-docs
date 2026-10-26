
<?php
defined('ABSPATH') || exit;

get_header();

$doc_id = get_queried_object_id();

$doc = array(
    'id'          => $doc_id,
    'post-date'   => get_the_date('', $doc_id),
    'image'       => get_the_post_thumbnail($doc_id, 'full', array('class' => 'team-photo')),
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
    'embed-pdf'   => get_post_meta($doc_id, 'comodoc-embed-pdf', true),
);

/* Normalize text metadata before output. */
foreach (
    array(
        'author', 'publication', 'event', 'abstract', 'volume',
        'number', 'page-start', 'page-end', 'date', 'doi', 'title'
    ) as $key
) {
    $doc[$key] = is_scalar($doc[$key])
        ? trim((string) $doc[$key])
        : '';
}

/* Validate and build the DOI URL. */
$doi = preg_replace('~^https?://(?:dx\.)?doi\.org/~i', '', $doc['doi']);
$doi = trim(preg_replace('~^doi:\s*~i', '', $doi));

$doi_url = '';

if (
    $doi !== ''
    && preg_match('~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i', $doi)
) {
    $encoded_doi = implode('/', array_map('rawurlencode', explode('/', $doi)));
    $doi_url = esc_url('https://doi.org/' . $encoded_doi);
}

/*
 * Resolve the document URL.
 * Prefer the WordPress attachment, then fall back to comodoc-file.
 */
$file_url = '';
$file_title = '';

if (
    $doc['file-id']
    && get_post_type($doc['file-id']) === 'attachment'
) {
    $attachment_url = wp_get_attachment_url($doc['file-id']);

    if (is_string($attachment_url) && $attachment_url !== '') {
        $file_url = esc_url($attachment_url);
        $file_title = get_the_title($doc['file-id']);
    }
}

if (
    $file_url === ''
    && is_string($doc['file'])
    && trim($doc['file']) !== ''
) {
    $stored_file_url = esc_url(trim($doc['file']));

    if ($stored_file_url !== '') {
        $file_url = $stored_file_url;
        $file_title = __('Download document', 'como-strap');
    }
}

/* Sanitize the external link. */
$external_link = '';

if (is_string($doc['link']) && trim($doc['link']) !== '') {
    $external_link = esc_url(trim($doc['link']));
}

/* Build the publication information. */
$publication = '';

$publication .= '<div itemscope itemtype="https://schema.org/ScholarlyArticle"';
$publication .= ' class="news-item show-on-scroll fadeInUp-on-scroll no-repeat">';

if ($doc['author'] !== '') {
    $publication .= '<span class="author" itemprop="author">'
        . esc_html($doc['author']) . '</span>. ';
}

if ($doc['title'] !== '') {
    $publication .= '<span class="title" itemprop="name">'
        . esc_html($doc['title']) . '</span>. ';
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
        . esc_html($doc['publication']) . '</span>';
    $publication .= '</span> ';

    if ($doc['date'] !== '') {
        $publication .= esc_html($doc['date']) . ' ';
    }
}

if ($doc['abstract'] !== '') {
    $publication .= '<span class="abstract" itemprop="abstract">'
        . esc_html($doc['abstract']) . '</span>. ';
}

if ($doc['volume'] !== '') {
    $publication .= '<span itemprop="isPartOf" itemscope ';
    $publication .= 'itemtype="https://schema.org/PublicationVolume">';

    $publication .= '<span class="volume" itemprop="volumeNumber">'
        . esc_html($doc['volume']) . '</span>';

    if ($doc['page-start'] !== '') {
        $publication .= ' <span class="pages" itemprop="pageStart">'
            . esc_html($doc['page-start']) . '</span>';

        if ($doc['page-end'] !== '') {
            $publication .= '-<span class="pages" itemprop="pageEnd">'
                . esc_html($doc['page-end']) . '</span>';
        }

        $publication .= '. ';
    }

    $publication .= '</span>';
}

if ($doi_url !== '') {
    $publication .= ' DOI: <a itemprop="sameAs" href="' . $doi_url . '"';
    $publication .= ' target="_blank" rel="noopener noreferrer"';
    $publication .= ' title="' . esc_attr($doc['title']) . '">';
    $publication .= esc_html($doi) . '</a>. ';
}

$publication .= '</div>';

if ($external_link !== '') {
    $publication .= '<p><a class="read-more" href="'
        . esc_url($external_link)
        . '" target="_blank" rel="noopener noreferrer" itemprop="url">'
        . esc_html__('Read More', 'como-strap') . ' &gt;</a></p>';
}

/*
 * Always show the document link when a URL exists.
 * Embed the PDF only when the Embed PDF checkbox is enabled.
 */
if ($file_url !== '') {
    $display_file_title = (
        is_scalar($file_title) && trim((string) $file_title) !== ''
    )
        ? (string) $file_title
        : __('Download document', 'como-strap');

    $publication .= '<p><a class="read-more" href="'
        . esc_url($file_url)
        . '" target="_blank" rel="noopener noreferrer" itemprop="contentUrl">'
        . esc_html($display_file_title) . '</a></p>';

    $embed_pdf = in_array(
        strtolower(trim((string) $doc['embed-pdf'])),
        array('1', 'yes', 'true', 'on'),
        true
    );

    if ($embed_pdf) {
        $pdf_label = $doc['title'] !== ''
            ? $doc['title']
            : __('PDF document', 'como-strap');

        $publication .= '<div class="comodoc-pdf-embed" '
            . 'style="width:100%; margin:1.5rem 0;">';

        $publication .= '<object data="' . esc_url($file_url)
            . '#toolbar=0&amp;navpanes=0&amp;view=FitH" '
            . 'type="application/pdf" '
            . 'aria-label="' . esc_attr($pdf_label) . '" '
            . 'style="display:block; width:100%; '
            . 'height:clamp(400px, 85vh, 1200px); border:0;">';

        $publication .= '<p>'
            . esc_html__('Your browser cannot display this PDF inline.', 'como-strap')
            . ' <a href="' . esc_url($file_url)
            . '" target="_blank" rel="noopener noreferrer">'
            . esc_html__('Open or download the PDF', 'como-strap')
            . '</a></p>';

        $publication .= '</object></div>';
    }
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
                                echo apply_filters('the_content', $publication); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
