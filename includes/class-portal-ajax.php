<?php 
# Handles Search, Filtering, and Meta Updates


// Hook for logged-in staff
add_action('wp_ajax_sip_load_orders', 'sip_ajax_load_orders');
add_action('wp_ajax_sip_save_meta', 'sip_ajax_save_meta');

// Hook for front-end/logged-out (helps prevent 400 errors)
add_action('wp_ajax_nopriv_sip_load_orders', 'sip_ajax_load_orders');
add_action('wp_ajax_nopriv_sip_save_meta', 'sip_ajax_save_meta');

function sip_ajax_load_orders() {
    
    $is_history = isset($_POST['history']) && $_POST['history'] === 'true';
    $search     = sanitize_text_field($_POST['search']);
    $date       = sanitize_text_field($_POST['date']);

    $args = [
        'limit'   => $is_history ? 200 : 50, // Fetch more for history to ensure we find the processed ones
        'orderby' => 'date',
        'order'   => 'DESC',
    ];

    // Handle the custom Search (Meta Query)
    if ($search) {
        $args['meta_query'] = [
            [
                'key'     => '_order_number',
                'value'   => $search,
                'compare' => 'LIKE' // 'LIKE' allows partial matches
            ]
        ];
    }
    if ($date)   $args['date_created'] = $date;
    
    $orders   = wc_get_orders($args);
    $response = [];

    foreach ($orders as $order) {
        $oid = $order->get_id();
        
        // Fetch statuses for filtering
        $invoice_status = get_post_meta($oid, '_sip_invoice_status', true);
        $is_processed   = get_post_meta($oid, '_sip_is_processed', true);
        // Fetch your custom invoice number if it exists, otherwise fallback to Order ID
        $custom_invoice_no = get_post_meta($oid, '_order_number', true); 
        $display_number = $custom_invoice_no ? $custom_invoice_no : $oid;
        
        //die; // Debugging line to check the display number output
        // Logical check: If we are in History mode, only add if generated/processed.
        // If not in History mode, add everything.
        $should_add = !$is_history || ($invoice_status === 'generated' || $is_processed === 'yes');

        if ($should_add) {
            $response[] = [
                'id'    => $oid,
                'display_number' => $display_number, // NEW: This is what employees see
                'date'  => $order->get_date_created()->date('Y-m-d'),
                'total' => $order->get_total(),
                'meta'  => [
                    'name'    => get_post_meta($oid, '_sip_cust_name', true) ?: '',
                    'trn'     => get_post_meta($oid, '_sip_cust_trn', true) ?: '',
                    'phone'   => get_post_meta($oid, '_sip_cust_phone', true) ?: '',
                    'address' => get_post_meta($oid, '_sip_cust_address', true) ?: '',
                ]
            ];
        }
    }

    wp_send_json_success($response);
}

// Save Data & Mark as Generated

function sip_ajax_save_meta() {
    // 1. Validate existence and basic security
    if ( !isset($_POST['order_id']) || empty($_POST['order_id']) ) {
        wp_send_json_error('Missing Order ID');
    }

    $oid = intval($_POST['order_id']);

    // 2. Sanitize and Save Meta Data
    // Note: We use the keys sent by the JS (name, phone, address, trn)
    
    if ( isset($_POST['name']) ) {
        update_post_meta($oid, '_sip_cust_name', sanitize_text_field($_POST['name']));
    }

    if ( isset($_POST['phone']) ) {
        update_post_meta($oid, '_sip_cust_phone', sanitize_text_field($_POST['phone']));
    }

    if ( isset($_POST['address']) ) {
        update_post_meta($oid, '_sip_cust_address', sanitize_textarea_field($_POST['address']));
    }

    if ( isset($_POST['trn']) ) {
        update_post_meta($oid, '_sip_cust_trn', sanitize_text_field($_POST['trn']));
    }

    // 3. Mark the invoice as generated (for filtering in "History")
    update_post_meta($oid, '_sip_invoice_status', 'generated');

    // 3. Mark the order as processed for the "History" filter
    update_post_meta($oid, '_sip_is_processed', 'yes');

    // 4. (Optional) Advanced Debugging to Error Log
    // Instead of echo/die (which breaks AJAX), we write to wp-content/debug.log
    if ( defined('WP_DEBUG') && WP_DEBUG === true ) {
        error_log("SIP AJAX Save Meta: Order ID $oid - Name: " . sanitize_text_field($_POST['name']) . ", TRN: " . sanitize_text_field($_POST['trn']));
    }
    // 5. Always return a proper JSON success
    wp_send_json_success(['message' => 'Order details saved successfully.']);
}

// This is sitting in your PHP file waiting for the JS window.open()
add_action('init', function() {
    // If the URL contains ?download_sip_pdf=XXX
    if (isset($_GET['download_sip_pdf'])) {
        $order_id = intval($_GET['download_sip_pdf']);
        
        // This is the EXACT MOMENT the Engine is triggered
        SIP_PDF_Engine::generate_invoice($order_id); 
    }
});

// Save Data & Mark as Generated


?>