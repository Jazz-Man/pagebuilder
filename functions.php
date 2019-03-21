<?php


if ( ! isset( $content_width ) ) $content_width = 1080;

function et_setup_theme() {
	global $themename, $shortname, $et_store_options_in_one_row, $default_colorscheme;
	$themename = 'Divi';
	$shortname = 'divi';
	$et_store_options_in_one_row = true;

	$default_colorscheme = "Default";

	$template_directory = get_template_directory();

	require_once( $template_directory . '/core/init.php' );

	et_core_setup( get_template_directory_uri() );

	if ( '3.0.61' === ET_CORE_VERSION ) {
		require_once $template_directory . '/core/functions.php';
		require_once $template_directory . '/core/components/init.php';
		et_core_patch_core_3061();
	}

	require_once( $template_directory . '/epanel/custom_functions.php' );

	require_once( $template_directory . '/includes/functions/choices.php' );

	require_once( $template_directory . '/includes/functions/sanitization.php' );

	require_once( $template_directory . '/includes/functions/sidebars.php' );

	load_theme_textdomain( 'Divi', $template_directory . '/lang' );

	require_once( $template_directory . '/epanel/core_functions.php' );

    	include_once( $template_directory . '/includes/widgets.php' );

	register_nav_menus( array(
		'primary-menu'   => esc_html__( 'Primary Menu', 'Divi' ),
		'secondary-menu' => esc_html__( 'Secondary Menu', 'Divi' ),
		'footer-menu'    => esc_html__( 'Footer Menu', 'Divi' ),
	) );

	// don't display the empty title bar if the widget title is not set
	remove_filter( 'widget_title', 'et_widget_force_title' );

	remove_filter( 'body_class', 'et_add_fullwidth_body_class' );

	add_action( 'wp_enqueue_scripts', 'et_add_responsive_shortcodes_css', 11 );

	// Declare theme supports
	add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']);

	add_theme_support( 'post-formats', array(
		'video', 'audio', 'quote', 'gallery', 'link'
	) );

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_theme_support( 'customize-selective-refresh-widgets' );

	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	add_action( 'woocommerce_before_main_content', 'et_divi_output_content_wrapper', 10 );
	add_action( 'eventon_before_main_content', 'et_divi_output_content_wrapper', 11 );

	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_after_main_content', 'et_divi_output_content_wrapper_end', 10 );
	add_action( 'eventon_after_main_content', 'et_divi_output_content_wrapper_end', 9 );

	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	// add wrapper so we can clear things
	add_action( 'woocommerce_before_single_product_summary', 'et_divi_output_product_wrapper', 0  );
	add_action( 'woocommerce_after_single_product_summary', 'et_divi_output_product_wrapper_end', 0  );

	// deactivate page templates and custom import functions
	remove_action( 'init', 'et_activate_features' );

	remove_action('admin_menu', 'et_add_epanel');

	// Load editor styling
	add_editor_style( 'css/editor-style.css' );

	// Load unminified scripts based on selected theme options field
	add_filter( 'et_load_unminified_scripts', 'et_divi_load_unminified_scripts' );

	// Load unminified styles based on selected theme options field
	add_filter( 'et_load_unminified_styles', 'et_divi_load_unminified_styles' );

	et_divi_version_rollback()->enable();
}
add_action( 'after_setup_theme', 'et_setup_theme' );

function et_divi_load_unminified_scripts( $load ) {
	/** @see ET_Support_Center::toggle_safe_mode */
	if ( et_core_is_safe_mode_active() ) {
		return true;
	}

	if ( 'false' === et_get_option( 'divi_minify_combine_scripts' ) ) {
		return true;
	}

	return $load;
}

function et_divi_load_unminified_styles( $load ) {
	/** @see ET_Support_Center::toggle_safe_mode */
	if ( et_core_is_safe_mode_active() ) {
		return true;
	}

	if ( 'false' === et_get_option( 'divi_minify_combine_styles' ) ) {
		return true;
	}

	return $load;
}

function et_theme_epanel_reminder(){
	global $shortname, $themename;

	$documentation_url         = 'http://www.elegantthemes.com/gallery/divi/readme.html';
	$documentation_option_name = $shortname . '_2_4_documentation_message';

	if ( false === et_get_option( $shortname . '_logo' ) && false === et_get_option( $documentation_option_name ) ) {
		$message = sprintf(
			et_get_safe_localization( __( 'Welcome to Divi! Before diving in to your new theme, please visit the <a style="color: #fff; font-weight: bold;" href="%1$s" target="_blank">Divi Documentation</a> page for access to dozens of in-depth tutorials.', $themename ) ),
			esc_url( $documentation_url )
		);

		printf(
			'<div class="notice is-dismissible" style="background-color: #6C2EB9; color: #fff; border-left: none;">
				<p>%1$s</p>
			</div>',
			$message
		);

		et_update_option( $documentation_option_name, 'triggered' );
	}
}
add_action( 'admin_notices', 'et_theme_epanel_reminder' );

if ( ! function_exists( 'et_divi_fonts_url' ) ) :
function et_divi_fonts_url() {
	if ( ! et_core_use_google_fonts() ) {
		return '';
	}

	$fonts_url = '';

	/* Translators: If there are characters in your language that are not
	 * supported by Open Sans, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$open_sans = _x( 'on', 'Open Sans font: on or off', 'Divi' );

	if ( 'off' !== $open_sans ) {
		$font_families = array();

		if ( 'off' !== $open_sans )
			$font_families[] = 'Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => implode( '%7C', $font_families ),
			'subset' => 'latin,latin-ext',
		);
		$fonts_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
	}

	return $fonts_url;
}
endif;

function et_divi_load_fonts() {
	$fonts_url = et_divi_fonts_url();

	// Get user selected font defined on customizer
	$et_gf_body_font = sanitize_text_field( et_get_option( 'body_font', 'none' ) );

	$is_et_fb_enabled = function_exists( 'et_fb_enabled' ) && et_fb_enabled();

	// Determine whether current page needs Open Sans or not
	$no_open_sans = ! is_customize_preview() && 'none' !== $et_gf_body_font && '' !== $et_gf_body_font && ! $is_et_fb_enabled;

	if ( ! empty( $fonts_url ) && ! $no_open_sans ) {
		wp_enqueue_style( 'divi-fonts', esc_url_raw( $fonts_url ), array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'et_divi_load_fonts' );

function et_add_home_link( $args ) {
	// add Home link to the custom menu WP-Admin page
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'et_add_home_link' );

function et_divi_load_scripts_styles(){
	global $wp_styles;

	$script_suffix = et_load_unminified_scripts() ? '' : '.min';
	$style_suffix  = et_load_unminified_styles() && ! is_child_theme() ? '.dev' : '';
	$template_dir  = get_template_directory_uri();
	$theme_version = et_get_theme_version();

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	if ( is_singular() && has_post_format( 'audio' ) ) {
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
	}

	$dependencies_array = array( 'jquery' );

	// load 'jquery-effects-core' if SlideIn/Fullscreen header used or if customizer opened
	if ( is_customize_preview() || 'slide' === et_get_option( 'header_style', 'left' ) || 'fullscreen' === et_get_option( 'header_style', 'left' ) ) {
		$dependencies_array[] = 'jquery-effects-core';
	}

	wp_enqueue_script( 'et-jquery-touch-mobile', $template_dir . '/includes/builder/scripts/jquery.mobile.custom.min.js', array( 'jquery' ), $theme_version, true );

	if ( et_load_unminified_scripts() ) {
		$dependencies_array[] = 'et-jquery-touch-mobile';
	}

	wp_enqueue_script( 'divi-custom-script', $template_dir . '/js/custom' . $script_suffix . '.js', $dependencies_array , $theme_version, true );
	wp_localize_script( 'divi-custom-script', 'DIVI', array(
		'item_count'  => esc_html__( '%d Item', 'divi' ),
		'items_count' => esc_html__( '%d Items', 'divi' ),
	) );

	if ( 'on' === et_get_option( 'divi_smooth_scroll', false ) ) {
		wp_enqueue_script( 'smooth-scroll', $template_dir . '/js/smoothscroll.js', array( 'jquery' ), $theme_version, true );
	}

	$et_gf_enqueue_fonts = array();
	$et_gf_heading_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'heading_font', 'none' ) ) );
	$et_gf_body_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'body_font', 'none' ) ) );
	$et_gf_button_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'all_buttons_font', 'none' ) ) );
	$et_gf_primary_nav_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'primary_nav_font', 'none' ) ) );
	$et_gf_secondary_nav_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'secondary_nav_font', 'none' ) ) );
	$et_gf_slide_nav_font = sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'slide_nav_font', 'none' ) ) );

	$site_domain = get_locale();
	$et_one_font_languages = et_get_one_font_languages();

	if ( 'none' != $et_gf_heading_font ) $et_gf_enqueue_fonts[] = $et_gf_heading_font;
	if ( 'none' != $et_gf_body_font ) $et_gf_enqueue_fonts[] = $et_gf_body_font;
	if ( 'none' != $et_gf_button_font ) $et_gf_enqueue_fonts[] = $et_gf_button_font;
	if ( 'none' != $et_gf_primary_nav_font ) $et_gf_enqueue_fonts[] = $et_gf_primary_nav_font;
	if ( 'none' != $et_gf_secondary_nav_font ) $et_gf_enqueue_fonts[] = $et_gf_secondary_nav_font;
	if ( 'none' != $et_gf_slide_nav_font ) $et_gf_enqueue_fonts[] = $et_gf_slide_nav_font;

	if ( isset( $et_one_font_languages[$site_domain] ) ) {
		$et_gf_font_name_slug = strtolower( str_replace( ' ', '-', $et_one_font_languages[$site_domain]['language_name'] ) );
		wp_enqueue_style( 'et-gf-' . $et_gf_font_name_slug, $et_one_font_languages[$site_domain]['google_font_url'], array(), null );
	} else if ( ! empty( $et_gf_enqueue_fonts ) && function_exists( 'et_builder_enqueue_font' ) ) {
		$et_old_one_font_languages = et_get_old_one_font_languages();

		foreach ( $et_gf_enqueue_fonts as $single_font ) {
			if ( isset( $et_old_one_font_languages[$site_domain] ) ) {
				$font_custom_default_data = $et_old_one_font_languages[$site_domain];

				// enqueue custom default font if needed
				if ( $single_font === $font_custom_default_data['font_family'] ) {
					$et_gf_font_name_slug = strtolower( str_replace( ' ', '-', $font_custom_default_data['language_name'] ) );
					wp_enqueue_style( 'et-gf-' . $et_gf_font_name_slug, $font_custom_default_data['google_font_url'], array(), null );
					continue;
				}
			}

			et_builder_enqueue_font( $single_font );
		}
	}

	/*
	 * Loads the main stylesheet.
	 */
	wp_enqueue_style( 'divi-style', get_stylesheet_directory_uri() . '/style' . $style_suffix . '.css', array(), $theme_version );
}
add_action( 'wp_enqueue_scripts', 'et_divi_load_scripts_styles' );


function et_divi_shortcodes_strings_handle( $handle ) {
	return et_load_unminified_scripts() ? $handle : 'divi-custom-script';
}
add_filter( 'et_shortcodes_strings_handle', 'et_divi_shortcodes_strings_handle' );

function et_divi_builder_modules_script_handle( $handle ) {
	return et_load_unminified_scripts() ? $handle : 'divi-custom-script';
}
add_filter( 'et_builder_modules_script_handle', 'et_divi_builder_modules_script_handle' );

function et_divi_builder_optimized_style_handle( $handle ) {
	return et_load_unminified_styles() ? $handle : 'divi-style';
}
add_filter( 'et_builder_optimized_style_handle', 'et_divi_builder_optimized_style_handle' );

/**
 * Added theme specific scripts that are being minified
 * @param array  of scripts
 * @return array of modified scripts
 */
function et_divi_builder_get_minified_scripts( $scripts ) {
	return array_merge( $scripts, array(
		'smooth-scroll',
	) );
}
add_filter( 'et_builder_get_minified_scripts', 'et_divi_builder_get_minified_scripts' );

function et_add_mobile_navigation(){
	if ( is_customize_preview() || ( 'slide' !== et_get_option( 'header_style', 'left' ) && 'fullscreen' !== et_get_option( 'header_style', 'left' ) ) ) {
		printf(
			'<div id="et_mobile_nav_menu">
				<div class="mobile_nav closed">
					<span class="select_page">%1$s</span>
					<span class="mobile_menu_bar mobile_menu_bar_toggle"></span>
				</div>
			</div>',
			esc_html__( 'Select Page', 'Divi' )
		);
	}
}
add_action( 'et_header_top', 'et_add_mobile_navigation' );

function et_add_viewport_meta(){
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />';
}
add_action( 'wp_head', 'et_add_viewport_meta' );

function et_maybe_add_scroll_to_anchor_fix() {
	$add_scroll_to_anchor_fix = et_get_option( 'divi_scroll_to_anchor_fix' );

	if ( 'on' === $add_scroll_to_anchor_fix ) {
		echo '<script>
				document.addEventListener( "DOMContentLoaded", function( event ) {
					window.et_location_hash = window.location.hash;
					if ( "" !== window.et_location_hash ) {
						// Prevent jump to anchor - Firefox
						window.scrollTo( 0, 0 );
						var et_anchor_element = document.getElementById( window.et_location_hash.substring( 1 ) );
						if( et_anchor_element === null ) {
						    return;
						}
						window.et_location_hash_style = et_anchor_element.style.display;
						// Prevent jump to anchor - Other Browsers
						et_anchor_element.style.display = "none";
					}
				} );
		</script>';
	}
}
add_action( 'wp_head', 'et_maybe_add_scroll_to_anchor_fix', 9 );

function et_remove_additional_stylesheet( $stylesheet ){
	global $default_colorscheme;
	return $default_colorscheme;
}
add_filter( 'et_get_additional_color_scheme', 'et_remove_additional_stylesheet' );

if ( ! function_exists( 'et_list_pings' ) ) :
function et_list_pings($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
<?php }
endif;

if ( ! function_exists( 'et_get_theme_version' ) ) :
function et_get_theme_version() {
	$theme_info = wp_get_theme();

	if ( is_child_theme() ) {
		$theme_info = wp_get_theme( $theme_info->parent_theme );
	}

	$theme_version = $theme_info->display( 'Version' );

	return $theme_version;
}
endif;

function et_add_post_meta_box( $post_type, $post ) {
	$allowed = et_pb_is_allowed( 'page_options' );
	$enabled = $post ? et_builder_enabled_for_post( $post->ID ) : et_builder_enabled_for_post_type( $post_type );
	$enabled = in_array( $post_type, et_builder_get_default_post_types() ) ? true : $enabled;
	$public  = et_builder_is_post_type_public( $post_type );

	if ( $allowed && $enabled && $public ) {
		add_meta_box( 'et_settings_meta_box', esc_html__( 'Divi Page Settings', 'Divi' ), 'et_single_settings_meta_box', $post_type, 'side', 'high' );
	}
}
add_action( 'add_meta_boxes', 'et_add_post_meta_box', 10, 2 );

if ( ! function_exists( 'et_pb_portfolio_meta_box' ) ) :
function et_pb_portfolio_meta_box() { ?>
	<div class="et_project_meta">
		<strong class="et_project_meta_title"><?php echo esc_html__( 'Skills', 'Divi' ); ?></strong>
		<p><?php echo get_the_term_list( get_the_ID(), 'project_tag', '', ', ' ); ?></p>

		<strong class="et_project_meta_title"><?php echo esc_html__( 'Posted on', 'Divi' ); ?></strong>
		<p><?php echo get_the_date(); ?></p>
	</div>
<?php }
endif;

if ( ! function_exists( 'et_single_settings_meta_box' ) ) :
function et_single_settings_meta_box( $post ) {
	$post_id = get_the_ID();

	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );

	$page_layout = get_post_meta( $post_id, '_et_pb_page_layout', true );

	$side_nav = get_post_meta( $post_id, '_et_pb_side_nav', true );

	$project_nav = get_post_meta( $post_id, '_et_pb_project_nav', true );

	$post_hide_nav = get_post_meta( $post_id, '_et_pb_post_hide_nav', true );
	$post_hide_nav = $post_hide_nav && 'off' === $post_hide_nav ? 'default' : $post_hide_nav;

	$show_title = get_post_meta( $post_id, '_et_pb_show_title', true );

	$is_builder_active = 'on' === get_post_meta( $post_id, '_et_pb_use_builder', true );

	if ( is_rtl() ) {
		$page_layouts = array(
			'et_left_sidebar'    => esc_html__( 'Left Sidebar', 'Divi' ),
			'et_right_sidebar'   => esc_html__( 'Right Sidebar', 'Divi' ),
			'et_no_sidebar'      => esc_html__( 'No Sidebar', 'Divi' ),
		);
	} else {
		$page_layouts = array(
			'et_right_sidebar'   => esc_html__( 'Right Sidebar', 'Divi' ),
			'et_left_sidebar'    => esc_html__( 'Left Sidebar', 'Divi' ),
			'et_no_sidebar'      => esc_html__( 'No Sidebar', 'Divi' ),
		);
	}

	// Fullwidth option available for default post types only. Not available for custom post types.
	if ( ! et_builder_is_post_type_custom( $post->post_type ) ) {
		$page_layouts['et_full_width_page'] = esc_html__( 'Fullwidth', 'Divi' );
	}

	if ( 'et_full_width_page' === $page_layout && ( ! isset( $page_layouts['et_full_width_page'] ) || ! $is_builder_active ) ) {
		$page_layout = 'et_no_sidebar';
	}

	$layouts = array(
		'light' => esc_html__( 'Light', 'Divi' ),
		'dark'  => esc_html__( 'Dark', 'Divi' ),
	);
	$post_bg_color  = ( $bg_color = get_post_meta( $post_id, '_et_post_bg_color', true ) ) && '' !== $bg_color
		? $bg_color
		: '#ffffff';
	$post_use_bg_color = get_post_meta( $post_id, '_et_post_use_bg_color', true )
		? true
		: false;
	$post_bg_layout = ( $layout = get_post_meta( $post_id, '_et_post_bg_layout', true ) ) && '' !== $layout
		? $layout
		: 'light'; ?>

	<p class="et_pb_page_settings et_pb_page_layout_settings">
		<label for="et_pb_page_layout" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Page Layout', 'Divi' ); ?>: </label>

		<select id="et_pb_page_layout" name="et_pb_page_layout">
		<?php
		foreach ( $page_layouts as $layout_value => $layout_name ) {
			printf( '<option value="%2$s"%3$s%4$s>%1$s</option>',
				esc_html( $layout_name ),
				esc_attr( $layout_value ),
				selected( $layout_value, $page_layout, false ),
				'et_full_width_page' === $layout_value && ! $is_builder_active ? ' style="display: none;"' : ''
			);
		} ?>
		</select>
	</p>
	<p class="et_pb_page_settings et_pb_side_nav_settings" style="display: none;">
		<label for="et_pb_side_nav" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Dot Navigation', 'Divi' ); ?>: </label>

		<select id="et_pb_side_nav" name="et_pb_side_nav">
			<option value="off" <?php selected( 'off', $side_nav ); ?>><?php esc_html_e( 'Off', 'Divi' ); ?></option>
			<option value="on" <?php selected( 'on', $side_nav ); ?>><?php esc_html_e( 'On', 'Divi' ); ?></option>
		</select>
	</p>
	<p class="et_pb_page_settings">
		<label for="et_pb_post_hide_nav" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Hide Nav Before Scroll', 'Divi' ); ?>: </label>

		<select id="et_pb_post_hide_nav" name="et_pb_post_hide_nav">
			<option value="default" <?php selected( 'default', $post_hide_nav ); ?>><?php esc_html_e( 'Default', 'Divi' ); ?></option>
			<option value="no" <?php selected( 'no', $post_hide_nav ); ?>><?php esc_html_e( 'Off', 'Divi' ); ?></option>
			<option value="on" <?php selected( 'on', $post_hide_nav ); ?>><?php esc_html_e( 'On', 'Divi' ); ?></option>
		</select>
	</p>

<?php if ( 'post' === $post->post_type ) : ?>
	<p class="et_pb_page_settings et_pb_single_title" style="display: none;">
		<label for="et_single_title" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Post Title', 'Divi' ); ?>: </label>

		<select id="et_single_title" name="et_single_title">
			<option value="on" <?php selected( 'on', $show_title ); ?>><?php esc_html_e( 'Show', 'Divi' ); ?></option>
			<option value="off" <?php selected( 'off', $show_title ); ?>><?php esc_html_e( 'Hide', 'Divi' ); ?></option>
		</select>
	</p>

	<p class="et_divi_quote_settings et_divi_audio_settings et_divi_link_settings et_divi_format_setting et_pb_page_settings">
		<label for="et_post_use_bg_color" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Use Background Color', 'Divi' ); ?></label>
		<input name="et_post_use_bg_color" type="checkbox" id="et_post_use_bg_color" <?php checked( $post_use_bg_color ); ?> />
	</p>

	<p class="et_post_bg_color_setting et_divi_format_setting et_pb_page_settings">
		<input id="et_post_bg_color" name="et_post_bg_color" class="color-picker-hex" type="text" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value', 'Divi' ); ?>" value="<?php echo esc_attr( $post_bg_color ); ?>" data-default-color="#ffffff" />
	</p>

	<p class="et_divi_quote_settings et_divi_audio_settings et_divi_link_settings et_divi_format_setting">
		<label for="et_post_bg_layout" style="font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Text Color', 'Divi' ); ?>: </label>
		<select id="et_post_bg_layout" name="et_post_bg_layout">
	<?php
		foreach ( $layouts as $layout_name => $layout_title )
			printf( '<option value="%s"%s>%s</option>',
				esc_attr( $layout_name ),
				selected( $layout_name, $post_bg_layout, false ),
				esc_html( $layout_title )
			);
	?>
		</select>
	</p>
<?php endif;

if ( 'project' === $post->post_type ) : ?>
	<p class="et_pb_page_settings et_pb_project_nav" style="display: none;">
		<label for="et_project_nav" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Project Navigation', 'Divi' ); ?>: </label>

		<select id="et_project_nav" name="et_project_nav">
			<option value="off" <?php selected( 'off', $project_nav ); ?>><?php esc_html_e( 'Hide', 'Divi' ); ?></option>
			<option value="on" <?php selected( 'on', $project_nav ); ?>><?php esc_html_e( 'Show', 'Divi' ); ?></option>
		</select>
	</p>
<?php endif;
}
endif;

function et_divi_post_settings_save_details( $post_id, $post ){
	global $pagenow;

	if ( 'post.php' !== $pagenow || ! $post || ! is_object( $post ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['et_settings_nonce'] ) || ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( isset( $_POST['et_post_use_bg_color'] ) )
		update_post_meta( $post_id, '_et_post_use_bg_color', true );
	else
		delete_post_meta( $post_id, '_et_post_use_bg_color' );

	if ( isset( $_POST['et_post_bg_color'] ) )
		update_post_meta( $post_id, '_et_post_bg_color', sanitize_text_field( $_POST['et_post_bg_color'] ) );
	else
		delete_post_meta( $post_id, '_et_post_bg_color' );

	if ( isset( $_POST['et_post_bg_layout'] ) )
		update_post_meta( $post_id, '_et_post_bg_layout', sanitize_text_field( $_POST['et_post_bg_layout'] ) );
	else
		delete_post_meta( $post_id, '_et_post_bg_layout' );

	if ( isset( $_POST['et_single_title'] ) )
		update_post_meta( $post_id, '_et_pb_show_title', sanitize_text_field( $_POST['et_single_title'] ) );
	else
		delete_post_meta( $post_id, '_et_pb_show_title' );

	if ( isset( $_POST['et_pb_post_hide_nav'] ) )
		update_post_meta( $post_id, '_et_pb_post_hide_nav', sanitize_text_field( $_POST['et_pb_post_hide_nav'] ) );
	else
		delete_post_meta( $post_id, '_et_pb_post_hide_nav' );

	if ( isset( $_POST['et_project_nav'] ) )
		update_post_meta( $post_id, '_et_pb_project_nav', sanitize_text_field( $_POST['et_project_nav'] ) );
	else
		delete_post_meta( $post_id, '_et_pb_project_nav' );

	if ( isset( $_POST['et_pb_page_layout'] ) ) {
		update_post_meta( $post_id, '_et_pb_page_layout', sanitize_text_field( $_POST['et_pb_page_layout'] ) );
	} else {
		delete_post_meta( $post_id, '_et_pb_page_layout' );
	}

	if ( isset( $_POST['et_pb_side_nav'] ) ) {
		update_post_meta( $post_id, '_et_pb_side_nav', sanitize_text_field( $_POST['et_pb_side_nav'] ) );
	} else {
		delete_post_meta( $post_id, '_et_pb_side_nav' );
	}
}
add_action( 'save_post', 'et_divi_post_settings_save_details', 10, 2 );

/**
 * Return the list of languages which support one font
 * @return array
 */
if ( ! function_exists( 'et_get_one_font_languages' ) ) :
function et_get_one_font_languages() {
	$one_font_languages = array(
		'ja' => array(
			'language_name'   => 'Japanese',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/notosansjapanese.css',
			'font_family'     => "'Noto Sans Japanese', serif",
		),
		'ko_KR' => array(
			'language_name'   => 'Korean',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/hanna.css',
			'font_family'     => "'Hanna', serif",
		),
		'ms_MY' => array(
			'language_name'   => 'Malay',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/notosansmalayalam.css',
			'font_family'     => "'Noto Sans Malayalam', serif",
		),
		'zh_CN' => array(
			'language_name'   => 'Chinese',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/cwtexfangsong.css',
			'font_family'     => "'cwTeXFangSong', serif",
		),
	);

	return $one_font_languages;
}
endif;

/**
 * Return the list of languages which supported one font previously
 * @return array
 */
if ( ! function_exists( 'et_get_old_one_font_languages' ) ) :
function et_get_old_one_font_languages() {
	$old_one_font_languages = array(
		'he_IL' => array(
			'language_name'   => 'Hebrew',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/alefhebrew.css',
			'font_family'     => 'Alef Hebrew',
		),
		'ar' => array(
			'language_name'   => 'Arabic',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/lateef.css',
			'font_family'     => 'Lateef',
		),
		'th' => array(
			'language_name'   => 'Thai',
			'google_font_url' => '//fonts.googleapis.com/earlyaccess/notosansthai.css',
			'font_family'     => 'Noto Sans Thai',
		),
	);

	return $old_one_font_languages;
}
endif;

/**
 * Return custom default font-family for the languages which supported one font previously
 * @param string
 * @return string
 */
if ( ! function_exists( 'et_pb_get_specific_default_font' ) ) :
function et_pb_get_specific_default_font( $font_family ) {
	// do nothing if font is not default
	if ( ! in_array( $font_family, array( 'none', '' ) ) ) {
		return $font_family;
	}

	$site_domain = get_locale();

	// array of the languages which were "one font languages" earlier and have specific defaults
	$specific_defaults = et_get_old_one_font_languages();

	if ( isset( $specific_defaults[ $site_domain ] ) ) {
		return $specific_defaults[ $site_domain ]['font_family'];
	}

	return $font_family;
}
endif;

function et_divi_customize_register( $wp_customize ) {
	global $wp_version;

	// Get WP major version
	$wp_major_version = substr( $wp_version, 0, 3 );

	$wp_customize->remove_section( 'title_tagline' );
	$wp_customize->remove_section( 'background_image' );
	$wp_customize->remove_section( 'colors' );
	$wp_customize->register_control_type( 'ET_Divi_Customize_Color_Alpha_Control' );

	if ( version_compare( $wp_major_version, '4.9', '>=' ) ) {
		wp_register_script( 'wp-color-picker-alpha', get_template_directory_uri() . '/includes/builder/scripts/ext/wp-color-picker-alpha.min.js', array( 'jquery', 'wp-color-picker' ) );
		wp_localize_script( 'wp-color-picker-alpha', 'et_pb_color_picker_strings', apply_filters( 'et_pb_color_picker_strings_builder', array(
			'legacy_pick'    => esc_html__( 'Select', 'et_builder' ),
			'legacy_current' => esc_html__( 'Current Color', 'et_builder' ),
		) ) );
	} else {
		wp_register_script( 'wp-color-picker-alpha', get_template_directory_uri() . '/includes/builder/scripts/ext/wp-color-picker-alpha-48.min.js', array( 'jquery', 'wp-color-picker' ) );
	}

	$option_set_name           = 'et_customizer_option_set';
	$option_set_allowed_values = apply_filters( 'et_customizer_option_set_allowed_values', array( 'module', 'theme' ) );

	$customizer_option_set = '';

	/**
	 * Set a transient,
	 * if 'et_customizer_option_set' query parameter is set to one of the allowed values
	 */
	if ( isset( $_GET[ $option_set_name ] ) && in_array( $_GET[ $option_set_name ], $option_set_allowed_values ) ) {
		$customizer_option_set = $_GET[ $option_set_name ];

		set_transient( 'et_divi_customizer_option_set', $customizer_option_set, DAY_IN_SECONDS );
	}

	if ( '' === $customizer_option_set && ( $et_customizer_option_set_value = get_transient( 'et_divi_customizer_option_set' ) ) ) {
		$customizer_option_set = $et_customizer_option_set_value;
	}

	et_builder_init_global_settings();

	if ( isset( $customizer_option_set ) && 'module' === $customizer_option_set ) {
		// display wp error screen if module customizer disabled for current user
		if ( ! et_pb_is_allowed( 'module_customizer' ) ) {
			wp_die( esc_html__( "you don't have sufficient permissions to access this page", 'Divi' ) );
		}

		$removed_default_sections = array( 'nav', 'static_front_page', 'custom_css' );
		foreach ( $removed_default_sections as $default_section ) {
			$wp_customize->remove_section( $default_section );
		}

		et_divi_customizer_module_settings( $wp_customize );
	} else {
		// display wp error screen if theme customizer disabled for current user
		if ( ! et_pb_is_allowed( 'theme_customizer' ) ) {
			wp_die( esc_html__( "you don't have sufficient permissions to access this page", 'Divi' ) );
		}

		et_divi_customizer_theme_settings( $wp_customize );
	}
}
add_action( 'customize_register', 'et_divi_customize_register' );

if ( ! function_exists( 'et_divi_customizer_theme_settings' ) ) :
/**
 * @param \WP_Customize_Manager $wp_customize
 */
function et_divi_customizer_theme_settings( $wp_customize ) {
	$site_domain = get_locale();

	$google_fonts = et_builder_get_fonts( array(
		'prepend_standard_fonts' => false,
	) );

	$user_fonts = et_builder_get_custom_fonts();

	// combine google fonts with custom user fonts
	$google_fonts = array_merge( $user_fonts, $google_fonts );

	$et_domain_fonts = array(
		'ru_RU' => 'cyrillic',
		'uk'    => 'cyrillic',
		'bg_BG' => 'cyrillic',
		'vi'    => 'vietnamese',
		'el'    => 'greek',
		'ar'    => 'arabic',
		'he_IL' => 'hebrew',
		'th'    => 'thai',
		'si_lk' => 'sinhala',
		'bn_bd' => 'bengali',
		'ta_lk' => 'tamil',
		'te'    => 'telegu',
		'km'    => 'khmer',
		'kn'    => 'kannada',
		'ml_in' => 'malayalam',
	);

	$et_one_font_languages = et_get_one_font_languages();

	$font_choices = array();
	$font_choices['none'] = array(
		'label' => 'Default Theme Font'
	);

	$removed_fonts_mapping = et_builder_old_fonts_mapping();

	foreach ( $google_fonts as $google_font_name => $google_font_properties ) {
		$use_parent_font = false;

		if ( isset( $removed_fonts_mapping[ $google_font_name ] ) ) {
			$parent_font = $removed_fonts_mapping[ $google_font_name ]['parent_font'];
			$google_font_properties['character_set'] = $google_fonts[ $parent_font ]['character_set'];
			$use_parent_font = true;
		}

		if ( '' !== $site_domain && isset( $et_domain_fonts[$site_domain] ) && isset( $google_font_properties['character_set'] ) && false === strpos( $google_font_properties['character_set'], $et_domain_fonts[$site_domain] ) ) {
			continue;
		}

		$font_choices[ $google_font_name ] = array(
			'label' => $google_font_name,
			'data'  => array(
				'parent_font'    => $use_parent_font ? $google_font_properties['parent_font'] : '',
				'parent_styles'  => $use_parent_font ? $google_fonts[$parent_font]['styles'] : $google_font_properties['styles'],
				'current_styles' => $use_parent_font && isset( $google_fonts[$parent_font]['styles'] ) && isset( $google_font_properties['styles'] ) ? $google_font_properties['styles'] : '',
				'parent_subset'  => $use_parent_font && isset( $google_fonts[$parent_font]['character_set'] ) ? $google_fonts[$parent_font]['character_set'] : '',
				'standard'       => isset( $google_font_properties['standard'] ) && $google_font_properties['standard'] ? 'on' : 'off',
			)
		);
	}

	$wp_customize->add_panel( 'et_divi_general_settings' , array(
		'title'		=> esc_html__( 'General Settings', 'Divi' ),
		'priority'	=> 1,
	) );

	$wp_customize->add_section( 'title_tagline', array(
		'title'    => esc_html__( 'Site Identity', 'Divi' ),
		'panel' => 'et_divi_general_settings',
	) );

	$wp_customize->add_section( 'et_divi_general_layout' , array(
		'title'		=> esc_html__( 'Layout Settings', 'Divi' ),
		'panel' => 'et_divi_general_settings',
	) );

	$wp_customize->add_section( 'et_divi_general_typography' , array(
		'title'		=> esc_html__( 'Typography', 'Divi' ),
		'panel' => 'et_divi_general_settings',
	) );

	$wp_customize->add_panel( 'et_divi_mobile' , array(
		'title'		=> esc_html__( 'Mobile Styles', 'Divi' ),
		'priority' => 6,
	) );

	$wp_customize->add_section( 'et_divi_mobile_tablet' , array(
		'title'		=> esc_html__( 'Tablet', 'Divi' ),
		'panel' => 'et_divi_mobile',
	) );

	$wp_customize->add_section( 'et_divi_mobile_phone' , array(
		'title'		=> esc_html__( 'Phone', 'Divi' ),
		'panel' => 'et_divi_mobile',
	) );

	$wp_customize->add_section( 'et_divi_mobile_menu' , array(
		'title'		=> esc_html__( 'Mobile Menu', 'Divi' ),
		'panel' => 'et_divi_mobile',
	) );

	$wp_customize->add_section( 'et_divi_general_background' , array(
		'title'		=> esc_html__( 'Background', 'Divi' ),
		'panel' => 'et_divi_general_settings',
	) );

	$wp_customize->add_panel( 'et_divi_header_panel', array(
		'title' => esc_html__( 'Header & Navigation', 'Divi' ),
		'priority' => 2,
	) );

	$wp_customize->add_section( 'et_divi_header_layout' , array(
		'title'		=> esc_html__( 'Header Format', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_section( 'et_divi_header_primary' , array(
		'title'		=> esc_html__( 'Primary Menu Bar', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_section( 'et_divi_header_secondary' , array(
		'title'		=> esc_html__( 'Secondary Menu Bar', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_section( 'et_divi_header_slide' , array(
		'title'		=> esc_html__( 'Slide In & Fullscreen Header Settings', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_section( 'et_divi_header_fixed' , array(
		'title'		=> esc_html__( 'Fixed Navigation Settings', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_section( 'et_divi_header_information' , array(
		'title'		=> esc_html__( 'Header Elements', 'Divi' ),
		'panel' => 'et_divi_header_panel',
	) );

	$wp_customize->add_panel( 'et_divi_footer_panel' , array(
		'title'		=> esc_html__( 'Footer', 'Divi' ),
		'priority'	=> 3,
	) );

	$wp_customize->add_section( 'et_divi_footer_layout' , array(
		'title'		=> esc_html__( 'Layout', 'Divi' ),
		'panel' => 'et_divi_footer_panel',
	) );

	$wp_customize->add_section( 'et_divi_footer_widgets' , array(
		'title'		=> esc_html__( 'Widgets', 'Divi' ),
		'panel' => 'et_divi_footer_panel',
	) );

	$wp_customize->add_section( 'et_divi_footer_elements' , array(
		'title'		=> esc_html__( 'Footer Elements', 'Divi' ),
		'panel' => 'et_divi_footer_panel',
	) );

	$wp_customize->add_section( 'et_divi_footer_menu' , array(
		'title'		=> esc_html__( 'Footer Menu', 'Divi' ),
		'panel' => 'et_divi_footer_panel',
	) );

	$wp_customize->add_section( 'et_divi_bottom_bar' , array(
		'title'		=> esc_html__( 'Bottom Bar', 'Divi' ),
		'panel' => 'et_divi_footer_panel',
	) );

	$wp_customize->add_section( 'et_color_schemes' , array(
		'title'       => esc_html__( 'Color Schemes', 'Divi' ),
		'priority'    => 7,
		'description' => esc_html__( 'Note: Color settings set above should be applied to the Default color scheme.', 'Divi' ),
	) );

	$wp_customize->add_panel( 'et_divi_buttons_settings' , array(
		'title'		=> esc_html__( 'Buttons', 'Divi' ),
		'priority'	=> 4,
	) );

	$wp_customize->add_section( 'et_divi_buttons' , array(
		'title'       => esc_html__( 'Buttons Style', 'Divi' ),
		'panel'       => 'et_divi_buttons_settings',
	) );

	$wp_customize->add_section( 'et_divi_buttons_hover' , array(
		'title'       => esc_html__( 'Buttons Hover Style', 'Divi' ),
		'panel'       => 'et_divi_buttons_settings',
	) );

	$wp_customize->add_panel( 'et_divi_blog_settings' , array(
		'title'		=> esc_html__( 'Blog', 'Divi' ),
		'priority'	=> 5,
	) );

	$wp_customize->add_section( 'et_divi_blog_post' , array(
		'title'       => esc_html__( 'Post', 'Divi' ),
		'panel'       => 'et_divi_blog_settings',
	) );

	$wp_customize->add_setting( 'et_divi[post_meta_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';

	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_meta_font_size]', array(
		'label'	      => esc_html__( 'Meta Text Size', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_meta_height]', array(
		'default'       => '1',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_float_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_meta_height]', array(
		'label'	      => esc_html__( 'Meta Line Height', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => .8,
			'max'  => 3,
			'step' => .1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_meta_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_meta_spacing]', array(
		'label'	      => esc_html__( 'Meta Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -2,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_meta_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[post_meta_style]', array(
		'label'	      => esc_html__( 'Meta Font Style', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_header_font_size]', array(
		'default'       => '30',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_header_font_size]', array(
		'label'	      => esc_html__( 'Header Text Size', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 72,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_header_height]', array(
		'default'       => '1',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_float_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_header_height]', array(
		'label'	      => esc_html__( 'Header Line Height', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0.8,
			'max'  => 3,
			'step' => 0.1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_header_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[post_header_spacing]', array(
		'label'	      => esc_html__( 'Header Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -2,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[post_header_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[post_header_style]', array(
		'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
		'section'     => 'et_divi_blog_post',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[boxed_layout]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[boxed_layout]', array(
		'label'		=> esc_html__( 'Enable Boxed Layout', 'Divi' ),
		'section'	=> 'et_divi_general_layout',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[content_width]', array(
		'default'       => '1080',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[content_width]', array(
		'label'	      => esc_html__( 'Website Content Width', 'Divi' ),
		'section'     => 'et_divi_general_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 960,
			'max'  => 1920,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[gutter_width]', array(
		'default'       => '3',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[gutter_width]', array(
		'label'	      => esc_html__( 'Website Gutter Width', 'Divi' ),
		'section'     => 'et_divi_general_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 1,
			'max'  => 4,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[use_sidebar_width]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[use_sidebar_width]', array(
		'label'		=> esc_html__( 'Use Custom Sidebar Width', 'Divi' ),
		'section'	=> 'et_divi_general_layout',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[sidebar_width]', array(
		'default'       => '21',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[sidebar_width]', array(
		'label'	      => esc_html__( 'Sidebar Width', 'Divi' ),
		'section'     => 'et_divi_general_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 19,
			'max'  => 33,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[section_padding]', array(
		'default'       => '4',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[section_padding]', array(
		'label'	      => esc_html__( 'Section Height', 'Divi' ),
		'section'     => 'et_divi_general_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[phone_section_height]', array(
		'default'       => et_get_option( 'tablet_section_height', '50' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[phone_section_height]', array(
		'label'	      => esc_html__( 'Section Height', 'Divi' ),
		'section'     => 'et_divi_mobile_phone',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 150,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[tablet_section_height]', array(
		'default'       => '50',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[tablet_section_height]', array(
		'label'	      => esc_html__( 'Section Height', 'Divi' ),
		'section'     => 'et_divi_mobile_tablet',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 150,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[row_padding]', array(
		'default'       => '2',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[row_padding]', array(
		'label'	      => esc_html__( 'Row Height', 'Divi' ),
		'section'     => 'et_divi_general_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[phone_row_height]', array(
		'default'       => et_get_option( 'tablet_row_height', '30' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[phone_row_height]', array(
		'label'	      => esc_html__( 'Row Height', 'Divi' ),
		'section'     => 'et_divi_mobile_phone',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 150,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[tablet_row_height]', array(
		'default'       => '30',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[tablet_row_height]', array(
		'label'	      => esc_html__( 'Row Height', 'Divi' ),
		'section'     => 'et_divi_mobile_tablet',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 150,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[cover_background]', array(
		'default'       => 'on',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[cover_background]', array(
		'label'		=> esc_html__( 'Stretch Background Image', 'Divi' ),
		'section'	=> 'et_divi_general_background',
		'type'      => 'checkbox',
	) );

	if ( ! is_null( $wp_customize->get_setting( 'background_color' ) ) ) {
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background_color', array(
			'label'		=> esc_html__( 'Background Color', 'Divi' ),
			'section'	=> 'et_divi_general_background',
		) ) );
	}

	if ( ! is_null( $wp_customize->get_setting( 'background_image' ) ) ) {
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'background_image', array(
			'label'		=> esc_html__( 'Background Image', 'Divi' ),
			'section'	=> 'et_divi_general_background',
		) ) );
	}

	// Remove default background_repeat setting and control since native
	// background_repeat field has different different settings
	$wp_customize->remove_setting( 'background_repeat' );
	$wp_customize->remove_control( 'background_repeat' );

	// Re-defined Divi specific background repeat option
	$wp_customize->add_setting( 'background_repeat', array(
		'default'           => apply_filters( 'et_divi_background_repeat_default', 'repeat' ),
		'sanitize_callback' => 'et_sanitize_background_repeat',
		'theme_supports'    => 'custom-background',
		'capability'        => 'edit_theme_options',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'background_repeat', array(
		'label'		=> esc_html__( 'Background Repeat', 'Divi' ),
		'section'	=> 'et_divi_general_background',
		'type'      => 'radio',
		'choices'   => et_divi_background_repeat_choices(),
	) );

	$wp_customize->add_control( 'background_position_x', array(
		'label'		=> esc_html__( 'Background Position', 'Divi' ),
		'section'	=> 'et_divi_general_background',
		'type'      => 'radio',
		'choices'    => array(
				'left'       => esc_html__( 'Left', 'Divi' ),
				'center'     => esc_html__( 'Center', 'Divi' ),
				'right'      => esc_html__( 'Right', 'Divi' ),
			),
	) );

	// Remove default background_attachment setting and control since native
	// background_attachment field has different different settings
	$wp_customize->remove_setting( 'background_attachment' );
	$wp_customize->remove_control( 'background_attachment' );

	$wp_customize->add_setting( 'background_attachment', array(
		'default'           => apply_filters( 'et_sanitize_background_attachment_default', 'scroll' ),
		'sanitize_callback' => 'et_sanitize_background_attachment',
		'theme_supports'    => 'custom-background',
		'capability'        => 'edit_theme_options',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'background_attachment', array(
		'label'		=> esc_html__( 'Background Position', 'Divi' ),
		'section'	=> 'et_divi_general_background',
		'type'      => 'radio',
		'choices'    => et_divi_background_attachment_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[body_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[body_font_size]', array(
		'label'	      => esc_html__( 'Body Text Size', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[body_font_height]', array(
		'default'       => '1.7',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_float_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[body_font_height]', array(
		'label'	      => esc_html__( 'Body Line Height', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0.8,
			'max'  => 3,
			'step' => 0.1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[phone_body_font_size]', array(
		'default'       => et_get_option( 'tablet_body_font_size', '14' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[phone_body_font_size]', array(
		'label'	      => esc_html__( 'Body Text Size', 'Divi' ),
		'section'     => 'et_divi_mobile_phone',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[tablet_body_font_size]', array(
		'default'       => et_get_option( 'body_font_size', '14' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[tablet_body_font_size]', array(
		'label'	      => esc_html__( 'Body Text Size', 'Divi' ),
		'section'     => 'et_divi_mobile_tablet',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[body_header_size]', array(
		'default'       => '30',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[body_header_size]', array(
		'label'	      => esc_html__( 'Header Text Size', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 22,
			'max'  => 72,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[body_header_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[body_header_spacing]', array(
		'label'	      => esc_html__( 'Header Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -2,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[body_header_height]', array(
		'default'       => '1',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_float_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[body_header_height]', array(
		'label'	      => esc_html__( 'Header Line Height', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0.8,
			'max'  => 3,
			'step' => 0.1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[body_header_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[body_header_style]', array(
		'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
		'section'     => 'et_divi_general_typography',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[phone_header_font_size]', array(
		'default'       => et_get_option( 'tablet_header_font_size', '30' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[phone_header_font_size]', array(
		'label'	      => esc_html__( 'Header Text Size', 'Divi' ),
		'section'     => 'et_divi_mobile_phone',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 22,
			'max'  => 72,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[tablet_header_font_size]', array(
		'default'       => et_get_option( 'body_header_size', '30' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[tablet_header_font_size]', array(
		'label'	      => esc_html__( 'Header Text Size', 'Divi' ),
		'section'     => 'et_divi_mobile_tablet',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 22,
			'max'  => 72,
			'step' => 1
		),
	) ) );

	if ( ! isset( $et_one_font_languages[$site_domain] ) ) {
		$wp_customize->add_setting( 'et_divi[heading_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[heading_font]', array(
			'label'		=> esc_html__( 'Header Font', 'Divi' ),
			'section'	=> 'et_divi_general_typography',
			'settings'	=> 'et_divi[heading_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices,
		) ) );

		$wp_customize->add_setting( 'et_divi[body_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[body_font]', array(
			'label'		=> esc_html__( 'Body Font', 'Divi' ),
			'section'	=> 'et_divi_general_typography',
			'settings'	=> 'et_divi[body_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[link_color]', array(
		'default'	=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[link_color]', array(
		'label'		=> esc_html__( 'Body Link Color', 'Divi' ),
		'section'	=> 'et_divi_general_typography',
		'settings'	=> 'et_divi[link_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[font_color]', array(
		'default'		=> '#666666',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[font_color]', array(
		'label'		=> esc_html__( 'Body Text Color', 'Divi' ),
		'section'	=> 'et_divi_general_typography',
		'settings'	=> 'et_divi[font_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[header_color]', array(
		'default'		=> '#666666',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[header_color]', array(
		'label'		=> esc_html__( 'Header Text Color', 'Divi' ),
		'section'	=> 'et_divi_general_typography',
		'settings'	=> 'et_divi[header_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[accent_color]', array(
		'default'		=> '#2ea3f2',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[accent_color]', array(
		'label'		=> esc_html__( 'Theme Accent Color', 'Divi' ),
		'section'	=> 'et_divi_general_layout',
		'settings'	=> 'et_divi[accent_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[color_schemes]', array(
		'default'		=> 'none',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_color_scheme',
	) );

	$wp_customize->add_control( 'et_divi[color_schemes]', array(
		'label'		=> esc_html__( 'Color Schemes', 'Divi' ),
		'section'	=> 'et_color_schemes',
		'settings'	=> 'et_divi[color_schemes]',
		'type'		=> 'select',
		'choices'	=> et_divi_color_scheme_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[header_style]', array(
		'default'       => 'left',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_header_style',
	) );

	$wp_customize->add_control( 'et_divi[header_style]', array(
		'label'		=> esc_html__( 'Header Style', 'Divi' ),
		'section'	=> 'et_divi_header_layout',
		'type'      => 'select',
		'choices'	=> et_divi_header_style_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[vertical_nav]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[vertical_nav]', array(
		'label'		=> esc_html__( 'Enable Vertical Navigation', 'Divi' ),
		'section'	=> 'et_divi_header_layout',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[vertical_nav_orientation]', array(
		'default'       => 'left',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_left_right',
	) );

	$wp_customize->add_control( 'et_divi[vertical_nav_orientation]', array(
		'label'		=> esc_html__( 'Vertical Menu Orientation', 'Divi' ),
		'section'	=> 'et_divi_header_layout',
		'type'      => 'select',
		'choices'	=> et_divi_left_right_choices(),
	) );

	if ( 'on' === et_get_option( 'divi_fixed_nav', 'on' ) ) {

		$wp_customize->add_setting( 'et_divi[hide_nav]', array(
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'wp_validate_boolean',
		) );

		$wp_customize->add_control( 'et_divi[hide_nav]', array(
			'label'		=> esc_html__( 'Hide Navigation Until Scroll', 'Divi' ),
			'section'	=> 'et_divi_header_layout',
			'type'      => 'checkbox',
		) );

	} // 'on' === et_get_option( 'divi_fixed_nav', 'on' )

	$wp_customize->add_setting( 'et_divi[show_header_social_icons]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[show_header_social_icons]', array(
		'label'		=> esc_html__( 'Show Social Icons', 'Divi' ),
		'section'	=> 'et_divi_header_information',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[show_search_icon]', array(
		'default'       => 'on',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[show_search_icon]', array(
		'label'		=> esc_html__( 'Show Search Icon', 'Divi' ),
		'section'	=> 'et_divi_header_information',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[slide_nav_show_top_bar]', array(
		'default'       => 'on',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[slide_nav_show_top_bar]', array(
		'label'		=> esc_html__( 'Show Top Bar', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[slide_nav_width]', array(
		'default'       => '320',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[slide_nav_width]', array(
		'label'	      => esc_html__( 'Menu Width', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 280,
			'max'  => 600,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[slide_nav_font_size]', array(
		'label'	      => esc_html__( 'Menu Text Size', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 24,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_top_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[slide_nav_top_font_size]', array(
		'label'	      => esc_html__( 'Top Bar Text Size', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 24,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[fullscreen_nav_font_size]', array(
		'default'       => '30',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[fullscreen_nav_font_size]', array(
		'label'	      => esc_html__( 'Menu Text Size', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 50,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[fullscreen_nav_top_font_size]', array(
		'default'       => '18',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[fullscreen_nav_top_font_size]', array(
		'label'	      => esc_html__( 'Top Bar Text Size', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 40,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_font_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[slide_nav_font_spacing]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -1,
			'max'  => 8,
			'step' => 1
		),
	) ) );

	if ( ! isset( $et_one_font_languages[$site_domain] ) ) {
		$wp_customize->add_setting( 'et_divi[slide_nav_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[slide_nav_font]', array(
			'label'		=> esc_html__( 'Font', 'Divi' ),
			'section'	=> 'et_divi_header_slide',
			'settings'	=> 'et_divi[slide_nav_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[slide_nav_font_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[slide_nav_font_style]', array(
		'label'	      => esc_html__( 'Font Style', 'Divi' ),
		'section'     => 'et_divi_header_slide',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_bg]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_bg]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_links_color]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_links_color]', array(
		'label'		=> esc_html__( 'Menu Link Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_links_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_links_color_active]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_links_color_active]', array(
		'label'		=> esc_html__( 'Active Link Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_links_color_active]',
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_top_color]', array(
		'default'		=> 'rgba(255,255,255,0.6)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_top_color]', array(
		'label'		=> esc_html__( 'Top Bar Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_top_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_search]', array(
		'default'		=> 'rgba(255,255,255,0.6)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_search]', array(
		'label'		=> esc_html__( 'Search Bar Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_search]',
	) ) );

	$wp_customize->add_setting( 'et_divi[slide_nav_search_bg]', array(
		'default'		=> 'rgba(0,0,0,0.2)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[slide_nav_search_bg]', array(
		'label'		=> esc_html__( 'Search Bar Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_slide',
		'settings'	=> 'et_divi[slide_nav_search_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[nav_fullwidth]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[nav_fullwidth]', array(
		'label'		=> esc_html__( 'Make Full Width', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[hide_primary_logo]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[hide_primary_logo]', array(
		'label'		=> esc_html__( 'Hide Logo Image', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[menu_height]', array(
		'default'       => '66',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[menu_height]', array(
		'label'	      => esc_html__( 'Menu Height', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 30,
			'max'  => 300,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[logo_height]', array(
		'default'       => '54',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[logo_height]', array(
		'label'	      => esc_html__( 'Logo Max Height', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 30,
			'max'  => 100,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[menu_margin_top]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[menu_margin_top]', array(
		'label'	      => esc_html__( 'Menu Top Margin', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 300,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[primary_nav_font_size]', array(
		'label'	      => esc_html__( 'Text Size', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 24,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_font_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[primary_nav_font_spacing]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -1,
			'max'  => 8,
			'step' => 1
		),
	) ) );

	if ( ! isset( $et_one_font_languages[$site_domain] ) ) {
		$wp_customize->add_setting( 'et_divi[primary_nav_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[primary_nav_font]', array(
			'label'		=> esc_html__( 'Font', 'Divi' ),
			'section'	=> 'et_divi_header_primary',
			'settings'	=> 'et_divi[primary_nav_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[primary_nav_font_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[primary_nav_font_style]', array(
		'label'	      => esc_html__( 'Font Style', 'Divi' ),
		'section'     => 'et_divi_header_primary',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_font_size]', array(
		'default'       => '12',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_fullwidth]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[secondary_nav_fullwidth]', array(
		'label'		=> esc_html__( 'Make Full Width', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[secondary_nav_font_size]', array(
		'label'	      => esc_html__( 'Text Size', 'Divi' ),
		'section'     => 'et_divi_header_secondary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 20,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_font_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[secondary_nav_font_spacing]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_header_secondary',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -1,
			'max'  => 8,
			'step' => 1
		),
	) ) );

	if ( ! isset( $et_one_font_languages[$site_domain] ) ) {
		$wp_customize->add_setting( 'et_divi[secondary_nav_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[secondary_nav_font]', array(
			'label'		=> esc_html__( 'Font', 'Divi' ),
			'section'	=> 'et_divi_header_secondary',
			'settings'	=> 'et_divi[secondary_nav_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[secondary_nav_font_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[secondary_nav_font_style]', array(
		'label'	      => esc_html__( 'Font Style', 'Divi' ),
		'section'     => 'et_divi_header_secondary',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[menu_link]', array(
		'default'		=> 'rgba(0,0,0,0.6)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[menu_link]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[menu_link]',
	) ) );

	$wp_customize->add_setting( 'et_divi[hide_mobile_logo]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[hide_mobile_logo]', array(
		'label'		=> esc_html__( 'Hide Logo Image', 'Divi' ),
		'section'	=> 'et_divi_mobile_menu',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[mobile_menu_link]', array(
		'default'		=> et_get_option( 'menu_link', 'rgba(0,0,0,0.6)' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[mobile_menu_link]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_mobile_menu',
		'settings'	=> 'et_divi[mobile_menu_link]',
	) ) );

	$wp_customize->add_setting( 'et_divi[menu_link_active]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[menu_link_active]', array(
		'label'		=> esc_html__( 'Active Link Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[menu_link_active]',
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_bg]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[primary_nav_bg]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[primary_nav_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_dropdown_bg]', array(
		'default'		=> et_get_option( 'primary_nav_bg', '#ffffff' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[primary_nav_dropdown_bg]', array(
		'label'		=> esc_html__( 'Dropdown Menu Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[primary_nav_dropdown_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_dropdown_line_color]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[primary_nav_dropdown_line_color]', array(
		'label'		=> esc_html__( 'Dropdown Menu Line Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[primary_nav_dropdown_line_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_dropdown_link_color]', array(
		'default'		=> et_get_option( 'menu_link', 'rgba(0,0,0,0.7)' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[primary_nav_dropdown_link_color]', array(
		'label'		=> esc_html__( 'Dropdown Menu Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'settings'	=> 'et_divi[primary_nav_dropdown_link_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[primary_nav_dropdown_animation]', array(
		'default'       => 'fade',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_dropdown_animation',
	) );

	$wp_customize->add_control( 'et_divi[primary_nav_dropdown_animation]', array(
		'label'		=> esc_html__( 'Dropdown Menu Animation', 'Divi' ),
		'section'	=> 'et_divi_header_primary',
		'type'      => 'select',
		'choices'	=> et_divi_dropdown_animation_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[mobile_primary_nav_bg]', array(
		'default'		=> et_get_option( 'primary_nav_bg', '#ffffff' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[mobile_primary_nav_bg]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_mobile_menu',
		'settings'	=> 'et_divi[mobile_primary_nav_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_bg]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[secondary_nav_bg]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'settings'	=> 'et_divi[secondary_nav_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_text_color_new]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[secondary_nav_text_color_new]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'settings'	=> 'et_divi[secondary_nav_text_color_new]',
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_dropdown_bg]', array(
		'default'		=> et_get_option( 'secondary_nav_bg', et_get_option( 'accent_color', '#2ea3f2' ) ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[secondary_nav_dropdown_bg]', array(
		'label'		=> esc_html__( 'Dropdown Menu Background Color', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'settings'	=> 'et_divi[secondary_nav_dropdown_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_dropdown_link_color]', array(
		'default'		=> et_get_option( 'secondary_nav_text_color_new', '#ffffff' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[secondary_nav_dropdown_link_color]', array(
		'label'		=> esc_html__( 'Dropdown Menu Text Color', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'settings'	=> 'et_divi[secondary_nav_dropdown_link_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[secondary_nav_dropdown_animation]', array(
		'default'       => 'fade',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_dropdown_animation',
	) );

	$wp_customize->add_control( 'et_divi[secondary_nav_dropdown_animation]', array(
		'label'		=> esc_html__( 'Dropdown Menu Animation', 'Divi' ),
		'section'	=> 'et_divi_header_secondary',
		'type'      => 'select',
		'choices'	=> et_divi_dropdown_animation_choices(),
	) );

	// Setting with no control kept for backwards compatbility
	$wp_customize->add_setting( 'et_divi[primary_nav_text_color]', array(
		'default'       => 'dark',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	// Setting with no control kept for backwards compatbility
	$wp_customize->add_setting( 'et_divi[secondary_nav_text_color]', array(
		'default'       => 'light',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	) );

	if ( 'on' === et_get_option( 'divi_fixed_nav', 'on' ) ) {
		$wp_customize->add_setting( 'et_divi[hide_fixed_logo]', array(
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'wp_validate_boolean',
		) );

		$wp_customize->add_control( 'et_divi[hide_fixed_logo]', array(
			'label'		=> esc_html__( 'Hide Logo Image', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'type'      => 'checkbox',
		) );

		$wp_customize->add_setting( 'et_divi[minimized_menu_height]', array(
			'default'       => '40',
			'type'          => 'option',
			'capability'    => 'edit_theme_options',
			'transport'     => 'postMessage',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[minimized_menu_height]', array(
			'label'	      => esc_html__( 'Fixed Menu Height', 'Divi' ),
			'section'     => 'et_divi_header_fixed',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 30,
				'max'  => 300,
				'step' => 1
			),
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_primary_nav_font_size]', array(
			'default'       => et_get_option( 'primary_nav_font_size', '14' ),
			'type'          => 'option',
			'capability'    => 'edit_theme_options',
			'transport'     => 'postMessage',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[fixed_primary_nav_font_size]', array(
			'label'	      => esc_html__( 'Text Size', 'Divi' ),
			'section'     => 'et_divi_header_fixed',
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 12,
				'max'  => 24,
				'step' => 1
			),
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_primary_nav_bg]', array(
			'default'		=> et_get_option( 'primary_nav_bg', '#ffffff' ),
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_alpha_color',
		) );

		$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[fixed_primary_nav_bg]', array(
			'label'		=> esc_html__( 'Primary Menu Background Color', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'settings'	=> 'et_divi[fixed_primary_nav_bg]',
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_secondary_nav_bg]', array(
			'default'		=> et_get_option( 'secondary_nav_bg', '#2ea3f2' ),
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_alpha_color',
		) );

		$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[fixed_secondary_nav_bg]', array(
			'label'		=> esc_html__( 'Secondary Menu Background Color', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'settings'	=> 'et_divi[fixed_secondary_nav_bg]',
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_menu_link]', array(
			'default'       => et_get_option( 'menu_link', 'rgba(0,0,0,0.6)' ),
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_alpha_color',
		) );

		$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[fixed_menu_link]', array(
			'label'		=> esc_html__( 'Primary Menu Link Color', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'settings'	=> 'et_divi[fixed_menu_link]',
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_secondary_menu_link]', array(
			'default'       => et_get_option( 'secondary_nav_text_color_new', '#ffffff' ),
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_alpha_color',
		) );

		$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[fixed_secondary_menu_link]', array(
			'label'		=> esc_html__( 'Secondary Menu Link Color', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'settings'	=> 'et_divi[fixed_secondary_menu_link]',
		) ) );

		$wp_customize->add_setting( 'et_divi[fixed_menu_link_active]', array(
			'default'       => et_get_option( 'menu_link_active', '#2ea3f2' ),
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_alpha_color',
		) );

		$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[fixed_menu_link_active]', array(
			'label'		=> esc_html__( 'Active Primary Menu Link Color', 'Divi' ),
			'section'	=> 'et_divi_header_fixed',
			'settings'	=> 'et_divi[fixed_menu_link_active]',
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[phone_number]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_html_input_text',
	) );

	$wp_customize->add_control( 'et_divi[phone_number]', array(
		'label'		=> esc_html__( 'Phone Number', 'Divi' ),
		'section'	=> 'et_divi_header_information',
		'type'      => 'text',
	) );

	$wp_customize->add_setting( 'et_divi[header_email]', array(
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'sanitize_email',
	) );

	$wp_customize->add_control( 'et_divi[header_email]', array(
		'label'		=> esc_html__( 'Email', 'Divi' ),
		'section'	=> 'et_divi_header_information',
		'type'      => 'text',
	) );

	$wp_customize->add_setting( 'et_divi[show_footer_social_icons]', array(
		'default'       => 'on',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[show_footer_social_icons]', array(
		'label'		=> esc_html__( 'Show Social Icons', 'Divi' ),
		'section'	=> 'et_divi_footer_elements',
		'type'      => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[footer_columns]', array(
		'default'       => '4',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_footer_column',
	) );

	$wp_customize->add_control( 'et_divi[footer_columns]', array(
		'label'		=> esc_html__( 'Column Layout', 'Divi' ),
		'section'	=> 'et_divi_footer_layout',
		'settings'	=> 'et_divi[footer_columns]',
		'type'		=> 'select',
		'choices'	=> et_divi_footer_column_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[footer_bg]', array(
		'default'		=> '#222222',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_bg]', array(
		'label'		=> esc_html__( 'Footer Background Color', 'Divi' ),
		'section'	=> 'et_divi_footer_layout',
		'settings'	=> 'et_divi[footer_bg]',
	) ) );

	$wp_customize->add_setting( 'et_divi[widget_header_font_size]', array(
		'default'       => absint( et_get_option( 'body_header_size', '30' ) ) * .6,
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[widget_header_font_size]', array(
		'label'	      => esc_html__( 'Header Text Size', 'Divi' ),
		'section'     => 'et_divi_footer_widgets',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 72,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[widget_header_font_style]', array(
		'default'       => et_get_option( 'widget_header_font_style', '' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[widget_header_font_style]', array(
		'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
		'section'     => 'et_divi_footer_widgets',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[widget_body_font_size]', array(
		'default'       => et_get_option( 'body_font_size', '14' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[widget_body_font_size]', array(
		'label'	      => esc_html__( 'Body/Link Text Size', 'Divi' ),
		'section'     => 'et_divi_footer_widgets',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[widget_body_line_height]', array(
		'default'       => '1.7',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_float_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[widget_body_line_height]', array(
		'label'	      => esc_html__( 'Body/Link Line Height', 'Divi' ),
		'section'     => 'et_divi_footer_widgets',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0.8,
			'max'  => 3,
			'step' => 0.1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[widget_body_font_style]', array(
		'default'       => et_get_option( 'footer_widget_body_font_style', '' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[widget_body_font_style]', array(
		'label'	      => esc_html__( 'Body Font Style', 'Divi' ),
		'section'     => 'et_divi_footer_widgets',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_widget_text_color]', array(
		'default'		=> '#fff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_widget_text_color]', array(
		'label'		=> esc_html__( 'Widget Text Color', 'Divi' ),
		'section'	=> 'et_divi_footer_widgets',
		'settings'	=> 'et_divi[footer_widget_text_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_widget_link_color]', array(
		'default'		=> '#fff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_widget_link_color]', array(
		'label'		=> esc_html__( 'Widget Link Color', 'Divi' ),
		'section'	=> 'et_divi_footer_widgets',
		'settings'	=> 'et_divi[footer_widget_link_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_widget_header_color]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_widget_header_color]', array(
		'label'		=> esc_html__( 'Widget Header Color', 'Divi' ),
		'section'	=> 'et_divi_footer_widgets',
		'settings'	=> 'et_divi[footer_widget_header_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_widget_bullet_color]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_widget_bullet_color]', array(
		'label'		=> esc_html__( 'Widget Bullet Color', 'Divi' ),
		'section'	=> 'et_divi_footer_widgets',
		'settings'	=> 'et_divi[footer_widget_bullet_color]',
	) ) );

	/* Footer Menu */
	$wp_customize->add_setting( 'et_divi[footer_menu_background_color]', array(
		'default'		=> 'rgba(255,255,255,0.05)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_menu_background_color]', array(
		'label'		=> esc_html__( 'Footer Menu Background Color', 'Divi' ),
		'section'	=> 'et_divi_footer_menu',
		'settings'	=> 'et_divi[footer_menu_background_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_menu_text_color]', array(
		'default'		=> '#bbbbbb',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_menu_text_color]', array(
		'label'		=> esc_html__( 'Footer Menu Text Color', 'Divi' ),
		'section'	=> 'et_divi_footer_menu',
		'settings'	=> 'et_divi[footer_menu_text_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_menu_active_link_color]', array(
		'default'		=> et_get_option( 'accent_color', '#2ea3f2' ),
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[footer_menu_active_link_color]', array(
		'label'		=> esc_html__( 'Footer Menu Active Link Color', 'Divi' ),
		'section'	=> 'et_divi_footer_menu',
		'settings'	=> 'et_divi[footer_menu_active_link_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_menu_letter_spacing]', array(
		'default'       => '0',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[footer_menu_letter_spacing]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_footer_menu',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 20,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_menu_font_style]', array(
		'default'       => et_get_option( 'footer_footer_menu_font_style', '' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[footer_menu_font_style]', array(
		'label'	      => esc_html__( 'Font Style', 'Divi' ),
		'section'     => 'et_divi_footer_menu',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[footer_menu_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[footer_menu_font_size]', array(
		'label'	      => esc_html__( 'Font Size', 'Divi' ),
		'section'     => 'et_divi_footer_menu',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1,
		),
	) ) );

	/* Bottom Bar */
	$wp_customize->add_setting( 'et_divi[bottom_bar_background_color]', array(
		'default'		=> 'rgba(0,0,0,0.32)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[bottom_bar_background_color]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_bottom_bar',
		'settings'	=> 'et_divi[bottom_bar_background_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[bottom_bar_text_color]', array(
		'default'		=> '#666666',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[bottom_bar_text_color]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_bottom_bar',
		'settings'	=> 'et_divi[bottom_bar_text_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[bottom_bar_font_style]', array(
		'default'       => et_get_option( 'footer_bottom_bar_font_style', '' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[bottom_bar_font_style]', array(
		'label'	      => esc_html__( 'Font Style', 'Divi' ),
		'section'     => 'et_divi_bottom_bar',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	$wp_customize->add_setting( 'et_divi[bottom_bar_font_size]', array(
		'default'       => '14',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[bottom_bar_font_size]', array(
		'label'	      => esc_html__( 'Font Size', 'Divi' ),
		'section'     => 'et_divi_bottom_bar',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[bottom_bar_social_icon_size]', array(
		'default'       => '24',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[bottom_bar_social_icon_size]', array(
		'label'	      => esc_html__( 'Social Icon Size', 'Divi' ),
		'section'     => 'et_divi_bottom_bar',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 10,
			'max'  => 32,
			'step' => 1,
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[bottom_bar_social_icon_color]', array(
		'default'		=> '#666666',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[bottom_bar_social_icon_color]', array(
		'label'		=> esc_html__( 'Social Icon Color', 'Divi' ),
		'section'	=> 'et_divi_bottom_bar',
		'settings'	=> 'et_divi[bottom_bar_social_icon_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[disable_custom_footer_credits]', array(
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'et_divi[disable_custom_footer_credits]', array(
		'label'   => esc_html__( 'Disable Footer Credits', 'Divi' ),
		'section' => 'et_divi_bottom_bar',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'et_divi[custom_footer_credits]', array(
		'default'           => '',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'et_sanitize_html_input_text',
	) );

	$wp_customize->add_control( 'et_divi[custom_footer_credits]', array(
		'label'    => esc_html__( 'Edit Footer Credits', 'Divi' ),
		'section'  => 'et_divi_bottom_bar',
		'settings' => 'et_divi[custom_footer_credits]',
		'type'     => 'textarea',
	) );

	$wp_customize->add_setting( 'et_divi[all_buttons_font_size]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_font_size', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_font_size]', array(
		'label'	      => esc_html__( 'Text Size', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 12,
			'max'  => 30,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_text_color]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_text_color]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'settings'	=> 'et_divi[all_buttons_text_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_bg_color]', array(
		'default'		=> 'rgba(0,0,0,0)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_bg_color]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'settings'	=> 'et_divi[all_buttons_bg_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_border_width]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_border_width', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_border_width]', array(
		'label'	      => esc_html__( 'Border Width', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_border_color]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_border_color]', array(
		'label'		=> esc_html__( 'Border Color', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'settings'	=> 'et_divi[all_buttons_border_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_border_radius]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_border_radius', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_border_radius]', array(
		'label'	      => esc_html__( 'Border Radius', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 50,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_spacing]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_spacing', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_spacing]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -2,
			'max'  => 10,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_font_style]', array(
		'default'       => '',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_style',
	) );

	$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[all_buttons_font_style]', array(
		'label'	      => esc_html__( 'Button Font Style', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'font_style',
		'choices'     => et_divi_font_style_choices(),
	) ) );

	if ( ! isset( $et_one_font_languages[$site_domain] ) ) {
		$wp_customize->add_setting( 'et_divi[all_buttons_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage',
			'sanitize_callback' => 'et_sanitize_font_choices',
		) );

		$wp_customize->add_control( new ET_Divi_Select_Option ( $wp_customize, 'et_divi[all_buttons_font]', array(
			'label'		=> esc_html__( 'Buttons Font', 'Divi' ),
			'section'	=> 'et_divi_buttons',
			'settings'	=> 'et_divi[all_buttons_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) ) );
	}

	$wp_customize->add_setting( 'et_divi[all_buttons_icon]', array(
		'default'       => 'yes',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'sanitize_callback' => 'et_sanitize_yes_no',
	) );

	$wp_customize->add_control( 'et_divi[all_buttons_icon]', array(
		'label'		=> esc_html__( 'Add Button Icon', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'type'      => 'select',
		'choices'	=> et_divi_yes_no_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[all_buttons_selected_icon]', array(
		'default'       => '5',
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_font_icon',
	) );

	$wp_customize->add_control( new ET_Divi_Icon_Picker_Option ( $wp_customize, 'et_divi[all_buttons_selected_icon]', array(
		'label'	      => esc_html__( 'Select Icon', 'Divi' ),
		'section'     => 'et_divi_buttons',
		'type'        => 'icon_picker',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_icon_color]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_icon_color]', array(
		'label'		=> esc_html__( 'Icon Color', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'settings'	=> 'et_divi[all_buttons_icon_color]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_icon_placement]', array(
		'default'       => 'right',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_left_right',
	) );

	$wp_customize->add_control( 'et_divi[all_buttons_icon_placement]', array(
		'label'		=> esc_html__( 'Icon Placement', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'type'      => 'select',
		'choices'	=> et_divi_left_right_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[all_buttons_icon_hover]', array(
		'default'       => 'yes',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_yes_no',
	) );

	$wp_customize->add_control( 'et_divi[all_buttons_icon_hover]', array(
		'label'		=> esc_html__( 'Only Show Icon on Hover', 'Divi' ),
		'section'	=> 'et_divi_buttons',
		'type'      => 'select',
		'choices'	=> et_divi_yes_no_choices(),
	) );

	$wp_customize->add_setting( 'et_divi[all_buttons_text_color_hover]', array(
		'default'		=> '#ffffff',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_text_color_hover]', array(
		'label'		=> esc_html__( 'Text Color', 'Divi' ),
		'section'	=> 'et_divi_buttons_hover',
		'settings'	=> 'et_divi[all_buttons_text_color_hover]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_bg_color_hover]', array(
		'default'		=> 'rgba(255,255,255,0.2)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_bg_color_hover]', array(
		'label'		=> esc_html__( 'Background Color', 'Divi' ),
		'section'	=> 'et_divi_buttons_hover',
		'settings'	=> 'et_divi[all_buttons_bg_color_hover]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_border_color_hover]', array(
		'default'		=> 'rgba(0,0,0,0)',
		'type'			=> 'option',
		'capability'	=> 'edit_theme_options',
		'transport'		=> 'postMessage',
		'sanitize_callback' => 'et_sanitize_alpha_color',
	) );

	$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[all_buttons_border_color_hover]', array(
		'label'		=> esc_html__( 'Border Color', 'Divi' ),
		'section'	=> 'et_divi_buttons_hover',
		'settings'	=> 'et_divi[all_buttons_border_color_hover]',
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_border_radius_hover]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_border_radius_hover', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'absint'
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_border_radius_hover]', array(
		'label'	      => esc_html__( 'Border Radius', 'Divi' ),
		'section'     => 'et_divi_buttons_hover',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 50,
			'step' => 1
		),
	) ) );

	$wp_customize->add_setting( 'et_divi[all_buttons_spacing_hover]', array(
		'default'       => ET_Global_Settings::get_value( 'all_buttons_spacing_hover', 'default' ),
		'type'          => 'option',
		'capability'    => 'edit_theme_options',
		'transport'     => 'postMessage',
		'sanitize_callback' => 'et_sanitize_int_number',
	) );

	$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[all_buttons_spacing_hover]', array(
		'label'	      => esc_html__( 'Letter Spacing', 'Divi' ),
		'section'     => 'et_divi_buttons_hover',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => -2,
			'max'  => 10,
			'step' => 1
		),
	) ) );
}
endif;

if ( ! function_exists( 'et_divi_customizer_module_settings' ) ) :
/**
 * @param \WP_Customize_Manager $wp_customize
 */
function et_divi_customizer_module_settings( $wp_customize ) {
		/* Section: Image */
		$wp_customize->add_section( 'et_pagebuilder_image', array(
			'priority'       => 10,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Image', 'Divi' ),
			'description'    => esc_html__( 'Image Module Settings', 'Divi' ),
		) );

			$wp_customize->add_setting( 'et_divi[et_pb_image-animation]', array(
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_image_animation',
			) );

			$wp_customize->add_control( 'et_divi[et_pb_image-animation]', array(
				'label'		=> esc_html__( 'Animation', 'Divi' ),
				'description' => esc_html__( 'This controls default direction of the lazy-loading animation.', 'Divi' ),
				'section'	=> 'et_pagebuilder_image',
				'type'      => 'select',
				'choices'	=> et_divi_image_animation_choices(),
			) );

		/* Section: Gallery */
		$wp_customize->add_section( 'et_pagebuilder_gallery', array(
			'priority'       => 20,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Gallery', 'Divi' ),
		) );

			// Zoom Icon Color
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-zoom_icon_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_gallery-zoom_icon_color', 'default' ), // default color should be theme's accent color
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_gallery-zoom_icon_color]', array(
				'label'		=> esc_html__( 'Zoom Icon Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_gallery',
				'settings'	=> 'et_divi[et_pb_gallery-zoom_icon_color]',
			) ) );

			// Hover Overlay Color
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-hover_overlay_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_gallery-hover_overlay_color', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_gallery-hover_overlay_color]', array(
				'label'		=> esc_html__( 'Hover Overlay Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_gallery',
				'settings'	=> 'et_divi[et_pb_gallery-hover_overlay_color]',
			) ) );

			// Title Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_gallery-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_gallery-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_gallery',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_gallery-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_gallery-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_gallery',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// caption font size Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-caption_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_gallery-caption_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_gallery-caption_font_size]', array(
				'label'	      => esc_html__( 'Caption Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_gallery',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// caption font style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_gallery-caption_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_gallery-caption_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_gallery-caption_font_style]', array(
				'label'	      => esc_html__( 'Caption Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_gallery',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Blurb */
		$wp_customize->add_section( 'et_pagebuilder_blurb', array(
			'priority'       => 30,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Blurb', 'Divi' ),
		) );

			// Header Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_blurb-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blurb-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_blurb-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_blurb',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

		/* Section: Tabs */
		$wp_customize->add_section( 'et_pagebuilder_tabs', array(
			'priority'       => 40,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Tabs', 'Divi' ),
		) );

			// Tab Title Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_tabs-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_tabs-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_tabs-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_tabs',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Tab Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_tabs-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_tabs-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_tabs-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_tabs',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Padding: Range 0 - 50px
			/* If padding is 20px then the content padding is 20px and the tab padding is: { padding: 10px(50%) 20px; }	*/
			$wp_customize->add_setting( 'et_divi[et_pb_tabs-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_tabs-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_tabs-padding]', array(
				'label'	      => esc_html__( 'Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_tabs',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

		/* Section: Slider */
		$wp_customize->add_section( 'et_pagebuilder_slider', array(
			'priority'       => 50,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Slider', 'Divi' ),
			// 'description'    => '',
		) );

			// Slider Padding: Top/Bottom Only
			$wp_customize->add_setting( 'et_divi[et_pb_slider-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_slider-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_slider-padding]', array(
				'label'	      => esc_html__( 'Top & Bottom Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 5,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

			// Header Font size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_slider-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_slider-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_slider-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_slider-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_slider-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_slider-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_slider',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Content Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_slider-body_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_slider-body_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_slider-body_font_size]', array(
				'label'	      => esc_html__( 'Content Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Content Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_slider-body_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_slider-body_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_slider-body_font_style]', array(
				'label'	      => esc_html__( 'Content Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_slider',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Testimonial */
		$wp_customize->add_section( 'et_pagebuilder_testimonial', array(
			'priority'       => 60,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Testimonial', 'Divi' ),
		) );

			// Author Name Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_testimonial-author_name_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_testimonial-author_name_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_testimonial-author_name_font_style]', array(
				'label'	      => esc_html__( 'Name Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_testimonial',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Author Details Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_testimonial-author_details_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_testimonial-author_details_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_testimonial-author_details_font_style]', array(
				'label'	      => esc_html__( 'Details Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_testimonial',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Portrait Border Radius
			$wp_customize->add_setting( 'et_divi[et_pb_testimonial-portrait_border_radius]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_testimonial-portrait_border_radius', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_testimonial-portrait_border_radius]', array(
				'label'	      => esc_html__( 'Portrait Border Radius', 'Divi' ),
				'section'     => 'et_pagebuilder_testimonial',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				),
			) ) );

			// Portrait Width
			$wp_customize->add_setting( 'et_divi[et_pb_testimonial-portrait_width]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_testimonial-portrait_width', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_testimonial-portrait_width]', array(
				'label'	      => esc_html__( 'Image Width', 'Divi' ),
				'section'     => 'et_pagebuilder_testimonial',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
				),
			) ) );

			// Portrait Height
			$wp_customize->add_setting( 'et_divi[et_pb_testimonial-portrait_height]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_testimonial-portrait_height', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_testimonial-portrait_height]', array(
				'label'	      => esc_html__( 'Image Height', 'Divi' ),
				'section'     => 'et_pagebuilder_testimonial',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
				),
			) ) );

		/* Section: Pricing Table */
		$wp_customize->add_section( 'et_pagebuilder_pricing_table', array(
			'priority'       => 70,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Pricing Table', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Subhead Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-subheader_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-subheader_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-subheader_font_size]', array(
				'label'	      => esc_html__( 'Subheader Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Subhead Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-subheader_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-subheader_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-subheader_font_style]', array(
				'label'	      => esc_html__( 'Subheader Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Price font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-price_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-price_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-price_font_size]', array(
				'label'	      => esc_html__( 'Price Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 100,
					'step' => 1,
				),
			) ) );

			// Price font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_pricing_tables-price_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_pricing_tables-price_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_pricing_tables-price_font_style]', array(
				'label'	      => esc_html__( 'Pricing Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_pricing_table',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Call To Action */
		$wp_customize->add_section( 'et_pagebuilder_call_to_action', array(
			'priority'       => 80,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Call To Action', 'Divi' ),
		) );

			// Header font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_cta-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_cta-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_cta-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_call_to_action',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_cta-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_cta-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_cta-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_call_to_action',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Padding: Range 0px - 200px
			$wp_customize->add_setting( 'et_divi[et_pb_cta-custom_padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_cta-custom_padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_cta-custom_padding]', array(
				'label'	      => esc_html__( 'Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_call_to_action',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
				),
			) ) );

		/* Section: Audio */
		$wp_customize->add_section( 'et_pagebuilder_audio', array(
			'priority'       => 90,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Audio', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_audio-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_audio-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_audio-title_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_audio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_audio-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_audio-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_audio-title_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_audio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Subhead Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_audio-caption_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_audio-caption_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_audio-caption_font_size]', array(
				'label'	      => esc_html__( 'Subheader Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_audio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Subhead Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_audio-caption_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_audio-caption_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_audio-caption_font_style]', array(
				'label'	      => esc_html__( 'Subheader Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_audio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Email Optin */
		$wp_customize->add_section( 'et_pagebuilder_subscribe', array(
			'priority'       => 100,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Email Optin', 'Divi' ),
		) );

			// Header font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_signup-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_signup-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_signup-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_subscribe',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_signup-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_signup-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_signup-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_subscribe',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Padding: Range 0px - 200px
			$wp_customize->add_setting( 'et_divi[et_pb_signup-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_signup-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_signup-padding]', array(
				'label'	      => esc_html__( 'Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_subscribe',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
				),
			) ) );

		/* Section: Login */
		$wp_customize->add_section( 'et_pagebuilder_login', array(
			'priority'       => 110,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Login', 'Divi' ),
			// 'description'    => '',
		) );

			// Header font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_login-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_login-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_login-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_login',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_login-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_login-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_login-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_login',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Padding: Range 0px - 200px
			$wp_customize->add_setting( 'et_divi[et_pb_login-custom_padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_login-custom_padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_login-custom_padding]', array(
				'label'	      => esc_html__( 'Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_login',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 200,
					'step' => 1,
				),
			) ) );

		/* Section: Portfolio */
		$wp_customize->add_section( 'et_pagebuilder_portfolio', array(
			'priority'       => 120,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Portfolio', 'Divi' ),
		) );

			// Zoom Icon Color
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-zoom_icon_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_portfolio-zoom_icon_color', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_portfolio-zoom_icon_color]', array(
				'label'		=> esc_html__( 'Zoom Icon Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_portfolio',
				'settings'	=> 'et_divi[et_pb_portfolio-zoom_icon_color]',
			) ) );

			// Hover Overlay Color
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-hover_overlay_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_portfolio-hover_overlay_color', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_portfolio-hover_overlay_color]', array(
				'label'		=> esc_html__( 'Hover Overlay Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_portfolio',
				'settings'	=> 'et_divi[et_pb_portfolio-hover_overlay_color]',
			) ) );

			// Title Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_portfolio-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_portfolio-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_portfolio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_portfolio-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_portfolio-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_portfolio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Category font size Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-caption_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_portfolio-caption_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_portfolio-caption_font_size]', array(
				'label'	      => esc_html__( 'Caption Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_portfolio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Category Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_portfolio-caption_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_portfolio-caption_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_portfolio-caption_font_style]', array(
				'label'	      => esc_html__( 'Caption Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_portfolio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Filterable Portfolio */
		$wp_customize->add_section( 'et_pagebuilder_filterable_portfolio', array(
			'priority'       => 130,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Filterable Portfolio', 'Divi' ),
		) );

			// Zoom Icon Color
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-zoom_icon_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-zoom_icon_color', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_filterable_portfolio-zoom_icon_color]', array(
				'label'		=> esc_html__( 'Zoom Icon Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_filterable_portfolio',
				'settings'	=> 'et_divi[et_pb_filterable_portfolio-zoom_icon_color]',
			) ) );

			// Hover Overlay Color
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-hover_overlay_color]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-hover_overlay_color', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_alpha_color',
			) );

			$wp_customize->add_control( new ET_Divi_Customize_Color_Alpha_Control( $wp_customize, 'et_divi[et_pb_filterable_portfolio-hover_overlay_color]', array(
				'label'		=> esc_html__( 'Hover Overlay Color', 'Divi' ),
				'section'	=> 'et_pagebuilder_filterable_portfolio',
				'settings'	=> 'et_divi[et_pb_filterable_portfolio-hover_overlay_color]',
			) ) );

			// Title Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Category font size Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-caption_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-caption_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-caption_font_size]', array(
				'label'	      => esc_html__( 'Caption Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Category Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-caption_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-caption_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-caption_font_style]', array(
				'label'	      => esc_html__( 'Caption Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Filters Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-filter_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-filter_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-filter_font_size]', array(
				'label'	      => esc_html__( 'Filters Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Filters Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_filterable_portfolio-filter_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_filterable_portfolio-filter_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_filterable_portfolio-filter_font_style]', array(
				'label'	      => esc_html__( 'Filters Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_filterable_portfolio',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Bar Counter */
		$wp_customize->add_section( 'et_pagebuilder_bar_counter', array(
			'priority'       => 140,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Bar Counter', 'Divi' ),
		) );

			// Label Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_counters-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_counters-title_font_size]', array(
				'label'	      => esc_html__( 'Label Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Labels Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_counters-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_counters-title_font_style]', array(
				'label'	      => esc_html__( 'Label Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Percent Font Size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_counters-percent_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-percent_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_counters-percent_font_size]', array(
				'label'	      => esc_html__( 'Percent Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Percent Font Style: : B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_counters-percent_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-percent_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_counters-percent_font_style]', array(
				'label'	      => esc_html__( 'Percent Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Bar Padding: Range 0px - 30px (top and bottom padding only)
			$wp_customize->add_setting( 'et_divi[et_pb_counters-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_counters-padding]', array(
				'label'	      => esc_html__( 'Bar Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

			// Bar Border Radius
			$wp_customize->add_setting( 'et_divi[et_pb_counters-border_radius]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_counters-border_radius', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_counters-border_radius]', array(
				'label'	      => esc_html__( 'Bar Border Radius', 'Divi' ),
				'section'     => 'et_pagebuilder_bar_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 80,
					'step' => 1,
				),
			) ) );

		/* Section: Circle Counter */
		$wp_customize->add_section( 'et_pagebuilder_circle_counter', array(
			'priority'       => 150,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Circle Counter', 'Divi' ),
		) );
			// Number Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_circle_counter-number_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_circle_counter-number_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_circle_counter-number_font_size]', array(
				'label'	      => esc_html__( 'Number Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_circle_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Number Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_circle_counter-number_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_circle_counter-number_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_circle_counter-number_font_style]', array(
				'label'	      => esc_html__( 'Number Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_circle_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Title Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_circle_counter-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_circle_counter-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_circle_counter-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_circle_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_circle_counter-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_circle_counter-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_circle_counter-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_circle_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Number Counter */
		$wp_customize->add_section( 'et_pagebuilder_number_counter', array(
			'priority'       => 160,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Number Counter', 'Divi' ),
		) );

			// Number Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_number_counter-number_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_number_counter-number_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_number_counter-number_font_size]', array(
				'label'	      => esc_html__( 'Number Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_number_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Number Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_number_counter-number_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_number_counter-number_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_number_counter-number_font_style]', array(
				'label'	      => esc_html__( 'Number Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_number_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Title Font Size: Range 10px - 72px
			$wp_customize->add_setting( 'et_divi[et_pb_number_counter-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_number_counter-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_number_counter-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_number_counter',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_number_counter-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_number_counter-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_number_counter-title_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_number_counter',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Accordion */
		$wp_customize->add_section( 'et_pagebuilder_accordion', array(
			'priority'       => 170,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Accordion', 'Divi' ),
		) );
			// Title Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_accordion-toggle_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_accordion-toggle_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_accordion-toggle_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_accordion',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Accordion Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_accordion-toggle_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_accordion-toggle_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_accordion-toggle_font_style]', array(
				'label'	      => esc_html__( 'Opened Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_accordion',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Inactive Accordion Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_accordion-inactive_toggle_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_accordion-inactive_title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_accordion-inactive_toggle_font_style]', array(
				'label'	      => esc_html__( 'Closed Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_accordion',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Toggle Accordion Icon Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_accordion-toggle_icon_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_accordion-toggle_icon_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_accordion-toggle_icon_size]', array(
				'label'	      => esc_html__( 'Toggle Icon Size', 'Divi' ),
				'section'     => 'et_pagebuilder_accordion',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 16,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Padding: Range 0 - 50px
			/* Padding effects each individual Accordion */
			$wp_customize->add_setting( 'et_divi[et_pb_accordion-custom_padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_accordion-custom_padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_accordion-custom_padding]', array(
				'label'	      => esc_html__( 'Toggle Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_accordion',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

		/* Section: Toggle */
		$wp_customize->add_section( 'et_pagebuilder_toggle', array(
			'priority'       => 180,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Toggle', 'Divi' ),
		) );

			// Title Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_toggle-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_toggle-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_toggle-title_font_size]', array(
				'label'	      => esc_html__( 'Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_toggle',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Toggle Title Font Style
			$wp_customize->add_setting( 'et_divi[et_pb_toggle-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_toggle-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_toggle-title_font_style]', array(
				'label'	      => esc_html__( 'Opened Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_toggle',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Inactive Toggle Title Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_toggle-inactive_title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_toggle-inactive_title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_toggle-inactive_title_font_style]', array(
				'label'	      => esc_html__( 'Closed Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_toggle',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Open& Close Icon Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_toggle-toggle_icon_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_toggle-toggle_icon_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_toggle-toggle_icon_size]', array(
				'label'	      => esc_html__( 'Toggle Icon Size', 'Divi' ),
				'section'     => 'et_pagebuilder_toggle',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 16,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Padding: Range 0 - 50px
			$wp_customize->add_setting( 'et_divi[et_pb_toggle-custom_padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_toggle-custom_padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_toggle-custom_padding]', array(
				'label'	      => esc_html__( 'Toggle Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_toggle',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

		/* Section: Contact Form */
		$wp_customize->add_section( 'et_pagebuilder_contact_form', array(
			'priority'       => 190,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Contact Form', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_contact_form-title_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_contact_form-title_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Input Field Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-form_field_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-form_field_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_contact_form-form_field_font_size]', array(
				'label'	      => esc_html__( 'Input Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Input Field Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-form_field_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-form_field_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_contact_form-form_field_font_style]', array(
				'label'	      => esc_html__( 'Input Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Input Field Padding: Range 0 - 50px
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_contact_form-padding]', array(
				'label'	      => esc_html__( 'Input Field Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

			// Captcha Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-captcha_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-captcha_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_contact_form-captcha_font_size]', array(
				'label'	      => esc_html__( 'Captcha Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Captcha Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_contact_form-captcha_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_contact_form-captcha_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_contact_form-captcha_font_style]', array(
				'label'	      => esc_html__( 'Captcha Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_contact_form',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Sidebar */
		$wp_customize->add_section( 'et_pagebuilder_sidebar', array(
			'priority'       => 200,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Sidebar', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_sidebar-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_sidebar-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_sidebar-header_font_size]', array(
				'label'	      => esc_html__( 'Widget Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_sidebar',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header font style
			$wp_customize->add_setting( 'et_divi[et_pb_sidebar-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_sidebar-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_sidebar-header_font_style]', array(
				'label'	      => esc_html__( 'Widget Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_sidebar',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Show/hide Vertical Divider
			$wp_customize->add_setting( 'et_divi[et_pb_sidebar-remove_border]', array(
				'default'		=> ET_Global_Settings::get_checkbox_value( 'et_pb_sidebar-remove_border', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'wp_validate_boolean',
			) );

			$wp_customize->add_control( 'et_divi[et_pb_sidebar-remove_border]', array(
				'label'		=> esc_html__( 'Remove Vertical Divider', 'Divi' ),
				'section'	=> 'et_pagebuilder_sidebar',
				'type'      => 'checkbox',
			) );

		/* Section: Divider */
		$wp_customize->add_section( 'et_pagebuilder_divider', array(
			'priority'       => 200,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Divider', 'Divi' ),
		) );

			// Show/hide Divider
			$wp_customize->add_setting( 'et_divi[et_pb_divider-show_divider]', array(
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'wp_validate_boolean',
			) );

			$wp_customize->add_control( 'et_divi[et_pb_divider-show_divider]', array(
				'label'		=> esc_html__( 'Show Divider', 'Divi' ),
				'section'	=> 'et_pagebuilder_divider',
				'type'      => 'checkbox',
			) );

			// Divider Style
			$wp_customize->add_setting( 'et_divi[et_pb_divider-divider_style]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_divider-divider_style', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_divider_style',
			) );

			$wp_customize->add_control( 'et_divi[et_pb_divider-divider_style]', array(
				'label'		=> esc_html__( 'Divider Style', 'Divi' ),
				'section'	=> 'et_pagebuilder_divider',
				'settings'	=> 'et_divi[et_pb_divider-divider_style]',
				'type'		=> 'select',
				'choices'	=> et_divi_divider_style_choices(),
			) );

			// Divider Weight
			$wp_customize->add_setting( 'et_divi[et_pb_divider-divider_weight]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_divider-divider_weight', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_divider-divider_weight]', array(
				'label'	      => esc_html__( 'Divider Weight', 'Divi' ),
				'section'     => 'et_pagebuilder_divider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				),
			) ) );

			// Divider Height
			$wp_customize->add_setting( 'et_divi[et_pb_divider-height]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_divider-height', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_divider-height]', array(
				'label'	      => esc_html__( 'Divider Height', 'Divi' ),
				'section'     => 'et_pagebuilder_divider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				),
			) ) );

			// Divider Position
			$wp_customize->add_setting( 'et_divi[et_pb_divider-divider_position]', array(
				'default'		=> ET_Global_Settings::get_value( 'et_pb_divider-divider_position', 'default' ),
				'type'			=> 'option',
				'capability'	=> 'edit_theme_options',
				'transport'		=> 'postMessage',
				'sanitize_callback' => 'et_sanitize_divider_position',
			) );

			$wp_customize->add_control( 'et_divi[et_pb_divider-divider_position]', array(
				'label'		=> esc_html__( 'Divider Position', 'Divi' ),
				'section'	=> 'et_pagebuilder_divider',
				'settings'	=> 'et_divi[et_pb_divider-divider_position]',
				'type'		=> 'select',
				'choices'	=> et_divi_divider_position_choices(),
			) );

		/* Section: Person */
		$wp_customize->add_section( 'et_pagebuilder_person', array(
			'priority'       => 210,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Person', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_team_member-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_team_member-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_team_member-header_font_size]', array(
				'label'	      => esc_html__( 'Name Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_person',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header font style
			$wp_customize->add_setting( 'et_divi[et_pb_team_member-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_team_member-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_team_member-header_font_style]', array(
				'label'	      => esc_html__( 'Name Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_person',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Subhead Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_team_member-subheader_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_team_member-subheader_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_team_member-subheader_font_size]', array(
				'label'	      => esc_html__( 'Subheader Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_person',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Subhead Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_team_member-subheader_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_team_member-subheader_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_team_member-subheader_font_style]', array(
				'label'	      => esc_html__( 'Subheader Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_person',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Network Icons size: Range 16px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_team_member-social_network_icon_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_team_member-social_network_icon_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_team_member-social_network_icon_size]', array(
				'label'	      => esc_html__( 'Social Network Icon Size', 'Divi' ),
				'section'     => 'et_pagebuilder_person',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 16,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

		/* Section: Blog */
		$wp_customize->add_section( 'et_pagebuilder_blog', array(
			'priority'       => 220,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Blog', 'Divi' ),
		) );

			// Post Title Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_blog-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_blog-header_font_size]', array(
				'label'	      => esc_html__( 'Post Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_blog',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Post Title Font Style
			$wp_customize->add_setting( 'et_divi[et_pb_blog-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_blog-header_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_blog',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Meta Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_blog-meta_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog-meta_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_blog-meta_font_size]', array(
				'label'	      => esc_html__( 'Meta Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_blog',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Meta Field Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_blog-meta_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog-meta_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_blog-meta_font_style]', array(
				'label'	      => esc_html__( 'Meta Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_blog',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Blog Grid */
		$wp_customize->add_section( 'et_pagebuilder_masonry_blog', array(
			'priority'       => 230,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Blog Grid', 'Divi' ),
		) );

			// Post Title Font Size
			$wp_customize->add_setting( 'et_divi[et_pb_blog_masonry-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog_masonry-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_blog_masonry-header_font_size]', array(
				'label'	      => esc_html__( 'Post Title Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_masonry_blog',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Post Title Font Style
			$wp_customize->add_setting( 'et_divi[et_pb_blog_masonry-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog_masonry-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_blog_masonry-header_font_style]', array(
				'label'	      => esc_html__( 'Title Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_masonry_blog',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Meta Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_blog_masonry-meta_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog_masonry-meta_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_blog_masonry-meta_font_size]', array(
				'label'	      => esc_html__( 'Meta Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_masonry_blog',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Meta Field Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_blog_masonry-meta_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_blog_masonry-meta_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_blog_masonry-meta_font_style]', array(
				'label'	      => esc_html__( 'Meta Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_masonry_blog',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Shop */
		$wp_customize->add_section( 'et_pagebuilder_shop', array(
			'priority'       => 240,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Shop', 'Divi' ),
		) );

			// Product Name Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_shop-title_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-title_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_shop-title_font_size]', array(
				'label'	      => esc_html__( 'Product Name Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Product Name Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_shop-title_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-title_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_shop-title_font_style]', array(
				'label'	      => esc_html__( 'Product Name Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Sale Badge Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_shop-sale_badge_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-sale_badge_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_shop-sale_badge_font_size]', array(
				'label'	      => esc_html__( 'Sale Badge Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Sale Badge Font Style:  B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_shop-sale_badge_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-sale_badge_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_shop-sale_badge_font_style]', array(
				'label'	      => esc_html__( 'Sale Badge Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Price Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_shop-price_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-price_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_shop-price_font_size]', array(
				'label'	      => esc_html__( 'Price Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Price Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_shop-price_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-price_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_shop-price_font_style]', array(
				'label'	      => esc_html__( 'Price Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Sale Price Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_shop-sale_price_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-sale_price_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_shop-sale_price_font_size]', array(
				'label'	      => esc_html__( 'Sale Price Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Sale Price Font Style: B / I / TT / U/
			$wp_customize->add_setting( 'et_divi[et_pb_shop-sale_price_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_shop-sale_price_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_shop-sale_price_font_style]', array(
				'label'	      => esc_html__( 'Sale Price Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_shop',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Countdown */
		$wp_customize->add_section( 'et_pagebuilder_countdown', array(
			'priority'       => 250,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Countdown', 'Divi' ),
		) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_countdown_timer-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_countdown_timer-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_countdown_timer-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_countdown',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_countdown_timer-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_countdown_timer-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_countdown_timer-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_countdown',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Social Follow */
		$wp_customize->add_section( 'et_pagebuilder_social_follow', array(
			'priority'       => 250,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Social Follow', 'Divi' ),
		) );

			// Follow Button Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_social_media_follow-icon_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_social_media_follow-icon_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_social_media_follow-icon_size]', array(
				'label'	      => esc_html__( 'Follow Font & Icon Size', 'Divi' ),
				'section'     => 'et_pagebuilder_social_follow',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Follow Button Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_social_media_follow-button_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_social_media_follow-button_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_social_media_follow-button_font_style]', array(
				'label'	      => esc_html__( 'Button Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_social_follow',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

		/* Section: Fullwidth Slider */
		$wp_customize->add_section( 'et_pagebuilder_fullwidth_slider', array(
			'priority'       => 270,
			'capability'     => 'edit_theme_options',
			'title'          => esc_html__( 'Fullwidth Slider', 'Divi' ),
		) );

			// Slider Padding: Top/Bottom Only
			$wp_customize->add_setting( 'et_divi[et_pb_fullwidth_slider-padding]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_fullwidth_slider-padding', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_fullwidth_slider-padding]', array(
				'label'	      => esc_html__( 'Top & Bottom Padding', 'Divi' ),
				'section'     => 'et_pagebuilder_fullwidth_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 5,
					'max'  => 50,
					'step' => 1,
				),
			) ) );

			// Header Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_fullwidth_slider-header_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_fullwidth_slider-header_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_fullwidth_slider-header_font_size]', array(
				'label'	      => esc_html__( 'Header Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_fullwidth_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 72,
					'step' => 1,
				),
			) ) );

			// Header Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_fullwidth_slider-header_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_fullwidth_slider-header_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_fullwidth_slider-header_font_style]', array(
				'label'	      => esc_html__( 'Header Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_fullwidth_slider',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );

			// Content Font size: Range 10px - 32px
			$wp_customize->add_setting( 'et_divi[et_pb_fullwidth_slider-body_font_size]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_fullwidth_slider-body_font_size', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( new ET_Divi_Range_Option ( $wp_customize, 'et_divi[et_pb_fullwidth_slider-body_font_size]', array(
				'label'	      => esc_html__( 'Content Font Size', 'Divi' ),
				'section'     => 'et_pagebuilder_fullwidth_slider',
				'type'        => 'range',
				'input_attrs' => array(
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				),
			) ) );

			// Content Font Style: B / I / TT / U
			$wp_customize->add_setting( 'et_divi[et_pb_fullwidth_slider-body_font_style]', array(
				'default'       => ET_Global_Settings::get_value( 'et_pb_fullwidth_slider-body_font_style', 'default' ),
				'type'          => 'option',
				'capability'    => 'edit_theme_options',
				'transport'     => 'postMessage',
				'sanitize_callback' => 'et_sanitize_font_style',
			) );

			$wp_customize->add_control( new ET_Divi_Font_Style_Option ( $wp_customize, 'et_divi[et_pb_fullwidth_slider-body_font_style]', array(
				'label'	      => esc_html__( 'Content Font Style', 'Divi' ),
				'section'     => 'et_pagebuilder_fullwidth_slider',
				'type'        => 'font_style',
				'choices'     => et_divi_font_style_choices(),
			) ) );
}
endif;

/**
 * Add action hook to the footer in customizer preview.
 */
function et_customizer_preview_footer_action() {
	if ( is_customize_preview() ) {
		do_action( 'et_customizer_footer_preview' );
	}
}
add_action( 'wp_footer', 'et_customizer_preview_footer_action' );

/**
 * Add container with social icons to the footer in customizer preview.
 * Used to get the icons and append them into the header when user enables the header social icons in customizer.
 */
function et_load_social_icons() {
	$post_id = is_singular() ? get_the_ID() : 0;
	$is_custom_post_type = et_builder_post_is_of_custom_post_type( $post_id );

	echo '<div class="et_customizer_social_icons" style="display:none;">';
		get_template_part( 'includes/social_icons', 'header' );
	echo '</div>';
	?>
	<script type="text/javascript">
      ( function( $ ) {
        var isCustomPostType = <?php echo json_encode( $is_custom_post_type ); ?>;

        $(document).ready(function() {
          $(document).trigger('et-customizer-preview-load', {
            isCustomPostType: isCustomPostType,
            selectorWrapper: '.et-db #et-boc'
    	  });
        });
      }( jQuery ) );
	</script>
	<?php
}
add_action( 'et_customizer_footer_preview', 'et_load_social_icons' );

function et_divi_customize_preview_js() {
	$theme_version = et_get_theme_version();
	wp_enqueue_script( 'divi-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), $theme_version, true );
	wp_localize_script( 'divi-customizer', 'et_main_customizer_data', array(
		'original_footer_credits' => et_get_original_footer_credits(),
	) );
}
add_action( 'customize_preview_init', 'et_divi_customize_preview_js' );

function et_divi_customize_preview_css() {
	$theme_version = et_get_theme_version();

	wp_enqueue_style( 'divi-customizer-controls-styles', get_template_directory_uri() . '/css/theme-customizer-controls-styles.css', array(), $theme_version );
	wp_enqueue_script( 'divi-customizer-controls-js', get_template_directory_uri() . '/js/theme-customizer-controls.js', array( 'jquery' ), $theme_version, true );
	wp_localize_script( 'divi-customizer-controls-js', 'et_divi_customizer_data', array(
		'is_old_wp' => et_pb_is_wp_old_version() ? 'old' : 'new',
		'color_palette' => implode( '|', et_pb_get_default_color_palette() ),
	) );
}
add_action( 'customize_controls_enqueue_scripts', 'et_divi_customize_preview_css' );

/**
 * Modifying builder options based on saved Divi values
 * @param array  current builder options values
 * @return array modified builder options values
 */
function et_divi_builder_options( $options ) {
	$options['all_buttons_icon'] = et_get_option( 'all_buttons_icon', 'yes' );

	return $options;
}
add_filter( 'et_builder_options', 'et_divi_builder_options' );

/**
 * Add custom customizer control
 * Check for WP_Customizer_Control existence before adding custom control because WP_Customize_Control is loaded on customizer page only
 *
 * @see _wp_customize_include()
 */
if ( class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Font style control for Customizer
	 */
	class ET_Divi_Font_Style_Option extends WP_Customize_Control {
		public $type = 'font_style';
		public function render_content() {
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
			</label>
			<?php $current_values = explode('|', $this->value() );
			if ( empty( $this->choices ) )
				return;
			foreach ( $this->choices as $value => $label ) :
				$checked_class = in_array( $value, $current_values ) ? ' et_font_style_checked' : '';
				?>
					<span class="et_font_style et_font_value_<?php echo esc_attr( $value ); echo $checked_class; ?>">
						<input type="checkbox" class="et_font_style_checkbox" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $current_values ) ); ?> />
					</span>
				<?php
			endforeach;
			?>
			<input type="hidden" class="et_font_styles" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
			<?php
		}
	}

	/**
	 * Icon picker control for Customizer
	 */
	class ET_Divi_Icon_Picker_Option extends WP_Customize_Control {
		public $type = 'icon_picker';

		public function render_content() {

		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			et_pb_font_icon_list(); ?>
			<input type="hidden" class="et_selected_icon" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		</label>
		<?php
		}
	}

	/**
	 * Range-based sliding value picker for Customizer
	 */
	class ET_Divi_Range_Option extends WP_Customize_Control {
		public $type = 'range';

		public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> data-reset_value="<?php echo esc_attr( $this->setting->default ); ?>" />
			<input type="number" <?php $this->input_attrs(); ?> class="et-pb-range-input" value="<?php echo esc_attr( $this->value() ); ?>" />
			<span class="et_divi_reset_slider"></span>
		</label>
		<?php
		}
	}

	/**
	 * Custom Select option which supports data attributes for the <option> tags
	 */
	class ET_Divi_Select_Option extends WP_Customize_Control {
		public $type = 'select';

		public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>

			<select <?php $this->link(); ?>>
				<?php
				foreach ( $this->choices as $value => $attributes ) {
					$data_output = '';

					if ( ! empty( $attributes['data'] ) ) {
						foreach( $attributes['data'] as $data_name => $data_value ) {
							if ( '' !== $data_value ) {
								$data_output .= sprintf( ' data-%1$s="%2$s"',
									esc_attr( $data_name ),
									esc_attr( $data_value )
								);
							}
						}
					}

					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . $data_output . '>' . esc_html( $attributes['label'] ) . '</option>';
				}
				?>
			</select>
		</label>
		<?php
		}
	}

	/**
	 * Color picker with alpha color support for Customizer
	 */
	class ET_Divi_Customize_Color_Alpha_Control extends WP_Customize_Control {
		public $type = 'et_coloralpha';

		public $statuses;

		public function __construct( $manager, $id, $args = array() ) {
			$this->statuses = array( '' => esc_html__( 'Default', 'Divi' ) );
			parent::__construct( $manager, $id, $args );

			// Printed saved value should always be in lowercase
			add_filter( "customize_sanitize_js_{$id}", array( $this, 'sanitize_saved_value' ) );
		}

		public function enqueue() {
			wp_enqueue_script( 'wp-color-picker-alpha' );
			wp_enqueue_style( 'wp-color-picker' );
		}

		public function to_json() {
			parent::to_json();
			$this->json['statuses'] = $this->statuses;
			$this->json['defaultValue'] = $this->setting->default;
		}

		public function render_content() {}

		public function content_template() {
			?>
			<# var defaultValue = '';
			if ( data.defaultValue ) {
				if ( '#' !== data.defaultValue.substring( 0, 1 ) && 'rgba' !== data.defaultValue.substring( 0, 4 ) ) {
					defaultValue = '#' + data.defaultValue;
				} else {
					defaultValue = data.defaultValue;
				}
				defaultValue = ' data-default-color=' + defaultValue; // Quotes added automatically.
			} #>
			<label>
				<# if ( data.label ) { #>
					<span class="customize-control-title">{{{ data.label }}}</span>
				<# } #>
				<# if ( data.description ) { #>
					<span class="description customize-control-description">{{{ data.description }}}</span>
				<# } #>
				<div class="customize-control-content">
					<input class="color-picker-hex" data-alpha="true" type="text" maxlength="30" placeholder="<?php esc_attr_e( 'Hex Value', 'Divi' ); ?>" {{ defaultValue }} />
				</div>
			</label>
			<?php
		}

		/**
		 * Ensure saved value to be printed in lowercase.
		 * Mismatched case causes broken 4.7 in Customizer. Color Alpha control only saves string.
		 * @param string  saved value
		 * @return string formatted value
		 */
		public function sanitize_saved_value( $value ) {
			return strtolower( $value );
		}
	}

}


/**
 * Outputting saved customizer style settings
 *
 * @return void
 */
function et_pb_print_css( $setting ) {

	// Defaults value
	$defaults = array(
		'key'       => false,
		'selector'  => false,
		'type'      => false,
		'default'   => false,
		'important' => false
	);

	// Parse given settings aginst defaults
	$setting = wp_parse_args( $setting, $defaults );

	if (
		$setting['key']      !== false ||
		$setting['selector'] !== false ||
		$setting['type']     !== false ||
		$setting['settings'] !== false
	) {

		// Some attribute requires !important tag
		if ( $setting['important'] ) {
			$important = "!important";
		} else {
			$important = "";
		}

		// get value
		$value = et_get_option( $setting['key'], $setting['default'] );

		// Output css based on its type
		if ( $value !== false && $value != $setting['default'] ) {
			switch ( $setting['type'] ) {
				case 'font-size':
					printf( '%1$s { font-size: %2$spx %3$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value ),
						$important );
					break;

				case 'font-size-post-header':
					$posts_font_size = (int)$value * (26 / 30 );
					printf( 'body.home-posts #left-area .et_pb_post h2, body.archive #left-area .et_pb_post h2, body.search #left-area .et_pb_post h2 { font-size:%1$spx }
						body.single .et_post_meta_wrapper h1 { font-size:%2$spx; }',
						esc_html( $posts_font_size ),
						esc_html( $value )
					);
					break;

				case 'font-style':
					printf( '%1$s { %2$s }',
						esc_html( $setting['selector'] ),
						et_pb_print_font_style( $value, $important )
					);
					break;

				case 'letter-spacing':
					printf( '%1$s { letter-spacing: %2$spx %3$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value ),
						$important
					);
					break;

				case 'line-height':
					printf( '%1$s { line-height: %2$sem %3$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value ),
						$important
					);
					break;

				case 'color':
					printf( '%1$s { color: %2$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'background-color':
					printf( '%1$s { background-color: %2$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'border-radius':
					printf( '%1$s { -moz-border-radius: %2$spx; -webkit-border-radius: %2$spx; border-radius: %2$spx; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'width':
					printf( '%1$s { width: %2$spx; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'height':
					printf( '%1$s { height: %2$spx; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'padding':
					printf( '%1$s { padding: %2$spx; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'padding-top-bottom':
					printf( '%1$s { padding: %2$spx 0; }',
						esc_html( $setting['selector'] ),
						esc_html( $value )
					);
					break;

				case 'padding-tabs':
					printf( '%1$s { padding: %2$spx %3$spx; }',
						esc_html( $setting['selector'] ),
						esc_html( ((int)$value * 0.5 ) ),
						esc_html( $value )
					);
					break;

				case 'padding-fullwidth-slider':
					printf( '%1$s { padding: %2$s %3$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value ) . '%',
						'0'
					);
					break;

				case 'padding-slider':
					printf( '%1$s { padding: %2$s %3$s; }',
						esc_html( $setting['selector'] ),
						esc_html( $value ) . '%',
                        esc_html( ((int)$value / 2 ) ) . '%'
					);
					break;

				case 'social-icon-size':
					$icon_margin 	= (int)$value * 0.57;
					$icon_dimension = (int)$value * 2;
					?>
					.et_pb_social_media_follow li a.icon{
						margin-right: <?php echo esc_html( $icon_margin ); ?>px;
						width: <?php echo esc_html( $icon_dimension ); ?>px;
						height: <?php echo esc_html( $icon_dimension ); ?>px;
					}

					.et_pb_social_media_follow li a.icon::before{
						width: <?php echo esc_html( $icon_dimension ); ?>px;
						height: <?php echo esc_html( $icon_dimension ); ?>px;
						font-size: <?php echo esc_html( $value ); ?>px;
						line-height: <?php echo esc_html( $icon_dimension ); ?>px;
					}
					<?php
					break;
			}
		}
	}
}

/**
 * Outputting saved customizer style(s) settings
 */
function et_pb_print_styles_css( $settings = array() ) {

	// $settings should be in array
	if ( is_array( $settings ) && ! empty( $settings ) ) {

		// Loop settings
		foreach ( $settings as $setting ) {

			// Print css
			et_pb_print_css( $setting );

		}
	}
}

/**
 * Outputting saved module styles settings. DRY
 *
 * @return void
 */
function et_pb_print_module_styles_css( $section = '', $settings = array() ) {
	$css = 'et_builder_maybe_wrap_css_selector';

	// Verify settings
	if ( is_array( $settings ) && ! empty( $settings ) ) {

		// Loop settings
		foreach ( $settings as $setting ) {

			// settings must have these elements: key, selector, default, and type
			if ( ! isset( $setting['key'] ) ||
				! isset( $setting['selector'] ) ||
				! isset( $setting['type'] ) ) {
				continue;
			}

			// Some attributes such as shop requires !important tag
			if ( isset( $setting['important'] ) && true === $setting['important'] ) {
				$important = ' !important';
			} else {
				$important = '';
			}

			// Prepare the setting key
			$key = "{$section}-{$setting['key']}";

			// Get the value
			$value = ET_Global_Settings::get_value( $key );
			$default_value = ET_Global_Settings::get_value( $key, 'default' );

			// Format the selector.
			$selector = $css( $setting['selector'], false );

			// Output CSS based on its type
			if ( false !== $value && $default_value !== $value ) {

				switch ( $setting['type'] ) {
					case 'font-size':

						printf( "%s { font-size: %spx%s; }\n", esc_html( $selector ), esc_html( $value ), $important );

						// Option with specific adjustment for smaller columns
						$smaller_title_sections = array(
							'et_pb_audio-title_font_size',
							'et_pb_blog-header_font_size',
							'et_pb_cta-header_font_size',
							'et_pb_contact_form-title_font_size',
							'et_pb_login-header_font_size',
							'et_pb_signup-header_font_size',
							'et_pb_slider-header_font_size',
							'et_pb_slider-body_font_size',
							'et_pb_countdown_timer-header_font_size',
						);

						if ( in_array( $key, $smaller_title_sections ) ) {

							// font size coefficient
							switch ( $key ) {
								case 'et_pb_slider-header_font_size':
									$font_size_coefficient = .565217391; // 26/46
									break;

								case 'et_pb_slider-body_font_size':
									$font_size_coefficient = .777777778; // 14/16
									break;

								default:
									$font_size_coefficient = .846153846; // 22/26
									break;
							}

							printf( '%1$s { font-size: %2$spx%3$s; }',
								esc_html( $css( '.et_pb_column_1_3 ' . $setting['selector'], false ) ),
								esc_html( $value * $font_size_coefficient ),
								$important
							);

							printf( '%1$s { font-size: %2$spx%3$s; }',
								esc_html( $css( '.et_pb_column_1_4 ' . $setting['selector'], false ) ),
								esc_html( $value * $font_size_coefficient ),
								$important
							);
						}

						break;

					case 'font-size':
						$value = (int)$value;

						printf( $css( '.et_pb_countdown_timer .title', false ) . ' { font-size: %spx; }', esc_html( $value ) );
						printf( $css( '.et_pb_column_3_8 .et_pb_countdown_timer .title', false ) . ' { font-size: %spx; }', esc_html( $value * ( 18 / 22 ) ) );
						printf( $css( '.et_pb_column_1_3 .et_pb_countdown_timer .title', false ) . ' { font-size: %spx; }', esc_html( $value * ( 18 / 22 ) ) );
						printf( $css( '.et_pb_column_1_4 .et_pb_countdown_timer .title', false ) . ' { font-size: %spx; }', esc_html( $value * ( 18 / 22 ) ) );
						break;

					case 'font-style':
						printf( "%s { %s }\n", esc_html( $selector ), et_pb_print_font_style( $value, $important ) );
						break;

					case 'color':
						printf( "%s { color: %s%s; }\n", esc_html( $selector ), esc_html( $value ), $important );
						break;

					case 'background-color':
						printf( "%s { background-color: %s%s; }\n", esc_html( $selector ), esc_html( $value ), $important );
						break;

					case 'border-radius':
						printf( "%s { -moz-border-radius: %spx; -webkit-border-radius: %spx; border-radius: %spx; }\n", esc_html( $selector ), esc_html( $value ), esc_html( $value ), esc_html( $value ) );
						break;

					case 'width':
						printf( "%s { width: %spx%s; }\n", esc_html( $selector ), esc_html( $value ), $important );
						break;

					case 'height':
						printf( "%s { height: %spx%s; }\n", esc_html( $selector ), esc_html( $value ), $important );
						break;

					case 'padding':
						printf( "%s { padding: %spx; }\n", esc_html( $selector ), esc_html( $value ) );
						break;

					case 'padding-top-bottom':
						printf( "%s { padding: %spx 0; }\n", esc_html( $selector ), esc_html( $value ) );
						break;

					case 'padding-tabs':
						$padding_tab_top_bottom 	= (int)$value * 0.133333333;
						$padding_tab_active_top 	= $padding_tab_top_bottom + 1;
						$padding_tab_active_bottom 	= $padding_tab_top_bottom - 1;
						$padding_tab_content 		= (int)$value * 0.8;

						// negative result will cause layout issue
						if ( $padding_tab_active_bottom < 0 ) {
							$padding_tab_active_bottom = 0;
						}

						printf(
							"%s { padding: %spx %spx %spx; }\n",
							esc_html( $css( '.et_pb_tabs_controls li', false ) ),
							esc_html( $padding_tab_active_top ),
							esc_html( $value ),
							esc_html( $padding_tab_active_bottom )
						);

						printf(
							"%s { padding: %spx %spx; }\n",
							esc_html( $css( '.et_pb_tabs_controls li.et_pb_tab_active', false ) ),
							esc_html( $padding_tab_top_bottom ),
							esc_html( $value )
						);

						printf(
							"%s { padding: %spx %spx; }\n",
							esc_html( $css( '.et_pb_all_tabs', false ) ),
							esc_html( $padding_tab_content ),
							esc_html( $value )
						);
						break;

					case 'padding-slider':
						printf( "%s { padding-top: %s; padding-bottom: %s }\n", esc_html( $selector ), esc_html( $value ) . '%', esc_html( $value ) . '%' );

						if ( 'et_pagebuilder_slider_padding' === $key ) {
							printf( '@media only screen and ( max-width: 767px ) { %1$s { padding-top: %2$s; padding-bottom: %2$s; } }', esc_html( $selector ), '16%' );
						}
						break;

					case 'padding-call-to-action':
						$value = (int)$value;

						printf(
							"%s { padding: %spx %spx !important; }\n",
							esc_html( $css( '.et_pb_promo', false ) ),
							esc_html( $value ),
							esc_html( $value * ( 60 / 40 ) )
						);

						printf(
							"%s { padding: %spx; }\n",
							esc_html( $css( '.et_pb_column_1_2 .et_pb_promo', false ) ),
							esc_html( $value )
						);

						printf(
							"%s { padding: %spx; }\n",
							esc_html( $css( '.et_pb_column_1_3 .et_pb_promo', false ) ),
							esc_html( $value )
						);

						printf(
							"%s { padding: %spx; }\n",
							esc_html( $css( '.et_pb_column_1_4 .et_pb_promo', false ) ),
							esc_html( $value )
						);

						break;

					case 'social-icon-size':
						$icon_margin 	= (int)$value * 0.57;
						$icon_dimension = (int)$value * 2;
						?>
						<?php echo $css( '.et_pb_social_media_follow li a.icon', false ); ?> {
							margin-right: <?php echo esc_html( $icon_margin ); ?>px;
							width: <?php echo esc_html( $icon_dimension ); ?>px;
							height: <?php echo esc_html( $icon_dimension ); ?>px;
						}

						<?php echo $css( '.et_pb_social_media_follow li a.icon::before', false ); ?> {
							width: <?php echo esc_html( $icon_dimension ); ?>px;
							height: <?php echo esc_html( $icon_dimension ); ?>px;
							font-size: <?php echo esc_html( $value ); ?>px;
							line-height: <?php echo esc_html( $icon_dimension ); ?>px;
						}

						<?php echo $css( '.et_pb_social_media_follow li a.follow_button', false ); ?> {
							font-size: <?php echo esc_html( $value ); ?>px;
						}
						<?php
						break;

					case 'border-top-style':
						printf( "%s { border-top-style: %s; }\n", esc_html( $selector ), esc_html( $value ) );
						break;

					case 'border-top-width':
						printf( "%s { border-top-width: %spx; }\n", esc_html( $selector ), esc_html( $value ) );
						break;
				}
			}
		}
	}
}

/**
 * Outputting font-style attributes & values saved by ET_Divi_Font_Style_Option on customizer
 *
 * @return string
 */
function et_pb_print_font_style( $styles = '', $important = '' ) {

	// Prepare variable
	$font_styles = "";

	if ( '' !== $styles && false !== $styles ) {
		// Convert string into array
		$styles_array = explode( '|', $styles );

		// If $important is in use, give it a space
		if ( $important && '' !== $important ) {
			$important = " " . $important;
		}

		// Use in_array to find values in strings. Otherwise, display default text

		// Font weight
		if ( in_array( 'bold', $styles_array ) ) {
			$font_styles .= "font-weight: bold{$important}; ";
		} else {
			$font_styles .= "font-weight: normal{$important}; ";
		}

		// Font style
		if ( in_array( 'italic', $styles_array ) ) {
			$font_styles .= "font-style: italic{$important}; ";
		} else {
			$font_styles .= "font-style: normal{$important}; ";
		}

		// Text-transform
		if ( in_array( 'uppercase', $styles_array ) ) {
			$font_styles .= "text-transform: uppercase{$important}; ";
		} else {
			$font_styles .= "text-transform: none{$important}; ";
		}

		// Text-decoration
		if ( in_array( 'underline', $styles_array ) ) {
			$font_styles .= "text-decoration: underline{$important}; ";
		} else {
			$font_styles .= "text-decoration: none{$important}; ";
		}
	}

	return esc_html( $font_styles );
}

function et_load_google_fonts_scripts() {
	$theme_version = et_get_theme_version();

	wp_enqueue_script( 'et_google_fonts', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.js', array( 'jquery' ), $theme_version, true );
	wp_localize_script( 'et_google_fonts', 'et_google_fonts_data', array(
		'user_fonts' => et_builder_get_custom_fonts(),
	) );
}
add_action( 'customize_controls_print_footer_scripts', 'et_load_google_fonts_scripts' );

function et_load_google_fonts_styles() {
	$theme_version = et_get_theme_version();

	wp_enqueue_style( 'et_google_fonts_style', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.css', array(), $theme_version );
}
add_action( 'customize_controls_print_styles', 'et_load_google_fonts_styles' );

if ( ! function_exists( 'et_divi_post_meta' ) ) :
function et_divi_post_meta() {
	$postinfo = is_single() ? et_get_option( 'divi_postinfo2' ) : et_get_option( 'divi_postinfo1' );

	if ( $postinfo ) :
		echo '<p class="post-meta">';
		echo et_pb_postinfo_meta( $postinfo, et_get_option( 'divi_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Divi' ), esc_html__( '1 comment', 'Divi' ), '% ' . esc_html__( 'comments', 'Divi' ) );
		echo '</p>';
	endif;
}
endif;

function et_video_embed_html( $video ) {
	if ( is_single() && 'video' === et_pb_post_format() ) {
		static $post_video_num = 0;

		$post_video_num++;

		// Hide first video in the post content on single video post page
		if ( 1 === $post_video_num ) {
			return '';
		}
	}

	return "<div class='et_post_video'>{$video}</div>";
}

function et_do_video_embed_html(){
	add_filter( 'embed_oembed_html', 'et_video_embed_html' );
}
add_action( 'et_before_content', 'et_do_video_embed_html' );

/**
 * Removes galleries on single gallery posts, since we display images from all
 * galleries on top of the page
 */
function et_delete_post_gallery( $content ) {
    $deleted = false;

	if ( ( is_single() || is_archive() ) && is_main_query() && has_post_format( 'gallery' ) ) :
		$regex = get_shortcode_regex();
		preg_match_all( "/{$regex}/s", $content, $matches );

		// $matches[2] holds an array of shortcodes names in the post
		foreach ( $matches[2] as $key => $shortcode_match ) {
			if ( 'gallery' === $shortcode_match ) {
				$content = str_replace( $matches[0][$key], '', $content );
				$deleted = true;
				break;
			}
		}
		$content = apply_filters('et_delete_post_gallery', $content, $deleted);
	endif;

    return $content;
}
add_filter( 'the_content', 'et_delete_post_gallery' );
// Include GB galleries in `get_post_gallery`
add_filter( 'et_gb_gallery_include_in_get_post_gallery', '__return_true' );

function et_divi_post_admin_scripts_styles( $hook ) {
	global $typenow;

	$theme_version = et_get_theme_version();
	$current_screen = get_current_screen();

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) return;

	if ( ! isset( $typenow ) ) return;

	if ( in_array( $typenow, array( 'post' ) ) ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'et-admin-post-script', get_template_directory_uri() . '/js/admin_post_settings.js', array( 'jquery' ), $theme_version );
	}
}
add_action( 'admin_enqueue_scripts', 'et_divi_post_admin_scripts_styles' );

function et_password_form() {
	$pwbox_id = rand();

	$form_output = sprintf(
		'<div class="et_password_protected_form">
			<h1>%1$s</h1>
			<p>%2$s:</p>
			<form action="%3$s" method="post">
				<p><label for="%4$s">%5$s: </label><input name="post_password" id="%4$s" type="password" size="20" maxlength="20" /></p>
				<p><button type="submit" class="et_submit_button et_pb_button">%6$s</button></p>
			</form>
		</div>',
		esc_html__( 'Password Protected', 'Divi' ),
		esc_html__( 'To view this protected post, enter the password below', 'Divi' ),
		esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ),
		esc_attr( 'pwbox-' . $pwbox_id ),
		esc_html__( 'Password', 'Divi' ),
		esc_html__( 'Submit', 'Divi' )
	);

	$output = sprintf(
		'<div class="et_pb_section et_section_regular">
			<div class="et_pb_row">
				<div class="et_pb_column et_pb_column_4_4">
					%1$s
				</div>
			</div>
		</div>',
		$form_output
	);

	return $output;
}
add_filter( 'the_password_form', 'et_password_form' );

/**
 * Determine whether current primary nav uses transparent nav or not based on primary nav background
 * @return bool
 */
function et_divi_is_transparent_primary_nav() {
	return 'rgba' == substr( et_get_option( 'primary_nav_bg', '#ffffff' ), 0, 4 );
}

function et_layout_body_class( $classes ) {
	do_action( 'et_layout_body_class_before', $classes );

	$vertical_nav = et_get_option( 'vertical_nav', false );
	if ( et_divi_is_transparent_primary_nav() && ( false === $vertical_nav || '' === $vertical_nav ) ) {
		$classes[] = 'et_transparent_nav';
	}

	// home-posts class is used by customizer > blog to work. It modifies post title and meta
	// of WP default layout (home, archive, single), but should not modify post title and meta of blog module (page as home)
	if ( in_array( 'home', $classes ) && ! in_array( 'page', $classes ) ) {
		$classes[] = 'home-posts';
	}

	if ( true === et_get_option( 'nav_fullwidth', false ) ) {
		if ( true === et_get_option( 'vertical_nav', false ) ) {
			$classes[] = 'et_fullwidth_nav_temp';
		} else {
			$classes[] = 'et_fullwidth_nav';
		}
	}

	if ( true === et_get_option( 'secondary_nav_fullwidth', false ) ) {
		$classes[] = 'et_fullwidth_secondary_nav';
	}

	if ( true === et_get_option( 'vertical_nav', false ) ) {
		$classes[] = 'et_vertical_nav';
		if ( 'right' === et_get_option( 'vertical_nav_orientation', 'left' ) ) {
			$classes[] = 'et_vertical_right';
		}
	} else if ( 'on' === et_get_option( 'divi_fixed_nav', 'on' ) ) {
		$classes[] = 'et_fixed_nav';
	} else if ( 'on' !== et_get_option( 'divi_fixed_nav', 'on' ) ) {
		$classes[] = 'et_non_fixed_nav';
	}

	if ( true === et_get_option( 'vertical_nav', false ) && 'on' === et_get_option( 'divi_fixed_nav', 'on' ) ) {
		$classes[] = 'et_vertical_fixed';
	}

	if ( true === et_get_option( 'boxed_layout', false ) ) {
		$classes[] = 'et_boxed_layout';
	}

	if ( true === et_get_option( 'hide_nav', false ) && ( ! is_singular() || is_singular() && 'no' !== get_post_meta( get_the_ID(), '_et_pb_post_hide_nav', true ) ) ) {
		$classes[] = 'et_hide_nav';
	} else {
		$classes[] = 'et_show_nav';
	}

	if ( true === et_get_option( 'hide_primary_logo', false ) ) {
		$classes[] = 'et_hide_primary_logo';
	}

	if ( true === et_get_option( 'hide_fixed_logo', false ) ) {
		$classes[] = 'et_hide_fixed_logo';
	}

	if ( true === et_get_option( 'hide_mobile_logo', false ) ) {
		$classes[] = 'et_hide_mobile_logo';
	}

	if ( false !== et_get_option( 'cover_background', true ) ) {
		$classes[] = 'et_cover_background';
	}

	$et_secondary_nav_items = et_divi_get_top_nav_items();

	if ( $et_secondary_nav_items->top_info_defined && 'slide' !== et_get_option( 'header_style', 'left' ) && 'fullscreen' !== et_get_option( 'header_style', 'left' ) ) {
		$classes[] = 'et_secondary_nav_enabled';
	}

	if ( $et_secondary_nav_items->two_info_panels && 'slide' !== et_get_option( 'header_style', 'left' ) && 'fullscreen' !== et_get_option( 'header_style', 'left' ) ) {
		$classes[] = 'et_secondary_nav_two_panels';
	}

	if ( $et_secondary_nav_items->secondary_nav && ! ( $et_secondary_nav_items->contact_info_defined || $et_secondary_nav_items->show_header_social_icons ) && 'slide' !== et_get_option( 'header_style', 'left' ) && 'fullscreen' !== et_get_option( 'header_style', 'left' ) ) {
		$classes[] = 'et_secondary_nav_only_menu';
	}

	if ( 'on' === get_post_meta( get_the_ID(), '_et_pb_side_nav', true ) && et_pb_is_pagebuilder_used( get_the_ID() ) ) {
		$classes[] = 'et_pb_side_nav_page';
	}

	if ( true === et_get_option( 'et_pb_sidebar-remove_border', false ) ) {
		$classes[] = 'et_pb_no_sidebar_vertical_divider';
	}

	if ( is_singular() && et_builder_enabled_for_post( get_the_ID() ) && 'on' == get_post_meta( get_the_ID(), '_et_pb_post_hide_nav', true ) ) {
		$classes[] = 'et_hide_nav';
	}

	if ( ! et_get_option( 'use_sidebar_width', false ) ) {
		$classes[] = 'et_pb_gutter';
	}

	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		if ( stristr( $_SERVER['HTTP_USER_AGENT'], "mac" ) ) {
			$classes[] = 'osx';
		} elseif ( stristr( $_SERVER['HTTP_USER_AGENT'], "linux" ) ) {
			$classes[] = 'linux';
		} elseif ( stristr( $_SERVER['HTTP_USER_AGENT'], "windows" ) ) {
			$classes[] = 'windows';
		}
	}

	$page_custom_gutter = get_post_meta( get_the_ID(), '_et_pb_gutter_width', true );
	$gutter_width = ! empty( $page_custom_gutter ) && is_singular() ? $page_custom_gutter :  et_get_option( 'gutter_width', '3' );
	$classes[] = esc_attr( "et_pb_gutters{$gutter_width}" );

	$primary_dropdown_animation = et_get_option( 'primary_nav_dropdown_animation', 'fade' );
	$classes[] = esc_attr( "et_primary_nav_dropdown_animation_{$primary_dropdown_animation}" );

	$secondary_dropdown_animation = et_get_option( 'secondary_nav_dropdown_animation', 'fade' );
	$classes[] = esc_attr( "et_secondary_nav_dropdown_animation_{$secondary_dropdown_animation}" );

	$footer_columns = et_get_option( 'footer_columns', '4' );
	$classes[] = esc_attr( "et_pb_footer_columns{$footer_columns}" );

	$header_style = et_get_option( 'header_style', 'left' );
	$classes[] = esc_attr( "et_header_style_{$header_style}" );

	if ( 'slide' === $header_style || 'fullscreen' === $header_style ) {
		$classes[] = esc_attr( "et_header_style_left" );
		if ( 'fullscreen' === $header_style && ! et_get_option( 'slide_nav_show_top_bar', true ) ) {
			// additional class if top bar disabled in Fullscreen menu
			$classes[] = esc_attr( "et_pb_no_top_bar_fullscreen" );
		}
	}

	$logo = et_get_option( 'divi_logo', '' );
	if ( '.svg' === substr( $logo, -4, 4 ) ) {
		$classes[] = 'et_pb_svg_logo';
	}

	// Add the page builder class.
	if ( et_pb_is_pagebuilder_used( get_the_ID() ) ) {
		$classes[] = 'et_pb_pagebuilder_layout';
	}

	// Add smooth scroll class name
	if ( 'on' === et_get_option( 'divi_smooth_scroll', false ) ) {
		$classes[] = 'et_smooth_scroll';
	}

	do_action( 'et_layout_body_class_after', $classes );

	return $classes;
}
add_filter( 'body_class', 'et_layout_body_class' );

if ( ! function_exists( 'et_layout_post_class' ) ):
function et_layout_post_class( $classes ) {
	global $template;

	$post_id       = get_the_ID();
	$post_type     = get_post_type( $post_id );
	$template_name = basename( $template );

	if ( 'page' === $post_type ) {
		// Don't add the class to pages.
		return $classes;
	}

	if ( in_array( $template_name, array( 'index.php', 'single.php' ) ) ) {
		// The class has already been added by one of the theme's templates.
		return $classes;
	}

	// Since the theme's templates are not being used, we don't add the class on CPT archive pages.
	if ( is_single() && et_pb_is_pagebuilder_used( $post_id ) ) {
		$classes[] = 'et_pb_post';
	}

	return $classes;
}
add_filter( 'post_class', 'et_layout_post_class' );
endif;

if ( ! function_exists( 'et_show_cart_total' ) ) {
	function et_show_cart_total( $args = array() ) {
		if ( ! class_exists( 'woocommerce' ) || ! WC()->cart ) {
			return;
		}

		$defaults = array(
			'no_text' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$items_number = WC()->cart->get_cart_contents_count();

		$url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();

		printf(
			'<a href="%1$s" class="et-cart-info">
				<span>%2$s</span>
			</a>',
			esc_url( $url ),
			( ! $args['no_text']
				? esc_html( sprintf(
					_nx( '%1$s Item', '%1$s Items', $items_number, 'WooCommerce items number', 'Divi' ),
					number_format_i18n( $items_number )
				) )
				: ''
			)
		);
	}
}

if ( ! function_exists( 'et_divi_get_top_nav_items' ) ) {
	function et_divi_get_top_nav_items() {
		$items = new stdClass;

		$items->phone_number = et_get_option( 'phone_number' );

		$items->email = et_get_option( 'header_email' );

		$items->contact_info_defined = $items->phone_number || $items->email;

		$items->show_header_social_icons = et_get_option( 'show_header_social_icons', false );

		$items->secondary_nav = wp_nav_menu( array(
			'theme_location' => 'secondary-menu',
			'container'      => '',
			'fallback_cb'    => '',
			'menu_id'        => 'et-secondary-nav',
			'echo'           => false,
		) );

		$items->top_info_defined = $items->contact_info_defined || $items->show_header_social_icons || $items->secondary_nav;

		$items->two_info_panels = $items->contact_info_defined && ( $items->show_header_social_icons || $items->secondary_nav );

		return $items;
	}
}

function et_divi_activate_features(){
	define( 'ET_SHORTCODES_VERSION', et_get_theme_version() );

	/* activate shortcodes */
	require_once( get_template_directory() . '/epanel/shortcodes/shortcodes.php' );
}
add_action( 'init', 'et_divi_activate_features' );

require_once( get_template_directory() . '/et-pagebuilder/et-pagebuilder.php' );

function et_modify_shop_page_columns_num( $columns_num ) {
	$default_sidebar_class = is_rtl() ? 'et_left_sidebar' : 'et_right_sidebar';

	if ( class_exists( 'woocommerce' ) && is_shop() ) {
		$columns_num = 'et_full_width_page' !== et_get_option( 'divi_shop_page_sidebar', $default_sidebar_class )
			? 3
			: 4;
	}

	return $columns_num;
}
add_filter( 'loop_shop_columns', 'et_modify_shop_page_columns_num' );

// WooCommerce

global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) {
	// Prevent Cache Warning From Being Displayed On First Install
	$current_theme_version[ et_get_theme_version() ] = 'ignore' ;
	update_option( 'et_pb_cache_notice', $current_theme_version );

	add_action( 'init', 'et_divi_woocommerce_image_dimensions', 1 );
}

/**
 * Default values for WooCommerce images changed in version 1.3
 * Checks if WooCommerce image dimensions have been updated already.
 */
function et_divi_check_woocommerce_images() {
	if ( 'checked' === et_get_option( 'divi_1_3_images' ) ) return;

	et_divi_woocommerce_image_dimensions();
	et_update_option( 'divi_1_3_images', 'checked' );
}
add_action( 'admin_init', 'et_divi_check_woocommerce_images' );

function et_divi_woocommerce_image_dimensions() {
	$catalog = array(
		'width' 	=> '400',
		'height'	=> '400',
		'crop'		=> 1,
	);

	$single = array(
		'width' 	=> '510',
		'height'	=> '9999',
		'crop'		=> 0,
	);

	$thumbnail = array(
		'width' 	=> '157',
		'height'	=> '157',
		'crop'		=> 1,
	);

	update_option( 'shop_catalog_image_size', $catalog );
	update_option( 'shop_single_image_size', $single );
	update_option( 'shop_thumbnail_image_size', $thumbnail );
}

if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ):
function woocommerce_template_loop_product_thumbnail() {
	printf( '<span class="et_shop_image">%1$s<span class="et_overlay"></span></span>',
		woocommerce_get_product_thumbnail()
	);
}
endif;

function et_divi_output_product_wrapper() {
	echo '<div class="clearfix">';
}


function et_divi_output_product_wrapper_end() {
	echo '</div><!-- #end wrapper -->';
}

function et_review_gravatar_size( $size ) {
	return '80';
}
add_filter( 'woocommerce_review_gravatar_size', 'et_review_gravatar_size' );


function et_divi_output_content_wrapper() {
	echo '
		<div id="main-content">
			<div class="container">
				<div id="content-area" class="clearfix">
					<div id="left-area">';
}

function et_divi_output_content_wrapper_end() {
	$default_sidebar_class = is_rtl() ? 'et_left_sidebar' : 'et_right_sidebar';
	$fullwidth_post = is_singular() && 'et_full_width_page' === get_post_meta( get_the_ID(), '_et_pb_page_layout', true );

	echo '</div> <!-- #left-area -->';

	if ( function_exists( 'woocommerce_get_sidebar' ) ) {
		$woo_fullwidth_page = ( is_shop() || is_product_category() || is_product_tag() || is_tax() ) && 'et_full_width_page' === et_get_option( 'divi_shop_page_sidebar', $default_sidebar_class );
		if ( ! $fullwidth_post && ! $woo_fullwidth_page ) {
			woocommerce_get_sidebar();
		}
	} else if ( ! $fullwidth_post ) {
		get_sidebar();
	}

	echo '
				</div> <!-- #content-area -->
			</div> <!-- .container -->
		</div> <!-- #main-content -->';
}

function et_add_divi_menu() {
	$core_page = add_menu_page( 'Divi', 'Divi', 'switch_themes', 'et_divi_options', 'et_build_epanel' );

	// Add Theme Options menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'theme_options' ) ) {

		if ( isset( $_GET['page'] ) && 'et_divi_options' === $_GET['page'] && isset( $_POST['action'] ) ) {
			if (
				( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'epanel_nonce' ) )
				||
				( 'reset' === $_POST['action'] && isset( $_POST['_wpnonce_reset'] ) && wp_verify_nonce( $_POST['_wpnonce_reset'], 'et-nojs-reset_epanel' ) )
			) {
				epanel_save_data( 'js_disabled' ); //saves data when javascript is disabled
			}
		}

		add_submenu_page( 'et_divi_options', esc_html__( 'Theme Options', 'Divi' ), esc_html__( 'Theme Options', 'Divi' ), 'manage_options', 'et_divi_options' );
	}
	// Add Theme Customizer menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'theme_customizer' ) ) {
		add_submenu_page( 'et_divi_options', esc_html__( 'Theme Customizer', 'Divi' ), esc_html__( 'Theme Customizer', 'Divi' ), 'manage_options', 'customize.php?et_customizer_option_set=theme' );
	}
	// Add Module Customizer menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'module_customizer' ) ) {
		add_submenu_page( 'et_divi_options', esc_html__( 'Module Customizer', 'Divi' ), esc_html__( 'Module Customizer', 'Divi' ), 'manage_options', 'customize.php?et_customizer_option_set=module' );
	}
	add_submenu_page( 'et_divi_options', esc_html__( 'Role Editor', 'Divi' ), esc_html__( 'Role Editor', 'Divi' ), 'manage_options', 'et_divi_role_editor', 'et_pb_display_role_editor' );
	// Add Divi Library menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'divi_library' ) ) {
		add_submenu_page( 'et_divi_options', esc_html__( 'Divi Library', 'Divi' ), esc_html__( 'Divi Library', 'Divi' ), 'manage_options', 'edit.php?post_type=et_pb_layout' );
	}

	add_action( "load-{$core_page}", 'et_pb_check_options_access' ); // load function to check the permissions of current user
	add_action( "load-{$core_page}", 'et_epanel_hook_scripts' );
	add_action( "admin_print_scripts-{$core_page}", 'et_epanel_admin_js' );
	add_action( "admin_head-{$core_page}", 'et_epanel_css_admin');
	add_action( "admin_print_scripts-{$core_page}", 'et_epanel_media_upload_scripts');
	add_action( "admin_head-{$core_page}", 'et_epanel_media_upload_styles');
}
add_action('admin_menu', 'et_add_divi_menu');

function add_divi_customizer_admin_menu() {
	if ( ! current_user_can( 'customize' ) ) {
		return;
	}

	global $wp_admin_bar;

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );

	// add Theme Customizer admin menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'theme_customizer' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'customize-divi-theme',
			'title'  => esc_html__( 'Theme Customizer', 'Divi' ),
			'href'   => $customize_url . '&et_customizer_option_set=theme',
			'meta'   => array(
				'class' => 'hide-if-no-customize',
			),
		) );
	}

	// add Module Customizer admin menu only if it's enabled for current user
	if ( et_pb_is_allowed( 'module_customizer' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'customize-divi-module',
			'title'  => esc_html__( 'Module Customizer', 'Divi' ),
			'href'   => $customize_url . '&et_customizer_option_set=module',
			'meta'   => array(
				'class' => 'hide-if-no-customize',
			),
		) );
	}
	$wp_admin_bar->remove_menu( 'customize' );
}
add_action( 'admin_bar_menu', 'add_divi_customizer_admin_menu', 999 );

function et_pb_hide_options_menu() {
	// do nothing if theme options should be displayed in the menu
	if ( et_pb_is_allowed( 'theme_options' ) ) {
		return;
	}

	$theme_version = et_get_theme_version();

	wp_enqueue_script( 'divi-custom-admin-menu', get_template_directory_uri() . '/js/menu_fix.js', array( 'jquery' ), $theme_version, true );
}
add_action( 'admin_enqueue_scripts', 'et_pb_hide_options_menu' );

function et_pb_check_options_access() {
	// display wp error screen if theme customizer disabled for current user
	if ( ! et_pb_is_allowed( 'theme_options' ) ) {
		wp_die( esc_html__( "you don't have sufficient permissions to access this page", 'Divi' ) );
	}
}

/**
 * Divi Support Center
 *
 * @since ??
 */
function et_add_divi_support_center(){
	// Make sure we don't load it twice
	if ( class_exists( 'ET_Support_Center' ) ) {
		return;
	}

	include_once 'core/components/SupportCenter.php';

	$support_center = new ET_Support_Center('divi_theme');
	$support_center->init();
}
add_action('init', 'et_add_divi_support_center' );

/**
 * Allowing blog and portfolio module pagination to work in non-hierarchical singular page.
 * Normally, WP_Query based modules wouldn't work in non-hierarchical single post type page
 * due to canonical redirect to prevent page duplication which could lead to SEO penalty.
 *
 * @see redirect_canonical()
 *
 * @return mixed string|bool
 */
function et_modify_canonical_redirect( $redirect_url, $requested_url ) {
	global $post;

	$allowed_shortcodes              = array( 'et_pb_blog', 'et_pb_portfolio' );
	$is_overwrite_canonical_redirect = false;

	// Look for $allowed_shortcodes in content. Once detected, set $is_overwrite_canonical_redirect to true
	foreach ( $allowed_shortcodes as $shortcode ) {
		if ( !empty( $post ) && has_shortcode( $post->post_content, $shortcode ) ) {
			$is_overwrite_canonical_redirect = true;
			break;
		}
	}

	// Only alter canonical redirect in 2 cases:
	// 1) If current page is singular, has paged and $allowed_shortcodes
	// 2) If current page is front_page, has page and $allowed_shortcodes
	if ( ( is_singular() & ! is_home() && get_query_var( 'paged' ) && $is_overwrite_canonical_redirect ) || ( is_front_page() && get_query_var( 'page' ) && $is_overwrite_canonical_redirect ) ) {
		return $requested_url;
	}

	return $redirect_url;
}
add_filter( 'redirect_canonical', 'et_modify_canonical_redirect', 10, 2 );

/**
 * Determines how many related products should be displayed on single product page
 * @param array  related products arguments
 * @return array modified related products arguments
 */
function et_divi_woocommerce_output_related_products_args( $args ) {
	$related_posts = 4; // default number

	if ( is_singular( 'product' ) ) {
		$page_layout = get_post_meta( get_the_ID(), '_et_pb_page_layout', true );

		if ( 'et_full_width_page' !== $page_layout ) {
			$related_posts = 3; // set to 3 if page has sidebar
		}
	}

	// Modify related and up-sell products args
	$args['posts_per_page'] = $related_posts;
	$args['columns']        = $related_posts;

	return $args;
}
add_filter( 'woocommerce_upsell_display_args', 'et_divi_woocommerce_output_related_products_args' );
add_filter( 'woocommerce_output_related_products_args', 'et_divi_woocommerce_output_related_products_args' );

function et_divi_maybe_change_frontend_locale( $locale ) {
	$option_name   = 'divi_disable_translations';
	$theme_options = get_option( 'et_divi' );

	$disable_translations = $theme_options[$option_name] ?? false;

	if ( 'on' === $disable_translations ) {
		return 'en_US';
	}

	return $locale;
}
add_filter( 'locale', 'et_divi_maybe_change_frontend_locale' );

/**
 * Enable Divi gallery override if user activates it
 * @return bool
 */
function et_divi_gallery_layout_enable( $option ) {
	$setting = et_get_option( 'divi_gallery_layout_enable' );

	return ( 'on' === $setting ) ? true : $option;
}
add_filter( 'et_gallery_layout_enable', 'et_divi_gallery_layout_enable' );

/**
 * Enable GB gallery to shortcode conversion
 *
 * @return bool
 */
function et_divi_gb_gallery_to_shortcode() {
    return et_divi_gallery_layout_enable( false );
}
add_filter( 'et_gb_gallery_to_shortcode', 'et_divi_gb_gallery_to_shortcode' );

/**
 * Register theme and modules Customizer portability.
 *
 * @since 2.7.0
 */
function et_divi_register_customizer_portability() {
	global $options;

	// Make sure the Portability is loaded.
	et_core_load_component( 'portability' );

	// Load ePanel options.
	et_load_core_options();

	// Exclude ePanel options.
	$exclude = array();

	foreach ( $options as $option ) {
		if ( isset( $option['id'] ) ) {
			$exclude[ $option['id'] ] = true;
		}
	}

	// Register the portability.
	et_core_portability_register( 'et_divi_mods', array(
		'name'    => esc_html__( 'Divi Customizer Settings', 'Divi' ),
		'type'    => 'options',
		'target'  => 'et_divi',
		'exclude' => $exclude,
		'view'    => is_customize_preview(),
	) );
}
add_action( 'admin_init', 'et_divi_register_customizer_portability' );

function et_register_updates_component() {
	et_core_enable_automatic_updates( get_template_directory_uri(), ET_CORE_VERSION );
}
add_action( 'admin_init', 'et_register_updates_component' );

/**
 * Register theme and modules Customizer portability link.
 *
 * @since 2.7.0
 *
 * @return bool Always return true.
 */
function et_divi_customizer_link() {
	if ( is_customize_preview() ) {
		echo et_core_portability_link( 'et_divi_mods', array( 'class' => 'et-core-customize-controls-close' ) );
	}
}
add_action( 'customize_controls_print_footer_scripts', 'et_divi_customizer_link' );


/**
 * Determine if it's a fresh Divi install by checking for the existence of 'divi_logo' key in 'et_divi' options array.
 *
 * @since ??
 *
 * @return bool
 */
if ( ! function_exists( 'et_divi_is_fresh_install' ) ):
function et_divi_is_fresh_install() {
    return false === et_get_option( 'divi_logo' );
}
endif;

if ( ! function_exists( 'et_get_original_footer_credits' ) ) :
function et_get_original_footer_credits() {
	return sprintf( __( 'Designed by %1$s | Powered by %2$s', 'Divi' ), '<a href="http://www.elegantthemes.com" title="Premium WordPress Themes">Elegant Themes</a>', '<a href="http://www.wordpress.org">WordPress</a>' );
}
endif;

if ( ! function_exists( 'et_get_footer_credits' ) ) :
function et_get_footer_credits() {
	$original_footer_credits = et_get_original_footer_credits();

	$disable_custom_credits = et_get_option( 'disable_custom_footer_credits', false );

	if ( $disable_custom_credits ) {
		return '';
	}

	$credits_format = '<%2$s id="footer-info">%1$s</%2$s>';

	$footer_credits = et_get_option( 'custom_footer_credits', '' );

	if ( '' === trim( $footer_credits ) ) {
		return et_get_safe_localization( sprintf( $credits_format, $original_footer_credits, 'p' ) );
	}

	return et_get_safe_localization( sprintf( $credits_format, $footer_credits, 'div' ) );
}
endif;

if ( ! function_exists( 'et_divi_filter_et_core_is_builder_used_on_current_request' ) ):
function et_divi_filter_et_core_is_builder_used_on_current_request( $is_builder_used ) {
	if ( $is_builder_used && ! is_singular() ) {
		$is_builder_used = 'on' === et_get_option( 'divi_blog_style', 'false' );
	}

	return $is_builder_used;
}
add_filter( 'et_core_is_builder_used_on_current_request', 'et_divi_filter_et_core_is_builder_used_on_current_request' );
endif;

if ( ! function_exists( 'et_divi_version_rollback' ) ) :
function et_divi_version_rollback() {
	global $themename, $shortname;
	static $instance = null;

	if ( null === $instance ) {
		$instance = new ET_Core_VersionRollback( $themename, $shortname, et_get_theme_version() );
	}

	return $instance;
}
endif;

/**
 * Filter the list of post types the Divi Builder is enabled on based on theme options.
 *
 * @since 3.10
 *
 * @param array<string, string> $options
 *
 * @return array<string, string>
 */
if ( ! function_exists( 'et_divi_filter_enabled_builder_post_type_options' ) ) :
function et_divi_filter_enabled_builder_post_type_options( $options ) {
	// Cache results to avoid unnecessary option fetching multiple times per request.
	static $stored_options = null;

	if ( null === $stored_options ) {
		$stored_options = et_get_option( 'et_pb_post_type_integration', array() );
	}

	return $stored_options;
}
endif;
add_filter( 'et_builder_enabled_builder_post_type_options', 'et_divi_filter_enabled_builder_post_type_options' );

/**
 * Caches expensive generation of truncate_post content
 *
 * @since 3.17.3
 *
 * @param bool $custom
 * @param string $content
 * @param WP_Post $post
 *
 * @return string
 */
if ( ! function_exists( 'et_divi_truncate_post_use_custom_content' ) ) :
function et_divi_truncate_post_use_custom_content( $custom, $content, $post ) {
	// If post doesn't use builder, no need to compute a custom value
	if ( ! et_pb_is_pagebuilder_used( $post->ID ) ) {
		return false;
	}

	$cached = get_post_meta( $post->ID, '_et_pb_truncate_post', true );

	if ( $cached ) {
		return $cached;
	}

	$custom = apply_filters( 'the_content', $content );
	// Save the result because expensive to compute.
	update_post_meta( $post->ID, '_et_pb_truncate_post', $custom );

	return $custom;
}
endif;
add_filter( 'et_truncate_post_use_custom_content', 'et_divi_truncate_post_use_custom_content', 10, 3 );

/**
 * Caches expensive generation of et_first_image
 *
 * @since 3.17.3
 *
 * @param bool $custom
 * @param string $content
 * @param WP_Post $post
 *
 * @return string
 */
if ( ! function_exists( 'et_divi_first_image_use_custom_content' ) ) :
function et_divi_first_image_use_custom_content( $custom, $content, $post ) {
	// If post doesn't use builder, no need to compute a custom value
	if ( ! et_pb_is_pagebuilder_used( $post->ID ) ) {
		return false;
	}

	$cached = get_post_meta( $post->ID, '_et_pb_first_image', true );

	if ( $cached ) {
		return $cached;
	}

	$custom = apply_filters( 'the_content', $content );
	// Save the result because expensive to compute.
	update_post_meta( $post->ID, '_et_pb_first_image', $custom );

	return $custom;
}
endif;
add_filter( 'et_first_image_use_custom_content', 'et_divi_first_image_use_custom_content', 10, 3 );

/**
 * Fired when post is saved in VB / BFB / BB
 *
 * @since 3.17.3
 *
 * @param integer $post_id
 *
 * @return void
 */
if ( ! function_exists( 'et_divi_save_post' ) ) :
function et_divi_save_post( $post_id ) {
	if ( ! $post_id ) {
		return;
	}

	// Unset cache
	update_post_meta( $post_id, '_et_pb_first_image', false );
	update_post_meta( $post_id, '_et_pb_truncate_post', false );
}
endif;
add_action( 'et_save_post', 'et_divi_save_post', 1 );

if ( ! function_exists( 'et_divi_footer_active_sidebars' ) ):
	function et_divi_footer_active_sidebars() {
		$et_active_sidebar = array( 2, 3, 4, 5, 6, 7 );
		if ( ! is_customize_preview() ) {
			if ( ! is_active_sidebar( 2 )
				 && ! is_active_sidebar( 3 )
				 && ! is_active_sidebar( 4 )
				 && ! is_active_sidebar( 5 )
				 && ! is_active_sidebar( 6 )
				 && ! is_active_sidebar( 7 ) ) {
				return false;
			}
			$footer_columns = et_get_option( 'footer_columns', '4' );
			switch ( $footer_columns ) {
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
					$et_active_sidebar = array();
					for ( $i = 1; $i <= $footer_columns; $i++ ) {
						array_push( $et_active_sidebar, ( $i + 1 ) );
					}
					break;
				case '_1_4__3_4':
				case '_3_4__1_4':
				case '_1_3__2_3':
				case '_2_3__1_3':
				case '_3_5__2_5':
				case '_2_5__3_5':
					$et_active_sidebar = array( 2, 3 );
					break;
				case '_1_4__1_2':
				case '_1_2__1_4':
				case '_1_5__3_5':
				case '_3_5__1_5':
				case '_1_4_1_2_1_4':
				case '_1_5_3_5_1_5':
					$et_active_sidebar = array( 2, 3, 4 );
					break;
				case '_1_2__1_6':
				case '_1_6__1_2':
					$et_active_sidebar = array( 2, 3, 4, 5 );
					break;
			}
		}

		return $et_active_sidebar;
	}
endif;
