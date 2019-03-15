<?php
/**
 * @package goodDollar
 */
/*
Plugin Name: good dollar
Plugin URI:  http://goodDollar.com/plugin
Description: good dollar.
Version: 1.0.0
Author: Reut Ovadia
Author URI: http://reutOvadia.com
License: GPLv2 or later
Text Domain: goodDollar
*/

/*
goodDollar fore donating with good dollars.
Copyright (C) Reut Ovadia

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


if(!function_exists('add_action')){
    die('Hey, you can\'t access this file, you silly human!');
}

/**
 * Check if WooCommerce is active
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    die('Must install woocommerce plugin to use the goodDollar plugin');
}

class GoodDollar
{
    private $gdImageHtml = '';

    public function __construct()
    {
        $gdImageSrc = plugins_url( 'gooddollar/assats/logo.jpg');
        $this->gdImageHtml = "<img src='$gdImageSrc' alt='goodDollar' height='50' width='70'>";

        add_action( 'woocommerce_review_order_before_submit', array($this,'custom_add_good_dollar_checkbox' ));
        add_action( 'wp_enqueue_scripts', array($this,'register_plugin_styles'));
        add_action( 'woocommerce_checkout_update_order_meta', array($this,'custom_checkout_field_update_order_meta'), 10, 1 );
        //add_action( 'woocommerce_order_status_completed', array($this, 'wc_send_order_to_mypage' ));
        add_filter('woocommerce_thankyou', array( $this, 'custom_add_good_dollar_thank_you' ));
    }

    public function activate()
    {
        $this->custom_add_good_dollar_checkbox();
        $this->custom_checkout_field_update_order_meta();
        $this->custom_add_good_dollar_thank_you();
        //$this->wc_send_order_to_mypage();
        
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        flush_rewrite_rules();
    }

    public function custom_post_type()
    {
        register_post_type('book', ['public'=>true, 'label'=>'Books']);
    }

    public function custom_add_good_dollar_checkbox()
    {
        echo '<div id="good_dollar_donation_checkBox_trigger_div" class="good_dollar_donation_class">';

        woocommerce_form_field( 'good_dollar_donation_checkBox_trigger', array(
            'type'      => 'checkbox',
            'class'     => array( 'form-row-wide'),
            'label'     => __("I Would like to add 2 $this->gdImageHtml for donation with my payment"), //, 'woocommerce'
        ),  WC()->checkout->get_value( 'good_dollar_donation_checkBox_trigger' ) );
        echo "<a href='https://www.gooddollar.org' target='_blank'><i class='fa fa-question-circle'></i> What is $this->gdImageHtml</a></div>";
        
    }

    public function custom_add_good_dollar_thank_you( $order_id)
    {
        if ( $order_id > 0 ) {
            $Order = wc_get_order( $order_id );
            $actionGD = get_post_meta($order_id, 'good_dollar_donation_checkBox_trigger');
            
            if(!empty($actionGD[0]) && $actionGD[0]){
                $gdImageSrc = plugins_url( 'gooddollar/assats/logo.jpg');
                echo "<div id='good_dollar_donation_thank_you' class='good_dollar_donation_class good_dollar_donation_thank_you_class'>Thank you for donating 2 $this->gdImageHtml <br> <a class='underlineText' id='good_dollar_transaction_details' href='https://rinkeby.etherscan.io/address/' >Go to transaction details</a></div>";
                echo "<div id='good_dollar_donation_false' class='good_dollar_donation_class good_dollar_donation_thank_you_class'>Unfortunately the donation of 2 $this->gdImageHtml could not be established. <a class='underlineText' href='https://www.gooddollar.org' target='_blank'>You can donate directly here</a></div>";
                $this->register_plugin_scripts();
            }
        }       
    }

    public function register_plugin_styles(){
        wp_register_style( 'style_good_dollar', plugins_url( 'gooddollar/style/style.css' ) );
	    wp_enqueue_style( 'style_good_dollar' );
    }

    public function register_plugin_scripts(){
        // wp_register_script( 'good_dollar_data_js', plugins_url( 'gooddollar-test/js/good_dollar_data.js' ) );
        // wp_enqueue_script( 'good_dollar_data_js' );
        wp_register_script( 'goodDollarActionsJS', plugins_url( 'gooddollar/js/goodDollarActions.js' ) );
        wp_enqueue_script( 'goodDollarActionsJS' );
    }

    public function custom_checkout_field_update_order_meta($order_id)
    {
        //https://stackoverflow.com/questions/45905237/add-a-custom-checkbox-in-woocommerce-checkout-which-value-shows-in-admin-edit-or
        //die($_POST['good_dollar_donation_checkBox_trigger']);
        if ( ! empty( $_POST['good_dollar_donation_checkBox_trigger'] ) ){
            update_post_meta( $order_id, 'good_dollar_donation_checkBox_trigger', $_POST['good_dollar_donation_checkBox_trigger'] );
            add_action( 'wp_enqueue_scripts', array($this,'register_plugin_scripts'));
        }
        
    }

    public function wc_send_order_to_mypage( $order ) 
    {
        //wp_enqueue_script( 'good-dollar-action', plugins_url( 'gooddollar-test/js/good_dollar_action.js', __FILE__ ));
        die(json_encode($order));
        // $shipping_add = [
        //             "firstname" => $order->shipping_first_name,
        //             "lastname" => $order->shipping_last_name,
        //             "address1" => $order->shipping_address_1,
        //             "address2" => $order->shipping_address_2,
        //             "city" => $order->shipping_city,
        //             "zipcode" => $order->shipping_postcode,
        //             "phone" => $order->shipping_phone,
        //             "state_name" => $order->shipping_state,
        //             "country" => $order->shipping_country
        //         ];
        //from $order you can get all the item information etc 
        //above is just a simple example how it works
        //your code to send data
        }

    // function cw_custom_checkbox_fields( $checkout ) {
    //     echo '<div class="cw_custom_class"><h3>'.__('Give Sepration Heading: ').'</h3>';
    //     woocommerce_form_field( 'custom_checkbox', array(
    //         'type'          => 'checkbox',
    //         'label'         => __('Agreegation Policy.'),
    //         'required'  => true,
    //     ), $checkout->get_value( 'custom_checkbox' ));
    //     echo '</div>';
    // }
}

$GoodDollar = new GoodDollar();

//activation
register_activation_hook(__FILE__, array($GoodDollar, 'activate'));


//deactivation
register_deactivation_hook(__FILE__, array($GoodDollar, 'deactivate'));

//unistall

