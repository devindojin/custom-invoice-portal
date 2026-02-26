<?php
# PDF rendering engine (mPDF integration)

class SIP_PDF_Engine {
    public static function generate_invoice($order_id) {

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_die('Order not found.');
    }

    // 1. Get the HTML Template FIRST
    $template_path = plugin_dir_path(__FILE__) . '../templates/invoice-template.php';
    if (!file_exists($template_path)) {
        wp_die('Invoice template not found at: ' . $template_path);
    }

    // 1. Prepare QR Code Data String
    $qr_data = "Invoice: " . $order->get_order_number() . "\n";
    $qr_data .= "Date: " . $order->get_date_created()->date('Y-m-d') . "\n";
    $qr_data .= "Customer: " . get_post_meta($order_id, '_sip_cust_name', true) . "\n";
    $qr_data .= "Total: " . $order->get_total() . " AED";

    // For the top of the invoice (Order ID Scan)
    $qr_html = '<barcode code="' . $order->get_id() . '" type="C128A" size="1" height="0.5" />';


    // / Inside your generate_invoice function
            $logo_path = plugin_dir_path(__FILE__) . '../assets/images/logo.png';
            $type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $data = file_get_contents($logo_path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    

            // print_r($base64); // Debug: Check the base64 string in the error log
            // die;



    ob_start();
    include $template_path;
    $html = ob_get_clean();

    // 2. Build the Items Table HTML
    $items_html = '';
    foreach ($order->get_items() as $item_id => $item) {
        $product    = $item->get_product();

      
        $image_html = '';

        if ($product) {
            // Get the image ID
            $image_id = $product->get_image_id();
            // Convert ID to full server path (Best for mPDF)
            $img_path = get_attached_file($image_id);

            if ($img_path && file_exists($img_path)) {
                $image_html = '<img src="' . $img_path . '" width="40" height="40" style="object-fit: cover; margin-right: 10px;">';
            }
        }


        $sku        = $product ? $product->get_sku() : 'N/A';
        $name       = $item->get_name();
        
        // Use get_total instead of wc_price here to keep the math clean for the loop
        $price      = number_format($order->get_item_subtotal($item, false, false), 2);
        $qty        = $item->get_quantity();
        $item_total = number_format($item->get_total(), 2);
        

        $items_html .= '
        <tr>
            <td>
            <div style="display: flex; align-items: center;">
                <div>' . esc_html($item->get_name()) . '</strong><br>
                    SKU: ' . esc_html($product ? $product->get_sku() : 'N/A') . '
                </div>
            </div> 
            </td>
            <td style="padding: 10px 0;text-align:right">Dhs. ' . $price . '</td>
            <td style="padding: 10px;text-align:center">' . $qty . '</td>
            <td style="padding: 10px 0;text-align:right">Dhs. ' . $item_total . '</td>
        </tr>';
    }

    // 3. Define all Dynamic Data
    $cust_name    = get_post_meta($order_id, '_sip_cust_name', true) ?: 'Walk-in';
    $cust_phone   = get_post_meta($order_id, '_sip_cust_phone', true) ?: 'N/A';
    $cust_address = get_post_meta($order_id, '_sip_cust_address', true) ?: '';
    
    
    // Custom Thank You Note based on language or preference
    $thank_you_note = "Thank you for shopping with us!";

    $payment_title = $order->get_payment_method_title();
    if ( empty($payment_title) ) {
        $payment_title = 'Walk-in / Cash'; // Default fallback
    }

    
    // 4. Perform ALL replacements at once
    $map = [
        '{{logo_base64}}' => $base64,
        '{{logo_path}}' => $logo_path,
        '{{qr_code}}'         => $qr_html,
        '{{item_loop}}'       => $items_html,
        '{{order_number}}'    => $order->get_order_number(),
        '{{order_date}}'      => $order->get_date_created()->date('M d, Y'),
        '{{customer_name}}'   => esc_html($cust_name),
        '{{customer_phone}}'  => esc_html($cust_phone),
        '{{customer_address}}'=> nl2br(esc_html($cust_address)),
        '{{thank_you_note}}'  => esc_html($thank_you_note),
        '{{subtotal}}'        => 'Dhs. ' . number_format($order->get_subtotal(), 2),
        '{{vat_amount}}'      => 'Dhs. ' . number_format($order->get_total_tax(), 2),
        '{{total_aed}}'       => number_format($order->get_total(), 2),
        '{{payment_method}}'  => $payment_title  // This gets the "Title" like "Credit Card"    
    ];

 

    $map['{{payment_method}}'] = $payment_title;

    $html = str_replace(array_keys($map), array_values($map), $html);

    // 5. Initialize mPDF and Output
    try {
        $mpdf = new \Mpdf\Mpdf([
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->SetTitle('Invoice-' . $order_id);
        $mpdf->WriteHTML($html);
        
        // Clear buffer to prevent "headers already sent"
        if (ob_get_length()) ob_end_clean();

        $mpdf->Output('Invoice-' . $order_id . '.pdf', 'I'); 
        exit;
    } catch (\Mpdf\MpdfException $e) {
        wp_die('PDF Error: ' . $e->getMessage());
    }
}
}
?>
