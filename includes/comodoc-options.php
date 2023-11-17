<?php
function comodoc_add_settings_page() {
  add_options_page(
    'Document Options',
    'Document Options',
    'manage_options',
    'comodoc_plugin_settings',
    'comodoc_render_settings_page'
  );
}
add_action( 'admin_menu', 'comodoc_add_settings_page' );
function comodoc_render_settings_page() {
?>
	<h2>Document Options</h2>
	<form action="options.php" method="post">
		<?php 
		settings_fields( 'comodoc_plugin_settings' );
		do_settings_sections( 'comodoc_plugin_settings' );
		?>
		<input
		  type="submit"
		  name="submit"
		  class="button button-primary"
		  value="<?php esc_attr_e( 'Save' ); ?>"
		/>
	</form>
<?php
}
function comodoc_register_settings() {
	register_setting(
		'comodoc_plugin_settings',
    	'comodoc_plugin_settings',
    	'comodoc_validate_example_plugin_settings'
	);
	add_settings_section(
    	'comodoc_plugin_settings',
    	'Hidden Admin Fields',
    	'comodoc_comodoc_plugin_settings_text',
    	'comodoc_plugin_settings'
  	);
	
	$fields = array(
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'publicly-queriable',
			'label'=>'Publicly Queriable',
			'options'=>array('yes'=>'Yes','no'=>'No'),
			'note'=>'Should individual Document pages be viewable',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'redirect-url',
			'label'=>'Redirect URL',
			'options'=>array(),
			'value'=>'',
			'note'=>'If not Publicly Queriable, where should pages redirect?',
			'callback'=>'comodoc_text_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-authors',
			'label'=>'Authors Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-publication',
			'label'=>'Publication Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-event',
			'label'=>'Event Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-abstract',
			'label'=>'Abstract Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-funding',
			'label'=>'Funding Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-volume',
			'label'=>'Volume Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-issue',
			'label'=>'Issue Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-pages',
			'label'=>'Pages Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-date',
			'label'=>'Date Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-doi',
			'label'=>'DOI Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-document',
			'label'=>'Document Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-document-2',
			'label'=>'Document 2 Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		),
		array(
			'var'=>'comodoc_plugin_settings',
			'name'=>'show-link',
			'label'=>'Link Field',
			'options'=>array('show'=>'Show','hide'=>'Hide'),
			'note'=>'',
			'callback'=>'comodoc_radio_field_callback'
		)
	);
	
	foreach ($fields as $field) {
		add_settings_field(
			$field['name'],
			$field['label'],
			$field['callback'],
			$field['var'],
			$field['var'],
			array(
				$field['var'],
				$field['name'],
				$field['note'],
				$field['options']
			)
		);	
	}
}
add_action( 'admin_init', 'comodoc_register_settings' );
// Sanitize Displaye Options
function comodoc_validate_example_plugin_settings( $input ) {
    $output = array();
    foreach ($input as $key=>$val) {
		if(isset($input[$key])) {
			$output[$key] = $input[$key];
        }  
    } 
    return $output;
}
function comodoc_comodoc_plugin_settings_text() {
  echo '<p>Select which fields should be hidden on the document admin screens</p>';
}
// Text Field Callback
function comodoc_text_callback($args){
	$options = get_option($args[0]);
    $val = ((isset($options[$args[1]])) ? $options[$args[1]] : ''); 
	?><input type="text" id="<?=$args[1]?>" name="<?=$args[0]?>[<?=$args[1]?>]" value="<?=$val?>" class="settings-input" style="width: 25em" /><br><label for="<?=$args[1]?>" class="como-label"><?=$args[2]?></label><?php
}
// Textarea Callback
function comodoc_textarea_callback($args){
	$options = get_option($args[0]);
    $val = ((isset($options[$args[1]])) ? $options[$args[1]] : ''); 
	?><textarea id="<?=$args[1]?>" name="<?=$args[0]?>[<?=$args[1]?>]" class="settings-input"><?=$val?></textarea><br><label for="<?=$args[1]?>" class="como-label"><?=$args[2]?></label><?php
}
// Radio Field Callback
function comodoc_radio_field_callback($args){
	$options = get_option($args[0]);
    $val = ((isset($options[$args[1]])) ? $options[$args[1]] : '');
	$radioOptions = $args[3];
	$b = 0;
	?>
	<?php
	foreach($radioOptions as $k=>$v) {
		$selected = (($k == $val) ? ' checked="checked"' : (((empty($val) && ($b ==0))) ? ' checked="checked"' : ''));
		?><input type="radio" name="<?=$args[0]?>[<?=$args[1]?>]" value="<?=$k?>"<?=$selected?>> <?=$v?> &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; <?php
		$b++;	
	}
	?>	
	<br><label for="<?=$args[1]?>" class="como-label"><?=$args[2]?></label><?php
}
// Select Callback
function comodoc_select_callback($args){
	$options = get_option($args[0]);
    $val = ((isset($options[$args[1]])) ? $options[$args[1]] : '');
	$selOptions = $args[3];
	?>
	<select id="<?=$args[1]?>" name="<?=$args[0]?>[<?=$args[1]?>]" class="settings-input" />
		<option value="">&lt; Select &gt;</option>
	<?php
		foreach($selOptions as $k=>$v) {
			$selected = (($k == $val) ? ' selected="selected"' : '');
			?><option value="<?=$k?>"<?=$selected?>><?=$v?></option><?php
		}
	?>	
	</select>
	<br><label for="<?=$args[1]?>" class="como-label"><?=$args[2]?></label><?php
}