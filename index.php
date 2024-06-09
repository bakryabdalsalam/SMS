/*
Plugin Name: WooCommerce SMS On Hold
Description: Sends an SMS when an order status changes to "on-hold".
Version: 1.0
Author: Bakry Abdelsalam
*/
<?php
add_action('woocommerce_order_status_on-hold', 'on_hold_sms', 10, 1);

function on_hold_sms($order_id) {
    error_log('on_hold_sms function triggered. Order ID: ' . $order_id);

    // Retrieve the order details using the order ID
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log('Order not found.');
        return;
    }
    
    // Get the customer user ID from the order metadata
    $user_id = get_post_meta($order_id, '_customer_user', true);
    error_log('Customer User ID: ' . $user_id);

    // Get the Aramex tracking number from the order metadata
    $ced_aramex_awno = get_post_meta($order_id, 'ced_aramex_awno', true);
    error_log('Aramex Tracking Number: ' . $ced_aramex_awno);
    
    // Generate the tracking URL for the shipment
    $track_url = "https://www.aramex.com/sa/ar/track/track-results-new?ShipmentNumber=" . $ced_aramex_awno;
    error_log('Tracking URL: ' . $track_url);
    
    // Create a new WooCommerce customer object using the user ID
    $customer = new WC_Customer($user_id);
    
    // Retrieve the customer's billing phone number
    $billing_phone = $customer->get_billing_phone();
    error_log('Billing Phone: ' . $billing_phone);
    
    // Prepare the data to send the SMS
    $url = "https://www.msegat.com/gw/";
    $dataArray = array(
        "userName" => 'yasay007',
        "apiKey" => '593fabdf8843b5864f4c1103631bd440',
        "userSender" => 'laftah',
        "numbers" => $billing_phone,
        "msg" => "تم شحن طلبك بنجاح رابط التتبع: {$track_url} شكراً لإختياركم لفتة!",
        "msgEncoding" => 'UTF8'
    );
    
    // Initialize a cURL session to send the SMS
    $ch = curl_init();
    $data = http_build_query($dataArray);
    $getUrl = $url . "?" . $data;
    error_log('cURL Request URL: ' . $getUrl);
    
    // Set cURL options for the request
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $getUrl);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    
    // Execute the cURL request and capture the response
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_error($ch)) {
        error_log('Request Error: ' . curl_error($ch));
    } else {
        error_log('SMS Response: ' . $response);
    }
    
    // Close the cURL session
    curl_close($ch);
}
