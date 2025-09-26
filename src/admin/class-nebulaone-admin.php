<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gocloudforce.com/
 * @since      1.0.0
 *
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/admin
 * @author     Cloudforce <support@gocloudforce.com>
 */
class NebulaOne_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Add admin menu page.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Register plugin settings.
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );

        // Enqueue admin scripts and styles.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Display admin notices for terms and conditions.
        add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );

        // Handle AJAX request for terms acceptance.
        add_action( 'wp_ajax_nebulaone_accept_terms', array( $this, 'handle_terms_acceptance' ) );
    }

    /**
     * Add an options page in the WordPress admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            esc_html__( 'nebulaONE AI Embed Settings', 'nebulaone-embed' ), // Page title
            esc_html__( 'nebulaONE AI Embed', 'nebulaone-embed' ),          // Menu title
            'manage_options',                                              // Capability
            'nebulaone-embed-settings',                                    // Menu slug
            array( $this, 'render_settings_page' )                         // Callback function
        );
    }

    /**
     * Renders the settings page in the admin dashboard.
     *
     * @since    1.0.0
     */
    public function render_settings_page() {
        $terms_accepted = get_option( 'nebulaone_terms_accepted' ) === 'yes';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'nebulaONE AI Embed Settings', 'nebulaone-embed' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'nebulaone-embed-settings-group' );
                do_settings_sections( 'nebulaone-embed-settings-group' );
                ?>
                <table class="form-table nebulaOne-settings-table">
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e( 'Host Name', 'nebulaone-embed' ); ?>
                            <span class="nebulaone-help" data-help="<?php esc_attr_e( 'Enter the full URL of the nebulaONE AI host, including https://.', 'nebulaone-embed' ); ?>">?</span>
                        </th>
                        <td><input type="text" name="nebulaone_host_name" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr( get_option( 'nebulaone_host_name' ) ); ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e( 'GPT System', 'nebulaone-embed' ); ?>
                            <span class="nebulaone-help" data-help="<?php esc_attr_e( 'Specify the GPT system endpoint configured under the Publish GPT Section.', 'nebulaone-embed' ); ?>">?</span>
                        </th>
                        <td><input type="text" name="nebulaone_gpt_system" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr( get_option( 'nebulaone_gpt_system' ) ); ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e( 'Title', 'nebulaone-embed' ); ?>
                            <span class="nebulaone-help" data-help="<?php esc_attr_e( 'Provide a title for the Chat Pop-up window, which will be displayed on your site.', 'nebulaone-embed' ); ?>">?</span>
                        </th>
                        <td><input type="text" name="nebulaone_title" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr( get_option( 'nebulaone_title' ) ); ?>" required /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e( 'Align button to left', 'nebulaone-embed' ); ?>
                            <span class="nebulaone-help" data-help="<?php esc_attr_e( 'Check this box to align the chat button to the left side of the screen.', 'nebulaone-embed' ); ?>">?</span>
                        </th>
                        <td><input type="checkbox" name="nebulaone_use_alt_script" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="1" <?php checked( 1, get_option( 'nebulaone_use_alt_script' ), true ); ?> /></td>
                    </tr>
                </table>
                <?php submit_button( null, 'primary', 'submit', $terms_accepted ? false : true ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_plugin_settings() {
        register_setting( 'nebulaone-embed-settings-group', 'nebulaone_host_name' );
        register_setting( 'nebulaone-embed-settings-group', 'nebulaone_gpt_system' );
        register_setting( 'nebulaone-embed-settings-group', 'nebulaone_title' );
        register_setting( 'nebulaone-embed-settings-group', 'nebulaone_use_alt_script' );
    }

    /**
     * Enqueue admin-specific scripts and styles.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style( 'nebulaone-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', array(), '1.0.0', 'all' );
        wp_enqueue_script( 'nebulaone-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin-script.js', array( 'jquery' ), '1.0.0', true );

        // Pass data to admin script.
        wp_localize_script(
            'nebulaone-admin-script',
            'nebulaOneAdmin',
            array(
                'termsAccepted' => get_option( 'nebulaone_terms_accepted' ) === 'yes',
                'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'nebulaone_accept_terms_nonce' ),
            )
        );
    }

    /**
     * Display the terms and conditions notice.
     *
     * @since    1.0.0
     */
    public function display_admin_notice() {
        if ( get_option( 'nebulaone_terms_accepted' ) !== 'yes' ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php printf( __( 'Please read and accept the <a href="#" id="nebulaone-terms-link">Terms and Conditions</a> to use the %s.', 'nebulaone-embed' ), 'nebulaONE AI Embed Plugin' ); ?></p>
                <p>
                    <button id="nebulaone-accept-terms" class="button button-primary"><?php esc_html_e( 'Accept Terms', 'nebulaone-embed' ); ?></button>
                </p>
            </div>
            <div id="nebulaone-terms-modal" style="display:none;">
                <h2><?php esc_html_e( 'Terms and Conditions', 'nebulaone-embed' ); ?></h2>
                <p>
                    <?php
                    echo wp_kses_post(
                        __(
                            '<b>nebulaONE® WordPress Plugin Usage Agreement</b><br /><br />
                            By clicking "Agree," you accept the terms of this nebulaONE® WordPress Plugin
                            Usage Agreement ("Agreement") for the use of our WordPress plugin ("Plugin")
                            designed for deploying nebulaONE® GPT Systems to WordPress websites.<br /><br />
                            <ol>
                            <li>
                                <b>Prerequisite License</b><br />
                                You must have a valid license and have accepted our End User License
                                Agreement ("EULA") for the nebulaONE® software ("Licensed Software") to use
                                this Plugin. If you do not have an active license, please visit our [Azure
                                Marketplace listing] for more information.
                            </li>
                            <br />
                            <li>
                                <b>Grant of License</b><br />
                                You are granted a non-exclusive, non-transferable license to use the Plugin
                                solely with the nebulaONE® Licensed Software, subject to compliance with the
                                EULA.
                            </li>
                            <br />
                            <li>
                                <b>Restrictions</b><br />
                                Modifying, distributing, selling, or sublicensing the Plugin is prohibited.
                                Use the Plugin only as authorized with the nebulaONE® Licensed Software.
                            </li>
                            <br />
                            <li>
                                <b>Disclaimer of Warranties</b><br />
                                The Plugin is provided "as-is" without any warranties. All express or
                                implied warranties, including merchantability and fitness for a particular
                                purpose, are disclaimed.
                            </li>
                            <br />
                            <li>
                                <b>Limitation of Liability</b><br />
                                We are not liable for any damages arising from the use or inability to use
                                the Plugin, even if advised of such possibilities. By using this Plugin, you
                                accept that Cloudforce is not liable for any damage caused by malicious
                                actors gaining access to your website via the Plugin.
                            </li>
                            <br />
                            <li>
                                <b>Termination</b><br />
                                This Agreement terminates automatically if you fail to comply with its
                                terms. You must cease using the Plugin upon termination.
                            </li>
                            <br />
                            <li>
                                <b>Governing Law</b><br />
                                This Agreement is governed by applicable local laws.
                            </li>
                            <br />
                            <li>
                                <b>Privacy Policy</b><br />
                                By using the Plugin, you agree to our
                                <a href="https://gocloudforce.com/terms-of-service/">Privacy Policy</a
                                >.<br />
                            </li>
                            </ol>

                            More details can be found at <a href="https://gocloudforce.com/">our website.</a
                            ><br /><br />
                            By clicking "Agree," you confirm that you have read and understood this
                            Agreement and agree to its terms.<br />
                            ',
                            'nebulaone-embed'
                        )
                    );
                    ?>
                </p>
                <button id="nebulaone-close-terms" class="button"><?php esc_html_e( 'Close', 'nebulaone-embed' ); ?></button>
            </div>
            <?php
        }
    }

    /**
     * Handle the terms acceptance via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_terms_acceptance() {
        check_ajax_referer( 'nebulaone_accept_terms_nonce', 'nonce' );

        if ( current_user_can( 'manage_options' ) ) {
            update_option( 'nebulaone_terms_accepted', 'yes' );
            wp_send_json_success();
        } else {
            wp_send_json_error( 'Unauthorized' );
        }
        wp_die();
    }
}