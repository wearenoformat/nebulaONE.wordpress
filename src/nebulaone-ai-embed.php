<?php
/*
Plugin Name: NebulaOne AI Embed
Plugin URI: https://github.com/wearenoformat/N1AI
Description: A chat interface for NebulaOne AI, seamlessly integrated into your WordPress site.
Version: 1.0.3
Author: Cloudforce
Author URI: https://gocloudforce.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: nebulaone-ai
Domain Path: /languages

Update URI: https://raw.githubusercontent.com/wearenoformat/N1AI/main/update.json
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define the plugin root directory path for easier use
if ( ! defined( 'N1AI_PLUGIN_DIR' ) ) {
    define( 'N1AI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once N1AI_PLUGIN_DIR . 'includes/class-nebulaone-ai.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nebulaone-activator.php
 */
require_once N1AI_PLUGIN_DIR . 'includes/class-nebulaone-activator.php';
register_activation_hook( __FILE__, array( 'NebulaOne_Activator', 'activate' ) );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is initiated from this class,
 * calling it here will activate the plugin.
 */
function run_nebulaone_ai() {
    $plugin = new NebulaOne_AI();
    $plugin->run();
}
run_nebulaone_ai();

/**
 * GitHub Plugin Updater.
 * This class handles checking for updates to the plugin on GitHub.
 */
require_once N1AI_PLUGIN_DIR . 'includes/class-nebulaone-ai-updater.php';

// Initialize the plugin updater
add_action( 'init', 'nebulaone_ai_init_updater' );
function nebulaone_ai_init_updater() {
    $github_pat = '';
    $branch_to_monitor = 'main';

    new NebulaOne_AI_Updater( __FILE__, 'wearenoformat', 'N1AI', $github_pat, $branch_to_monitor );
}