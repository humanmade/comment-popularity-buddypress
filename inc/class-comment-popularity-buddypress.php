<?php

/**
 * Class HMN_Comment_Popularity_BuddyPress
 */
class HMN_Comment_Popularity_BuddyPress {

	/**
	 * Plugin version number.
	 */
	const HMN_CPBP_PLUGIN_VERSION = '0.1.0';

	/**
	 * The minimum PHP version compatibility.
	 */
	const HMN_CPBP_REQUIRED_PHP_VERSION = '5.3.2';

	const HMN_CPBP_REQUIRED_WP_VERSION = '3.8.4';

	/**
	 * The instance of HMN_Comment_Popularity_BuddyPress.
	 *
	 * @var the single class instance.
	 */
	private static $instance;

	/**
	 * Creates a new HMN_Comment_Popularity object, and registers with WP hooks.
	 */
	private function __construct() {


	}

	/**
	 * Run checks on plugin activation.
	 */
	public static function activate() {

		if ( ! class_exists( 'HMN_Comment_Popularity' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'This plugin requires Comment Popularity. Sorry about that.', 'comment-popularity-buddypress' ), 'Comment Popularity BuddyPress', array( 'back_link' => true ) );
		}

		global $wp_version;

		if ( version_compare( $wp_version, self::HMN_CPBP_REQUIRED_WP_VERSION, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( 'This plugin requires WordPress version %s. Sorry about that.', 'comment-popularity-buddypress' ), self::HMN_CPBP_REQUIRED_WP_VERSION ), 'Comment Popularity BuddyPress', array( 'back_link' => true ) );
		}
	}

	/**
	 * Disallow object cloning
	 */
	private function __clone() {}

	/**
	 * Provides access to the class instance
	 *
	 * @return HMN_Comment_Popularity
	 */
	public static function get_instance() {

		if ( ! self::$instance instanceof HMN_Comment_Popularity_BuddyPress ) {
			self::$instance = new HMN_Comment_Popularity_BuddyPress();

		}

		return self::$instance;
	}

	/**
	 * Load the Javascripts
	 */
	public function enqueue_scripts() {}

	/**
	 * Loads the plugin language files.
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$hmn_cp_lang_dir = basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/';
		$hmn_cp_lang_dir = apply_filters( 'hmn_cpbp_languages_directory', $hmn_cp_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'comment-popularity-buddypress' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'comment-popularity-buddypress', $locale );

		// Setup paths to current locale file
		$mofile_local  = $hmn_cp_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/comment-popularity-buddypress/' . $mofile;

		if ( file_exists( $mofile_global ) ) {

			// Look in global /wp-content/languages/comment-popularity folder
			load_textdomain( 'comment-popularity-buddypress', $mofile_global );

		} elseif ( file_exists( $mofile_local ) ) {

			// Look in local /wp-content/plugins/comment-popularity-buddypress/languages/ folder
			load_textdomain( 'comment-popularity-buddypress', $mofile_local );

		} else {

			// Load the default language files
			load_plugin_textdomain( 'comment-popularity-buddypress', false, $hmn_cp_lang_dir );

		}
	}

}
