<?php

/**
 * Restrict Cash On Delivery Orders For Above 50 AED only For UAE
 *
 * Restrict Cash On Delivery Orders for a limit of 150 after the limit is crossed the cash on delivery option disables only for uae
 */
function restrict_cod_by_order_total( $gateways ) {
    // Check if WooCommerce customer object exists and contains billing information
    if ( is_object(WC()->customer) && method_exists(WC()->customer, 'get_billing_country') ) {
        // Get the customer's billing country
        $customer_country = WC()->customer->get_billing_country();

        // Define the maximum order total for COD to be available
        $maximum_order_total = 150;

        // Check if the customer's billing country is UAE
        if ( $customer_country === 'AE' ) {
            // Get the current cart total
            $cart_total = WC()->cart->subtotal;

            // If the cart total is above the maximum, remove COD from the available gateways
            if ( $cart_total > $maximum_order_total && isset( $gateways['cod'] ) ) {
                unset( $gateways['cod'] );
            }
        }
    }

    return $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'restrict_cod_by_order_total' );

/**
 * Hide Cod & Telr For India and show the razorpay
 *
 * hide the cod option for indian Contry
 */
add_filter('woocommerce_available_payment_gateways', 'hide_cod_and_telr_for_india');

function hide_cod_and_telr_for_india($available_gateways) {
    if (is_admin()) return $available_gateways;

    // Check if the WC()->customer is available and not null
    if (is_object(WC()->customer)) {
        // Check if the current shipping country is India
        $shipping_country = WC()->customer->get_shipping_country();

        if ($shipping_country === 'IN') {
            // Hide the Cash on Delivery (COD) payment method
            if (isset($available_gateways['cod'])) {
                unset($available_gateways['cod']);
            }

            // Hide the Telr payment method with ID 'wctelr'
            if (isset($available_gateways['wctelr'])) {
                unset($available_gateways['wctelr']);
            }
        }
    }

    return $available_gateways;
}

/**
 * Disable Unique SKUs
 *
 * disable Similar SKU Uniqueness in woo-commerce 
 */
/**
 * Disable SKU uniqueness check for all products.
 *
 * @param bool $has_unique_sku Does the product have a unique SKU.
 * @param int $product_id Product ID.
 * @param string $sku SKU to check.
 *
 * @return bool
 */
function disable_sku_uniqueness_check_for_products($has_unique_sku, $product_id, $sku) {
    return false; // Return false to indicate that SKU uniqueness is not required for all products.
}
add_filter('wc_product_has_unique_sku', 'disable_sku_uniqueness_check_for_products', 10, 3);

/**
 *  Hide Razorpay For UAE
 *
 * hide the razorpay payment gateway for Uae Country 
 */
add_filter('woocommerce_available_payment_gateways', 'hide_razorpay_for_uae');

function hide_razorpay_for_uae($available_gateways) {
    if (is_admin()) return $available_gateways;

    // Check if the WC()->customer is available and not null
    if (is_object(WC()->customer)) {
        // Check if the current shipping country is United Arab Emirates
        $shipping_country = WC()->customer->get_shipping_country();

        if ($shipping_country === 'AE') {
            // Hide the Razorpay payment method with ID 'razorpay'
            if (isset($available_gateways['razorpay'])) {
                unset($available_gateways['razorpay']);
            }
        }
    }

    return $available_gateways;
}

/**
 * Restrict Previous Date to be Picked 
 *
 * Date Picker only Lets you pick the future dates on the checkout page
 */
function add_min_date_to_datepicker() {
    // Get the current date
    $current_date = date("Y-m-d");
    
    // Get the items in the cart
    $cart_items = WC()->cart->get_cart();
    
    // Check if any cart item has backorder
    $has_backorder = false;
    foreach ($cart_items as $cart_item_key => $cart_item) {
        if ($cart_item['data']->is_on_backorder($cart_item['quantity'])) {
            $has_backorder = true;
            break; // Exit loop if any item is on backorder
        }
    }

    // Add a min attribute to the date input field
    echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var dateField = document.getElementById("_shopengine_Preferred_Delivery_Date");
            if (dateField) {
                // Add minimum date based on backorder status
                if (' . ($has_backorder ? 'true' : 'false') . ') {
                    // If any item has backorder, set min date to tomorrow
                    var tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1); // Adding one day
                    var formattedMinDate = tomorrow.toISOString().split("T")[0];
                    dateField.setAttribute("min", formattedMinDate);
                } else {
                    // If no item has backorder, set min date to today
                    dateField.setAttribute("min", "' . $current_date . '");
                }
            }
        });
    </script>';
}

add_action('wp_footer', 'add_min_date_to_datepicker');

/**
 * Disable Webhook
 */
function disable_webhook_by_id($webhook_id) {
    global $wpdb;
    // Update the status of the webhook to 'disabled' in the 'wp_woocommerce_api_keys' table
    $wpdb->update(
        $wpdb->prefix . 'woocommerce_api_keys',
        array('status' => 'disabled'),
        array('key_id' => $webhook_id)
    );
}

// Usage: Call the function with the webhook ID you want to disable
disable_webhook_by_id(29);

/**
 * Display Only Three States - On the checkout 
 *
 * Displays only 3 states at checkout Karnataka, Kerala &amp; Coimbatore 
 */
add_filter('woocommerce_states', 'custom_woocommerce_states_for_india');

function custom_woocommerce_states_for_india($states) {
    $states['IN'] = array(
        'KL' => __('Kerala', 'woocommerce'),
        'KA' => __('Karnataka', 'woocommerce'),
        'AP' => __('Andhra Pradesh', 'woocommerce'),
    );
    return $states;
}

/**
 * Webhook Trigger After Payment Completion
 *
 * Webhook Trigger After Payment Completion - Only after the payment is completed for cod its gonna directly trigger the webhook 
 */
add_action('woocommerce_order_status_completed', 'send_order_webhook', 10, 1);

function send_order_webhook($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);
    // Make sure the order exists
    if (!$order) {
        return;
    }
    // Check if the shipping country is India
    $shipping_country = $order->get_shipping_country();
    if ($shipping_country === 'IN') {
        // For India, trigger the webhook if payment status is completed
        if ($order->get_status() === 'completed') {
            send_webhook($order);
        }
    } elseif ($shipping_country === 'AE') {
        // For UAE, trigger the webhook if payment status is processing or completed
        $payment_status = $order->get_status();
        if ($payment_status === 'completed'||$payment_status === 'processing') {
            send_webhook($order);
        }
    }
}

function send_webhook($order) {
    // Prepare the data to be sent in the webhook
    $order_data = $order->get_data();

    // Include all line items with entire product data and quantity
    $line_items = array();
    foreach ($order->get_items() as $item_id => $item) {
        // Get the product object
        $product = $item->get_product();
        // Get the product data
        $product_data = $product->get_data();
        // Add the quantity to the product data
        $product_data['quantity'] = $item->get_quantity();
        // Add the line item to the array
        $line_items[] = $product_data;
    }

    $order_data['line_items'] = $line_items;

    // Encode the data to JSON
    $json_data = json_encode($order_data);

    // Webhook URL to send the data the ammas server for rista auto bill genration
    $webhook_url = 'https://ammaspastries.ae/integration/rishtaorder';
	
    // Setup the webhook request
    $args = array(
        'body'        => $json_data,
        'headers'     => array(
            'Content-Type' => 'application/json',
            'x-custom-secret' => 'Nk]Zo>{pcF0nG d54*k{C9J4!ku3)HL %z}/~tX^&8})B+}x9<'
        ),
        'timeout'     => 30,
        'redirection' => 5,
        'blocking'    => true,
        'httpversion' => '1.0',
        'sslverify'   => true, // Set to true if your webhook URL uses HTTPS and has a valid SSL certificate
    );

    // Send the webhook request
    $response = wp_safe_remote_post($webhook_url, $args);

    // Check the response
    if (is_wp_error($response)) {
        error_log('Webhook request failed: ' . $response->get_error_message());
    } else {
        // Webhook request was successful
        $response_body = wp_remote_retrieve_body($response);
        error_log('Webhook response: ' . $response_body);
    }
}

/**
 * Remove all the bloat Notices expect the checkout & out of stock notice
 *
 * Just keep error notice on the checkout page
 */
add_filter( 'woocommerce_notice_types', 'customize_checkout_notices' );

function customize_checkout_notices( $notice_types ) {
    // Check if it's the checkout page
    if ( is_checkout() ) {
        // Keep only 'error' notices
        $allowed_notices = array( 'error' );
        $notice_types = array_intersect_key( $notice_types, array_flip( $allowed_notices ) );
    } else {
        // If it's not the checkout page, disable all notices
        $notice_types = array();
    }
    return $notice_types;
}

/**
 * Register New Post Type for RISTA Fulfilled & UnFulfilled
 */
// Register custom order statuses
add_action('init', 'register_custom_order_statuses');

function register_custom_order_statuses() {
    register_post_status('wc-fulfilled', array(
        'label'                     => _x('Fulfilled', 'Order status', 'woocommerce'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Fulfilled <span class="count">(%s)</span>', 'Fulfilled <span class="count">(%s)</span>', 'woocommerce'),
        'text_color'                => '#6c757d', // Text color for Fulfilled status
        'background_color'          => '#f8f9fa', // Background color for Fulfilled status
    ));

    register_post_status('wc-unfulfilled', array(
        'label'                     => _x('Unfulfilled', 'Order status', 'woocommerce'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Unfulfilled <span class="count">(%s)</span>', 'Unfulfilled <span class="count">(%s)</span>', 'woocommerce'),
        'text_color'                => '#ffffff', // Text color for Unfulfilled status
        'background_color'          => '#dc3545', // Background color for Unfulfilled status
    ));
}


// Hook into the WooCommerce filter to add custom order statuses to the dropdown
add_filter('wc_order_statuses', 'add_custom_order_statuses_to_dropdown');

function add_custom_order_statuses_to_dropdown($order_statuses) {
    // Add your custom order statuses to the list
    $order_statuses['wc-fulfilled'] = _x('Fulfilled', 'Order status', 'woocommerce');
    $order_statuses['wc-unfulfilled'] = _x('Unfulfilled', 'Order status', 'woocommerce');
    
    return $order_statuses;
}

/**
 * Endpoint For Webhook CallBack - Rista Server
 *
 * The callback url will have structure like this:{success: true,invoiceNumber: "APW1234"}InvoiceNumber is the Order IDThe webhook endpoint URL for updating order status is: https://ammaspastries.in/wp-json/webhook/v1/update-order-status
 */
// Register a custom endpoint to handle webhook callbacks
add_action('rest_api_init', 'register_webhook_endpoint');

function register_webhook_endpoint() {
    register_rest_route('webhook/v1', '/update-order-status', array(
        'methods' => 'POST',
        'callback' => 'handle_webhook_callback',
    ));
}

function handle_webhook_callback($request) {
    // Retrieve JSON payload from the request
    $payload = json_decode($request->get_body(), true);

    // Check if payload contains necessary data
    if (isset($payload['success']) && isset($payload['invoiceNumber'])) {
        $success = $payload['success'];
        $order_id = $payload['invoiceNumber'];
        
        // Check if the order exists
        $order = wc_get_order($order_id);
        if ($order) {
            // Update order status based on success
            $status = $success ? 'wc-fulfilled' : 'wc-unfulfilled';
            $order->update_status($status);
            
            return new WP_REST_Response('Order status updated successfully', 200);
        } else {
            return new WP_REST_Response('Invalid order ID', 400);
        }
    } else {
        return new WP_REST_Response('Invalid payload data', 400);
    }
}
