<?php
/**
 * Plugin Name: Calendly Integration
 * Description: A WordPress plugin to integrate Calendly with a popup display based on time delay or button click.
 * Version: 1.0
 * Author: D.Kandekore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Create the admin menu and settings page
function calendly_integration_admin_menu() {
    add_menu_page(
        'Calendly Integration Settings',
        'Calendly Integration',
        'manage_options',
        'calendly-integration',
        'calendly_integration_settings_page'
    );
}
add_action('admin_menu', 'calendly_integration_admin_menu');

function calendly_integration_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the user has posted settings and if the nonce is valid
    if (isset($_POST['calendly_link']) && check_admin_referer('calendly_nonce_action', 'calendly_nonce')) {
        // Save the settings
        update_option('calendly_link', sanitize_text_field($_POST['calendly_link']));
        update_option('calendly_delay', sanitize_text_field($_POST['calendly_delay']));
    }

    // Retrieve existing values
    $calendly_link = get_option('calendly_link', '');
    $calendly_delay = get_option('calendly_delay', '0');

    // The HTML for the settings page
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="" method="post">
            <?php
            // Security field
            wp_nonce_field('calendly_nonce_action', 'calendly_nonce');
            ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="calendly_link">Calendly Link:</label>
                    </th>
                    <td>
                        <input type="url" name="calendly_link" id="calendly_link" value="<?php echo esc_attr($calendly_link); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="calendly_delay">Popup Delay (0-10 minutes):</label>
                    </th>
                    <td>
                        <input type="number" name="calendly_delay" id="calendly_delay" value="<?php echo esc_attr($calendly_delay); ?>" class="small-text" min="0" max="10" step="1">
                    </td>
                </tr>
            </table>

            <?php
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Enqueue the necessary scripts
function calendly_integration_enqueue_scripts() {
    wp_enqueue_script('calendly-widget', 'https://assets.calendly.com/assets/external/widget.js', array(), null, true);
    wp_enqueue_script('jquery');
    

    ));
}
add_action('wp_enqueue_scripts', 'calendly_integration_enqueue_scripts');

// Create a shortcode for the button
function calendly_integration_button_shortcode($atts) {
    $calendly_link = get_option('calendly_link', '');
    $output = '<button id="calendly-button" onclick="openCalendlyPopup()">Schedule Meeting</button>';
    
    // Add inline script to handle the popup
    $output .= '<script type="text/javascript">
                    function openCalendlyPopup() {
                        Calendly.initPopupWidget({url: "' . esc_js($calendly_link) . '"});
                        return false;
                    }
                </script>';
    
    return $output;
}
add_shortcode('calendly_button', 'calendly_integration_button_shortcode');

// Implement the delay functionality
function calendly_integration_footer_script() {
    $delay = get_option('calendly_delay', '0') * 60 * 1000; // Convert minutes to milliseconds

    // Output the script only if there is a delay set, or immediately if delay is zero.
    if ($delay >= 0) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function initCalendly() {
                    if (window.Calendly) {
                        Calendly.initPopupWidget({url: "<?php echo esc_js(get_option('calendly_link', '')); ?>"});
                    } else {
                        // Retry after a short delay if Calendly is not defined
                        setTimeout(initCalendly, 500);
                    }
                }

                // Set the delay
                setTimeout(initCalendly, <?php echo intval($delay); ?>);
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'calendly_integration_footer_script');


// Activation and deactivation hooks
function calendly_integration_activate() {
    add_option('calendly_link', '');
    add_option('calendly_delay', '0');
}
register_activation_hook(__FILE__, 'calendly_integration_activate');

function calendly_integration_deactivate() {

    delete_option('calendly_link');
    delete_option('calendly_delay');
}
register_deactivation_hook(__FILE__, 'calendly_integration_deactivate');
