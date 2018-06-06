<?php

// - cURL
if (in_array('curl', get_loaded_extensions())) {
    define('PF_CURL', '');
    $pfVersion = curl_version();
    $pfFeatures .= ' curl '. $pfVersion['version'] .';';
} else {
    $pfFeatures .= ' nocurl;';
}


#define('PF_DEBUG', ($this->debug)  ? true : false);

// General Defines
define('PF_TIMEOUT', 15);
define('PF_EPSILON', 0.01);

// Messages
// Error
define('PF_ERR_AMOUNT_MISMATCH', 'Amount mismatch');
define('PF_ERR_BAD_ACCESS', 'Bad access of page');
define('PF_ERR_BAD_SOURCE_IP', 'Bad source IP address');
define('PF_ERR_CONNECT_FAILED', 'Failed to connect to VitePay');
define('PF_ERR_INVALID_SIGNATURE', 'Security signature mismatch');
define('PF_ERR_MERCHANT_ID_MISMATCH', 'Merchant ID mismatch');
define('PF_ERR_NO_SESSION', 'No saved session found for ITN transaction');
define('PF_ERR_ORDER_ID_MISSING_URL', 'Order ID not present in URL');
define('PF_ERR_ORDER_ID_MISMATCH', 'Order ID mismatch');
define('PF_ERR_ORDER_INVALID', 'This order ID is invalid');
define('PF_ERR_ORDER_NUMBER_MISMATCH', 'Order Number mismatch');
define('PF_ERR_ORDER_PROCESSED', 'This order has already been processed');
define('PF_ERR_PDT_FAIL', 'PDT query failed');
define('PF_ERR_PDT_TOKEN_MISSING', 'PDT token not present in URL');
define('PF_ERR_SESSIONID_MISMATCH', 'Session ID mismatch');
define('PF_ERR_UNKNOWN', 'Unkown error occurred');

// General
define('PF_MSG_OK', 'Payment was successful');
define('PF_MSG_FAILED', 'Payment has failed');
define(
'PF_MSG_PENDING',
    'The payment is pending. Please note, you will receive another Instant'.
    ' Transaction Notification when the payment status changes to'.
    ' "Completed", or "Failed"'
);


class Vitepay_AIM extends WC_Payment_Gateway
{
// Setup our Gateway's id, description and other values
    function __construct()
    {

        // The global ID for this Payment method
        $this->id = "vitepay_aim";

        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("vitepay", 'vitepay-aim');

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("Vitepay Payment Gateway Plug-in for WooCommerce", 'vitepay-aim');

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __("vitepay", 'vitepay-aim');

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;

        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;

        // Supports the default credit card form
        //$this->supports = array( 'default_credit_card_form' );

        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // Lets check for SSL
        //add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );

        // Save settings
        if (is_admin()) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }


        add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( &$this, 'handle_vitepay_callback' )  );

    } // End __construct()
    // Build the administration fields for this specific Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'     => __( 'Enable / Disable', 'vitepay-aim' ),
                'label'     => __( 'Enable this payment gateway', 'vitepay-aim' ),
                'type'      => 'checkbox',
                'default'   => 'no',
            ),
            'title' => array(
                'title'     => __( 'Title', 'vitepay-aim' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Payment title the customer will see during the checkout process.', 'vitepay-aim' ),
                'default'   => __( 'Credit card', 'vitepay-aim' ),
            ),
            'description' => array(
                'title'     => __( 'Description', 'vitepay-aim' ),
                'type'      => 'textarea',
                'desc_tip'  => __( 'Payment description the customer will see during the checkout process.', 'vitepay-aim' ),
                'default'   => __( 'Pay securely using Orange Money.', 'vitepay-aim' ),
                'css'       => 'max-width:350px;'
            ),
            'api_key' => array(
                'title'     => __( 'Vitepay API key', 'vitepay-aim' ),
                'type'      => 'text',
                'desc_tip'  => __( 'This is the API Key provided by vitepay when you signed up for an account.', 'vitepay-aim' ),
            ),
            'api_secret' => array(
                'title'     => __( 'Vitepay API secret', 'vitepay-aim' ),
                'type'      => 'password',
                'desc_tip'  => __( 'This is the Secret Key provided by vitepay when you signed up for an account.', 'vitepay-aim' ),
            ),
            'environment' => array(
                'title'     => __( 'vitepay Test Mode', 'vitepay-aim' ),
                'label'     => __( 'Enable Test Mode', 'vitepay-aim' ),
                'type'      => 'checkbox',
                'description' => __( 'Place the payment gateway in test mode.', 'vitepay-aim' ),
                'default'   => 'no',
            ),
            'debug' => array(
                'title'     => __( 'vitepay Debug Mode', 'vitepay-aim' ),
                'label'     => __( 'Enable Debug Mode', 'vitepay-aim' ),
                'type'      => 'checkbox',
                'description' => __( 'Place the payment gateway in debug mode for transaction logs.', 'vitepay-aim' ),
                'default'   => 'no',
            ),
            'default_locale' => array(
                'title'     => __( 'Default Locale', 'vitepay-aim' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Your website default language, should be fr until further notice.', 'vitepay-aim' ),
            ),
            'default_currency' => array(
                'title'     => __( 'Default Currency', 'vitepay-aim' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Your website default currency, should be XOF until further notice.', 'vitepay-aim' ),
            ),
            'default_country' => array(
                'title'     => __( 'Country', 'vitepay-aim' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Your website default location, should be ML until further notice', 'vitepay-aim' ),
            )
        );
    }




    /**
     * pflog
     *
     * Log function for logging output.
     *
     * @author Jonathan Smit -- Original
     * @author Cheick Tall
     * @param $msg String Message to log
     * @param $close Boolean Whether to close the log file or not
     */
    public function pflog($msg = '', $close = false)
    {
        static $fh = 0;
        //global $module;

        // Only log if debugging is enabled
        if ($this->debug) {
            if ($close) {
                fclose($fh);
            } else {
                // If file doesn't exist, create it
                if (!$fh) {
                    $fh = fopen(plugin_dir_path( __FILE__ ) .'/vitepay.log', 'a+');
                }

                // If file was successfully created
                if ($fh) {
                    $line = date('Y-m-d H:i:s') .' : '. $msg ."\n";

                    fwrite($fh, $line);
                }
            }
        }
    }

    public function process_payment( $order_id ) {
        global $woocommerce;

        $pfError = false;
        $pfErrMsg = '';
        $this->pflog('-------VitePay payement started-------');

        // Get this Order's information so that we know
        // who to charge and how much
        $customer_order = new WC_Order( $order_id );

        // Are we testing right now or is it a real transaction
        $environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';

        // Decide which URL to post to
        $environment_url = ( "FALSE" == $environment )
            ? 'https://api.vitepay.com/v1/prod/payments'
            : 'https://api.vitepay.com/v1/sandbox/payments';

        #$environment_url = "http://requestb.in/1lx1fq91";


        $order_id = str_replace( "#", "", $customer_order->get_order_number() );
        $order_id = str_replace( "n°", "", $order_id );

        //$original_url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $amount_100 = $customer_order->order_total * 100;
        $currency_code = $this->default_currency;
        $api_secret = $this->api_secret;
        $callback_url = get_site_url() . '?wc-api=Vitepay_AIM&vp_request=true';
        $upped = strtoupper("$order_id;$amount_100;$currency_code;$callback_url;$api_secret");
        $hash = SHA1($upped);

        $blog_title = get_bloginfo('name');

        //currency check
        $CurrentCurrency = '';
        $CurrentCurrency = get_option('woocommerce_currency');
        if ($CurrentCurrency != '' ) {
            $this->pflog('Currency set to: '.$CurrentCurrency);

            if($CurrentCurrency != 'FCFA' && $CurrentCurrency != 'XOF') {


                $this->pflog("Bad currency, seul le CFA est accepté sur Vitepay pour l'instant");
                $old_currency = $this->client_currency;
                $this->client_currency = 'FCFA';
                $amount_100 = $customer_order->order_total * 100;
                $this->client_currency = $old_currency;
                $this->pflog("New amount, after conversion:".$amount_100);

                throw new Exception(__('La plateforme Vitepay accepte uniquement le FCFA. Tantez de changer de devise et reessayez', 'vitepay-aim'));

            }
        }
        $this->pflog("Sending hash::".$hash);
        // This is where the fun stuff begins
        $payload = array(
            //API info
            'api_key'               => $this->api_key,
            'hash'                  => $hash,
            'api_version'           => '1',

            // Order Details
            'payment[language_code]'=> $this->default_locale, # fr
            'payment[currency_code]'=> $this->default_currency, # XOF
            'payment[country_code]' => $this->default_country, # ML
            'payment[order_id]'     => $order_id,
            'payment[description]'  => $blog_title,
            'payment[amount_100]'   => $amount_100,
            'payment[buyer_ip_adress]'=> $_SERVER['REMOTE_ADDR'],
            'payment[return_url]'   => $this->get_return_url( $customer_order ), # URL called if process was OK
            'payment[decline_url]'  => $this->get_return_url( $customer_order ), # URL called when payment's failed
            "payment[cancel_url]"   => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]", # URL called when User's hit cancel
            'payment[callback_url]' => get_site_url() . '?wc-api=Vitepay_AIM&vp_request=true', # URL for server-2-server call
            'payment[email]'        => $customer_order->billing_email,
            'payment[p_type]'       => 'orange_money'
        );

        // Send this payload to Authorize.net for processing
        $response = wp_remote_post( $environment_url, array(
            'method'    => 'POST',
            'body'      => http_build_query( $payload ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout'   => 90,
            'sslverify' => true,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->pflog('debug:' . $response->get_error_message());
            $this->pflog(' ', true);
            throw new Exception(__('Nous rencontrons actuellement des difficultés, prières de reessayer ultérieurement. ->'.$response->get_error_message(), 'vitepay-aim'));
        }
        if ( !filter_var(wp_remote_retrieve_body( $response ), FILTER_VALIDATE_URL) ) {
            $pfErrMsg = wp_remote_retrieve_body( $response );
            $this->pflog($pfErrMsg, true);
            throw new Exception(__('Nous rencontrons actuellement des difficultés, prières de reessayer ultérieurement.. ->'.wp_remote_retrieve_body( $response ), 'vitepay-aim'));
        }
        if ( empty( $response['body'] ) ) {
            $pfError = true;
            $pfErrMsg = PF_ERR_CONNECT_FAILED;
            $this->pflog('debug:' . $pfErrMsg);
            $this->pflog(' ', true);
            throw new Exception(__('Problèmes de connection, prières de reessayer ultérieurement.', 'vitepay-aim'));
        }

        // Retrieve the body's resopnse if no errors found
        $response_body = wp_remote_retrieve_body( $response );

        if(!$pfError) {
            // Redirect to thank you page
            $this->pflog('---redirected to vp successfully---');
            $this->pflog('', true);
            return array(
                'result' => 'success',
                'redirect' => $response_body,
            );

        } else {

            $this->pflog('debug:' . $pfErrMsg );
            $this->pflog('', true);
            return array(
                'result' => 'failure'
            );


        }

    }

    function handle_vitepay_callback() {

        @ob_clean();

        global $woocommerce;
        $pfError = false;

        if ( isset( $_REQUEST['order_id'] ) && isset( $_REQUEST['authenticity'] ) && isset( $_REQUEST['vp_request'] )) {

            if ($_REQUEST['vp_request']) {$this->pflog('---VP_requested checkout---');}
            else {
                $this->pflog('not a vp request... ');
                $_REQUEST['success']='0';
            }

            $order_id = $_REQUEST['order_id'];
            if ($order_id != '') {
                $order = new WC_Order($order_id);

                $our_authenticity = sprintf('%s;%s;%s;%s', $order_id, $order->order_total * 100, 'XOF', $this->api_secret);
                $our_authenticity = strtoupper(sha1($our_authenticity));

                if ($our_authenticity == $_REQUEST['authenticity']) {

                    if ($_REQUEST['success']=='1') {

                        $order->payment_complete();
                        $order->add_order_note( __( 'VitePay payment completed.', 'vitepay-aim' ) );
                        $woocommerce->cart->empty_cart();
                        echo json_encode(array("status"=>1, "message" => "OK"));
                        $this->pflog('changed cart n# '.$order_id.' to paid and replied to vp ok');

                    } else if ($_REQUEST['failure']=='1') {

                        wc_add_notice( 'VitePay payment has failed', 'error' );
                        // Add note to the order for your reference
                        $order->update_status('failed',  __( 'VitePay payment failed.', 'vitepay-aim' ));
                        $order->add_order_note( 'Error: '. 'VitePay payment has failed.' );
                        $pfError = true;
                        $pfErrMsg = PF_MSG_FAILED;
                        echo json_encode(array("status"=>1, "message" => "KO"));

                    }

                } else {

                    $pfError = true;
                    $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
                    $this->pflog('Error occurred: '. $pfErrMsg);
                    echo json_encode(array(
                        "status"=>0,
                        "our_authenticity"=>$our_authenticity,
                        "error" => "bad_authenticity"
                    ));
                }

                if ($pfError) {$this->pflog('Error occurred: '. $pfErrMsg);}
                else $this->pflog('---Checkout completed successfully---');
                $this->pflog('', true);
                exit();

            } else {
                $pfError = true;
                $pfErrMsg = PF_ERR_SESSIONID_MISMATCH;
            }
        } else {

            $pfError = true;
            $pfErrMsg = PF_ERR_PDT_TOKEN_MISSING;
        }

        if ($pfError) {$this->pflog('Error occurred: '. $pfErrMsg);}

        echo json_encode(array("status"=>0));
        $this->pflog('', true);
        exit();
    }

}
