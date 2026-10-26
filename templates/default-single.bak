<?php
defined('ABSPATH') || exit;

// Default Document Display Template
// Escape metadata at output and validate URLs before constructing HTML.

$comodocDisplay = '<div class="row justify-content-center">';
$comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';
$comodocDisplay .= '<ul class="publication-list">';

if (isset($comodoc_array) && is_iterable($comodoc_array)) {
    foreach ($comodoc_array as $doc) {
        if (!is_array($doc)) {
            continue;
        }

        // Normalize expected fields to safe scalar strings.
        $fields = array(
            'date', 'author', 'title', 'publication', 'event',
            'volume', 'number', 'page-start', 'page-end', 'doi',
            'file', 'link',
        );

        foreach ($fields as $field) {
            $doc[$field] = isset($doc[$field]) && is_scalar($doc[$field])
                ? trim((string) $doc[$field])
                : '';
        }

        $comodocDisplay .= '<li itemscope itemtype="https://schema.org/ScholarlyArticle" class="publication row">';

        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';

        if ($doc['date'] !== '') {
            $comodocDisplay .= '<span class="date" itemprop="datePublished">'
                . esc_html($doc['date'])
                . '</span>';
        }

        $comodocDisplay .= '<br></div>';

        $comodocDisplay .= '<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9">';

        if ($doc['author'] !== '') {
            $comodocDisplay .= '<span class="author" itemprop="author">'
                . esc_html($doc['author'])
                . '</span>. ';
        }

        if ($doc['title'] !== '') {
            $comodocDisplay .= '<span class="title" itemprop="name">'
                . esc_html($doc['title'])
                . '</span>. ';
        }

        if ($doc['publication'] !== '') {
            $comodocDisplay .= '<span itemscope itemtype="https://schema.org/Periodical">';
            $comodocDisplay .= '<span class="pubName" itemprop="name">'
                . esc_html($doc['publication'])
                . '</span></span>. ';
        }

        if ($doc['event'] !== '') {
            $comodocDisplay .= '<span class="event" itemprop="name">'
                . esc_html($doc['event'])
                . '</span>. ';
        }

        if ($doc['volume'] !== '') {
            $comodocDisplay .= '<span itemprop="isPartOf" itemscope itemtype="https://schema.org/PublicationVolume">';

            $comodocDisplay .= 'vol. <span class="volume" itemprop="volumeNumber">'
                . esc_html($doc['volume'])
                . '</span>';

            if ($doc['number'] !== '') {
                $comodocDisplay .= ' no. <span class="number" itemprop="issueNumber">'
                    . esc_html($doc['number'])
                    . '</span>';
            }

            $comodocDisplay .= ', ';

            if ($doc['page-start'] !== '') {
                $comodocDisplay .= 'pp. <span class="pages" itemprop="pageStart">'
                    . esc_html($doc['page-start'])
                    . '</span>-';

                $page_end = $doc['page-end'] !== ''
                    ? $doc['page-end']
                    : $doc['page-start'];

                $comodocDisplay .= '<span class="pages" itemprop="pageEnd">'
                    . esc_html($page_end)
                    . '</span>, ';
            }

            $comodocDisplay .= '</span>';
        }

        // Validate DOI before building the resolver URL.
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

            $comodocDisplay .= 'doi: <a itemprop="sameAs" href="'
                . $doi_url
                . '" target="_blank" rel="noopener noreferrer" title="'
                . esc_attr($doc['title'])
                . '">'
                . esc_html($doi)
                . '</a><br>';
        }

        // Prefer the document file URL, falling back to the external link.
        $view_url = '';

        if ($doc['file'] !== '') {
            $view_url = esc_url($doc['file']);
        } elseif ($doc['link'] !== '') {
            $view_url = esc_url($doc['link']);
        }

        if ($view_url !== '') {
            $view_text = insertStringText('View', 'publications');

            // Treat translated/custom label output as plain text.
            if (!is_scalar($view_text)) {
                $view_text = 'View';
            }

            $comodocDisplay .= '<br><a class="more-link" href="'
                . $view_url
                . '" target="_blank" rel="noopener noreferrer">'
                . esc_html((string) $view_text)
                . '</a>';
        }

        $comodocDisplay .= '</div></li>';
    }
}

$comodocDisplay .= '</ul><!-- /doclist -->';
$comodocDisplay .= '</div></div>';