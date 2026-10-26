<?php
/*
Plugin Name: Como Documents
Plugin URI: http://www.comocreative.com/
Version: 2.0.1
Author: Como Creative LLC
Description: Plugin designed to enable and easy Document Gallery. 
Shortcode example: [comodocs featured=TRUE/FALSE template=TEMPLATE NAME document-cat=MEMBER_TYPE orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC limit=#]  
Custom templates can be created in your theme in a folder named "comostrap-docs" 
*/
defined('ABSPATH') or die('No Hackers!');
/* Include plugin updater. */
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/updater.php' );
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/comodoc-options.php' );
function document_cpt() {
	$labels = array(
		'name'                => _x( 'Documents', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Document', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Documents', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Document Member:', 'text_domain' ),
		'all_items'           => __( 'All Documents', 'text_domain' ),
		'view_item'           => __( 'View Document', 'text_domain' ),
		'add_new_item'        => __( 'Add Document', 'text_domain' ),
		'add_new'             => __( 'Add New Document', 'text_domain' ),
		'edit_item'           => __( 'Edit Document', 'text_domain' ),
		'update_item'         => __( 'Update Document', 'text_domain' ),
		'search_items'        => __( 'Search Documents', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                => 'document',
		'with_front'          => true,
		'pages'               => true,
		'feeds'               => true,
	);
	$capabilities = array(
		'edit_post'           => 'edit_document',
		'read_post'           => 'read_document',
		'delete_post'         => 'delete_document',
		'delete_posts'        => 'delete_documents',
		'edit_posts'          => 'edit_documents',
		'edit_others_posts'   => 'edit_others_documents',
		'delete_others_posts' => 'delete_others_documents',
		'delete_private_posts'=> 'delete_private_documents',
		'delete_published_posts'=> 'delete_published_documents',
		'publish_posts'       => 'publish_documents',
		'read_private_posts'  => 'read_private_documents',
	);
	$args = array(
		'label'               => __( 'como_documents', 'text_domain' ),
		'description'         => __( 'Documents', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array('title','revisions','thumbnail','page-attributes'),
		'taxonomies'          => array(),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 23,
		'menu_icon'           => 'dashicons-media-document',
		'can_export'          => true,
		'has_archive'         => false, //Set to false to hide Archive Page
		'exclude_from_search' => false,
		'publicly_queryable'  => true, // Set to false to hide Single Pages
		'rewrite'             => $rewrite,
		'capabilities'        => $capabilities,
	);
	register_post_type( 'document', $args );
}
add_action( 'init', 'document_cpt', 0 );
// Document Category Taxonomy
add_action( 'init', 'create_document_tax', 0 );
function create_document_tax() {
	$labels = array(
		'name'              => _x( 'Document Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Document Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Document Categories' ),
		'all_items'         => __( 'All Document Categories' ),
		'parent_item'       => __( 'Parent Document Category' ),
		'parent_item_colon' => __( 'Parent Document Category:' ),
		'edit_item'         => __( 'Edit Document Category' ),
		'update_item'       => __( 'Update Document Category' ),
		'add_new_item'      => __( 'Add New Document Category' ),
		'new_item_name'     => __( 'New Document Category Name' ),
		'menu_name'         => __( 'Document Category' ),
	);
	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'document' ),
	);
	register_taxonomy( 'document-cat', array( 'document' ), $args );
}
// Give Admin & Editor Access to Documents
function add_document_capability() {
	$roles = array('administrator','editor');
	foreach ($roles as $role) {
		$role = get_role($role);
		if ( ! $role ) {
			continue;
		}
		$role->add_cap('edit_document'); 
		$role->add_cap('read_document');
		$role->add_cap('delete_document');
		$role->add_cap('delete_documents');
		$role->add_cap('edit_documents');
		$role->add_cap('edit_others_documents');
		$role->add_cap('delete_others_documents');
		$role->add_cap('delete_private_documents');
		$role->add_cap('delete_published_documents');
		$role->add_cap('publish_documents');
		$role->add_cap('read_private_documents');
	}
}
add_action( 'admin_init', 'add_document_capability');
// Add Admin Sort
add_action( 'restrict_manage_posts', 'comodocs_restrict_manage_posts');
function comodocs_restrict_manage_posts() {
	global $typenow;
	$taxonomy = 'document-cat';
	if($typenow == "document"){
		$filters = array($taxonomy);
		foreach ($filters as $tax_slug) {
			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms( array( 'taxonomy' => $tax_slug, 'hide_empty' => false ) );
			echo '<select name="' . esc_attr( $tax_slug ) . '" id="' . esc_attr( $tax_slug ) . '" class="postform">';
			echo '<option value="">' . esc_html( 'Show All ' . $tax_name ) . '</option>';
			if ( ! is_wp_error( $terms ) && is_array( $terms ) ) {
				$selected_term = isset( $_GET[ $tax_slug ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax_slug ] ) ) : '';
				foreach ( $terms as $term ) {
					$selected = selected( $selected_term, $term->slug, false );
					echo '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( $term->name . ' (' . $term->count . ')' ) . '</option>';
				}
			}
			echo '</select>'; 
		}
	}
}
// Add Columns to Admin Screen
//add_filter( 'manage_document_posts_columns', 'set_custom_edit_document_columns', 1);
function set_custom_edit_document_columns($columns) {
	unset($columns['title']);
	unset($columns['date']);
	unset($columns['wps_post_thumbs']);
	unset($columns['taxonomy-deal-type']);
	$columns['title'] = __('Candidate', 'como-docs');
	$columns['comodoc-date'] = __('Date', 'como-docs');
	
	$custCols[] = array('comodoc-date'); 
	$custCols = getCustomFields($custCols);
	$custCount = ((is_array($custCols)) ? count($custCols) : 0);
	if ($custCount > 0) {
		for ($c=0;$c<$custCount;$c++) {
			if (($custCols[$c][3] != 'title-column') && ($custCols[$c][3] != 'progress-column')) {
				if ($custCols[$c][1] != 'title') {
					$columns[$custCols[$c][0]] = __($custCols[$c][2], 'como-docs');
				} 
			}
		}
	}
	$columns['date'] = __( 'Date Published', 'como-docs' ); 
	return $columns;
}
// Add the data to the custom columns for the post type:
//add_action( 'manage_document_posts_custom_column' , 'custom_document_column', 1, 2 );
function custom_document_column( $column, $post_id ) {
	
	$custCols = array();
	$custCols[] = array('comodoc-date','text','Date','text-column'); 
	$custCols = getCustomFields($custCols);
	$custCount = ((is_array($custCols)) ? count($custCols) : 0);
			
	if ($custCount > 0) {
		for ($c=0; $c<$custCount; $c++) {
			$key = true;
			do {
				if ($column == $custCols[$c][0]) {
					if ($custCols[$c][3] == 'title-column') {
						echo 'title'; 
						$key = false;
					} else {
						echo get_post_meta($post_id ,$custCols[$c][0], true);
						$key = false; 						
					}
				} else {
					$key = false;
				}
			 } while ($key);
		}
	}
}
/* ##################### Document Info Meta Box ##################### */
function comodoc_init() {
	add_meta_box('como_section_meta', __('Document Info','comostrap-textdomain'),'comodoc_meta_callback','document','normal','high');
	add_action( 'admin_enqueue_scripts', 'comodoc_fileupload_engueue' );
}
add_action('admin_init','comodoc_init', 1);

// Loads the image management javascript
function comodoc_fileupload_engueue() {
	wp_enqueue_media();
  	wp_register_script('meta-file-upload', plugin_dir_url( __FILE__ ) . '/js/document-upload.js', array('jquery'));
  	wp_localize_script('meta-file-upload', 'meta_image',
    	array(
        	'title' => __( 'Choose or Upload a File', 'comostrap-textdomain' ),
           	'button' => __( 'Use this file', 'comostrap-textdomain' ),
      	)
   	);
  	wp_enqueue_script( 'meta-file-upload' );
}
function comodoc_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'comodoc_nonce' );
    $comodoc_stored_meta = get_post_meta( $post->ID );
	//print_r($comodoc_stored_meta);
	$display = get_option('comodoc_plugin_settings');
	?>
    
    <p <?=((isset($display['show-authors'])) ? (($display['show-authors'] == 'hide') ? 'style="display: none"' : '') : '')?>><label for="comodoc-author" class="comometa-row-title"><?php _e( 'Author(s)', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-author" id="comodoc-author" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-author'] ) ) echo $comodoc_stored_meta['comodoc-author'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-publication'])) ? (($display['show-publication'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-publication" class="comometa-row-title"><?php _e( 'Publication', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-publication" id="comodoc-publication" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-publication'] ) ) echo $comodoc_stored_meta['comodoc-publication'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-event'])) ? (($display['show-event'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-event" class="comometa-row-title"><?php _e( 'Event', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-event" id="comodoc-event" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-event'] ) ) echo $comodoc_stored_meta['comodoc-event'][0]; ?>" /></span></p>
    
	<p<?=((isset($display['show-abstract'])) ? (($display['show-abstract'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-abstract" class="comometa-row-title"><?php _e( 'Abstract', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-abstract" id="comodoc-abstract" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-abstract'] ) ) echo $comodoc_stored_meta['comodoc-abstract'][0]; ?>" /></span></p>
	<p<?=((isset($display['show-funding'])) ? (($display['show-funding'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-funding" class="comometa-row-title"><?php _e( 'Funding', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-funding" id="comodoc-funding" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-funding'] ) ) echo $comodoc_stored_meta['comodoc-funding'][0]; ?>" /></span></p>
	<p<?=((isset($display['show-volume'])) ? (($display['show-volume'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-volume" class="comometa-row-title"><?php _e( 'Volume Number', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-volume" id="comodoc-volume" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-volume'] ) ) echo $comodoc_stored_meta['comodoc-volume'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-issue'])) ? (($display['show-issue'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-number" class="comometa-row-title"><?php _e( 'Issue Number', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-number" id="comodoc-number" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-number'] ) ) echo $comodoc_stored_meta['comodoc-number'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-pages'])) ? (($display['show-pages'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-page-start" class="comometa-row-title"><?php _e( 'Pages', 'como-docs' )?></label>
  	<span class="comometa-row-content">Start: <input type="text" class="inline" name="comodoc-page-start" id="comodoc-page-start" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-page-start'] ) ) echo $comodoc_stored_meta['comodoc-page-start'][0]; ?>" /> End: <input type="text" class="inline" name="comodoc-page-end" id="comodoc-page-end" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-page-end'] ) ) echo $comodoc_stored_meta['comodoc-page-end'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-date'])) ? (($display['show-date'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-date" class="comometa-row-title"><?php _e( 'Date', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-date" id="comodoc-date" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-date'] ) ) echo $comodoc_stored_meta['comodoc-date'][0]; ?>" /></span></p>
    
    <p<?=((isset($display['show-doi'])) ? (($display['show-doi'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-doi" class="comometa-row-title"><?php _e( 'DOI', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-doi" id="comodoc-doi" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-doi'] ) ) echo $comodoc_stored_meta['comodoc-doi'][0]; ?>" /></span></p>
 
    <?php
    $comodoc_embed_pdf = isset($comodoc_stored_meta['comodoc-embed-pdf'][0])
        && (string) $comodoc_stored_meta['comodoc-embed-pdf'][0] === '1';
    ?>
    <p class="comodoc-embed-pdf-setting">
        <label for="comodoc-embed-pdf" class="comometa-row-title"><?php esc_html_e('Embed PDF', 'como-docs'); ?></label>
        <span class="comometa-row-content">
            <label>
                <input type="checkbox" name="comodoc-embed-pdf" id="comodoc-embed-pdf" value="1" <?php checked($comodoc_embed_pdf); ?> />
                <?php esc_html_e('Embed this document’s PDF in the single-document page and the default-single-embed template.', 'como-docs'); ?>
            </label>
        </span>
    </p>

    <p class="image-upload"<?=((isset($display['show-document'])) ? (($display['show-document'] == 'hide') ? ' style="display: none"' : '') : '')?>>
        <label for="comodoc-file" class="comometa-row-title"><?php _e( 'Document File', 'comostrap-textdomain' )?></label>
        <span class="comometa-row-content upload-field">
			<input type="text" name="comodoc-file" id="comodoc-file" class="como-upload-field" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-file'] ) ) echo $comodoc_stored_meta['comodoc-file'][0]; ?>" />
			<input type="hidden" name="comodoc-file-id" id="comodoc-file-id" class="como-upload-id-field" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-file-id'] ) ) echo $comodoc_stored_meta['comodoc-file-id'][0]; ?>" />	
			<?php
				if (!empty($comodoc_stored_meta['comodoc-file'][0])) {
					$upload1class = 'hidden';
					$remove1class = ''; 
				} else {
					$upload1class = '';
					$remove1class = 'hidden';
				}
			?>
			<input type="button" class="remove-upload-button <?=$remove1class?>" value="<?php _e( 'Remove File', 'comostrap-textdomain' )?>" />
			<input type="button" class="meta-upload-button <?=$upload1class?>" value="<?php _e( 'Choose or Upload a File', 'comostrap-textdomain' )?>" />
		</span>	
    </p>
    
    <p class="image-upload"<?=((isset($display['show-document-2'])) ? (($display['show-document-2'] == 'hide') ? ' style="display: none"' : '') : '')?>>
        <label for="comodoc-file" class="comometa-row-title"><?php _e( 'Document File 2', 'comostrap-textdomain' )?></label>
        <span class="comometa-row-content upload-field">
			<input type="text" name="comodoc-file-2" id="comodoc-file-2" class="como-upload-field" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-file-2'] ) ) echo $comodoc_stored_meta['comodoc-file-2'][0]; ?>" />
			<input type="hidden" name="comodoc-file-id-2" id="comodoc-file-id-2" class="como-upload-id-field" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-file-id-2'] ) ) echo $comodoc_stored_meta['comodoc-file-id-2'][0]; ?>" />
			
			<?php
				if (!empty($comodoc_stored_meta['comodoc-file-2'][0])) {
					$upload2class = 'hidden';
					$remove2class = ''; 
				} else {
					$upload2class = '';
					$remove2class = 'hidden';
				}
			?>
			<input type="button" class="remove-upload-button <?=$remove2class?>" value="<?php _e( 'Remove File', 'comostrap-textdomain' )?>" />
			<input type="button" class="meta-upload-button <?=$upload2class?>" value="<?php _e( 'Choose or Upload a File', 'comostrap-textdomain' )?>" />
		</span>
    </p>
    
    <p<?=((isset($display['show-link'])) ? (($display['show-link'] == 'hide') ? ' style="display: none"' : '') : '')?>><label for="comodoc-link" class="comometa-row-title"><?php _e( 'Link', 'como-docs' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comodoc-link" id="comodoc-link" value="<?php if ( isset ( $comodoc_stored_meta['comodoc-link'] ) ) echo $comodoc_stored_meta['comodoc-link'][0]; ?>" /></span></p>
  	
  	<input type="hidden" name="comoupdate_flag" value="true" />
    
    <?php 
}
// Saves the Document Info Section meta input
function comodoc_meta_save( $post_id ) {
    if ( ! isset( $_POST['comoupdate_flag'] ) ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['comodoc_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['comodoc_nonce'] ) ), basename( __FILE__ ) ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $meta_types = array(
        'comodoc-author'       => 'text',
        'comodoc-publication'  => 'text',
        'comodoc-event'        => 'text',
        'comodoc-abstract'     => 'textarea',
        'comodoc-funding'      => 'text',
        'comodoc-volume'       => 'text',
        'comodoc-number'       => 'text',
        'comodoc-page-start'   => 'text',
        'comodoc-page-end'     => 'text',
        'comodoc-date'         => 'text',
        'comodoc-doi'          => 'text',
        'comodoc-file'         => 'url',
        'comodoc-file-id'      => 'int',
        'comodoc-file-2'       => 'url',
        'comodoc-file-id-2'    => 'int',
        'comodoc-link'         => 'url',
        'comodoc-embed-pdf'    => 'checkbox',
    );

    foreach ( $meta_types as $key => $type ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            delete_post_meta( $post_id, $key );
            continue;
        }

        $value = wp_unslash( $_POST[ $key ] );
        if ( is_array( $value ) ) {
            continue;
        }

        switch ( $type ) {
            case 'url':
                $value = esc_url_raw( $value );
                break;
            case 'int':
                $value = absint( $value );
                break;
            case 'textarea':
                $value = sanitize_textarea_field( $value );
                break;
            case 'checkbox':
                $value = ( '1' === (string) $value ) ? '1' : '0';
                break;
            default:
                $value = sanitize_text_field( $value );
                break;
        }

        update_post_meta( $post_id, $key, $value );
    }
}
add_action( 'save_post', 'comodoc_meta_save' );

// Adds the meta box stylesheet when appropriate 
function comodocs_admin_styles(){
    global $typenow;

    if($typenow == 'document') {
        wp_enqueue_style('comodoc_meta_box_styles', plugin_dir_url( __FILE__ ) .'css/admin.min.css');
    }
}

// Allow users to show/hide individual Document pages.
add_action('admin_print_styles', 'comodocs_admin_styles');
add_action('template_redirect', 'como_redirect_single_doc_post', 100);

function como_redirect_single_doc_post() {
    if (!is_singular('document')) {
        return;
    }

    // get_option() may return false when the setting has never been saved.
    $display = get_option('comodoc_plugin_settings', array());
    if (!is_array($display)) {
        $display = array();
    }

    // Only redirect when the option is explicitly set to "no".
    if (!isset($display['publicly-queriable']) || $display['publicly-queriable'] !== 'no') {
        return;
    }

    $redirect = home_url('/');
    if (!empty($display['redirect-url']) && is_string($display['redirect-url'])) {
        $candidate = esc_url_raw($display['redirect-url']);
        if ($candidate !== '') {
            $redirect = wp_validate_redirect($candidate, home_url('/'));
        }
    }

    wp_safe_redirect($redirect, 301);
    exit;
}

/**
 * Print String into Variable Function.
 */
if ( ! function_exists( 'insertStringText' ) ) {
	function insertStringText( $text, $txtdomain ) {
		return __( $text, $txtdomain );
	}
}

/* ##################### Shortcode to Show Documents ##################### */
// Usage: [comodocs template=TEMPLATE NAME document-cat=DOCUMENT_CATEGORY orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC]
class ComoDocs_Shortcode {
	static $add_script;
	static $add_style;
	static function init() {
		add_shortcode('comodocs', array(__CLASS__, 'handle_shortcode'));
		//add_action('init', array(__CLASS__, 'register_script'));
		//add_action('wp_footer', array(__CLASS__, 'print_script'));
	}
	
	static function handle_shortcode($atts) {
		
		if (!is_admin()) {
			self::$add_style = false;
			self::$add_script = false;
			$comodoc_template = (isset($atts['template']) ? $atts['template'] : 'default');
			$document_id = (isset($atts['id']) ? $atts['id'] : '');
			$document_cat = (isset($atts['document-cat']) ? $atts['document-cat'] : '');
			$orderby = (isset($atts['orderby']) ? $atts['orderby'] : 'menu_order');
			$order = (isset($atts['order']) ? $atts['order'] : 'ASC');
			$limit = (isset($atts['limit']) ? $atts['limit'] : '');
			$args = array('post_type'=>'document','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>$orderby,'order'=>$order);
			if ($document_id) { $args['p'] = $document_id; } else {
				if ($document_cat) { $args['tax_query'] = array(array('taxonomy'=>'document-cat','field'=>'slug','terms'=>$document_cat)); }
				if ($limit) { $args['posts_per_page'] = $limit; }
			}
			$query = new WP_Query( $args );
			if ($query->have_posts()) { 
				unset($comodoc_array);
				while ($query->have_posts()) {
					$query->the_post(); 
					unset($doc);
					$doc['id'] = get_the_ID();
					$doc['post-date'] = get_the_date();
					$doc['image'] = get_the_post_thumbnail($doc['id'],'full',array('class'=>'team-photo'));
					$doc['title'] = get_the_title();
					$doc['author'] = get_post_meta($doc['id'],'comodoc-author',true);
					$doc['publication'] = get_post_meta($doc['id'],'comodoc-publication',true);
					$doc['event'] = get_post_meta($doc['id'],'comodoc-event',true);
					$doc['abstract'] = get_post_meta($doc['id'],'comodoc-abstract',true);
					$doc['funding'] = get_post_meta($doc['id'],'comodoc-funding',true);
					$doc['volume'] = get_post_meta($doc['id'],'comodoc-volume',true);
					$doc['number'] = get_post_meta($doc['id'],'comodoc-number',true);
					$doc['page-start'] = get_post_meta($doc['id'],'comodoc-page-start',true);
					$doc['page-end'] = get_post_meta($doc['id'],'comodoc-page-end',true);
					$doc['date'] = get_post_meta($doc['id'],'comodoc-date',true);
					$doc['doi'] = get_post_meta($doc['id'],'comodoc-doi',true);
					$doc['file'] = get_post_meta($doc['id'],'comodoc-file',true);
					$doc['file-id'] = get_post_meta($doc['id'],'comodoc-file-id',true);
					$doc['file-2'] = get_post_meta($doc['id'],'comodoc-file-2',true);
					$doc['file-id-2'] = get_post_meta($doc['id'],'comodoc-file-id-2',true);
					$doc['link'] = get_post_meta($doc['id'],'comodoc-link',true);
                    $doc['embed-pdf'] = get_post_meta($doc['id'], 'comodoc-embed-pdf', true);
					$doc['content'] = get_the_content();
					$doc['category'] = $document_cat; 
					$comodoc_array[] = $doc;
				}
				
				
				echo '<p><strong>$comodoc_template: '. $comodoc_template .'</strong></p>'; 
				
				if ($comodoc_template) {
					$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-docs/'. $comodoc_template .'.php';
					if (file_exists($temp)) {
						include($temp);
					} else {
						$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-docs/'. $comodoc_template .'.php';
						if (file_exists($temp)) {
							include(plugin_dir_path( __FILE__ ) .'templates/'. $comodoc_template .'.php');	
						} else {
							include(plugin_dir_path( __FILE__ ) .'templates/default.php');
						}
					}
				} else {
					include(plugin_dir_path( __FILE__ ) .'templates/default.php');
				}
				$comodocs = $comodocDisplay;
			}
			if (isset($comodocs)) { return $comodocs; }
		}
	}
	
	// Register & Print Scripts
	/*static function register_script() {
		wp_register_script('comoteams_script', plugins_url('js/comoteams.js', __FILE__), array('jquery'), '1.0', true);
	}
	static function print_script() {
		if ( ! self::$add_script )
			return;
		wp_print_scripts('comoteams_script');
	}*/
}
ComoDocs_Shortcode::init();

define('PLUGIN_URL', plugin_dir_url(__FILE__));
// Usage: [comodocs-filtered page=1 nav-template=TEMPLATE list-template=TEMPLATE pagination-template=TEMPLATE tax=DOCUMENT_CATEGORY text-loading=LOADING TEXT text-results=RESULT TEXT scroll-top=true/false scroll-offset=OFFSET scroll-delay=DELAY text- orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC]
class ComoDocsFilteredPaged_Shortcode {
	static $add_script;
	static $add_style;
	static function init() {
		add_shortcode('comodocs-filtered', array(__CLASS__, 'handle_shortcode'));
		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}
	static function handle_shortcode($atts) {
		if (!is_admin()) {
			self::$add_style = false;
			self::$add_script = true;
			$a = shortcode_atts( array(
				'tax'     		=> 'post_tag', // Taxonomy
				'terms'    		=> false, // Get specific taxonomy terms only
				'active'   		=> false, // Set active term by ID
				'per_page' 		=> 10, // How many posts per page
				'orderby'		=> 'date', // Order By
				'order'			=> 'DESC', // Order Direction
				'nav-template'	=> 'default-nav', // Navigation Template
				'list-template'	=> 'default-list', // List Template
				'pagination-template'	=> 'default-pagination', // Pagination Template
				'text-loading'	=> 'Loading...', // List Template
				'text-results'	=> 'Results', // List Template
				'scroll-top'	=> 'false', // Scroll to top of page on pagination click
				'scroll-offset'	=> 0, // Scroll offset
				'scroll-delay'	=> 0 // Scroll offset
			), $atts );
			
			$result = NULL;
			$terms  = get_terms( array( 'taxonomy' => $a['tax'], 'hide_empty' => true ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				ob_start(); 
				$nav_template = (isset($atts['nav-template']) ? $atts['nav-template'] : 'default-nav');
				if ($nav_template) {
					$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-docs/'. $nav_template .'.php';
					if (file_exists($temp)) {
						include($temp);
					} else {
						include(plugin_dir_path( __FILE__ ) .'templates/default-nav.php');
					}
				} else {
					include(plugin_dir_path( __FILE__ ) .'templates/default-nav.php');
				}
				$result = ob_get_clean();
			}
			return $result;
		}
	}
	// Register & Print Scripts
	static function register_script() {
		wp_register_script('comodocs/js', PLUGIN_URL .'js/ajax-filter-docs.min.js', ['jquery'], null, true);
	}
	static function print_script() {
		if ( ! self::$add_script )
			return;
		wp_enqueue_script('comodocs/js');
		wp_localize_script( 'comodocs/js', 'comoDocs', array(
			'nonce'    => wp_create_nonce( 'comoDocs' ),
			'ajax_url' => admin_url( 'admin-ajax.php' )
		));
	}
}
ComoDocsFilteredPaged_Shortcode::init();

// AJAX filter posts by taxonomy term
function vb_filter_posts() {
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'comoDocs' ) ) {
        wp_send_json( array( 'status' => 403, 'message' => 'Permission denied', 'content' => false, 'found' => 0 ), 403 );
    }

    $params = isset( $_POST['params'] ) && is_array( $_POST['params'] ) ? wp_unslash( $_POST['params'] ) : array();
    $tax = isset( $params['tax'] ) ? sanitize_key( $params['tax'] ) : 'document-cat';
    $term = isset( $params['term'] ) ? sanitize_title( $params['term'] ) : 'all-terms';
    $page = isset( $params['page'] ) ? max( 1, absint( $params['page'] ) ) : 1;
    $qty = isset( $params['qty'] ) ? absint( $params['qty'] ) : 10;
    $qty = min( 100, max( 1, $qty ) );
    $orderby = isset( $params['orderby'] ) ? sanitize_key( $params['orderby'] ) : 'date';
    $allowed_orderby = array( 'date', 'title', 'menu_order', 'modified', 'ID', 'name', 'rand' );
    if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
        $orderby = 'date';
    }
    $order = isset( $params['order'] ) ? strtoupper( sanitize_text_field( $params['order'] ) ) : 'DESC';
    $order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

    // Only use the plugin's document taxonomy for this document filter.
    if ( 'document-cat' !== $tax || ! taxonomy_exists( $tax ) ) {
        wp_send_json( array( 'status' => 400, 'message' => 'Invalid taxonomy.', 'content' => false, 'found' => 0 ), 400 );
    }

    if ( 'all-terms' !== $term && ! term_exists( $term, $tax ) ) {
        wp_send_json( array( 'status' => 400, 'message' => 'Term does not exist.', 'content' => false, 'found' => 0 ), 400 );
    }

    $tax_qry = array();
    if ( 'all-terms' !== $term ) {
        $tax_qry[] = array(
            'taxonomy' => $tax,
            'field'    => 'slug',
            'terms'    => $term,
        );
    }

    // Restrict template names to simple filenames and verify the file exists.
    $list_template = isset( $params['list-template'] ) ? sanitize_file_name( $params['list-template'] ) : 'default-list';
    $pagination_template = isset( $params['pagination-template'] ) ? sanitize_file_name( $params['pagination-template'] ) : 'default-pagination';
    $theme_dir = trailingslashit( get_stylesheet_directory() ) . 'como-docs/';
    $plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'templates/';
    $list_path = $theme_dir . $list_template . '.php';
    if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $list_template ) || ! is_file( $list_path ) ) {
        $list_path = $plugin_dir . 'default-list.php';
    }
    $pagination_path = $theme_dir . $pagination_template . '.php';
    if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $pagination_template ) || ! is_file( $pagination_path ) ) {
        $pagination_template = 'default-pagination';
    }

    $args = array(
        'paged'          => $page,
        'post_type'      => 'document',
        'post_status'    => 'publish',
        'posts_per_page' => $qty,
        'orderby'        => $orderby,
        'order'          => $order,
    );
    if ( ! empty( $tax_qry ) ) {
        $args['tax_query'] = $tax_qry;
    }

    $qry = new WP_Query( $args );
    ob_start();

    if ( $qry->have_posts() ) {
        while ( $qry->have_posts() ) {
            $qry->the_post();
            $doc = get_post_meta( get_the_ID() );
            include $list_path;
        }
        vb_ajax_pager( $qry, $page, $pagination_template );
        $found = (int) $qry->found_posts;
        wp_reset_postdata();
        $content = ob_get_clean();
        wp_send_json( array( 'status' => 200, 'found' => $found, 'content' => $content ) );
    }

    wp_reset_postdata();
    $content = ob_get_clean();
    wp_send_json( array( 'status' => 201, 'message' => 'No posts found', 'found' => 0, 'content' => $content ) );
}
add_action('wp_ajax_do_filter_posts', 'vb_filter_posts');
add_action('wp_ajax_nopriv_do_filter_posts', 'vb_filter_posts'); 

// Pagination
function vb_ajax_pager( $query = null, $paged = 1, $pgTemplate = 'default-pagination' ) {
    if (!$query)
        return;
    $paginate = paginate_links([
        'base'      => '%_%',
        'type'      => 'array',
        'total'     => $query->max_num_pages,
        'format'    => '#page=%#%',
        'current'   => max( 1, $paged ),
        'prev_text' => 'Prev',
        'next_text' => 'Next'
    ]);
    if ($query->max_num_pages > 1) : ?>
		<?php
		$pagination_template = (($pgTemplate) ? $pgTemplate : 'default-pagination');
		if ($pagination_template) {
			$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-docs/'. $pagination_template .'.php';
			if (file_exists($temp)) {
				include($temp);
			} else {
				include(plugin_dir_path( __FILE__ ) .'templates/default-pagination.php');
			}
		} else {
			include(plugin_dir_path( __FILE__ ) .'templates/default-pagination.php');
		}
		?>
    <?php endif;
}

/********* TinyMCE Button Add-On ***********/
add_action( 'after_setup_theme', 'comodoc_button_setup' );
if (!function_exists('comodoc_button_setup')) {
    function comodoc_button_setup() {
        add_action( 'init', 'comodoc_button' );
    }
}
if ( ! function_exists( 'comodoc_button' ) ) {
    function comodoc_button() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }
        add_filter( 'mce_external_plugins', 'comodoc_add_buttons' );
        add_filter( 'mce_buttons', 'comodoc_register_buttons' );
    }
}
if ( ! function_exists( 'comodoc_add_buttons' ) ) {
    function comodoc_add_buttons( $plugin_array ) {
        $plugin_array['comodocButton'] = plugin_dir_url( __FILE__ ) .'js/tinymce_document_button.js';
        return $plugin_array;
    }
}
if ( ! function_exists( 'comodoc_register_buttons' ) ) {
    function comodoc_register_buttons( $buttons ) {
        array_push( $buttons, 'comodocButton' );
        return $buttons;
    }
}
// Output TinyMCE configuration after the editor scripts are available.
add_action( 'after_wp_tiny_mce', 'comodoc_tinymce_extra_vars' );
if ( ! function_exists( 'comodoc_tinymce_extra_vars' ) ) {
    function comodoc_tinymce_extra_vars() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        $docTemplates = array(
            array( 'value' => 'default', 'text' => 'Default' ),
        );
		
		// Get Main PLugin Templates
        $templateDir = WP_PLUGIN_DIR . '/como-docs/templates/';
        if ( is_dir( $templateDir ) && is_readable( $templateDir ) ) {
            $files = glob( $templateDir . '*.php' );
            if ( is_array( $files ) ) {
                foreach ( $files as $file ) {
                    $name = basename( $file, '.php' );
                    if ( 'default' === $name || ! preg_match( '/^[a-zA-Z0-9_-]+$/', $name ) ) {
                        continue;
                    }
                    $docTemplates[] = array( 'value' => $name, 'text' => $name );
                }
            }
        }
		
		// Get Child Theme Templates 
		$templateDir = trailingslashit( get_stylesheet_directory() ) . 'como-docs/';
        if ( is_dir( $templateDir ) && is_readable( $templateDir ) ) {
            $files = glob( $templateDir . '*.php' );
            if ( is_array( $files ) ) {
                foreach ( $files as $file ) {
                    $name = basename( $file, '.php' );
                    if ( 'default' === $name || ! preg_match( '/^[a-zA-Z0-9_-]+$/', $name ) ) {
                        continue;
                    }
                    $docTemplates[] = array( 'value' => $name, 'text' => $name );
                }
            }
        }

        $taxArr = array();
        $terms = get_terms( array( 'taxonomy' => 'document-cat', 'hide_empty' => true ) );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $taxArr[] = array( 'value' => $term->slug, 'text' => $term->name );
            }
        }

        // Keep the legacy JS contract: these two properties are JSON strings.
        $config = array(
            'button_name' => esc_html__( 'Embed Doc(s)', 'comodoc' ),
            'button_title' => esc_html__( 'Embed Doc(s)', 'comodoc' ),
            'document_template_select_options' => wp_json_encode( $docTemplates ),
            'doc_category_options' => wp_json_encode( $taxArr ),
        );
        ?>
        <script type="text/javascript">
            window.tinyMCE_document = <?php echo wp_json_encode( $config ); ?>;
        </script>
        <?php
    }
}

/**
 * Place Document Settings under the Documents admin menu.
 * The options page callback is defined by includes/comodoc-options.php.
 */
function comodoc_move_settings_under_documents() {
    if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'comodoc_render_settings_page' ) ) {
        return;
    }

    remove_submenu_page( 'options-general.php', 'comodoc_plugin_settings' );
    add_submenu_page(
        'edit.php?post_type=document',
        __( 'Document Settings', 'como-docs' ),
        __( 'Document Settings', 'como-docs' ),
        'manage_options',
        'comodoc_plugin_settings',
        'comodoc_render_settings_page'
    );
}
add_action( 'admin_menu', 'comodoc_move_settings_under_documents', 99 );
