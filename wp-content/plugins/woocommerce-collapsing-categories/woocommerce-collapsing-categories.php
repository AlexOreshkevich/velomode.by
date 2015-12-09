<?php
/* 
*	Plugin Name: WooCommerce collapsing categories
*	Description: This plugin allows you to create an expandable list of product categories and subcategories.
*	Version: 0.0.1
*	Author: subhansanjaya
*	Author URI: http://www.weaveapps.com
*/
if(! defined( 'ABSPATH' )) exit; // Exit if accessed directly

class Woocommerce_Collapsing_Categories {

	//default settings
	private $defaults = array(
	'settings' => array(
		'duration' => '400',
		'posts_order' => 'desc',
		'posts_orderby' => 'id',
		'category' => '',
		'level' => '0',
		'hide_if_empty' => false,
		'show_count' => true
	),
	'version' => '0.0.1',
	'configuration' => array(
		'deactivation_delete' => false,
		'loading_place' => 'footer',
		'load_velocity' => true,
		'load_mtree' => true,
	)
);

	private $options = array();
	private $tabs = array();

	public function __construct() {

		register_activation_hook(__FILE__, array(&$this, 'wa_wcc_activation'));
		register_deactivation_hook(__FILE__, array(&$this, 'wa_wcc_deactivation'));

		//create widget
		require('includes/class-wcc-widget.php');
		$WCC_Widget = new WCC_Widget();

		//Add admin option
		add_action('admin_menu', array(&$this, 'admin_menu_options'));
		add_action('admin_init', array(&$this, 'register_settings'));

		//add text domain for localization
		add_action('plugins_loaded', array(&$this, 'load_textdomain'));

		//load defaults
		add_action('plugins_loaded', array(&$this, 'load_defaults'));

		//update plugin version
		update_option('wa_wcc_version', $this->defaults['version'], '', 'no');
		$this->options['settings'] = array_merge($this->defaults['settings'], (($array = get_option('wa_wcc_settings')) === FALSE ? array() : $array));
		$this->options['configuration'] = array_merge($this->defaults['configuration'], (($array = get_option('wa_wcc_configuration')) === FALSE ? array() : $array));
	
		//insert js and css files
		add_action('wp_enqueue_scripts', array(&$this, 'wa_wcc_load_scripts'));

		add_action('admin_enqueue_scripts', array(&$this, 'admin_include_scripts'));

		//settings link
		add_filter('plugin_action_links', array(&$this, 'wa_wcc_settings_link'), 2, 2);

		//add shortcode
		add_shortcode( 'wa-wcc', array(&$this, 'wa_wcc_display_list'));
	}

	/* multi site activation hook */
	public function wa_wcc_activation($networkwide) {

		if(is_multisite() && $networkwide) {
			global $wpdb;

			$activated_blogs = array();
			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));

			foreach($blogs_ids as $blog_id)
			{
				switch_to_blog($blog_id);
				$this->activate_single();
				$activated_blogs[] = (int)$blog_id;
			}

			switch_to_blog($current_blog_id);
			update_site_option('wa_wcc_activated_blogs', $activated_blogs, array());
		}
		else
			$this->activate_single();
	}

	public function activate_single() {

		add_option('wa_wcc_settings', $this->defaults['settings'], '', 'no');
		add_option('wa_wcc_version', $this->defaults['version'], '', 'no');
		add_option('wa_wcc_configuration', $this->defaults['configuration'], '', 'no');

	}

	/*  multi-site deactivation hook */
	public function wa_wcc_deactivation($networkwide) {

		if(is_multisite() && $networkwide) {
			global $wpdb;

			$current_blog_id = $wpdb->blogid;
			$blogs_ids = $wpdb->get_col($wpdb->prepare('SELECT blog_id FROM '.$wpdb->blogs, ''));

			if(($activated_blogs = get_site_option('wa_wcc_activated_blogs', FALSE, FALSE)) === FALSE)
				$activated_blogs = array();

			foreach($blogs_ids as $blog_id)
			{
				switch_to_blog($blog_id);
				$this->deactivate_single(TRUE);

				if(in_array((int)$blog_id, $activated_blogs, TRUE))
					unset($activated_blogs[array_search($blog_id, $activated_blogs)]);
			}

			switch_to_blog($current_blog_id);
			update_site_option('wa_wcc_activated_blogs', $activated_blogs);
		}
		else
			$this->deactivate_single();
	}

	public function deactivate_single($multi = FALSE) {

		if($multi === TRUE) {
			$options = get_option('wa_wcc_settings');
			$check = $options['deactivation_delete'];
		}
		else {
		$check = $this->options['settings']['deactivation_delete'];
		
		if($check === TRUE) {
			delete_option('wa_wcc_settings');
			delete_option('wa_wcc_version');
			delete_option('wa_wcc_configuration');
			}

		}
	}

	/* settings link in management screen */
	public function wa_wcc_settings_link($actions, $file) {

		if(false !== strpos($file, 'woocommerce-collapsing-categories'))
		 $actions['settings'] = '<a href="options-general.php?page=woocommerce-collapsing-categories">Settings</a>';
		return $actions; 

	}

	/* display list */
	public function wa_wcc_display_list() {

	global $wpdb, $post;

	?>
	<script>

	jQuery(document).ready(function($) {

	$("li.current-cat-parent").addClass('current-cat');

	var mtree = $('ul.mtree');

	mtree.addClass('default');

	});

	</script>

	<?php

	$show_count = !empty($this->options['settings']['show_count']) ? true : false;
	$wa_wcc_hide_if_empty = !empty($this->options['settings']['hide_if_empty']) ? true : false;
	$qp_order= $this->options['settings']['posts_order'];
	$qp_orderby= $this->options['settings']['posts_orderby'];
	$terms= $this->options['settings']['category'];
	$wa_wcc_level = $this->options['settings']['level'];

	if($qp_order=="rand") {  

		$qp_orderby="rand"; 
	}

	if(!empty($terms) ) {

	$terms = implode(', ',$terms);

	} else {

	$terms = ''; 

	}

	$wcc_subcat_args = array(
	'post_type' => 'product',
	'taxonomy' => 'product_cat',
	'title_li' => '',
	'orderby' => $qp_orderby,
	'order'    => $qp_order,
	'depth' => $wa_wcc_level,
	'show_count' => (bool)$show_count,
	'hide_empty' => (bool)$wa_wcc_hide_if_empty,
	'echo' => false,
	'exclude'  => $terms,
	'show_option_none'   => __('No Categories Found','trwca'),
	'link_after' => '',
	);


		$wcc_categories = wp_list_categories( $wcc_subcat_args );
	    $wcc_categories = str_replace('<ul', '<em id="parent"></em><ul', $wcc_categories);
	    $wcc_categories = preg_replace('/<\/a> \(([0-9]+)\)/', ' <span class="count">(\\1)</span></a>', $wcc_categories);

		if(!isset($wcc_categories)||empty($wcc_categories)){ 

			return false;
		}

		$list_categories = '';	
		$list_categories.= '<div class="wcc_block">';
		$list_categories.= '<ul class="mtree">';
		$list_categories.= $wcc_categories; 
		$list_categories.='</ul>';
		$list_categories.='</div>';

		wp_reset_postdata();

		$this->wa_wcc_mtree_script();

		return $list_categories;

	}


	//load mtree scripts
	public function wa_wcc_mtree_script() {

		$args_mtree = apply_filters('mtree_options', array(
		'duration' =>   $this->options['settings']['duration'],
		'easing_type' =>  'easeOutQuart',
		));

		if($this->options['configuration']['load_mtree'] === TRUE) {

		wp_register_script('wa_wcc_mtree',plugins_url('/assets/js/mtree.js', __FILE__),array('jquery'),'',($this->options['configuration']['loading_place'] === 'header' ? false : true));
	    wp_enqueue_script('wa_wcc_mtree'); 

	    wp_localize_script('wa_wcc_mtree','mtree_options',$args_mtree);

	    } 


	}

	/* insert css files for admin area */
	public function admin_include_scripts() {

			wp_register_style('wa_wcc_admin',plugins_url('assets/css/admin.css', __FILE__ ));
			wp_enqueue_style('wa_wcc_admin');

	}


	/* insert css files js files */
	public function wa_wcc_load_scripts($jquery_true) {

		wp_register_style('wa_wcc_mtree_css_file', plugins_url('/assets/css/mtree.css',__FILE__));
		wp_enqueue_style('wa_wcc_mtree_css_file');


		if($this->options['configuration']['load_velocity'] === TRUE) {

	    wp_register_script('wa_wcc_velocity',plugins_url('/assets/js/jquery.velocity.min.js', __FILE__),array('jquery'),'',($this->options['configuration']['loading_place'] === 'header' ? false : true));
	    wp_enqueue_script('wa_wcc_velocity'); 

		}

	}

		/* load default settings */
	public function load_defaults(){
		
		$this->choices = array(
			'yes' => __('Enable', 'wa_wcc_txt'),
			'no' => __('Disable', 'wa_wcc_txt')
		);

		$this->loading_places = array(
			'header' => __('Header', 'wa_wcc_txt'),
			'footer' => __('Footer', 'wa_wcc_txt')
		);

		$this->tabs = array(
			'general-settings' => array(
				'name' => __('General', 'wa_wcc_txt'),
				'key' => 'wa_wcc_settings',
				'submit' => 'save_wa_wcc_settings',
				'reset' => 'reset_wa_wcc_settings',
			),
            'configuration' => array(
                'name' => __('Advanced', 'wa_wcc_txt'),
                'key' => 'wa_wcc_configuration',
                'submit' => 'save_wa_wcc_configuration',
                'reset' => 'reset_wa_wcc_configuration'
            )
		);
	}



	/* admin menu */
	public function admin_menu_options(){
		add_options_page(
			__('WC categories', 'wa_wcc_txt'),
			__('WC categories', 'wa_wcc_txt'),
			'manage_options',
			'woocommerce-collapsing-categories',
			array(&$this, 'options_page')
		);
	}

	/* register setting for plugins page */
	public function register_settings() {

		register_setting('wa_wcc_settings', 'wa_wcc_settings', array(&$this, 'validate_options'));
		//general settings
		add_settings_section('wa_wcc_settings', __('', 'wa_wcc_txt'), '', 'wa_wcc_settings');

		add_settings_field('wa_wcc_posts_orderby', __('Category orderby', 'wa_wcc_txt'), array(&$this, 'wa_wcc_posts_orderby'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_posts_order', __('Category order', 'wa_wcc_txt'), array(&$this, 'wa_wcc_posts_order'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_posts_category', __('Exclude categories', 'wa_wcc_txt'), array(&$this, 'wa_wcc_posts_category'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_hide_if_empty', __('Hide if empty', 'wa_wcc_txt'), array(&$this, 'wa_wcc_hide_if_empty'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_show_count', __('Show post count', 'wa_wcc_txt'), array(&$this, 'wa_wcc_show_count'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_duration', __('Easing duration', 'wa_wcc_txt'), array(&$this, 'wa_wcc_duration'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_level', __('Level', 'wa_wcc_txt'), array(&$this, 'wa_wcc_level'), 'wa_wcc_settings', 'wa_wcc_settings');
		add_settings_field('wa_wcc_custom_css', __('Custom styles', 'wa_wcc_txt'), array(&$this, 'wa_wcc_custom_css'), 'wa_wcc_settings', 'wa_wcc_settings');
	
		//advance settings
		register_setting('wa_wcc_configuration', 'wa_wcc_configuration', array(&$this, 'validate_options'));
		
		add_settings_section('wa_wcc_configuration', __('', 'wa_wcc_txt'), '', 'wa_wcc_configuration');
		add_settings_field('wa_wcc_load_mtree', __('Load mTree', 'wa_wcc_txt'), array(&$this, 'wa_wcc_load_mtree'), 'wa_wcc_configuration', 'wa_wcc_configuration');
		add_settings_field('wa_wcc_load_velocity', __('Load Velocity', 'wa_wcc_txt'), array(&$this, 'wa_wcc_load_velocity'), 'wa_wcc_configuration', 'wa_wcc_configuration');
		add_settings_field('wa_wcc_loading_place', __('Loading place', 'wa_wcc_txt'), array(&$this, 'wa_wcc_loading_place'), 'wa_wcc_configuration', 'wa_wcc_configuration');	
		add_settings_field('wa_wcc_deactivation_delete', __('Deactivation', 'wa_wcc_txt'), array(&$this, 'wa_wcc_deactivation_delete'), 'wa_wcc_configuration', 'wa_wcc_configuration');
	
	}



	/* category */
	public function wa_wcc_posts_category(){
		
		echo '<div id="wa_wcc_posts_category">';
		$tax_selected = 'category';
		?>

					<select  name="wa_wcc_settings[category][]" multiple>
					<option value=''>choose...</option>
					<?php

		    $categories = get_terms('product_cat', array('post_type' => array('product'),'fields' => 'all'));


					 if(!empty( $categories )) {

					 foreach ($categories as $key => $value) { ?>

						<option value="<?php echo $value->term_id; ?>"
					<?php 
					if(!empty($this->options['settings']['category'] )) {

						$arr = $this->options['settings']['category'];

						if(is_string($this->options['settings']['category'])) {

							$arr = explode(",",$this->options['settings']['category']);
						}

					foreach ($arr as $contractor) {

							if($value->term_id==$contractor){ selected( $value->term_id, $value->term_id ); }
					}
				}
					?> ><?php echo $value->name; ?></option><?php } }?>
				</select>
	<?php		

	echo '</div><p class="description">'.__('Please, hold down the control or command button to select multiple options.', 'wa_wcc_txt').'</p></div>';
		
	}


	/* post order */
	public	function wa_wcc_posts_order() {

	    $options = $this->options['settings']['posts_order'];
	     
    	$html = '<select id="wa_wcc_posts_order" name="wa_wcc_settings[posts_order]">';
        $html .= '<option value="asc"' . selected( esc_attr($this->options['settings']['posts_order']), 'asc', false) . '>Ascending </option>';
        $html .= '<option value="desc"' . selected( esc_attr($this->options['settings']['posts_order']), 'desc', false) . '>Descending</option>';
		$html .= '<option value="rand"' . selected( esc_attr($this->options['settings']['posts_order']), 'rand', false) . '>Random</option>';
    	$html .= '</select>';    
	    echo $html;

	} 

	/* posts order by */
	public function wa_wcc_posts_orderby(){

		echo '
		<div id="wa_wcc_posts_orderby">
			<input type="text" name="wa_wcc_settings[posts_orderby]" value="'.esc_attr($this->options['settings']['posts_orderby']).'" />
		</div>';

	}


	/* hide if empty */
	public function wa_wcc_hide_if_empty(){
		echo '
		<div id="wa_wcc_hide_if_empty" class="wplikebtns">';

		foreach($this->choices as $val => $trans)
		{
			echo '
			<input id="rll-galleries-'.$val.'" type="radio" name="wa_wcc_settings[hide_if_empty]" value="'.esc_attr($val).'" '.checked(($val === 'yes' ? TRUE : FALSE), $this->options['settings']['hide_if_empty'], FALSE).' />
			<label for="rll-galleries-'.$val.'">'.$trans.'</label>';
		}

		echo '</div>';
	}

	/* show count */
	public function wa_wcc_show_count(){
		echo '
		<div id="wa_wcc_show_count" class="wplikebtns">';

		foreach($this->choices as $val => $trans)
		{
			echo '
			<input id="rll-galleries-'.$val.'" type="radio" name="wa_wcc_settings[show_count]" value="'.esc_attr($val).'" '.checked(($val === 'yes' ? TRUE : FALSE), $this->options['settings']['show_count'], FALSE).' />
			<label for="rll-galleries-'.$val.'">'.$trans.'</label>';
		}

		echo '</div>';
	}

	/* custom css */
	public function wa_wcc_custom_css(){
		echo '
		<div id="wa_wcc_custom_css">
			<textarea  name="wa_wcc_settings[custom_css]" placeholder=".wa_wcc_list { color: #ccc !important; }"  >'.esc_attr($this->options['settings']['custom_css']).'</textarea>';
		echo '<p class="description">'.__('custom styles or override existing styles to meet your requirements.', 'wa_wcc_txt').'</p></div>';

	}

	/* time out */
	public function wa_wcc_duration() {

		echo '<div id="wa_wcc_duration">
			<input type="text"  value="'.esc_attr($this->options['settings']['duration']).'" name="wa_wcc_settings[duration]" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/>
		</div>';

	}

	/* level */
	public function wa_wcc_level() {

		echo '<div id="wa_wcc_level">
			<input type="text"  value="'.esc_attr($this->options['settings']['level']).'" name="wa_wcc_settings[level]" onkeypress="return event.charCode >= 48 && event.charCode <= 57"/>';
		echo '<p class="description">'.__('Please, set 0 to show all levels.', 'wa_wcc_txt').'</p></div>';

	}


	/* load mtree */
	public function wa_wcc_load_mtree() {

		echo '<div id="wa_wcc_load_mtree" class="wplikebtns">';

		foreach($this->choices as $val => $trans) {
			echo '
			<input id="rll-galleries-'.$val.'" type="radio" name="wa_wcc_configuration[load_mtree]" value="'.esc_attr($val).'" '.checked(($val === 'yes' ? TRUE : FALSE), $this->options['configuration']['load_mtree'], FALSE).' />
			<label for="rll-galleries-'.$val.'">'.$trans.'</label>';
		}

		echo '<p class="description">'.__('Disable this option, if this script has already loaded on your web site.', 'wa_wcc_txt').'</p></div>';
	}


	/* load mtree */
	public function wa_wcc_load_velocity() {

		echo '<div id="wa_wcc_load_velocity" class="wplikebtns">';

		foreach($this->choices as $val => $trans) {
			echo '
			<input id="rll-galleries-'.$val.'" type="radio" name="wa_wcc_configuration[load_velocity]" value="'.esc_attr($val).'" '.checked(($val === 'yes' ? TRUE : FALSE), $this->options['configuration']['load_velocity'], FALSE).' />
			<label for="rll-galleries-'.$val.'">'.$trans.'</label>';
		}

		echo '<p class="description">'.__('Disable this option, if this script has already loaded on your web site.', 'wa_wcc_txt').'</p></div>';
	}



	/* loading place */
	public function wa_wcc_loading_place() {

		echo '<div id="wa_wcc_loading_place" class="wplikebtns">';

		foreach($this->loading_places as $val => $trans) {
			$val = esc_attr($val);

			echo '
			<input id="rll-loading-place-'.$val.'" type="radio" name="wa_wcc_configuration[loading_place]" value="'.$val.'" '.checked($val, $this->options['configuration']['loading_place'], false).' />
			<label for="rll-loading-place-'.$val.'">'.esc_html($trans).'</label>';
		}

		echo '<p class="description">'.__('Select where all the scripts should be placed.', 'wa_wcc_txt').'</p></div>';
	}



	/* deactivation on delete */
	public function wa_wcc_deactivation_delete(){
		echo '
		<div id="wa_wcc_deactivation_delete" class="wplikebtns">';
		foreach($this->choices as $val => $trans)
		{
			echo '
			<input id="wa-wcc-deactivation-delete-'.$val.'" type="radio" name="wa_wcc_configuration[deactivation_delete]" value="'.esc_attr($val).'" '.checked(($val === 'yes' ? TRUE : FALSE), $this->options['configuration']['deactivation_delete'], FALSE).' />
			<label for="wa-wcc-deactivation-delete-'.$val.'">'.$trans.'</label>';
		}
		echo '
			<p class="description">'.__('Delete settings on plugin deactivation.', 'wa_wcc_txt').'</p>
		</div>';
	}


		/* options page */
	public function options_page() {

		$tab_key = (isset($_GET['tab']) ? $_GET['tab'] : 'general-settings');
		echo '<div class="wrap">'.screen_icon().'
			<h2>'.__('WooCommerce collapsing categories', 'wa_wcc_txt').'</h2>
			<h2 class="nav-tab-wrapper">';

		foreach($this->tabs as $key => $name) {

		echo '
			<a class="nav-tab '.($tab_key == $key ? 'nav-tab-active' : '').'" href="'.esc_url(admin_url('options-general.php?page=woocommerce-collapsing-categories&tab='.$key)).'">'.$name['name'].'</a>';
		}
		echo '</h2><div class="wa-wcc-settings"><div class="wa-wcc-credits"><h3 class="hndle">'.__('WooCommerce collapsing categories', 'wa_wcc_txt').'</h3>
					<div class="inside">
					<p class="inner">'.__('Configuration: ', 'wa_wcc_txt').' <a href="https://wordpress.org/plugins/woocommerce-collapsing-categories/installation/" target="_blank" title="'.__('Plugin URL', 'wa_wcc_txt').'">'.__('Plugin URI', 'wa_wcc_txt').'</a></p>
					</p><hr />
					<h4 class="inner">'.__('Do you like this plugin?', 'wa_wcc_txt').'</h4>
					<p class="inner">'.__('Please, ', 'wa_wcc_txt').'<a href="http://wordpress.org/support/view/plugin-reviews/woocommerce-collapsing-categories" target="_blank" title="'.__('rate it', 'wa_wcc_txt').'">'.__('rate it', 'wa_wcc_txt').'</a> '.__('on WordPress.org', 'wa_wcc_txt').'<br />          
					</div>
						<hr />
					<div style="width:auto; margin:auto; text-align:center;"><a href="http://weaveapps.com/shop/wordpress-plugins/woocommerce-collapsing-categories-wordpress-plugin/" target="_blank"><img width="270" height="70" src="'.plugins_url('assets/images/wcc-pro.png',__FILE__).'"/></a></div>
					
				</div><form action="options.php" method="post">';
	
		wp_nonce_field('update-options');
		
		settings_fields($this->tabs[$tab_key]['key']);
		
		do_settings_sections($this->tabs[$tab_key]['key']);
		
		echo '<p class="submit">';
		
		submit_button('', 'primary', $this->tabs[$tab_key]['submit'], FALSE);
	
		echo ' ';
		
		echo submit_button(__('Reset to defaults', 'wa_wcc_txt'), 'secondary', $this->tabs[$tab_key]['reset'], FALSE);
		
		echo '</p></form></div><div class="clear"></div></div>';
	}


	/* load text domain for localization */
	public function load_textdomain(){
		load_plugin_textdomain('wa_wcc_txt', FALSE, dirname(plugin_basename(__FILE__)).'/lang/');
	}

		/* validate options and register settings */
	public function validate_options($input) {

		if(isset($_POST['save_wa_wcc_settings'])) {

			$input['show_count'] = (isset($input['show_count'], $this->choices[$input['show_count']]) ? ($input['show_count'] === 'yes' ? true : false) : $this->defaults['settings']['show_count']);
			$input['hide_if_empty'] = (isset($input['hide_if_empty'], $this->choices[$input['hide_if_empty']]) ? ($input['hide_if_empty'] === 'yes' ? true : false) : $this->defaults['settings']['hide_if_empty']);
			$input['posts_order'] = sanitize_text_field(isset($input['posts_order']) && $input['posts_order'] !== '' ? $input['posts_order'] : $this->defaults['settings']['posts_order']);
			$input['posts_orderby'] = sanitize_text_field(isset($input['posts_orderby']) && $input['posts_orderby'] !== '' ? $input['posts_orderby'] : $this->defaults['settings']['posts_orderby']);
			$input['category'] =$input['category'];
			$input['duration'] =isset($input['duration']) ? $input['duration'] : '400';
			$input['custom_css'] =isset($input['custom_css']) ? $input['custom_css'] : $this->defaults['settings']['custom_css'];
			$input['level'] =isset($input['level']) ? $input['level'] : '0';


		}elseif(isset($_POST['reset_wa_wcc_settings'])) {

			$input = $this->defaults['settings'];

			add_settings_error('reset_general_settings', 'general_reset', __('Settings restored to defaults.', 'wa_wcc_txt'), 'updated');
		
		}	elseif(isset($_POST['reset_wa_wcc_configuration'])) {

				$input = $this->defaults['configuration'];

				add_settings_error('reset_nivo_settings', 'nivo_reset', __('Settings of were restored to defaults.', 'wa_wcc_txt'), 'updated');

		}	else if(isset($_POST['save_wa_wcc_configuration'])) {

			$input['loading_place'] = (isset($input['loading_place'], $this->loading_places[$input['loading_place']]) ? $input['loading_place'] : $this->defaults['configuration']['loading_place']);
			$input['deactivation_delete'] = (isset($input['deactivation_delete'], $this->choices[$input['deactivation_delete']]) ? ($input['deactivation_delete'] === 'yes' ? true : false) : $this->defaults['configuration']['deactivation_delete']);
			$input['load_jquery'] = (isset($input['load_jquery'], $this->choices[$input['load_jquery']]) ? ($input['load_jquery'] === 'yes' ? true : false) : $this->defaults['configuration']['load_jquery']);
			$input['load_mtree'] = (isset($input['load_mtree'], $this->choices[$input['load_mtree']]) ? ($input['load_mtree'] === 'yes' ? true : false) : $this->defaults['configuration']['load_mtree']);
			$input['load_velocity'] = (isset($input['load_velocity'], $this->choices[$input['load_velocity']]) ? ($input['load_velocity'] === 'yes' ? true : false) : $this->defaults['configuration']['load_velocity']);
		
		}

		return $input;
	}

}
$woocommerce_collapsing_categories = new Woocommerce_Collapsing_Categories();