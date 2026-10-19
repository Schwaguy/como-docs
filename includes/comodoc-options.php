<?php
defined('ABSPATH') || exit;

/**
 * Register the Document Options submenu under Documents.
 */
function comodoc_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=document',
        __('Document Options', 'como-documents'),
        __('Document Options', 'como-documents'),
        'manage_options',
        'comodoc_plugin_settings',
        'comodoc_render_settings_page'
    );
}
add_action('admin_menu', 'comodoc_add_settings_page');

/**
 * Render the settings page.
 */
function comodoc_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(
            esc_html__('You are not allowed to access this page.', 'como-documents'),
            '',
            array('response' => 403)
        );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Document Options', 'como-documents'); ?></h1>

        <form action="options.php" method="post">
            <?php
            settings_fields('comodoc_plugin_settings');
            do_settings_sections('comodoc_plugin_settings');
            submit_button(__('Save', 'como-documents'));
            ?>
        </form>
    </div>
    <?php
}

/**
 * Define allowed setting values and defaults.
 */
function comodoc_get_setting_definitions() {
    return array(
        'publicly-queriable' => array(
            'type'    => 'choice',
            'allowed' => array('yes', 'no'),
            'default' => 'yes',
        ),
        'redirect-url' => array(
            'type'    => 'url',
            'default' => '',
        ),
        'show-authors' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-publication' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-event' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-abstract' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-funding' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-volume' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-issue' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-pages' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-date' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-doi' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-document' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-document-2' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
        'show-link' => array(
            'type'    => 'choice',
            'allowed' => array('show', 'hide'),
            'default' => 'show',
        ),
    );
}

/**
 * Register plugin settings and fields.
 */
function comodoc_register_settings() {
    register_setting(
        'comodoc_plugin_settings',
        'comodoc_plugin_settings',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'comodoc_validate_example_plugin_settings',
            'default'           => array(),
        )
    );

    add_settings_section(
        'comodoc_plugin_settings',
        __('Document Display Settings', 'como-documents'),
        'comodoc_comodoc_plugin_settings_text',
        'comodoc_plugin_settings'
    );

    $visibility_options = array(
        'show' => __('Show', 'como-documents'),
        'hide' => __('Hide', 'como-documents'),
    );

    $fields = array(
        array(
            'name'     => 'publicly-queriable',
            'label'    => __('Publicly Queryable', 'como-documents'),
            'options'  => array(
                'yes' => __('Yes', 'como-documents'),
                'no'  => __('No', 'como-documents'),
            ),
            'note'     => __('Should individual Document pages be viewable?', 'como-documents'),
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'redirect-url',
            'label'    => __('Redirect URL', 'como-documents'),
            'options'  => array(),
            'note'     => __('If documents are not publicly queryable, where should pages redirect?', 'como-documents'),
            'callback' => 'comodoc_text_callback',
        ),
        array(
            'name'     => 'show-authors',
            'label'    => __('Authors Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-publication',
            'label'    => __('Publication Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-event',
            'label'    => __('Event Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-abstract',
            'label'    => __('Abstract Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-funding',
            'label'    => __('Funding Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-volume',
            'label'    => __('Volume Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-issue',
            'label'    => __('Issue Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-pages',
            'label'    => __('Pages Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-date',
            'label'    => __('Date Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-doi',
            'label'    => __('DOI Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-document',
            'label'    => __('Document Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-document-2',
            'label'    => __('Document 2 Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
        array(
            'name'     => 'show-link',
            'label'    => __('Link Field', 'como-documents'),
            'options'  => $visibility_options,
            'note'     => '',
            'callback' => 'comodoc_radio_field_callback',
        ),
    );

    foreach ($fields as $field) {
        add_settings_field(
            $field['name'],
            $field['label'],
            $field['callback'],
            'comodoc_plugin_settings',
            'comodoc_plugin_settings',
            array(
                'comodoc_plugin_settings',
                $field['name'],
                $field['note'],
                $field['options'],
            )
        );
    }
}
add_action('admin_init', 'comodoc_register_settings');

/**
 * Sanitize submitted settings using an allowlist.
 */
function comodoc_validate_example_plugin_settings($input) {
    if (!is_array($input)) {
        add_settings_error(
            'comodoc_plugin_settings',
            'invalid_settings',
            __('Invalid settings submitted.', 'como-documents')
        );

        $existing = get_option('comodoc_plugin_settings', array());

        return is_array($existing) ? $existing : array();
    }

    $definitions = comodoc_get_setting_definitions();
    $existing = get_option('comodoc_plugin_settings', array());

    if (!is_array($existing)) {
        $existing = array();
    }

    $output = array();

    foreach ($definitions as $key => $definition) {
        if ($definition['type'] === 'url') {
            $value = isset($input[$key]) && is_string($input[$key])
                ? trim(sanitize_text_field(wp_unslash($input[$key])))
                : '';

            if ($value === '') {
                $output[$key] = '';
                continue;
            }

            $url = esc_url_raw($value, array('http', 'https'));

            if ($url && wp_http_validate_url($url)) {
                $output[$key] = $url;
            } else {
                add_settings_error(
                    'comodoc_plugin_settings',
                    'invalid_redirect_url',
                    __('The redirect URL must be a valid HTTP or HTTPS URL.', 'como-documents')
                );

                $output[$key] = isset($existing[$key]) && is_string($existing[$key])
                    ? $existing[$key]
                    : $definition['default'];
            }

            continue;
        }

        $value = isset($input[$key]) && is_string($input[$key])
            ? sanitize_text_field(wp_unslash($input[$key]))
            : '';

        if (in_array($value, $definition['allowed'], true)) {
            $output[$key] = $value;
        } else {
            $output[$key] = isset($existing[$key]) &&
                in_array($existing[$key], $definition['allowed'], true)
                ? $existing[$key]
                : $definition['default'];
        }
    }

    return $output;
}

/**
 * Settings section description.
 */
function comodoc_comodoc_plugin_settings_text() {
    echo '<p>' .
        esc_html__(
            'Choose which fields should be displayed on document pages.',
            'como-documents'
        ) .
        '</p>';
}

/**
 * Safely retrieve an option array.
 */
function comodoc_get_settings_array($option_name) {
    $options = get_option($option_name, array());

    return is_array($options) ? $options : array();
}

/**
 * Text / URL field callback.
 */
function comodoc_text_callback($args) {
    $option_name = isset($args[0]) ? sanitize_key($args[0]) : '';
    $field_name  = isset($args[1]) ? sanitize_key($args[1]) : '';
    $note        = isset($args[2]) ? $args[2] : '';

    $options = comodoc_get_settings_array($option_name);

    $value = isset($options[$field_name]) && is_string($options[$field_name])
        ? $options[$field_name]
        : '';

    $field_id = $option_name . '-' . $field_name;
    ?>
    <input
        type="url"
        id="<?php echo esc_attr($field_id); ?>"
        name="<?php echo esc_attr($option_name . '[' . $field_name . ']'); ?>"
        value="<?php echo esc_attr($value); ?>"
        class="settings-input"
        style="width: 25em"
    />
    <br />
    <label for="<?php echo esc_attr($field_id); ?>" class="como-label">
        <?php echo esc_html($note); ?>
    </label>
    <?php
}

/**
 * Textarea field callback retained for compatibility.
 */
function comodoc_textarea_callback($args) {
    $option_name = isset($args[0]) ? sanitize_key($args[0]) : '';
    $field_name  = isset($args[1]) ? sanitize_key($args[1]) : '';
    $note        = isset($args[2]) ? $args[2] : '';

    $options = comodoc_get_settings_array($option_name);

    $value = isset($options[$field_name]) && is_string($options[$field_name])
        ? $options[$field_name]
        : '';

    $field_id = $option_name . '-' . $field_name;
    ?>
    <textarea
        id="<?php echo esc_attr($field_id); ?>"
        name="<?php echo esc_attr($option_name . '[' . $field_name . ']'); ?>"
        class="settings-input"
    ><?php echo esc_textarea($value); ?></textarea>
    <br />
    <label for="<?php echo esc_attr($field_id); ?>" class="como-label">
        <?php echo esc_html($note); ?>
    </label>
    <?php
}

/**
 * Radio field callback.
 */
function comodoc_radio_field_callback($args) {
    $option_name = isset($args[0]) ? sanitize_key($args[0]) : '';
    $field_name  = isset($args[1]) ? sanitize_key($args[1]) : '';
    $note        = isset($args[2]) ? $args[2] : '';

    $radio_options = isset($args[3]) && is_array($args[3])
        ? $args[3]
        : array();

    $options = comodoc_get_settings_array($option_name);
    $definitions = comodoc_get_setting_definitions();

    $allowed = isset($definitions[$field_name]['allowed'])
        ? $definitions[$field_name]['allowed']
        : array_keys($radio_options);

    $default = isset($definitions[$field_name]['default'])
        ? $definitions[$field_name]['default']
        : '';

    $value = isset($options[$field_name]) &&
        is_string($options[$field_name]) &&
        in_array($options[$field_name], $allowed, true)
        ? $options[$field_name]
        : $default;

    $field_id = $option_name . '-' . $field_name;
    ?>
    <fieldset>
        <?php foreach ($radio_options as $key => $label) : ?>
            <label for="<?php echo esc_attr($field_id . '-' . $key); ?>">
                <input
                    type="radio"
                    id="<?php echo esc_attr($field_id . '-' . $key); ?>"
                    name="<?php echo esc_attr($option_name . '[' . $field_name . ']'); ?>"
                    value="<?php echo esc_attr($key); ?>"
                    <?php checked($value, $key); ?>
                />
                <?php echo esc_html($label); ?>
            </label>
            &nbsp;&nbsp;&nbsp;
        <?php endforeach; ?>
    </fieldset>

    <?php if ($note !== '') : ?>
        <p class="description" id="<?php echo esc_attr($field_id . '-description'); ?>">
            <?php echo esc_html($note); ?>
        </p>
    <?php endif; ?>
    <?php
}

/**
 * Select field callback retained for compatibility.
 */
function comodoc_select_callback($args) {
    $option_name = isset($args[0]) ? sanitize_key($args[0]) : '';
    $field_name  = isset($args[1]) ? sanitize_key($args[1]) : '';
    $note        = isset($args[2]) ? $args[2] : '';

    $select_options = isset($args[3]) && is_array($args[3])
        ? $args[3]
        : array();

    $options = comodoc_get_settings_array($option_name);

    $value = isset($options[$field_name]) && is_string($options[$field_name])
        ? $options[$field_name]
        : '';

    $field_id = $option_name . '-' . $field_name;
    ?>
    <select
        id="<?php echo esc_attr($field_id); ?>"
        name="<?php echo esc_attr($option_name . '[' . $field_name . ']'); ?>"
        class="settings-input"
    >
        <option value="">
            <?php esc_html_e('< Select >', 'como-documents'); ?>
        </option>

        <?php foreach ($select_options as $key => $label) : ?>
            <option
                value="<?php echo esc_attr($key); ?>"
                <?php selected($value, (string) $key); ?>
            >
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br />
    <label for="<?php echo esc_attr($field_id); ?>" class="como-label">
        <?php echo esc_html($note); ?>
    </label>
    <?php
}