<?php
/**
 * Plugin Name: Comment Popularity - BuddyPress
 * Plugin URI: https://comment-popularity.hmn.md
 * Description: An add-on for Comment Popularity that integrates with BuddyPress. Send voting activity to your activity stream.
 * Version: 0.1.0
 * Author: Human Made Limited
 * Author URI: https://hmn.md
 * License: GPLv2+
 * Text Domain: comment-popularity-buddypress
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Human Made Ltd (https://hmn.md/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

defined( 'ABSPATH' ) || exit;

// Check PHP version. We need at least 5.3.2.
if ( version_compare( phpversion(), '5.3.2', '<' ) ) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	wp_die( sprintf( __( 'This plugin requires PHP Version %s. Sorry about that.', 'comment-popularity-buddypress' ), '5.3.2' ), 'Comment Popularity BuddyPress', array( 'back_link' => true ) );
}

// Main plugin class
require_once plugin_dir_path( __FILE__ ) . 'inc/class-comment-popularity-buddypress.php';

register_activation_hook( __FILE__, array( 'HMN_Comment_Popularity_BuddyPress', 'activate' ) );

// Only load code that needs BuddyPress to run once BP is loaded and initialized.
function hmn_cpbp_init() {

	add_action( 'plugins_loaded', array( 'HMN_Comment_Popularity_BuddyPress', 'get_instance' ) );

}
add_action( 'bp_include', 'hmn_cpbp_init' );
