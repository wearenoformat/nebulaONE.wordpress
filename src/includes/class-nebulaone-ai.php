<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    NebulaOne_AI
 * @subpackage NebulaOne_AI/includes
 * @author     Cloudforce
 */
class NebulaOne_AI {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     * (Placeholder - if you use a separate 'loader' class).
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version.
     * Load dependencies, and set the hooks for the admin and public sides.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Define plugin constants based on main plugin file headers
        $plugin_data = get_file_data( plugin_dir_path( dirname( __FILE__ ) ) . 'nebulaone-ai-embed.php', array( 'Version' => 'Version', 'Text Domain' => 'Text Domain' ) );

        if ( defined( 'NEBULAONE_AI_VERSION' ) ) {
            $this->version = NEBULAONE_AI_VERSION;
        } else {
            $this->version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0';
        }
        $this->plugin_name = isset( $plugin_data['Text Domain'] ) ? $plugin_data['Text Domain'] : 'nebulaone-ai';


        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the files that are used to orchestrate the plugin,
     * as well as the classes for the admin and public-facing
     * side of the site.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         * (If you have a separate loader class, you'd include it here and instantiate it).
         * For now, we'll directly instantiate admin and public.
         */

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-nebulaone-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-nebulaone-public.php';

        // Note: class-nebulaone-activator.php is handled by register_activation_hook in main plugin file.
        // class-nebulaone-ai-updater.php is required in the main plugin file.
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new NebulaOne_Admin();
        // All hooks are already handled within the NebulaOne_Admin constructor,
        // so just instantiating it is enough.
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new NebulaOne_Public();
        // All hooks are already handled within the NebulaOne_Public constructor.
    }

    /**
     * Run the plugin.
     * This method is called from the main plugin file.
     *
     * @since    1.0.0
     */
    public function run() {
        // If you had a 'loader' class, you'd call its run method here.
        // For this setup, simply instantiating admin/public classes
        // in the constructor handles the hook registration.
    }

    /**
     * The name of the plugin used to uniquely identify it within WordPress.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}