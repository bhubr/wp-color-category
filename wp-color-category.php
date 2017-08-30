<?php
/*
Plugin Name: WP Color Category
Description: Assign a color to a category (or more generically, a term)
Version: 0.9.0 (août 2012)
Author: Benoit Hubert
Copyright: 2011-2012 Benoit Hubert
*/

/*
TODO :
- Add translation
*/

define( 'WPCCP_MSG_MAIN_PAGE', __( "Category Colors", "wpccp" ) );

include( 'class-wpccp-settings.php' );

class WP_ColorCategory_Plugin {

	/**
	 * This is used to hold the (one or many) subpage data in this manner: 
	 * Key (slug) => Value( title and parent menu)
	 */
	private $subpages = array(
		'wpccp_main'	=>	array(
			'title'		=> WPCCP_MSG_MAIN_PAGE,	// Subpage title
			'parent'	=> 'themes.php'			// Menu this subpage will be hooked to
		)
	);
	
	/* Settings object */
	var $settings;
        private $_pluginDirRel;
	
	function __construct() {
		// Get a Settings object instance
		$this->settings = new WP_ColorCategory_Plugin_Settings;
		$this->_pluginDirRel = dirname( plugin_basename( __FILE__ ) );
                add_action( 'admin_init', array( $this->settings, 'settingsInit') );
                add_action( 'plugins_loaded', array( $this, 'initTranslation' ), 1 );
		add_action( 'admin_menu', array( $this, 'addAdminPages' ) );
		add_action( 'admin_print_styles', array( $this, 'loadStyles' ) );
		add_action( 'wp_print_scripts', array( $this, 'loadScripts' ) );
		add_action( 'wp_ajax_wpccp_tax_get_terms', array( $this, 'wpccp_ajax_get_terms' ) );
                // Hook edit tag form action
//                add_action( 'add_tag_form_fields', array( $this, 'addCategoryFormField' ) );
//                add_action( 'edit_tag_form', array( $this, 'editCategoryFormField' ) );
		//add_filter( 'category', array( $this, 'categoryNameColored' ) );
	}
	
        function addCategoryFormField( $arg ) { ?>
<div class="form-field">
	<label for="tag-color">Couleur</label>
	<input name="color" id="tag-color" type="text" class="colorpickerField" value="" size="40" />
	<p>Assigner une couleur à cette catégorie/rubrique.</p>
</div>
        
        <?php }
        
        function editCategoryFormField( $arg ) { ?>
<tr class="form-field">
	<th scope="row" valign="top"><label for="tag-color">Couleur</label></th>
	<td><input name="color" id="tag-color" type="text" class="colorpickerField" value="" size="40" />
	<p>Assigner une couleur à cette catégorie/rubrique.</p></td>
</tr>
        
        <?php }
        
	/**
	 * Register the admin subpages
	 */
	function addAdminPages() {
		foreach( $this->subpages as $slug => $data ) {
			$title_tr = __( $data['title'], 'wpccp' );
			add_submenu_page( $data['parent'], $title_tr, $title_tr, 'manage_options', $slug, array( $this, $slug ) );
		}
	}

	/**
	 * Display a navigation menu below the page title, to give easier access to this plugin's subpages
	 */
	function formPagesNavMenu( $selected ) {
		echo '<h3>';
		$print_sep = false;
		foreach( $this->subpages as $slug => $data ) {
			$title_tr = __( $data['title'], 'wpccp' );
			$separator = $print_sep ? '&nbsp;|&nbsp;' : '';
			$item_class = ( $selected == $slug ) ?  " class='select'" : "";
			echo "$separator<span$item_class><a href='themes.php?page=$slug'>$title_tr</a></span>";
			$print_sep = true;
		}
		echo '</h3>';
	}
	
	/**
	 * Get form slug from its file name (e.g. calling with argument plugindir/forms/myform.php will return myform )
	 */
	function getFormFromFile( $file ) {
		$form = explode( DIRECTORY_SEPARATOR, $file );
		$form = array_reverse( $form );
		$form = substr( $form[0], 0, -4 );
		return $form;
	}

	/**
	 * Get page title from slug
	 */
	function getPageTitle( $slug ) {
		$data = $this->subpages[$slug];
		return $data['title'];
	}
	
	/**
	 * Load per-page CSS files
	 */
	function loadStyles() {
		global $pagenow;
		if( !current_user_can( 'manage_options' ) || $pagenow != 'themes.php' || !isset( $_GET['page'] ) ) return;
		switch ( $_GET['page'] ) {
			case 'wpccp_main' :
				wp_enqueue_style('css_colorpicker_main', plugins_url('/colorpicker/css/colorpicker.css', __FILE__) );
				wp_enqueue_style('css_colorpicker_layout', plugins_url('/colorpicker/css/layout.css', __FILE__) );
				break;
			default :
				break;
		}
	}
	
	/** 
	 * Load per-page Javascripts
	 */
	function loadScripts() {
		global $pagenow;
		if( !current_user_can( 'manage_options' ) || $pagenow != 'themes.php' || !isset( $_GET['page'] ) ) return;
		switch ( $_GET['page'] ) {
			case 'wpccp_main' :
				wp_enqueue_script('js_wpccp_main', plugins_url('js/wpccp_main.js', __FILE__) );
				wp_enqueue_script('js_colorpicker_main', plugins_url('/colorpicker/js/colorpicker.js', __FILE__) );
				wp_enqueue_script('js_colorpicker_eye', plugins_url('/colorpicker/js/eye.js', __FILE__) );
				wp_enqueue_script('js_colorpicker_utils', plugins_url('/colorpicker/js/utils.js', __FILE__) );
				wp_enqueue_script('js_colorpicker_layout', plugins_url('/colorpicker/js/layout.js', __FILE__) );
				break;
			default :
				break;
		}
	}
	
        /**
        * Initializes translation 
        */
            function initTranslation() { 
                    load_plugin_textdomain( 'wpccp', false, $this->_pluginDirRel . '/languages' );
            }

	/**
	 * Return filtered category name : i.e. wrap it with color container
	 */
	/*function categoryNameColored( $name ) {
		return "<span style='background: #ddd;'>$name</span>";
	}*/

	/**
	 * Magic function related to admin subpages
	 * Each subpage is registered by its "slug"
	 * The associated callback function's name is the slug
	 * This includes a separate filename holding the callback stuff, with the slug as its basename
	 * This avoids bloating the class with HTML stuff
	 */
	function __call( $name, $arguments ) {
		include( "forms/$name.php" );
	}
	
}

// Register plugin object as a globals
global $wpccp;

// Todo : handle namespace collision checking
if( class_exists( 'WP_ColorCategory_Plugin' ) ) {
	$wpccp = new WP_ColorCategory_Plugin;
}
  
?>