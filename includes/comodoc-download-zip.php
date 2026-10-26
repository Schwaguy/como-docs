<?php
defined('ABSPATH') || exit;

/**
 * Build and stream a ZIP archive of published document attachments.
 *
 * This file should be loaded by WordPress, rather than including wp-load.php
 * from a publicly accessible standalone script.
 */

if (!class_exists('ZipArchive')) {
    wp_die(
        esc_html__('ZIP archive support is unavailable on this server.', 'como-documents'),
        '',
        array('response' => 500)
    );
}

if (!current_user_can('read')) {
    wp_die(
        esc_html__('You are not authorized to download these documents.', 'como-documents'),
        '',
        array('response' => 403)
    );
}

// Read and validate request parameters.
$requested_doc_id = isset($_REQUEST['document-id'])
    ? absint(wp_unslash($_REQUEST['document-id']))
    : 0;

$requested_limit = isset($_REQUEST['document-limit'])
    ? wp_unslash($_REQUEST['document-limit'])
    : '-1';

$requested_category = isset($_REQUEST['document-cat'])
    ? sanitize_title(wp_unslash($_REQUEST['document-cat']))
    : '';

$requested_zip_name = isset($_REQUEST['zip-filename'])
    ? sanitize_file_name(wp_unslash($_REQUEST['zip-filename']))
    : 'documents';

// Keep the archive name simple and predictable.
$requested_zip_name = pathinfo($requested_zip_name, PATHINFO_FILENAME);
$requested_zip_name = sanitize_file_name($requested_zip_name);

if ($requested_zip_name === '') {
    $requested_zip_name = 'documents';
}

// Accept -1 for all matching documents, or a positive bounded limit.
if ((string) $requested_limit === '-1') {
    $doc_limit = -1;
} else {
    $doc_limit = absint($requested_limit);

    if ($doc_limit < 1 || $doc_limit > 500) {
        wp_die(
            esc_html__('Invalid document limit.', 'como-documents'),
            '',
            array('response' => 400)
        );
    }
}

$args = array(
    'post_type'              => 'document',
    'post_status'            => 'publish',
    'posts_per_page'         => $requested_doc_id ? 1 : $doc_limit,
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'fields'                 => 'ids',
);

if ($requested_doc_id) {
    $args['p'] = $requested_doc_id;
}

if (!$requested_doc_id && $requested_category !== '') {
    $term = get_term_by('slug', $requested_category, 'document-cat');

    if (!$term || is_wp_error($term)) {
        wp_die(
            esc_html__('Invalid document category.', 'como-documents'),
            '',
            array('response' => 400)
        );
    }

    $args['tax_query'] = array(
        array(
            'taxonomy'         => 'document-cat',
            'field'            => 'term_id',
            'terms'            => array((int) $term->term_id),
            'include_children' => false,
        ),
    );
}

$query = new WP_Query($args);
$files = array();
$used_names = array();

foreach ($query->posts as $post_id) {
    $attachment_id = absint(
        get_post_meta($post_id, 'comodoc-file-id', true)
    );

    if (!$attachment_id || get_post_type($attachment_id) !== 'attachment') {
        continue;
    }

    // Only include attachments that WordPress recognizes as local files.
    $path = get_attached_file($attachment_id, true);

    if (!$path || !is_string($path) || !is_file($path) || !is_readable($path)) {
        continue;
    }

    $uploads = wp_get_upload_dir();
    $uploads_basedir = realpath($uploads['basedir']);
    $real_path = realpath($path);

    // Prevent adding arbitrary files outside the WordPress uploads directory.
    if (
        !$uploads_basedir ||
        !$real_path ||
        strpos(
            $real_path,
            trailingslashit($uploads_basedir)
        ) !== 0
    ) {
        continue;
    }

    $filename = sanitize_file_name(basename($real_path));

    if ($filename === '') {
        continue;
    }

    // Prevent duplicate ZIP entry names from overwriting one another.
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $candidate = $filename;
    $counter = 1;

    while (isset($used_names[strtolower($candidate)])) {
        $suffix = '-' . $counter;
        $candidate = $base . $suffix;

        if ($extension !== '') {
            $candidate .= '.' . $extension;
        }

        $counter++;
    }

    $used_names[strtolower($candidate)] = true;

    $files[] = array(
        'path' => $real_path,
        'name' => $candidate,
    );
}

wp_reset_postdata();

if (empty($files)) {
    wp_die(
        esc_html__('No downloadable documents were found.', 'como-documents'),
        '',
        array('response' => 404)
    );
}

// Use a private temporary file rather than writing the archive into the
// current working directory or a publicly accessible URL path.
$temp_file = wp_tempnam($requested_zip_name . '.zip');

if (!$temp_file) {
    wp_die(
        esc_html__('Could not create a temporary ZIP archive.', 'como-documents'),
        '',
        array('response' => 500)
    );
}

$zip = new ZipArchive();
$open_result = $zip->open($temp_file, ZipArchive::OVERWRITE);

if ($open_result !== true) {
    @unlink($temp_file);

    wp_die(
        esc_html__('Could not create the ZIP archive.', 'como-documents'),
        '',
        array('response' => 500)
    );
}

foreach ($files as $file) {
    if (!$zip->addFile($file['path'], $file['name'])) {
        $zip->close();
        @unlink($temp_file);

        wp_die(
            esc_html__('A document could not be added to the ZIP archive.', 'como-documents'),
            '',
            array('response' => 500)
        );
    }
}

if (!$zip->close() || !is_file($temp_file)) {
    @unlink($temp_file);

    wp_die(
        esc_html__('Could not finalize the ZIP archive.', 'como-documents'),
        '',
        array('response' => 500)
    );
}

$download_name = $requested_zip_name . '-' . gmdate('Ymd-His') . '.zip';
$file_size = filesize($temp_file);

if ($file_size === false) {
    @unlink($temp_file);

    wp_die(
        esc_html__('Could not read the ZIP archive size.', 'como-documents'),
        '',
        array('response' => 500)
    );
}

// Prevent PHP warnings or earlier output from corrupting the ZIP response.
while (ob_get_level() > 0) {
    ob_end_clean();
}

nocache_headers();
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $download_name . '"');
header('Content-Length: ' . (string) $file_size);
header('X-Content-Type-Options: nosniff');

$handle = fopen($temp_file, 'rb');

if ($handle !== false) {
    fpassthru($handle);
    fclose($handle);
}

@unlink($temp_file);
exit;