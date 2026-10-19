<?php
defined('ABSPATH') || exit;

// Default Document Display Template.
// Expected fields: id, image, title, author, publication, event,
// volume, number, page-start, page-end, date, pub-date, doi, file, link.

if (!isset($comodocDisplay) || !is_string($comodocDisplay)) {
    $comodocDisplay = '';
}

$comodocDisplay .= '<ul class="publication-list">';

if (isset($comodoc_array) && is_iterable($comodoc_array)) {
    foreach ($comodoc_array as $doc) {
        if (!is_array($doc)) {
            continue;
        }

        // Normalize all expected fields.
        $fields = array(
            'id', 'date', 'pub-date', 'author', 'title',
            'publication', 'event', 'volume', 'number',
            'page-start', 'page-end', 'doi', 'file', 'link',
        );

        foreach ($fields as $field) {
            $doc[$field] = isset($doc[$field]) && is_scalar($doc[$field])
                ? trim((string) $doc[$field])
                : '';
        }

        $doc_id = absint($doc['id']);

        // Generate category classes safely.
        $category_classes = array();

        if ($doc_id > 0) {
            $term_list = get_the_terms($doc_id, 'document-cat');

            if (is_array($term_list)) {
                foreach ($term_list as $term) {
                    if ($term instanceof WP_Term) {
                        $slug = sanitize_html_class($term->slug);

                        if ($slug !== '') {
                            $category_classes[] = $slug;
                        }
                    }
                }
            }
        }

        $category_classes = array_unique($category_classes);

        $class_attribute = implode(' ', $category_classes);

        // Reset the link for every document.
        $doclink = '';

        if ($doc['file'] !== '') {
            $doclink = esc_url($doc['file']);
        } elseif ($doc['link'] !== '') {
            $doclink = esc_url($doc['link']);
        }

        // Format the publication date safely.
        $date_timestamp = false;

        if ($doc['pub-date'] !== '') {
            $date_timestamp = strtotime($doc['pub-date']);
        }

        $formatted_date = $date_timestamp !== false
            ? wp_date('F, Y', $date_timestamp)
            : '';

        $comodocDisplay .= '<li itemscope itemtype="https://schema.org/ScholarlyArticle" class="publication row';

        if ($class_attribute !== '') {
            $comodocDisplay .= ' ' . esc_attr($class_attribute);
        }

        $comodocDisplay .= '">';

        // Date column.
        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3 col-xl-2">';

        if ($formatted_date !== '') {
            $comodocDisplay .= '<span class="date" itemprop="datePublished">'
                . esc_html($formatted_date)
                . '</span>';
        }

        $comodocDisplay .= '</div>';

        // Article column.
        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9 col-xl-10"><p>';

        if ($doc['author'] !== '') {
            $comodocDisplay .= '<span class="author" itemprop="author">'
                . esc_html($doc['author'])
                . '</span>. ';
        }

        if ($doc['title'] !== '') {
            $title_html = '<span class="title" itemprop="name">'
                . esc_html($doc['title'])
                . '</span>';

            if ($doclink !== '') {
                $comodocDisplay .= '<a href="' . $doclink
                    . '" target="_blank" rel="noopener noreferrer">'
                    . $title_html
                    . '</a>. ';
            } else {
                $comodocDisplay .= $title_html . '. ';
            }
        }

        if ($doc['event'] !== '') {
            $comodocDisplay .= '<span class="event" itemprop="name">'
                . esc_html($doc['event'])
                . '</span>. ';
        }

        if ($doc['publication'] !== '') {
            $comodocDisplay .= '<span itemscope itemtype="https://schema.org/Periodical">'
                . '<span class="pubName" itemprop="name">'
                . esc_html($doc['publication'])
                . '</span></span>';

            if ($doc['date'] !== '') {
                $comodocDisplay .= ' ' . esc_html($doc['date']);
            }

            $comodocDisplay .= '; ';
        }

        // Volume, issue, and page numbers.
        if ($doc['volume'] !== '' || $doc['number'] !== '') {
            $comodocDisplay .= '<span itemprop="isPartOf" itemscope itemtype="https://schema.org/PublicationVolume">';

            if ($doc['volume'] !== '') {
                $comodocDisplay .= '<span class="volume" itemprop="volumeNumber">'
                    . esc_html($doc['volume'])
                    . '</span>';
            }

            if ($doc['number'] !== '') {
                if ($doc['volume'] !== '') {
                    $comodocDisplay .= ' ';
                }

                $comodocDisplay .= '<span class="number" itemprop="issueNumber">'
                    . esc_html($doc['number'])
                    . '</span>';
            }

            if ($doc['page-start'] !== '') {
                $comodocDisplay .= ' ';

                $comodocDisplay .= '<span class="pages" itemprop="pageStart">'
                    . esc_html($doc['page-start'])
                    . '</span>';

                if ($doc['page-end'] !== '') {
                    $comodocDisplay .= '-<span class="pages" itemprop="pageEnd">'
                        . esc_html($doc['page-end'])
                        . '</span>';
                }
            }

            $comodocDisplay .= '</span>. ';
        }

        // Validate the DOI before building its URL.
        $doi = preg_replace(
            '~^https?://(?:dx\.)?doi\.org/~i',
            '',
            $doc['doi']
        );

        $doi = preg_replace('~^doi:\s*~i', '', trim($doi));

        $doi_valid = (
            $doi !== ''
            && (bool) preg_match(
                '~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i',
                $doi
            )
        );

        if ($doi_valid) {
            $encoded_doi = implode(
                '/',
                array_map('rawurlencode', explode('/', $doi))
            );

            $doi_url = esc_url('https://doi.org/' . $encoded_doi);

            $comodocDisplay .= 'DOI: <a itemprop="sameAs" href="'
                . $doi_url
                . '" target="_blank" rel="noopener noreferrer" title="'
                . esc_attr($doc['title'])
                . '">'
                . esc_html($doi)
                . '</a>. ';
        }

        $comodocDisplay .= '</p>';

        // View link: only show it when a valid URL exists.
        if ($doclink !== '') {
            $view_text = insertStringText('View', 'publications');

            if (!is_scalar($view_text)) {
                $view_text = 'View';
            }

            $comodocDisplay .= '<a class="more-link" href="'
                . $doclink
                . '" target="_blank" rel="noopener noreferrer">'
                . esc_html((string) $view_text)
                . '</a>';
        }

        $comodocDisplay .= '</div></li>';
    }
}

// Final note.
$note = insertStringText(
    'Note: This is a select list of our scientific publications.',
    'publications'
);

if (!is_scalar($note)) {
    $note = 'Note: This is a select list of our scientific publications.';
}

$comodocDisplay .= '<li class="publication row">'
    . '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3 col-xl-2"></div>'
    . '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9 col-xl-10">'
    . '<em>' . esc_html((string) $note) . '</em>'
    . '</div></li>';

$comodocDisplay .= '</ul><!-- /doclist -->';


/**
 * Add publication filtering JavaScript in the footer.
 *
 * A uniquely named callback avoids collisions with other templates
 * or plugins defining a generic add_script_to_footer() function.
 */
if (!function_exists('comodocs_publications_footer_script')) {
    function comodocs_publications_footer_script() {
        $raw_filter = get_query_var('pganchor');

        $current_filter = (
            is_string($raw_filter)
            && $raw_filter !== ''
        )
            ? sanitize_html_class($raw_filter)
            : 'all';

        if ($current_filter === '') {
            $current_filter = 'all';
        }

        // Explicitly restrict the value used in CSS class selectors.
        if (
            $current_filter !== 'all'
            && !preg_match('/^[a-zA-Z0-9_-]+$/', $current_filter)
        ) {
            $current_filter = 'all';
        }

        $filter_json = wp_json_encode(
            $current_filter,
            JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        if ($filter_json === false) {
            $filter_json = '"all"';
        }
        ?>
        <script type="text/javascript">
        jQuery(function ($) {
            'use strict';

            var initialFilter = <?php echo $filter_json; ?>;

            function isSafeFilter(value) {
                return typeof value === 'string'
                    && /^[a-zA-Z0-9_-]+$/.test(value);
            }

            function updatePubs(current) {
                if (!isSafeFilter(current) && current !== 'all') {
                    current = 'all';
                }

                if (current === 'all') {
                    $('.publication-nav a').removeClass('active');
                    $('.publication').addClass('active');
                    return;
                }

                $('.publication-nav .menu-item')
                    .not('.' + current)
                    .removeClass('active');

                $('.publication-nav .menu-item.' + current)
                    .addClass('active');

                $('.publication')
                    .not('.' + current)
                    .removeClass('active');

                $('.publication.' + current).addClass('active');
            }

            updatePubs(initialFilter);

            $('.publication-nav').on('click', 'a', function (event) {
                var url;
                var segments;
                var filterValue;

                try {
                    url = new URL(this.href, window.location.origin);
                    segments = url.pathname.split('/').filter(Boolean);
                } catch (error) {
                    return;
                }

                filterValue = 'all';

                var baseIndex = segments.indexOf('scientific-publications');

                if (baseIndex !== -1 && segments[baseIndex + 1]) {
                    filterValue = segments[baseIndex + 1];
                }

                if (!isSafeFilter(filterValue)) {
                    filterValue = 'all';
                }

                updatePubs(filterValue);

                var newPath = '/science/scientific-publications/';

                if (filterValue !== 'all') {
                    newPath += filterValue + '/';
                }

                window.history.pushState(
                    null,
                    '',
                    newPath
                );

                event.preventDefault();
            });

            window.addEventListener('popstate', function () {
                var segments = window.location.pathname.split('/').filter(Boolean);
                var baseIndex = segments.indexOf('scientific-publications');
                var filterValue = 'all';

                if (baseIndex !== -1 && segments[baseIndex + 1]) {
                    filterValue = segments[baseIndex + 1];
                }

                if (!isSafeFilter(filterValue)) {
                    filterValue = 'all';
                }

                updatePubs(filterValue);
            });
        });
        </script>
        <?php
    }

    add_action('wp_footer', 'comodocs_publications_footer_script', 100);
}