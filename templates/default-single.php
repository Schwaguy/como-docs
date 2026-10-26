
<?php
/**
 * Default Single Document Display Template
 *
 * Displays document metadata safely.
 *
 * Expected input:
 *   $comodoc_array - Array or iterable of document records.
 *
 * @package ComoDocs
 */

defined('ABSPATH') || exit;

// Initialize the output.
$comodocDisplay  = '<div class="row justify-content-center">';
$comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';
$comodocDisplay .= '<ul class="publication-list">';

/**
 * Convert a document field to a trimmed string.
 *
 * @param mixed $value Field value.
 * @return string
 */
if (!function_exists('comodoc_template_string')) {
    function comodoc_template_string($value) {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}

/**
 * Safely obtain a URL for use in an HTML href attribute.
 *
 * Only HTTP and HTTPS URLs are accepted.
 *
 * @param mixed $value URL value.
 * @return string
 */
if (!function_exists('comodoc_template_url')) {
    function comodoc_template_url($value) {
        if (!is_scalar($value)) {
            return '';
        }

        $url = trim((string) $value);

        if ($url === '') {
            return '';
        }

        // Reject URLs without an HTTP or HTTPS scheme.
        if (!preg_match('~^https?://~i', $url)) {
            return '';
        }

        // WordPress escapes and filters the URL.
        $url = esc_url($url, array('http', 'https'));

        return is_string($url) ? $url : '';
    }
}

/**
 * Normalize a DOI into its identifier form.
 *
 * @param mixed $value DOI value.
 * @return string
 */
if (!function_exists('comodoc_template_doi')) {
    function comodoc_template_doi($value) {
        $doi = comodoc_template_string($value);

        if ($doi === '') {
            return '';
        }

        // Remove a DOI resolver prefix, if supplied.
        $doi = preg_replace(
            '~^https?://(?:dx\.)?doi\.org/~i',
            '',
            $doi
        );

        // Remove a plain "doi:" prefix.
        $doi = preg_replace('~^doi:\s*~i', '', trim($doi));

        // Accept standard DOI identifiers only.
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

// Process the document records.
if (isset($comodoc_array) && is_iterable($comodoc_array)) {
    foreach ($comodoc_array as $doc) {
        if (!is_array($doc)) {
            continue;
        }

        // Normalize expected fields.
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
        );

        foreach ($fields as $field) {
            $doc[$field] = isset($doc[$field])
                ? comodoc_template_string($doc[$field])
                : '';
        }

        $comodocDisplay .=
            '<li itemscope itemtype="https://schema.org/ScholarlyArticle" class="publication row">';

        // Date column.
        $comodocDisplay .=
            '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';

        if ($doc['date'] !== '') {
            $comodocDisplay .=
                '<span class="date" itemprop="datePublished">'
                . esc_html($doc['date'])
                . '</span>';
        }

        $comodocDisplay .= '</div>';

        // Document details column.
        $comodocDisplay .=
            '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9">';

        // Author.
        if ($doc['author'] !== '') {
            $comodocDisplay .=
                '<span class="author" itemprop="author">'
                . esc_html($doc['author'])
                . '</span>. ';
        }

        // Title.
        if ($doc['title'] !== '') {
            $comodocDisplay .=
                '<span class="title" itemprop="name">'
                . esc_html($doc['title'])
                . '</span>. ';
        }

        // Publication.
        if ($doc['publication'] !== '') {
            $comodocDisplay .=
                '<span itemscope itemtype="https://schema.org/Periodical">'
                . '<span class="pubName" itemprop="name">'
                . esc_html($doc['publication'])
                . '</span>'
                . '</span>. ';
        }

        // Event.
        if ($doc['event'] !== '') {
            $comodocDisplay .=
                '<span class="event">'
                . esc_html($doc['event'])
                . '</span>. ';
        }

        // Volume, issue number, and page range.
        if (
            $doc['volume'] !== ''
            || $doc['number'] !== ''
            || $doc['page-start'] !== ''
            || $doc['page-end'] !== ''
        ) {
            $comodocDisplay .=
                '<span class="publication-details" '
                . 'itemscope itemtype="https://schema.org/PublicationVolume">';

            if ($doc['volume'] !== '') {
                $comodocDisplay .=
                    'vol. <span class="volume" itemprop="volumeNumber">'
                    . esc_html($doc['volume'])
                    . '</span>';
            }

            if ($doc['number'] !== '') {
                $comodocDisplay .=
                    ($doc['volume'] !== '' ? ' ' : '')
                    . 'no. <span class="number" itemprop="issueNumber">'
                    . esc_html($doc['number'])
                    . '</span>';
            }

            $pageStart = $doc['page-start'];
            $pageEnd   = $doc['page-end'];

            if ($pageStart !== '' || $pageEnd !== '') {
                $comodocDisplay .= ' ';

                if ($pageStart !== '' && $pageEnd !== '') {
                    $comodocDisplay .=
                        'pp. <span class="pages" itemprop="pageStart">'
                        . esc_html($pageStart)
                        . '</span>-'
                        . '<span class="pages" itemprop="pageEnd">'
                        . esc_html($pageEnd)
                        . '</span>';
                } elseif ($pageStart !== '') {
                    $comodocDisplay .=
                        'p. <span class="pages" itemprop="pageStart">'
                        . esc_html($pageStart)
                        . '</span>';
                } else {
                    $comodocDisplay .=
                        'p. <span class="pages" itemprop="pageEnd">'
                        . esc_html($pageEnd)
                        . '</span>';
                }
            }

            $comodocDisplay .= '</span>. ';
        }

        // DOI link.
        $doi = comodoc_template_doi($doc['doi']);

        if ($doi !== '') {
            // Encode the DOI while preserving its slash-separated structure.
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
                    'doi: <a itemprop="sameAs" href="'
                    . $doiUrl
                    . '" target="_blank" rel="noopener noreferrer"'
                    . ' title="' . esc_attr($doc['title']) . '">'
                    . esc_html($doi)
                    . '</a><br>';
            }
        }

        // Prefer the document file URL, then fall back to the external link.
        $viewUrl = comodoc_template_url($doc['file']);

        if ($viewUrl === '') {
            $viewUrl = comodoc_template_url($doc['link']);
        }

        if ($viewUrl !== '') {
            /*
             * Avoid a fatal error if insertStringText() is not loaded.
             * The translation helper is optional; "View" is the fallback.
             */
            $viewText = 'View';

            if (function_exists('insertStringText')) {
                $translatedText = insertStringText('View', 'publications');

                if (is_scalar($translatedText)) {
                    $translatedText = trim((string) $translatedText);

                    if ($translatedText !== '') {
                        $viewText = $translatedText;
                    }
                }
            } else {
                $viewText = __('View', 'publications');
            }

            $comodocDisplay .=
                '<br><a class="more-link" href="'
                . $viewUrl
                . '" target="_blank" rel="noopener noreferrer">'
                . esc_html($viewText)
                . '</a>';
        }

        $comodocDisplay .= '</div>';
        $comodocDisplay .= '</li>';
    }
}

// Close the wrapper.
$comodocDisplay .= '</ul><!-- /doclist -->';
$comodocDisplay .= '</div></div>';
