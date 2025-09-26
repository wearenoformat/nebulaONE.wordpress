<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://gocloudforce.com/
 * @since      1.0.0
 *
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/public
 * @author     Cloudforce <support@gocloudforce.com>
 */
class NebulaOne_Public {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue public-facing scripts and styles.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue if terms are accepted and required options are set.
        if ( get_option( 'nebulaone_terms_accepted' ) !== 'yes' ) {
            return;
        }

        $host_name      = rtrim( esc_url_raw( get_option( 'nebulaone_host_name' ) ), '/' );
        $gpt_system     = sanitize_text_field( get_option( 'nebulaone_gpt_system' ) );
        $title          = sanitize_text_field( get_option( 'nebulaone_title' ) );
        $use_alt_script = (bool) get_option( 'nebulaone_use_alt_script' ); // This value determines alignment

        // Check if all required options are set.
        if ( empty( $host_name ) || empty( $gpt_system ) || empty( $title ) ) {
            return;
        }

        $script_file = 'script.js'; // Always load script.js
        $script_url  = plugin_dir_url( __FILE__ ) . 'js/' . $script_file;

        wp_register_script( 'nebulaone-embed-main', $script_url, array(), '1.0.0', true );

        // Data to pass to the JavaScript.
        $script_data = array(
            'hostName'      => $host_name,
            'gptSystem'     => $gpt_system,
            'title'         => $title,
            'pluginBaseUrl' => plugins_url( 'public', dirname( __FILE__ ) ), // Corrected path confirmed by you
            'alignLeft'     => $use_alt_script, // Pass the boolean for alignment
        );

        // Add the inline script to be executed before the main script.
        wp_add_inline_script(
            'nebulaone-embed-main',
            'const nebulaInstance = ' . wp_json_encode( $script_data ) . ';',
            'before'
        );

        wp_enqueue_script( 'nebulaone-embed-main' );
        wp_enqueue_style( 'nebulaone-embed-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.0.0', 'all' );
    }
}