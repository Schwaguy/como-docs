<?php
/**
 * Embedded Single Document Display Template
 * Supports document-ID shortcodes and responsive embedded PDF viewers.
 *
 * @package ComoDocs
 */

defined('ABSPATH') || exit;

/**
 * Convert a value to a safe scalar string.
 */
if (!function_exists('comodoc_template_string')) {
    function comodoc_template_string($value) {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}

/**
 * Validate an HTTP/HTTPS URL.
 */
if (!function_exists('comodoc_template_url')) {
    function comodoc_template_url($value) {
        if (!is_scalar($value)) {
            return '';
        }

        $url = trim((string) $value);

        if (
            $url === ''
            || !preg_match('~^https?://~i', $url)
        ) {
            return '';
        }

        return esc_url($url, array('http', 'https'));
    }
}

/**
 * Validate and normalize a DOI.
 */
if (!function_exists('comodoc_template_doi')) {
    function comodoc_template_doi($value) {
        $doi = comodoc_template_string($value);

        if ($doi === '') {
            return '';
        }

        $doi = preg_replace(
            '~^https?://(?:dx\.)?doi\.org/~i',
            '',
            $doi
        );

        $doi = preg_replace('~^doi:\s*~i', '', trim($doi));

        if (
            !is_string($doi)
            || !preg_match(
                '~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i',
                $doi
            )
        ) {
            return '';
        }

        return $doi;
    }
}

/**
 * Get the first non-empty document meta value from known key variants.
 */
if (!function_exists('comodoc_template_meta')) {
    function comodoc_template_meta($post_id, $keys) {
        foreach ($keys as $key) {
            $value = get_post_meta($post_id, $key, true);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }
}

/**
 * Determine whether the shortcode specifies a document ID.
 *
 * The shortcode handler may expose its attributes as $a or $atts.
 */
$requestedDocId = 0;

if (isset($a) && is_array($a) && isset($a['id'])) {
    $requestedDocId = absint($a['id']);
} elseif (isset($atts) && is_array($atts) && isset($atts['id'])) {
    $requestedDocId = absint($atts['id']);
} elseif (isset($comodoc_atts) && is_array($comodoc_atts)
    && isset($comodoc_atts['id'])) {
    $requestedDocId = absint($comodoc_atts['id']);
}

/**
 * Normalize incoming document records.
 */
$documents = array();

if (isset($comodoc_array) && is_iterable($comodoc_array)) {
    foreach ($comodoc_array as $record) {
        if (!is_array($record)) {
            continue;
        }

        $recordId = isset($record['id']) ? absint($record['id']) : 0;

        if ($requestedDocId && $recordId !== $requestedDocId) {
            continue;
        }

        $documents[] = $record;
    }
}

/**
 * If a specific ID was requested but the shortcode handler did not
 * provide its record, load that published document directly.
 */
if ($requestedDocId && empty($documents)) {
    $post = get_post($requestedDocId);

    if (
        $post instanceof WP_Post
        && $post->post_type === 'document'
        && $post->post_status === 'publish'
    ) {
        $fileValue = comodoc_template_meta(
            $requestedDocId,
            array('comodoc-file', 'comodoc_file', 'file')
        );

        // Some installations store an attachment ID rather than a URL.
        if (
            $fileValue !== ''
            && ctype_digit($fileValue)
            && (int) $fileValue > 0
        ) {
            $attachmentUrl = wp_get_attachment_url((int) $fileValue);

            if ($attachmentUrl) {
                $fileValue = $attachmentUrl;
            }
        }

        $documents[] = array(
            'id'          => $requestedDocId,
            'date'        => get_the_date('Y-m-d', $post),
            'author'      => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-author')
            ),
            'title'       => get_the_title($post),
            'publication' => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-publication')
            ),
            'event'       => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-event')
            ),
            'volume'      => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-volume')
            ),
            'number'      => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-number')
            ),
            'page-start'  => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-page-start')
            ),
            'page-end'    => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-page-end')
            ),
            'doi'         => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-doi')
            ),
            'file'        => $fileValue,
            'link'        => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-link', 'comodoc_link', 'link')
            ),
            'embed-pdf'   => comodoc_template_meta(
                $requestedDocId,
                array('comodoc-embed-pdf')
            ),
        );
    }
}

/**
 * Start output.
 */
$comodocDisplay  = '<div class="row justify-content-center">';
$comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';

if (empty($documents) && $requestedDocId) {
    $comodocDisplay .= '<p class="comodoc-not-found">'
        . esc_html__('Document not found or unavailable.', 'publications')
        . '</p>';
} else {
    $comodocDisplay .= '<ul class="publication-list">';

    foreach ($documents as $doc) {
        $fields = array(
            'date',
            'author',
            'title',
            'publication',
            'event',
            'volume',
            'number',
            'page-start',
            'page-end',
            'doi',
            'file',
            'link',
            'embed-pdf',
        );

        foreach ($fields as $field) {
            $doc[$field] = isset($doc[$field])
                ? comodoc_template_string($doc[$field])
                : '';
        }

        $docId = isset($doc['id']) ? absint($doc['id']) : 0;

        // Resolve the PDF and external link.
        $fileUrl = comodoc_template_url($doc['file']);
        $linkUrl = comodoc_template_url($doc['link']);
        $viewUrl = $fileUrl !== '' ? $fileUrl : $linkUrl;

        // When an ID was requested, ensure only that document is shown.
        if ($requestedDocId && $docId !== $requestedDocId) {
            continue;
        }

        $comodocDisplay .=
            '<li itemscope itemtype="https://schema.org/ScholarlyArticle" '
            . 'class="publication row">';

        // Date.
        $comodocDisplay .=
            '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';

        if ($doc['date'] !== '') {
            $comodocDisplay .=
                '<span class="date" itemprop="datePublished">'
                . esc_html($doc['date'])
                . '</span>';
        }

        $comodocDisplay .= '</div>';

        // Metadata.
        $comodocDisplay .=
            '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9">';

        if ($doc['author'] !== '') {
            $comodocDisplay .=
                '<span class="author" itemprop="author">'
                . esc_html($doc['author']) . '</span>. ';
        }

        if ($doc['title'] !== '') {
            $comodocDisplay .=
                '<span class="title" itemprop="name">'
                . esc_html($doc['title']) . '</span>. ';
        }

        if ($doc['publication'] !== '') {
            $comodocDisplay .=
                '<span itemscope itemtype="https://schema.org/Periodical">'
                . '<span class="pubName" itemprop="name">'
                . esc_html($doc['publication'])
                . '</span></span>. ';
        }

        if ($doc['event'] !== '') {
            $comodocDisplay .=
                '<span class="event">'
                . esc_html($doc['event']) . '</span>. ';
        }

        // Volume, issue, and page range.
        if (
            $doc['volume'] !== ''
            || $doc['number'] !== ''
            || $doc['page-start'] !== ''
            || $doc['page-end'] !== ''
        ) {
            $comodocDisplay .= '<div class="publication-details">';

            if ($doc['volume'] !== '') {
                $comodocDisplay .=
                    'Vol. <span class="volume">'
                    . esc_html($doc['volume']) . '</span> ';
            }

            if ($doc['number'] !== '') {
                $comodocDisplay .=
                    'No. <span class="number">'
                    . esc_html($doc['number']) . '</span> ';
            }

            if ($doc['page-start'] !== '' && $doc['page-end'] !== '') {
                $comodocDisplay .=
                    'pp. <span class="pages">'
                    . esc_html($doc['page-start'])
                    . '</span>-<span class="pages">'
                    . esc_html($doc['page-end'])
                    . '</span>';
            } elseif ($doc['page-start'] !== '') {
                $comodocDisplay .=
                    'p. <span class="pages">'
                    . esc_html($doc['page-start']) . '</span>';
            } elseif ($doc['page-end'] !== '') {
                $comodocDisplay .=
                    'p. <span class="pages">'
                    . esc_html($doc['page-end']) . '</span>';
            }

            $comodocDisplay .= '</div>';
        }

        // DOI.
        $doi = comodoc_template_doi($doc['doi']);

        if ($doi !== '') {
            $encodedDoi = implode(
                '/',
                array_map('rawurlencode', explode('/', $doi))
            );

            $doiUrl = esc_url(
                'https://doi.org/' . $encodedDoi,
                array('https')
            );

            if ($doiUrl !== '') {
                $comodocDisplay .=
                    '<div class="document-doi">DOI: '
                    . '<a itemprop="sameAs" href="' . $doiUrl . '" '
                    . 'target="_blank" rel="noopener noreferrer">'
                    . esc_html($doi) . '</a></div>';
            }
        }

        // View/download link.
        if ($viewUrl !== '') {
            $viewText = __('View', 'publications');

            if (function_exists('insertStringText')) {
                $translatedText = insertStringText('View', 'publications');

                if (
                    is_scalar($translatedText)
                    && trim((string) $translatedText) !== ''
                ) {
                    $viewText = (string) $translatedText;
                }
            }

            $comodocDisplay .=
                '<p class="document-actions">'
                . '<a class="more-link" href="' . $viewUrl . '" '
                . 'target="_blank" rel="noopener noreferrer">'
                . esc_html($viewText) . '</a></p>';
        }

        /*
         * Embedded PDF viewer.
         *
         * For [comodocs id="123"], this embeds the PDF belonging
         * to document 123, provided its file field contains a PDF URL.
         */
        $embedPdf = in_array(
            strtolower($doc['embed-pdf']),
            array('1', 'yes', 'true', 'on'),
            true
        );

        if ($embedPdf && $fileUrl !== '') {
            $pdfTitle = $doc['title'] !== ''
                ? $doc['title']
                : __('Document PDF', 'publications');

            $comodocDisplay .=
                '<div class="comodoc-pdf-viewer" '
                . 'style="width:100%; margin:20px 0;">';

            $comodocDisplay .=
                '<object data="' . $fileUrl . '" '
                . 'type="application/pdf" width="100%" '
                . 'style="display:block; width:100%; '
                . 'height:clamp(400px, 85vh, 1200px);" '
                . 'aria-label="' . esc_attr($pdfTitle) . '">';

            $comodocDisplay .=
                '<p>'
                . esc_html__(
                    'Your browser does not support embedded PDFs.',
                    'publications'
                )
                . ' <a href="' . $fileUrl . '" '
                . 'target="_blank" rel="noopener noreferrer">'
                . esc_html__(
                    'Open or download the PDF',
                    'publications'
                )
                . '</a>.</p>';

            $comodocDisplay .= '</object></div>';
        }

        $comodocDisplay .= '</div></li>';
    }

    $comodocDisplay .= '</ul><!-- /doclist -->';
}

$comodocDisplay .= '</div></div>';
