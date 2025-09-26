<?php
/**
 * Fired during plugin updates from GitHub.
 *
 * @link       https://gocloudforce.com/
 * @since      1.0.0
 *
 * @package    NebulaOne_AI
 * @subpackage NebulaOne_AI/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * GitHub Plugin Updater Class
 *
 * This class handles checking for updates to the plugin on GitHub.
 */
class NebulaOne_AI_Updater {

    private $file;
    private $plugin_slug;
    private $basename;
    private $active;
    private $username;
    private $repository;
    private $authorize_token;
    private $branch; // New property to store the branch name
    private $plugin_data;
    private $github_response;

    /**
     * Constructor.
     *
     * @param string $file The plugin file path.
     * @param string $username The GitHub username/organization.
     * @param string $repository The GitHub repository name.
     * @param string $authorize_token (Optional) Personal Access Token.
     * @param string $branch (Optional) The branch to monitor for releases/tags. Defaults to 'main'.
     */
    public function __construct( $file, $username, $repository, $authorize_token = '', $branch = 'main' ) {
        $this->file            = $file;
        $this->username        = $username;
        $this->repository      = $repository;
        $this->authorize_token = $authorize_token;
        $this->branch          = $branch; // Store the branch name

        $this->basename    = plugin_basename( $this->file );
        $this->plugin_slug = basename( dirname( $this->file ) );
        $this->active      = is_plugin_active( $this->basename );

        add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
        add_filter( 'site_transient_update_plugins', array( $this, 'update_plugins_filter' ) );
        add_filter( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );
    }

    /**
     * Get plugin data from WordPress.
     *
     * @return array
     */
    private function get_plugin_data() {
        if ( ! isset( $this->plugin_data ) ) {
            $this->plugin_data = get_plugin_data( $this->file );
        }
        return $this->plugin_data;
    }

    /**
     * Get data from GitHub releases.
     *
     * IMPORTANT: The GitHub `releases/latest` API endpoint always returns the latest *tag-based release*
     * across ALL branches. To truly "test from a branch" you must create your releases (tags)
     * *on* or *from* your specified branch (`main` in this case).
     *
     * If you wanted to get the latest commit from a branch (not a release), the API call
     * and subsequent parsing would be different (e.g., `https://api.github.com/repos/%s/%s/commits/%s`).
     * This current implementation focuses on standard GitHub Releases.
     *
     * @return object|false
     */
    private function get_github_response() {
        if ( ! isset( $this->github_response ) ) {
            $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );

            $args = array(
                'timeout' => 10,
                'headers' => array( 'Accept' => 'application/vnd.github.v3+json' )
            );

            if ( $this->authorize_token ) {
                $args['headers']['Authorization'] = 'token ' . $this->authorize_token;
            }

            $response = wp_remote_get( $request_uri, $args );

            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                error_log( 'NebulaOne AI Updater: GitHub API Error: ' . print_r( $response, true ) ); // For debugging
                $this->github_response = false;
            } else {
                $this->github_response = json_decode( wp_remote_retrieve_body( $response ) );
            }
        }
        return $this->github_response;
    }

    /**
     * Filter the plugin update transient.
     *
     * @param object $transient
     * @return object
     */
    public function update_plugins_filter( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $plugin_data = $this->get_plugin_data();
        $github_response = $this->get_github_response();
        
        $is_update_available = false;
        if ($github_response && is_object($github_response)) {
            $local_ver = preg_replace('/[^0-9\.]/', '', $plugin_data['Version']);
            $github_ver = preg_replace('/[^0-9\.]/', '', $github_response->tag_name);
            
            if ( version_compare( $github_ver, $local_ver, '>' ) ) {
                $is_update_available = true;
            }
        }

        if ( $is_update_available ) {
            $new_version_info = new stdClass();
            $new_version_info->slug        = $this->plugin_slug;
            $new_version_info->new_version = $github_response->tag_name;
            $new_version_info->url         = $plugin_data['PluginURI'];

            $package_url = $github_response->zipball_url; 

            $zip_asset = null;
            if ( isset( $github_response->assets ) && is_array( $github_response->assets ) ) {
                foreach ( $github_response->assets as $asset ) {
                    if ( isset( $asset->browser_download_url ) && substr( $asset->name, -4 ) === '.zip' ) {
                        $zip_asset = $asset;
                        break;
                    }
                }
            }

            if ( $zip_asset ) {
                $package_url = $zip_asset->browser_download_url;
            }
            
            $new_version_info->package = $package_url;
            $transient->response[ $this->basename ] = $new_version_info;
        }

        return $transient;
    }

    /**
     * Filter the plugin details for the "View details" link.
     *
     * @param bool|object $result
     * @param string $action
     * @param object $args
     * @return bool|object
     */
    public function plugins_api_filter( $result, $action, $args ) {
        if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
            return $result;
        }

        $github_response = $this->get_github_response();

        if ( ! $github_response ) {
            return $result;
        }

        $plugin_data = $this->get_plugin_data();

        $result = new stdClass();
        $result->name              = $plugin_data['Name'];
        $result->slug              = $this->plugin_slug;
        $result->version           = $github_response->tag_name;
        $result->author            = $plugin_data['Author'];
        $result->author_profile    = $plugin_data['AuthorURI'];
        $result->last_updated      = $github_response->published_at;
        $result->homepage          = $plugin_data['PluginURI'];

        $download_link = $github_response->zipball_url;
        $zip_asset = null;
        if ( isset( $github_response->assets ) && is_array( $github_response->assets ) ) {
            foreach ( $github_response->assets as $asset ) {
                if ( isset( $asset->browser_download_url ) && substr( $asset->name, -4 ) === '.zip' ) {
                    $zip_asset = $asset;
                    break;
                }
            }
        }
        if ( $zip_asset ) {
            $download_link = $zip_asset->browser_download_url;
        }

        $result->download_link     = $download_link;


        $result->banners           = array();
        $result->icons             = array();

        $result->sections = array(
            'description' => $plugin_data['Description'],
            'changelog'   => wp_kses_post( $github_response->body ) ?: 'No changelog provided in this release.',
        );

        if ( preg_match( '/Tested up to:\s*([\d\.]+)/i', $github_response->body, $matches ) ) {
            $result->tested = $matches[1];
        }
        if ( preg_match( '/Requires at least:\s*([\d\.]+)/i', $github_response->body, $matches ) ) {
            $result->requires = $matches[1];
        } else {
            $result->requires = isset( $plugin_data['RequiresWP'] ) ? $plugin_data['RequiresWP'] : null;
        }

        return $result;
    }

    /**
     * After update, if this plugin was updated, deactivate and reactivate it to ensure code runs.
     *
     * @param WP_Upgrader $upgrader
     * @param array $options
     */
    public function after_update( $upgrader, $options ) {
        if ( $this->active && 'update' == $options['action'] && 'plugin' == $options['type'] ) {
            foreach( $options['plugins'] as $plugin ) {
                if ( $plugin === $this->basename ) {
                    deactivate_plugins( $plugin );
                    activate_plugin( $plugin );
                    break;
                }
            }
        }
    }
}