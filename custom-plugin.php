<?php
/**
 * Plugin Name: Customkm Menu
 * Plugin URI: https://qrolic.com
 * Description: Customkm Plugin for your site.
 * Version: 6.5.2
 * Author: krishna
 * Author URI:https://qrolic.com
 * Text Domain: customkm-menu
 * Domain Path: /languages
 *
 * Customkm Menu plugin use for add CUSTOM MENU,CUSTOM SUBMENU,create field and save data using ,
 * OPTION API AND SETTING API.
 *
 * Create register post type PORTFOLIO and save their custom field with view.
 *
 * Create code for display recent post type from portfolio menu.
 *
 * Build a widget that displays recent comments or posts in the sidebar of your WordPress site.
 *
 * Incorporate AJAX into customkm plugin to perform asynchronous tasks, such as loading content dynamically or submitting form data without page reloads.
 *
 * Create shortcode for Add form with different fields and insert data in custom post type Portfolio.
 */

/**
 * Enqueues the stylesheet for the plugin.
 */
function your_plugin_enqueue_styles() {
	// Enqueue CSS file located within your plugin directory
	wp_enqueue_style( 'your-plugin-style', plugins_url( '/css/portfolio-submission-form.css', __FILE__ ), array(), '1.0', 'all' );
}
add_action( 'wp_enqueue_scripts', 'your_plugin_enqueue_styles' );



/**
 * Adds custom fields to the portfolio post type.
 */
function custom_portfolio_custom_fields() {
	add_meta_box(
		'portfolio_fields',
		'Portfolio Fields',
		'render_portfolio_fields',
		'portfolio',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'custom_portfolio_custom_fields' );

/**
 * Renders the custom fields for the portfolio post type.
 */
function render_portfolio_fields() {
	// Retrieve existing values for fields
	include_once plugin_dir_path( __FILE__ ) . 'templates/portfolio-renders.php';
}

// Save Custom Fields
function save_portfolio_custom_fields( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Save client name
	if ( isset( $_POST['plugin_options_nonce'] ) && wp_verify_nonce( $_POST['plugin_options_nonce'], 'update_plugin_options' ) ) {
		update_post_meta( $post_id, 'client_name', sanitize_text_field( $_POST['client_name'] ) );
	}

	// Save project URL
	if ( isset( $_POST['plugin_options_nonce'] ) && wp_verify_nonce( $_POST['plugin_options_nonce'], 'update_plugin_options' ) ) {
		update_post_meta( $post_id, 'project_url', esc_url( $_POST['project_url'] ) );
	}
}
add_action( 'save_post', 'save_portfolio_custom_fields' );

// Add plugin page in admin menu
function custom_ajax_plugin_menu() {
	add_menu_page(
		'Custom AJAX Plugin Settings',    // Page title
		esc_html__( 'Custom AJAX Plugin', 'customkm-menu' ),         // Menu title
		'manage_options',         // Capability
		'custom-ajax-plugin-settings',         // Menu slug
		'custom_ajax_plugin_settings_page',    // Callback function
		'dashicons-menu', // Icon
		28 // Position of the menu in the admin sidebar
	);
}

/**
 * Callback function to display the plugin settings page.
 */
function custom_ajax_plugin_settings_page() {
	include_once plugin_dir_path( __FILE__ ) . 'templates/custom-ajax.php';
}
add_action( 'admin_menu', 'custom_ajax_plugin_menu' );

/**
 * Handles AJAX request to update store name.
 */
function custom_ajax_plugin_ajax_handler() {
	if ( isset( $_POST['store_name'] ) ) {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'custom_ajax_plugin_ajax_nonce' ) ) {
			echo 'ok';
		}
		// Sanitize and save store name
		$store_name = sanitize_text_field( $_POST['store_name'] );
		update_option( 'store_name', $store_name );
		echo 'Store name updated successfully!';
	}
	wp_die();
}
add_action( 'wp_ajax_custom_ajax_plugin_update_store_name', 'custom_ajax_plugin_ajax_handler' );

/**
 * Enqueues JavaScript for AJAX.
 */
function custom_ajax_plugin_enqueue_scripts( $hook ) {
	if ( 'toplevel_page_custom-ajax-plugin-settings' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'custom-ajax-plugin-script', plugins_url( '/js/custom-ajax-plugin-script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	wp_localize_script(
		'custom-ajax-plugin-script',
		'custom_ajax_plugin_ajax_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);
}
add_action( 'admin_enqueue_scripts', 'custom_ajax_plugin_enqueue_scripts' );

/**
 * Registers a shortcode to display recent portfolio posts.
 */
add_shortcode( 'recent_portfolio_posts', 'display_recent_portfolio_posts_shortcode' );
// Shortcode callback function to display recent portfolio posts
function display_recent_portfolio_posts_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count' => 4,               // Default number of posts to display
		),
		$atts
	);

	$args = array(
		'post_type'      => 'portfolio', // Custom post type name
		'posts_per_page' => $atts['count'],
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$recent_portfolio_posts = new WP_Query( $args );

	if ( $recent_portfolio_posts->have_posts() ) {
		$output = '<ul>';
		while ( $recent_portfolio_posts->have_posts() ) {
			$recent_portfolio_posts->the_post();
			$output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
		}
		$output .= '</ul>';
		wp_reset_postdata(); // Reset post data query
	} else {
		$output = 'No recent portfolio posts found.';
	}

	return $output;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-kmd-widget.php';



// Enqueue jQuery in WordPress
function enqueue_jquery() {
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_jquery' );

/**
 * Creates a shortcode for the form.
 */
function portfolio_submission_form_shortcode( $atts ) {
		// Extract shortcode attributes
		$atts = shortcode_atts(
			array(
				'title' => 'My Form Submission', // Default title if not provided
			),
			$atts,
			'portfolio_submission_form'
		);

	ob_start();
	?>
	<div class="my">
	<h2 style="font-weight: bold;"><?php echo esc_html( $atts['title'] ); ?></h2>
	<?php include_once plugin_dir_path( __FILE__ ) . 'templates/portfolio-form.php'; ?>
	<div id="response_msg"></div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'portfolio_submission_form', 'portfolio_submission_form_shortcode' );

function my_plugin_enqueue_scripts() {
	// Enqueue custom script
	wp_enqueue_script(
		'my-custom-script', // Handle
		plugin_dir_url( __FILE__ ) . 'js/custom-script.js', // URL to script
		array( 'jquery' ), // Dependencies
		'1.0', // Version number (optional)
		true // Load script in footer (optional)
	);

	// Pass Ajax URL to script
	wp_localize_script(
		'my-custom-script', // Script handle
		'ajaxurl', // Object name
		admin_url( 'admin-ajax.php' ) // Data
	);
}
// Hook into appropriate action
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_scripts' );

/**
 * Processes form submission for portfolio.
 */
function process_portfolio_submission() {
	if ( isset( $_POST['portfolio_submission_nonce_field'] ) && wp_verify_nonce( $_POST['portfolio_submission_nonce_field'], 'portfolio_submission_nonce' ) ) {
		if ( isset( $_POST['name'] ) && isset( $_POST['email'] ) ) {

			if ( empty( $_POST['name'] ) || empty( $_POST['email'] ) || empty( $_POST['company_name'] ) || empty( $_POST['phone'] ) || empty( $_POST['address'] ) ) {
				echo 'Please fill out all required fields.';
				die();
			}
			$name         = sanitize_text_field( $_POST['name'] );
			$company_name = sanitize_text_field( $_POST['company_name'] );
			$email        = sanitize_email( $_POST['email'] );
			$phone        = sanitize_text_field( $_POST['phone'] );
			$address      = sanitize_textarea_field( $_POST['address'] );

			$portfolio_data = array(
				'post_title'  => $name,
				'post_type'   => 'portfolio',
				'post_status' => 'publish',
				'meta_input'  => array(
					'client_name'  => $name,
					'email'        => $email,
					'phone'        => $phone,
					'company_name' => $company_name,
					'address'      => $address,
					'email_result' => $email_result,
				),
			);
			// Insert the post into the database
			$post_id = wp_insert_post( $portfolio_data );

			if ( is_wp_error( $post_id ) ) {
				echo 'Error: ' . esc_html( $post_id->get_error_message() );
			} else {
				echo '<div id="success-message">Success! Your portfolio has been submitted with email .</div>';
				echo '<script>
						setTimeout(function() {
							document.getElementById("success-message").style.display = "none";
						}, 5000); // Hide after 5 seconds
						</script>';
			}
		}
	}
	die();
}
add_action( 'wp_ajax_portfolio_submission', 'process_portfolio_submission' );
add_action( 'wp_ajax_nopriv_portfolio_submission', 'process_portfolio_submission' );

/**
 * Adds a submenu page to the custom AJAX plugin settings.
 */
function my_plugin_submenu() {

		add_submenu_page(
			'custom-ajax-plugin-settings',
			'Submenu Page Title',       // Page title
			esc_html__( 'Submenu Menu Title', 'customkm-menu' ),       // Menu title
			'manage_options',           // Capability
			'customkm-submenu-slug',    // Submenu slug
			'my_plugin_page_content'  // Callback function for submenu content
		);
}
add_action( 'admin_menu', 'my_plugin_submenu' );

/**
 * Callback function to render plugin page content.
 */
function my_plugin_page_content() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Post Retrievals', 'customkm-menu' ); ?></h1>
		<p><?php echo esc_html__( 'Retrieve posts using the REST API', 'customkm-menu' ); ?></p>
		<?php
		// Retrieve posts using REST API
		$url      = home_url();
		$response = wp_remote_get( rest_url( 'wp/v2/portfolio' ) );
		// Check if request was successful
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			// Decode the JSON response body
			$posts = json_decode( wp_remote_retrieve_body( $response ), true );
			// Check if there are posts
			if ( ! empty( $posts ) ) {
				include_once plugin_dir_path( __FILE__ ) . 'templates/form.php';
				foreach ( $posts as $post ) {
					$query = new WP_Query(
						array(
							'post_type' => 'portfolio',
							'p'         => $post['id'],
						)
					); // Specify post type as portfolio
					while ( $query->have_posts() ) :
						$query->the_post(); // Using $query to iterate through posts
						// Get the post ID
						$post_id = get_the_ID();
						//print_r( $post_id );
						$client_name = get_post_meta( $post_id, 'client_name', true );
						$address     = get_post_meta( $post_id, 'address', true );
						$email       = get_post_meta( $post_id, 'email', true );
						$phone       = get_post_meta( $post_id, 'phone', true );
						$company     = get_post_meta( $post_id, 'company_name', true );
						include plugin_dir_path( __FILE__ ) . 'templates/post-row.php';
					endwhile;
					wp_reset_postdata(); // Reset post data
				}
				echo '</table>';
			} else {
				// No posts found message
				echo '<p>No posts found.</p>';
			}
		} else {
			// Error message if request was not successful
			echo '<p>An error occurred while retrieving posts.</p>';
		}
		?>
	</div>

	<?php
}

/**
 * Enqueue the external JavaScript file.
 */
function enqueue_delete_post_script() {
	// Create a nonce
	$nonce = wp_create_nonce( 'delete_post_nonce' );
	wp_enqueue_script( 'delete-post-js', plugins_url( 'js/delete-post.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	// Localize the script with the 'ajaxurl' and the nonce
	wp_localize_script(
		'delete-post-js',
		'delete_post_object',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => $nonce,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'enqueue_delete_post_script' );

/**
 * Handle AJAX request to delete post.
 */
add_action( 'wp_ajax_delete_post_action', 'delete_post_action_callback' );
function delete_post_action_callback() {
	check_ajax_referer( 'delete_post_nonce', 'nonce' );
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	// Check if user has permission to delete post
	if ( current_user_can( 'delete_post', $post_id ) ) {
		// Delete the post
		wp_delete_post( $post_id, true );
		echo 'success';
	} else {
		echo 'error';
	}

	// Always exit to avoid further execution
	wp_die();
}

/**
 * Callback function to return a simple response.
 */
function prefix_get_endpoint_phrase() {
	// rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
	return rest_ensure_response( 'Hello World, this is the WordPress REST API' );
}

/**
 * Registers routes for the example endpoint.
 */
function prefix_register_example_routes() {
	// register_rest_route() handles more arguments but we are going to stick to the basics for now.
	register_rest_route(
		'hello-world/v1',
		'/phrase',
		array(
			// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
			'methods'  => WP_REST_Server::READABLE,
			// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
			'callback' => 'prefix_get_endpoint_phrase',
		)
	);
}
add_action( 'rest_api_init', 'prefix_register_example_routes' );

/**
 * Adds a plugin page to the admin menu.
 */
function example_plugin_menu() {
	add_submenu_page(
		'custom-ajax-plugin-settings',
		'Example Plugin Page',    // Page title
		esc_html__( 'Plugin Page', 'customkm-menu' ),        // Menu title
		'manage_options',         // Capability
		'example-plugin',         // Menu slug
		'example_plugin_page',    // Callback function
		'dashicons-admin-page', // Icon
		27 // Position of the menu in the admin sidebar
	);
}
add_action( 'admin_menu', 'example_plugin_menu' );

// Plugin page content
function example_plugin_page() {
	wp_enqueue_style( 'plugin-custom-styles', plugin_dir_url( __FILE__ ) . 'css/plugin-styles.css' );
	include_once plugin_dir_path( __FILE__ ) . 'templates/example-plugin.php';
}

/**
 * Adds a submenu page to the custom AJAX plugin settings.
 */
function customkm_menu_page() {
	add_submenu_page(
		'custom-ajax-plugin-settings',
		'Customkm Menu',              // Page title
		esc_html__( 'Customkm Menu', 'customkm-menu' ),              // Menu title
		'manage_options',           // Capability
		'customkm-page-slug',         // Menu slug
		'customkm_page_content',      // Callback function
		'dashicons-admin-generic',  // Icon
		25                          // Position
	);
}
add_action( 'admin_menu', 'customkm_menu_page' );

/**
 * Custom page content for submenu.
 */
function customkm_page_content() {
	include_once plugin_dir_path( __FILE__ ) . 'templates/customkm-page.php';
}

/**
 * Saves data using Option API.
 */
add_action( 'init', 'data_save_table' );
function data_save_table() {
	if ( isset( $_POST['plugin_options_nonce'] ) && wp_verify_nonce( $_POST['plugin_options_nonce'], 'update_plugin_options' ) ) {
		$data_to_store = $_POST['name'];
		$key           = 'name';
		update_option( $key, $data_to_store );
	}
}

/**
 * Adds shortcode to fetch data.
 */
add_shortcode( 'fetch_data', 'fetch_data_shortcode' );
function fetch_data_shortcode() {
	$key        = 'name';                  // Specify the key used to save the data
	$saved_data = get_option( $key ); // Retrieve the saved data
	return $saved_data;             // Return the data
}

/**
 * Adds a submenu page using Settings API.
 */
function my_custom_submenu_page() {
	add_submenu_page(
		'options-general.php', // Parent menu slug
		'My Submenu Page', // Page title
		'My Submenu', // Menu title
		'manage_options', // Capability required to access
		'my-custom-submenu', // Menu slug
		'my_custom_submenu_callback' // Callback function to display content
	);
}
add_action( 'admin_menu', 'my_custom_submenu_page' );

/**
 * Callback function to display submenu page content.
 */
function my_custom_submenu_callback() {
	include_once plugin_dir_path( __FILE__ ) . 'templates/custom-submenu.php';
}

/**
 * Registers settings and fields.
 */
function my_custom_settings_init() {
	register_setting(
		'my-custom-settings-group', // Option group
		'my_option_name', // Option name
		'my_sanitize_callback' // Sanitization callback function
	);

	add_settings_section(
		'my-settings-section', // Section ID
		'My Settings Section', // Section title
		'my_settings_section_callback', // Callback function to display section description (optional)
		'my-custom-settings-group' // Parent page slug
	);

	add_settings_field(
		'my-setting-field', // Field ID
		'My Setting Field', // Field title
		'my_setting_field_callback', // Callback function to display field input
		'my-custom-settings-group', // Parent page slug
		'my-settings-section' // Section ID
	);
}
add_action( 'admin_init', 'my_custom_settings_init' );

/**
 * Callback function to display section description (optional).
 */
function my_settings_section_callback() {
	echo '<p>This is a description of my settings section.</p>';
}

/**
 * Callback function to display field input.
 */
function my_setting_field_callback() {
	$option_value = get_option( 'my_option_name' );
	?>
	<input type="text" name="my_option_name" value="<?php echo esc_attr( $option_value ); ?>">
	<?php
}

/**
 * Sanitization callback function.
 */
function my_sanitize_callback( $input ) {
	return sanitize_text_field( $input );
}

/**
 * Loads text domain for localization.
 */
function load_customkm_menu_textdomain() {
	load_plugin_textdomain( 'customkm-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'load_customkm_menu_textdomain' );

/**
 * Includes necessary files.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-class.php';