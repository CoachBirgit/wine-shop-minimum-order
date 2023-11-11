<?php
/**
 * Plugin Name: Wine Shop Minimum Order
 * Plugin URI: https://coachbirgit.com/plugins/wine-shop-minimum-order
 * Description: A WooCommerce extension that enforces a minimum order quantity of wine bottles in the cart with customizable increments, designed for wineries and wine shops looking to optimize their sales process and contribute to a greener environment.
 * Version: 1.0.0
 * Author: Birgit Olzem
 * Author URI: https://coachbirgit.com
 * Text Domain: wine-shop-minimum-order
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 7.5.x
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wine_Shop_Minimum_Order' ) ) :

class Wine_Shop_Minimum_Order {

    public function __construct() {
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_item_quantities' ) );
        add_action( 'wp_ajax_wine_shop_minimum_order_update_cart', array( $this, 'ajax_update_cart' ) );
        add_action( 'wp_ajax_nopriv_wine_shop_minimum_order_update_cart', array( $this, 'ajax_update_cart' ) );
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
        add_action( 'woocommerce_settings_tabs_wine_shop_minimum_order', array( $this, 'settings_tab_content' ) );
        add_action( 'woocommerce_update_options_wine_shop_minimum_order', array( $this, 'update_settings' ) );
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'wine-shop-minimum-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function enqueue_scripts() {
        if ( is_cart() || is_checkout() ) {
            wp_enqueue_script( 'wine-shop-minimum-order-ajax', plugin_dir_url( __FILE__ ) . 'js/wine-shop-minimum-order-ajax.js', array( 'jquery' ), '1.0.0', true );
            wp_localize_script( 'wine-shop-minimum-order-ajax', 'wine_shop_minimum_order_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }
    }

    public function check_cart_item_quantities() {
        $min_bottles = get_option( 'wc_wine_shop_minimum_order_min_bottles', 6 );
        $custom_message_minimum = get_option( 'wc_wine_shop_minimum_order_custom_message_minimum', 'You must have a minimum of %s bottles in your cart.' );
        $custom_message_divisible = get_option( 'wc_wine_shop_minimum_order_custom_message_divisible', 'You must have a multiple of %s bottles in your cart.' );
        $cart_items_count = WC()->cart->get_cart_contents_count();
    
        if ($cart_items_count < $min_bottles) {
            wc_add_notice( sprintf( __( $custom_message_minimum, 'wine-shop-minimum-order' ), $min_bottles ), 'error' );
        } elseif ( $cart_items_count % $min_bottles !== 0 ) {
            wc_add_notice( sprintf( __( $custom_message_divisible, 'wine-shop-minimum-order' ), $min_bottles ), 'error' );
        }
    }

    public function ajax_update_cart() {
        $min_bottles = get_option( 'wc_wine_shop_minimum_order_min_bottles', 6 );
        $cart_items_count = WC()->cart->get_cart_contents_count();

        if ( $cart_items_count % $min_bottles === 0 ) {
            $result = array(
                'success' => true,
                'message' => __( 'Your cart has been updated!', 'wine-shop-minimum-order' )
           
            );
        } else {
            $result = array(
                'success' => false,
                'message' => sprintf( __( 'You must have a multiple of %s bottles in your cart.', 'wine-shop-minimum-order' ), $min_bottles )
            );
        }

        wp_send_json( $result );
    }

    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['wine_shop_minimum_order'] = __( 'Wine Shop Minimum Order', 'wine-shop-minimum-order' );
        return $settings_tabs;
    }

    public function settings_tab_content() {
        woocommerce_admin_fields( $this->get_settings() );
    }

    public function update_settings() {
        woocommerce_update_options( $this->get_settings() );
    }

    public function get_settings() {
        $settings = array(
            'section_title' => array(
                'name' => __( 'Wine Shop Minimum Order Settings', 'wine-shop-minimum-order' ),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_wine_shop_minimum_order_section_title'
            ),
            'min_bottles' => array(
                'name' => __( 'Minimum Bottles', 'wine-shop-minimum-order' ),
                'type' => 'number',
                'desc' => __( 'Minimum number of bottles required in the cart.', 'wine-shop-minimum-order' ),
                'id' => 'wc_wine_shop_minimum_order_min_bottles',
                'default' => 6,
                'desc_tip' => true
            ),
            'custom_message_minimum' => array(
                'name' => __( 'Minimum Quantity Message', 'wine-shop-minimum-order' ),
                'type' => 'textarea',
                'desc' => __( 'Custom message displayed when the cart does not meet the minimum quantity. Use %s as a placeholder for the minimum quantity.', 'wine-shop-minimum-order' ),
                'id' => 'wc_wine_shop_minimum_order_custom_message_minimum',
                'default' => __( 'You must have a minimum of %s bottles in your cart.', 'wine-shop-minimum-order' ),
                'desc_tip' => true
            ),
            'custom_message_divisible' => array(
                'name' => __( 'Divisible Quantity Message', 'wine-shop-minimum-order' ),
                'type' => 'textarea',
                'desc' => __( 'Custom message displayed when the cart quantity is not divisible by the minimum quantity. Use %s as a placeholder for the minimum quantity.', 'wine-shop-minimum-order' ),
                'id' => 'wc_wine_shop_minimum_order_custom_message_divisible',
                'default' => __( 'You must have a multiple of %s bottles in your cart.', 'wine-shop-minimum-order' ),
                'desc_tip' => true
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_wine_shop_minimum_order_section_end'
            )
        );

        return apply_filters( 'wc_wine_shop_minimum_order_settings', $settings );
    }

}

new Wine_Shop_Minimum_Order();

endif;