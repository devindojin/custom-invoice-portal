<?php
/*
Plugin Name: Staff Walk-in Invoice Portal
Description: Standalone staff portal for WooCommerce order management and PDF invoicing via /staff-portal/
Version: 1.1
*/

if (!defined('ABSPATH')) exit;

// Load Composer autoloader for mPDF
if (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
}

// 1. Constants
define('SIP_PATH', plugin_dir_path(__FILE__));
define('SIP_URL', plugin_dir_url(__FILE__));
define('SIP_ACCESS_KEY', 'staff_internal_2026'); // Secret key for the direct link

// 2. Include Backend Logic
require_once SIP_PATH . 'includes/class-portal-ajax.php';
require_once SIP_PATH . 'includes/class-pdf-engine.php';

/**
 * Enqueue Portal Assets with Dependencies
 */
add_action('wp_enqueue_scripts', 'sip_enqueue_portal_assets');

function sip_enqueue_portal_assets() {
    // Only load these if the staff_portal query var is present
    if (get_query_var('staff_portal')) {
        
        // CSS Dependencies
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css');
        wp_enqueue_style('portal-style', SIP_URL . 'assets/css/portal-style.css');

        // JS Dependencies (Bootstrap, DataTables Core, DataTables Bootstrap 5)
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
        wp_enqueue_script('datatables-core', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', true);
        wp_enqueue_script('datatables-bs5', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', array('datatables-core'), '1.13.6', true);

        // Your Custom Portal Script
        wp_enqueue_script('sip-portal-js', SIP_URL . 'assets/js/portal-script.js', array('jquery', 'datatables-bs5', 'bootstrap-js'), time().'1.1', true);

        // Pass PHP data to JS
        wp_localize_script('sip-portal-js', 'sip_settings', array(
            'ajax_url'   => admin_url('admin-ajax.php'),
            'site_url'   => home_url(),
            'access_key' => SIP_ACCESS_KEY, // Pass key to JS for generating PDF links
            'nonce'      => wp_create_nonce('sip_portal_nonce')
        ));
    }
}

// 3. Register the Portal URL (yoursite.com/staff-portal)
add_action('init', function() {
    add_rewrite_rule('^staff-portal/?$', 'index.php?staff_portal=1', 'top');
});

add_filter('query_vars', function($vars) {
    $vars[] = 'staff_portal';
    return $vars;
});

// 4. Portal Page & PDF Download Listener
add_action('template_redirect', function() {
    
    // A. Handle Portal View
    if (get_query_var('staff_portal')) {
        // Validate Secret Key
        if (!isset($_GET['key']) || $_GET['key'] !== SIP_ACCESS_KEY) {
            wp_die('Access Denied: Invalid Security Key');
        }

        // Output the Portal Template
        include SIP_PATH . 'templates/portal-ui.php';
        exit;
    }

    // B. Handle PDF Generation Trigger
    if (isset($_GET['download_sip_pdf'])) {
        // Validate Secret Key for PDF access
        if (!isset($_GET['key']) || $_GET['key'] !== SIP_ACCESS_KEY) {
            wp_die('Access Denied: Security key required for PDF generation.');
        }

        $order_id = intval($_GET['download_sip_pdf']);
        if ($order_id > 0) {
            SIP_PDF_Engine::generate_invoice($order_id);
        }
        exit;
    }
});

// 5. Activation Hook: Flush rewrite rules to make /staff-portal/ work immediately
register_activation_hook(__FILE__, function() {
    add_rewrite_rule('^staff-portal/?$', 'index.php?staff_portal=1', 'top');
    flush_rewrite_rules();
});