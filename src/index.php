<?php
/*
Plugin Name: nebulaONE® WordPress Plugin
Description: A plugin to embed a nebulaONE® instance in your WordPress site.
Version: 1.1
Author: Cloudforce
*/

// Function to add an options page in the WordPress admin menu.
function nebulaone_embed_menu()
{
    // Adds a new menu item under "Settings" for the nebulaONE WordPress plugin settings page.
    add_options_page(
        'nebulaONE® Embed Settings',     // The text to be displayed in the title tags of the page when the menu is selected.
        'nebulaONE® Embed',              // The text to be used for the menu.
        'manage_options',                // Required capability to access this menu item.
        'nebulaone-embed-settings',      // The slug name for the settings page.
        'nebulaone_embed_settings_page'  // The function that renders the settings page.
    );
}
// Hooks the nebulaone_embed_menu function to the WordPress 'admin_menu' action.
add_action('admin_menu', 'nebulaone_embed_menu');

// Function to render the settings page in the admin dashboard.
function nebulaone_embed_settings_page()
{
    $terms_accepted = get_option('nebulaone_terms_accepted') === 'yes';
?>
    <div class="wrap">
        <h1>nebulaONE® Embed Settings</h1>
        <form method="post" action="options.php">
            <?php
            // Outputs security fields for the settings group.
            settings_fields('nebulaone-embed-settings-group');
            // Prints out all settings sections added to a particular settings group.
            do_settings_sections('nebulaone-embed-settings-group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">nebulaONE® Endpoint <span class="nebulaone-help" data-help="Enter the full URL of the nebulaONE® instance, including https://.">?</span></th>
                    <td><input type="text" name="nebulaone_host_name" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr(get_option('nebulaone_host_name')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Agent <span class="nebulaone-help" data-help="Specify the Agent endpoint configured under the Publish section.">?</span></th>
                    <td><input type="text" name="nebulaone_gpt_system" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr(get_option('nebulaone_gpt_system')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Title <span class="nebulaone-help" data-help="Provide a title for the Chat Pop-up window, which will be displayed on your site.">?</span></th>
                    <td><input type="text" name="nebulaone_title" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="<?php echo esc_attr(get_option('nebulaone_title')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Align button to left <span class="nebulaone-help" data-help="Check this box to align the chat button to the left side of the screen.">?</span></th>
                    <td><input type="checkbox" name="nebulaone_use_alt_script" <?php echo $terms_accepted ? '' : 'disabled'; ?> value="1" <?php checked(1, get_option('nebulaone_use_alt_script'), true); ?> /></td>
                </tr>
            </table>
            <?php submit_button(null, 'primary', 'submit', $terms_accepted ? false : true); ?>
        </form>
    </div>
    <?php
    if (!$terms_accepted) {
    ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var submitButton = document.querySelector('input[name="submit"]');
                submitButton.disabled = true;

                document.getElementById('nebulaone-accept-terms').addEventListener('click', function() {
                    submitButton.disabled = false;
                });
            });
        </script>
    <?php
    }
    ?>
    <style>
        .nebulaone-help {
            cursor: pointer;
            color: #0073aa;
            margin-left: 5px;
        }

        .nebulaone-help:hover {
            text-decoration: underline;
        }

        .nebulaone-help-tooltip {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
    </style>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var helpIcons = document.querySelectorAll('.nebulaone-help');
            var activeTooltip = null;

            helpIcons.forEach(function(icon) {
                icon.addEventListener('click', function(event) {
                    if (activeTooltip) {
                        activeTooltip.remove();
                        activeTooltip = null;
                    }

                    var tooltip = document.createElement('div');
                    tooltip.className = 'nebulaone-help-tooltip';
                    tooltip.innerText = icon.getAttribute('data-help');
                    document.body.appendChild(tooltip);
                    var rect = icon.getBoundingClientRect();
                    tooltip.style.left = rect.left + window.scrollX + 'px';
                    tooltip.style.top = rect.top + window.scrollY + 20 + 'px';
                    tooltip.style.display = 'block';
                    activeTooltip = tooltip;

                    event.stopPropagation();
                });
            });

            document.addEventListener('click', function() {
                if (activeTooltip) {
                    activeTooltip.remove();
                    activeTooltip = null;
                }
            });
        });
    </script>
    <?php
}

// Function to register settings for the plugin.
function nebulaone_embed_register_settings()
{
    register_setting('nebulaone-embed-settings-group', 'nebulaone_host_name');
    register_setting('nebulaone-embed-settings-group', 'nebulaone_gpt_system');
    register_setting('nebulaone-embed-settings-group', 'nebulaone_title');
    register_setting('nebulaone-embed-settings-group', 'nebulaone_use_alt_script');
}
// Hooks the registration function to the 'admin_init' action.
add_action('admin_init', 'nebulaone_embed_register_settings');

// Function to embed a script on the frontend if not in the admin area.
function nebulaone_embed_script()
{
    if (!is_admin()) {
        // Fetch and sanitize options set in the admin panel.
        $host_name = rtrim(esc_js(get_option('nebulaone_host_name')), '/');
        $gpt_system = esc_js(get_option('nebulaone_gpt_system'));
        $title = esc_js(get_option('nebulaone_title'));
        $use_alt_script = get_option('nebulaone_use_alt_script');

        // Checks if all required options are set.
        if ($host_name && $gpt_system && $title) {
            // Constructs the URL for the external script.
            $script_url = $host_name . ($use_alt_script ? '/embed/script-alt.js' : '/embed/script.js');
            // Registers the main external JavaScript file.
            wp_register_script('nebulaone-embed-main', $script_url, array(), null, true);

            // Constructs the inline JavaScript to pass configuration options to the script.
            $inline_script = "
            const nebulaInstance = {
                hostName: '$host_name',
                gptSystem: '$gpt_system',
                title: '$title'
            };
            ";
            // Adds the inline script to be executed before the main script.
            wp_add_inline_script('nebulaone-embed-main', $inline_script, 'before');

            // Enqueues the main JavaScript file to be included on the frontend.
            wp_enqueue_script('nebulaone-embed-main');
        }
    }
}
// Hooks the script enqueue function to the 'wp_enqueue_scripts' action.
add_action('wp_enqueue_scripts', 'nebulaone_embed_script');

// Function to add a settings link in the plugin list page.
function nebulaone_embed_settings_link($links)
{
    // Adds a "Settings" link to the plugin entry.
    $settings_link = '<a href="options-general.php?page=nebulaone-embed-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
// Adds a filter to include the settings link in the plugins list.
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nebulaone_embed_settings_link');

// Function to show terms and conditions on plugin activation.
function nebulaone_embed_activate()
{
    add_option('nebulaone_terms_accepted', 'no');
}
register_activation_hook(__FILE__, 'nebulaone_embed_activate');

// Function to display the terms and conditions notice.
function nebulaone_embed_admin_notice()
{
    if (get_option('nebulaone_terms_accepted') !== 'yes') {
    ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('Please read and accept the <a href="#" id="nebulaone-terms-link">Terms and Conditions</a> to use the nebulaONE® WordPress Plugin.', 'nebulaone-embed'); ?></p>
            <p>
                <button id="nebulaone-accept-terms" class="button button-primary"><?php _e('Accept Terms', 'nebulaone-embed'); ?></button>
            </p>
        </div>
        <div id="nebulaone-terms-modal" style="display:none;">
            <h2><?php _e('Terms and Conditions', 'nebulaone-embed'); ?></h2>
            <p>
                <?php _e('<b>nebulaONE® WordPress Plugin Usage Agreement</b><br /><br />
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
                ', 'nebulaone-embed'); ?>
            </p>
            <button id="nebulaone-close-terms" class="button"><?php _e('Close', 'nebulaone-embed'); ?></button>
        </div>
        <script type="text/javascript">
            document.getElementById('nebulaone-terms-link').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('nebulaone-terms-modal').style.display = 'block';
            });
            document.getElementById('nebulaone-close-terms').addEventListener('click', function() {
                document.getElementById('nebulaone-terms-modal').style.display = 'none';
            });
            document.getElementById('nebulaone-accept-terms').addEventListener('click', function() {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    }
                };
                xhr.send('action=nebulaone_accept_terms');
            });
        </script>
<?php
    }
}
add_action('admin_notices', 'nebulaone_embed_admin_notice');

// Function to handle the terms acceptance.
function nebulaone_accept_terms()
{
    update_option('nebulaone_terms_accepted', 'yes');
    wp_die();
}
add_action('wp_ajax_nebulaone_accept_terms', 'nebulaone_accept_terms');
