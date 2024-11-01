<?php
/**
 * Plugin Name: WooCommerce Service Fee
 * Plugin URI: http://www.bjorntech.se/servicefee
 * Description: Apply a service fee to orders by default or if the order value is below a minimum amount.
 * Version: 2.0.0
 * Text Domain: woocommerce-service-fee
 * Domain Path: /languages
 * Author: BjornTech
 * Author URI: http://www.bjorntech.com
 * Requires PHP: 7.1
 * PHP version 7.1
 *
 * @category Service Fee
 * @package  Main
 * @author   BjornTech <info@bjorntech.se>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3.0 (or later)
 * @link     http://www.bjorntech.se/servicefee
 **/

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'init_woocommerce_service_fee', 0);

/**
 * Adds notice in case of WooCommerce being inactive
 *
 * @return null
 */
function wc_woocommerce_service_fee_inactive_notice()
{
    $class = 'notice notice-error';
    $headline = __('Service Fee requires WooCommerce to be active.', 'woocommerce-service-fee');
    $message = __('Go to the plugins page to activate WooCommerce', 'woocommerce-service-fee');
    printf('<div class="notice notice-error"><h2>%1$s</h2><p>%2$s</p></div>', $headline, $message);
}

function init_woocommerce_service_fee()
{

    if (!function_exists('is_woocommerce_activated')) {
        function is_woocommerce_activated()
        {
            if (class_exists('woocommerce')) {return true;} else {return false;}
        }
    }

    if (!is_woocommerce_activated()) {
        add_action('admin_notices', 'wc_woocommerce_service_fee_inactive_notice');
        return;
    }

    require_once 'classes/woocommerce-service-fee-settings.php';

    /**
     *    Main class
     *
     */
    class WC_Service_Fee extends WC_Integration
    {

        /**
         *  Class instance
         *
         * @var    mixed
         * @access public
         * @static
         */
        public static $instance = null;

        /**
         * Constructor for main class
         * 
         **/
        public function __construct()
        {
            global $woocommerce;
            $this->id = 'woocommerce-service-fee';
            $this->method_title = __('Service Fee', 'woocommerce-service-fee');
            $this->method_description = __('Apply a service fee to orders by default or if the order value is below a minimum amount.', 'woocommerce-service-fee');
            $this->init_form_fields();
            $this->init_settings();
            add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Initialize integration settings form fields.
         *
         * @return void
         */
        public function init_form_fields()
        {
            $this->form_fields = WC_Service_Fee_Settings::get_fields();
        }

        /**
         * Returns a new instance of self, if it does not already exist.
         *
         * @access public
         * @static
         * @return WC_Service_Fee
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Hooks and filters to use
         */
        public function hooks_and_filters()
        {
            add_action('plugins_loaded', array($this, 'woocommerce_service_fee_install'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
            if ($this->get_option('option_enabled') == 'yes') {
                add_action('woocommerce_cart_calculate_fees', array(&$this, 'add_service_fee'));
            }
        }

        /**
         * Show action links on the plugin screen.
         *
         * @param    mixed $links Plugin Action links
         * @return    array
         */
        public static function plugin_action_links($links)
        {
            $action_links = array(
                'settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=integration&section=woocommerce-service-fee') . '" aria-label="' . esc_attr__('View settings', 'woocommerce') . '">' . esc_html__('Settings', 'woocommerce') . '</a>',
            );

            return array_merge($action_links, $links);
        }

        public function add_service_fee()
        {
            global $woocommerce;

            $raw_option_cost = wc_clean($this->get_option('option_cost', 0));
            $is_percentage = (bool) strstr($raw_option_cost, '%');
            $option_cost = wc_format_decimal($raw_option_cost);
            $option_min_order = floatval($this->get_option('option_min_order', 0));
            $option_tax_class = $this->get_option('option_tax_class');
            $option_mix_max = $this->get_option('option_mix_max', 'minimum');
            $total = $woocommerce->cart->subtotal;

            if ($is_percentage) {
                $option_cost = ($option_cost / 100) * $total;
                $option_cost = wc_format_decimal($option_cost);
            }

            if (($option_min_order == 0) ||
                (($option_mix_max == 'minimum') && ($total <= $option_min_order)) ||
                (($option_mix_max == 'maximum') && ($total >= $option_min_order))) {
                $woocommerce->cart->add_fee(
                    $this->get_option('option_label'),
                    $option_cost,
                    $option_tax_class == 'no_tax' ? false : true,
                    $option_tax_class == 'no_tax' ? '' : $option_tax_class
                );
            }

        }

        public function woocommerce_service_fee_install()
        {

            $current_version = $this->get_option('plugin_version');

            if ($current_version != '' && version_compare($current_version, '1.15.0', '<')) {
                if ($this->get_option('option_taxable') != 'yes') {
                    $this->update_option('option_tax_class', 'no_tax');
                }
                if ($this->get_option('option_type') == 'percentage') {
                    $this->update_option('option_cost', $this->get_option('option_cost') . '%');
                }

                $this->update_option('plugin_version', '1.15.1');
            }
        }
    }

    /**
     * Make the object available for later use
     *
     * @return WC_Service_Fee
     */
    function WC_SF()
    {
        return WC_Service_Fee::get_instance();
    }

    /**
     * Instantiate
     */
    WC_SF();
    WC_SF()->hooks_and_filters();

    /**
     * Add the integration to WooCommerce
     */
    function add_servicefee_integration($integrations)
    {
        $integrations[] = 'WC_Service_Fee';
        return $integrations;
    }

    add_filter('woocommerce_integrations', 'add_servicefee_integration');
}
