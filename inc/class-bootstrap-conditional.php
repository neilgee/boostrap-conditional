<?php
/**
 * Bootstrap Conditional
 *
 * @since  1.0.0
 *
 * @category  WordPress_Plugin
 * @package   Bootstrap Conditional
 * @author    Neil Gowran
 * @link      https://wordpress.org/plugins/bootstrap-conditional/
 */

/**
 * Main plugin class
 *
 * @since  1.0.0
 */
class Bootstrap_Conditional{
	/**
	 * Bootstrap Conditional version
	 *
	 * @var version
	 */
	public $bl_version = '1.3.0';
	/**
	 * Holds an instance of the object
	 *
	 * @var Bootstrap_Conditional
	 */
	protected static $instance = null;
	/**
	 * Returns the running object
	 *
	 * @return Bootstrap_Conditional
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Init plugin
	 */
	public function __construct() {
		// Nothing here.
	}

	/**
	 * Initiate hooks
	 */
	public function hooks() {
		// Plugin text domain.
		// add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ),  );
		add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
		// add_action( 'admin_menu', array( $this, 'plugin_page' ) );
		// add_action( 'admin_init', array( $this, 'plugin_settings' ) );

		// WP 3.0+.
		add_action( 'add_meta_boxes', array( $this, 'post_options_metabox' ) );
		add_action( 'save_post', array( $this, 'bcmeta_save' ) );
	}

	/**
	 * Add the bootstrap option metabox to all post types
	 */
	public function post_options_metabox() {
		add_meta_box( 'post_options_bl', __( 'Load Bootstrap', 'bootstrap-conditional' ), array( $this, 'bcmeta_create' ), get_post_types(), 'side', 'low' );
	}



	/**
	 * Setup JavaScript and CSS
	 */
	public function enqueue_scripts() {

		/* Get the current post ID. */
		$post_id = get_the_ID();
		$add_popper = get_post_meta( $post_id, '_bootstrap_check_popper', true );
		$value_version = get_post_meta( $post_id, '_bootstrap_check_version', true );

		if( $add_popper !=='' && is_singular() && $value_version !=='5' ){
			wp_enqueue_script( 'popper', plugin_dir_url( dirname( __FILE__ ) ) . 'js/popper.min.js', array('jquery'), '1.16.1', true );
			wp_enqueue_script( 'popper_init', plugin_dir_url( dirname( __FILE__ ) ) . 'js/popper-init.js', array( 'popper'), $this->bl_version, true );
		}
		if( $add_popper !=='' && is_singular() && $value_version =='5' ){
			wp_enqueue_script( 'popper2.6.0', plugin_dir_url( dirname( __FILE__ ) ) . 'js/popper.2.9.0.min.js', array(), '2.6.0', true );
		}

		if( $value_version =='None' ){
			return;
		}
		elseif( $value_version =='3' && is_singular() ){
			wp_enqueue_script( 'bootstrap-3', plugin_dir_url( dirname( __FILE__ ) ) . 'js/bootstrap-3.min.js', array(), '3.4.1', true );
			wp_enqueue_style( 'bootstrap-3', plugin_dir_url( dirname( __FILE__ ) ) . 'css/bootstrap-3.min.css', array(), '3.4.1', 'all' );
		}
		elseif( $value_version =='4' && is_singular() ){
			wp_enqueue_script( 'bootstrap-4', plugin_dir_url( dirname( __FILE__ ) ) . 'js/bootstrap-4.min.js', array(), '4.6.0', true );
			wp_enqueue_style( 'bootstrap-4', plugin_dir_url( dirname( __FILE__ ) ) . 'css/bootstrap-4.min.css', array(), '4.6.0', 'all' );
		}
		elseif ( $value_version =='5' && is_singular() ){
			wp_enqueue_script( 'bootstrap-5', plugin_dir_url( dirname( __FILE__ ) ) . 'js/bootstrap-5.min.js', array(), '5.0.0', true );
			wp_enqueue_style( 'bootstrap-5', plugin_dir_url( dirname( __FILE__ ) ) . 'css/bootstrap-5.min.css', array(), '5.0.0', 'all' );
		}

		if( $add_popper !=='' && is_singular() && $value_version =='5' ){
			wp_enqueue_script( 'popper_init5', plugin_dir_url( dirname( __FILE__ ) ) . 'js/popper-init.5.js', array( 'popper2.6.0'), $this->bl_version, true );
		}
	}

	/**
	 * Dequeue BeaverBuilder Bootstrap minimal CSS
	 */
	public function print_styles() {

		/* Get the current post ID. */
		$post_id = get_the_ID();
		$theme = wp_get_theme(); // gets the current theme
		$value_version = get_post_meta( $post_id, '_bootstrap_check_version', true );

		if( $value_version =='None' ){
			return;
		}
		elseif ( 'Beaver Builder Theme' == $theme->parent_theme && is_singular() ) {
			wp_dequeue_style( 'base' );
        	wp_deregister_style( 'base' );
			wp_dequeue_style( 'base-4' );
        	wp_deregister_style( 'base-4' );
			//echo 'You are BB';
		}
	}
	
	/**
	 * Create Bootstrap Meta
	 *
	 * @link https://gist.github.com/emilysnothere/943ea6274dc160cec271
	 */
	public function bcmeta_create() {
		$post_id = get_the_ID();
		$value_popper = get_post_meta( $post_id, '_bootstrap_check_popper', true );
		$value_version = get_post_meta( $post_id, '_bootstrap_check_version', true );
		wp_nonce_field( 'bootstrap_nonce_' . $post_id, 'bootstrap_nonce' );
		?>
		<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="_bootstrap_check_version">Choose Bootstrap Version :  </label></p>
		<select name="_bootstrap_check_version" id="_bootstrap_check_version">
			<option value="None"<?php selected( $value_version, "None", ); ?> >No Bootstrap</option>
			<option value="3"<?php selected( $value_version, "3" ); ?> >Bootstrap 3</option>
			<option value="4"<?php selected( $value_version, "4" ); ?> >Bootstrap 4</option>
			<option value="5"<?php selected( $value_version, "5" ); ?> >Bootstrap 5</option>
		</select>

		<div class="misc-pub-section misc-pub-section-last">
			<label class="selectit">
				<input type="checkbox" value="1" <?php checked( $value_popper, true, true ); ?> name="_bootstrap_check_popper" /><?php esc_attr_e( 'Add PopperJS', 'bootstrap-conditional' ); ?>
			</label>
		</div>

		<?php

		//var_dump($value_version);
	}

	/**
	 * Save Bootstrap Conditional Meta
	 *
	 * @param int $post_id post ID.
	 */
	public function bcmeta_save( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$bootstrap_nonce = filter_input( INPUT_POST, 'bootstrap_nonce', FILTER_SANITIZE_STRING );
		if ( ! $bootstrap_nonce || ! wp_verify_nonce( $bootstrap_nonce, 'bootstrap_nonce_' . $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$bootstrap_check = filter_input( INPUT_POST, '_bootstrap_check', FILTER_SANITIZE_STRING );
		if ( $bootstrap_check ) {
			update_post_meta( $post_id, '_bootstrap_check', $bootstrap_check );
		} else {
			delete_post_meta( $post_id, '_bootstrap_check' );
		}

		$bootstrap_check_popper = filter_input( INPUT_POST, '_bootstrap_check_popper', FILTER_SANITIZE_STRING );
		if ( $bootstrap_check_popper ) {
			update_post_meta( $post_id, '_bootstrap_check_popper', $bootstrap_check_popper );
		} else {
			delete_post_meta( $post_id, '_bootstrap_check_popper' );
		}

		$bootstrap_check_version = filter_input( INPUT_POST, '_bootstrap_check_version', FILTER_SANITIZE_STRING );
		if ( $bootstrap_check_version ) {
			update_post_meta( $post_id, '_bootstrap_check_version', $bootstrap_check_version );
		} else {
			delete_post_meta( $post_id, '_bootstrap_check_version' );
		}
	}

}

/**
 * Helper function to get/return the Bootstrap_Conditional object
 *
 * @return Bootstrap_Conditionalobject
 */
function bootstrap_conditional() {
	return Bootstrap_Conditional::get_instance();
}