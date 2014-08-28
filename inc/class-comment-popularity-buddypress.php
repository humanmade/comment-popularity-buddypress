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

		add_action( 'hmn_cp_comment_vote', array( $this, 'log_voting_to_activity_stream' ), 10, 3 );

		add_action( 'bp_register_activity_actions', array( $this, 'register_plugin_activity_actions' ) );

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

	public function log_voting_to_activity_stream( $user_id, $comment_id, $action ) {

		$comment = get_comment( $comment_id );
		$user_name    = self::get_comment_author( $comment, 'name' );
		$post_id      = $comment->comment_post_ID;
		$post_title   = ( $post = get_post( $post_id ) ) ? "\"$post->post_title\"" : __( 'a post', 'comment-popularity-stream' );

		$activity_id = bp_activity_add(
			array(
				'action' => __( 'Vote on a comment', 'comment-popularity-buddypress' ),
				'content' => sprintf( _x(
					'Voted on %1$s\'s comment on %2$s ( %3$s )',
					'1: Comment author, 2: Post name, 3: Vote Type',
					'comment-popularity-buddypress'
				), $user_name, $post_title, $action ),
				'component' => 'activity',
				'type' => 'activity_update'
			)
		);

		return $activity_id;
	}

	public function register_plugin_activity_actions() {

		$bp = buddyPress();

		$bp->bp_plugin = new stdClass();
		$bp->bp_plugin->id = 'comment_popularity';

		// Bail if activity is not active
		if ( ! bp_is_active( 'activity' ) )
			return false;

		bp_activity_set_action( $bp->bp_plugin->id, 'comment_popularity_update', __( 'Comment vote' ) );

	}

	/**
	 * Fetches the comment author and returns the specified field.
	 *
	 * This also takes into consideration whether or not the blog requires only
	 * name and e-mail or that users be logged in to comment. In either case it
	 * will try to see if the e-mail provided does belong to a registered user.
	 *
	 * @param  object|int  $comment  A comment object or comment ID
	 * @param  string      $field    What field you want to return
	 * @return int|string  $output   User ID or user display name
	 */
	public static function get_comment_author( $comment, $field = 'id' ) {
		$comment = is_object( $comment ) ? $comment : get_comment( absint( $comment ) );

		$req_name_email = get_option( 'require_name_email' );
		$req_user_login = get_option( 'comment_registration' );

		$user_id   = 0;
		$user_name = __( 'Guest', 'comment-popularity-buddypress' );

		if ( $req_name_email && isset( $comment->comment_author_email ) && isset( $comment->comment_author ) ) {
			$user      = get_user_by( 'email', $comment->comment_author_email );
			$user_id   = isset( $user->ID ) ? $user->ID : 0;
			$user_name = isset( $user->display_name ) ? $user->display_name : $comment->comment_author;
		}

		if ( $req_user_login ) {
			$user      = wp_get_current_user();
			$user_id   = $user->ID;
			$user_name = $user->display_name;
		}

		if ( 'id' === $field ) {
			$output = $user_id;
		} elseif ( 'name' === $field ) {
			$output = $user_name;
		}

		return $output;
	}

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
