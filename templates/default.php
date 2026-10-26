<?php
defined('ABSPATH') || exit;

// Default Document Display Template.
// Expected fields include: id, author, title, publication, event,
// volume, number, page-start, page-end, pub-date, date, doi, file, link.

echo '<p><strong>$comodoc_template: '. $comodoc_template .'</strong></p>';

$comodocDisplay = '<div class="row justify-content-center">';
$comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';
$comodocDisplay .= '<ul class="publication-list">';

if (isset($comodoc_array) && is_iterable($comodoc_array)) {
    foreach ($comodoc_array as $doc) {
        if (!is_array($doc)) {
            continue;
        }

        // Normalize metadata before use.
        $fields = array(
            'id', 'author', 'title', 'publication', 'event',
            'volume', 'number', 'page-start', 'page-end',
            'pub-date', 'date', 'doi', 'file', 'link',
        );

        foreach ($fields as $field) {
            $doc[$field] = isset($doc[$field]) && is_scalar($doc[$field])
                ? trim((string) $doc[$field])
                : '';
        }

        $doc_id = absint($doc['id']);

        // Collect taxonomy slugs for CSS classes.
        $categories = array();

        if ($doc_id > 0) {
            $terms = get_the_terms($doc_id, 'document-cat');

            if (is_array($terms)) {
                foreach ($terms as $term) {
                    if ($term instanceof WP_Term) {
                        $slug = sanitize_html_class($term->slug);

                        if ($slug !== '') {
                            $categories[] = $slug;
                        }
                    }
                }
            }
        }

        $category_class = implode(' ', array_unique($categories));

        // Reset link for each document to prevent stale URL reuse.
        $doclink = '';

        if ($doc['file'] !== '') {
            $doclink = esc_url($doc['file']);
        } elseif ($doc['link'] !== '') {
            $doclink = esc_url($doc['link']);
        }

        // Format publication date using the WordPress timezone.
        $formatted_date = '';

        if ($doc['pub-date'] !== '') {
            $timestamp = strtotime($doc['pub-date']);

            if ($timestamp !== false) {
                $formatted_date = wp_date('F, Y', $timestamp);
            }
        }

        $comodocDisplay .= '<li itemscope itemtype="https://schema.org/ScholarlyArticle" class="publication row';

        if ($category_class !== '') {
            $comodocDisplay .= ' ' . esc_attr($category_class);
        }

        $comodocDisplay .= '">';

        // Date column.
        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';

        if ($formatted_date !== '') {
            $comodocDisplay .= '<span class="date" itemprop="datePublished">'
                . esc_html($formatted_date)
                . '</span>';
        }

        $comodocDisplay .= '</div>';

        // Article column.
        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9"><p>';

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

        // Volume, issue and page numbers.
        if ($doc['volume'] !== '') {
            $comodocDisplay .= '<span itemprop="isPartOf" itemscope itemtype="https://schema.org/PublicationVolume">';

            $comodocDisplay .= '<span class="volume" itemprop="volumeNumber">'
                . esc_html($doc['volume'])
                . '</span>';

            if ($doc['number'] !== '') {
                $comodocDisplay .= ' <span class="number" itemprop="issueNumber">'
                    . esc_html($doc['number'])
                    . '</span>';
            }

            if ($doc['page-start'] !== '') {
                $comodocDisplay .= ': <span class="pages" itemprop="pageStart">'
                    . esc_html($doc['page-start'])
                    . '</span>';

                if ($doc['page-end'] !== '') {
                    $comodocDisplay .= '-<span class="pages" itemprop="pageEnd">'
                        . esc_html($doc['page-end'])
                        . '</span>';
                }

                $comodocDisplay .= '. ';
            }

            $comodocDisplay .= '</span>';
        }

        // Validate DOI before constructing its URL.
        $doi = preg_replace(
            '~^https?://(?:dx\.)?doi\.org/~i',
            '',
            $doc['doi']
        );

        $doi = preg_replace('~^doi:\s*~i', '', trim($doi));

        $valid_doi = (
            $doi !== ''
            && (bool) preg_match(
                '~^10\.\d{4,9}/[-._;()/:A-Z0-9]+$~i',
                $doi
            )
        );

        if ($valid_doi) {
            $encoded_doi = implode(
                '/',
                array_map('rawurlencode', explode('/', $doi))
            );

            $doi_url = esc_url('https://doi.org/' . $encoded_doi);

            $comodocDisplay .= ' DOI: <a itemprop="sameAs" href="'
                . $doi_url
                . '" target="_blank" rel="noopener noreferrer" title="'
                . esc_attr($doc['title'])
                . '">'
                . esc_html($doi)
                . '</a>. ';
        }

        $comodocDisplay .= '</p>';

        if ($doclink !== '') {
            $view_label = insertStringText('View', 'publications');

            if (!is_scalar($view_label)) {
                $view_label = 'View';
            }

            $comodocDisplay .= '<a class="more-link" href="'
                . $doclink
                . '" target="_blank" rel="noopener noreferrer">'
                . esc_html((string) $view_label)
                . '</a>';
        }

        $comodocDisplay .= '</div></li>';
    }
}

// Publication note.
$note = insertStringText(
    'Note: This is a select list of our scientific publications.',
    'publications'
);

if (!is_scalar($note)) {
    //$note = 'Note: This is a select list of our scientific publications.';
}

$comodocDisplay .= '<li class="publication row">'
    . '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3"></div>'
    . '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9"><em>'
    . esc_html((string) $note)
    . '</em></div></li>';

$comodocDisplay .= '</ul><!-- /doclist -->';
$comodocDisplay .= '</div></div>';

/**
 * Register the footer script only once.
 * The filter is JSON-encoded and restricted to safe CSS class characters.
 */
if (!function_exists('comodocs_publications_footer_script')) {
    function comodocs_publications_footer_script() {
        $raw_filter = get_query_var('pganchor');

        $current_filter = (
            is_string($raw_filter) && $raw_filter !== ''
        )
            ? sanitize_html_class($raw_filter)
            : 'all';

        if (
            $current_filter === ''
            || (
                $current_filter !== 'all'
                && !preg_match('/^[a-zA-Z0-9_-]+$/', $current_filter)
            )
        ) {
            $current_filter = 'all';
        }

        $filter_json = wp_json_encode(
            $current_filter,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        if ($filter_json === false) {
            $filter_json = '"all"';
        }
        ?>
        <script>
        jQuery(function ($) {
            'use strict';

            var initialFilter = <?php echo $filter_json; ?>;
            var basePath = '/science/scientific-publications/';

            function safeFilter(value) {
                return typeof value === 'string'
                    && /^[a-zA-Z0-9_-]+$/.test(value);
            }

            function updatePubs(current) {
                if (current !== 'all' && !safeFilter(current)) {
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

            function filterFromPath(pathname) {
                var segments = pathname.split('/').filter(Boolean);
                var index = segments.indexOf('scientific-publications');

                if (index === -1 || !segments[index + 1]) {
                    return 'all';
                }

                var value = segments[index + 1];

                return safeFilter(value) ? value : 'all';
            }

            updatePubs(initialFilter);

            $('.publication-nav').on('click', 'a', function (event) {
                var filterValue = filterFromPath(
                    new URL(this.href, window.location.origin).pathname
                );

                updatePubs(filterValue);

                var newPath = basePath;

                if (filterValue !== 'all') {
                    newPath += filterValue + '/';
                }

                window.history.pushState(null, '', newPath);
                event.preventDefault();
            });

            window.addEventListener('popstate', function () {
                updatePubs(filterFromPath(window.location.pathname));
            });
        });
        </script>
        <?php
    }

    add_action('wp_footer', 'comodocs_publications_footer_script', 100);
}