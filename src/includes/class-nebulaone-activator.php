<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://gocloudforce.com/
 * @since      1.0.0
 *
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/includes
 */

/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    NebulaOne_Embed
 * @subpackage NebulaOne_Embed/includes
 * @author     Cloudforce <support@gocloudforce.com>
 */
class NebulaOne_Activator {

    /**
     * Short Description. (e.g. Set default options or create custom tables)
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Set 'nebulaone_terms_accepted' option to 'no' if it doesn't exist.
        add_option( 'nebulaone_terms_accepted', 'no' );
    }

}