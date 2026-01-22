<?php

/**
 * @package wc-payment-gateway-base
 */

declare(strict_types=1);

namespace AlexMinza\WC_Payment_Gateway;

defined('ABSPATH') || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class WC_Payment_Gateway_WBC_Base extends AbstractPaymentMethodType
{
    /**
     * The gateway instance.
     *
     * @var WC_Payment_Gateway_Base
     */
    protected $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        $gateways = WC()->payment_gateways()->payment_gateways();

        $this->gateway  = $gateways[$this->name];
        $this->settings = get_option($this->gateway->get_option_key(), array());
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        return $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        $script_id = "wc-{$this->name}-block-frontend";

        wp_register_script(
            $script_id,
            plugins_url('assets/js/blocks.js', $this->gateway::$mod_plugin_file),
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ),
            $this->gateway::MOD_VERSION,
            true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                $script_id,
                $this->gateway::MOD_TEXT_DOMAIN,
                plugin_dir_path($this->gateway::$mod_plugin_file) . 'languages'
            );
        }

        return array($script_id);
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        return array(
            'id'          => $this->gateway->id,
            'title'       => $this->gateway->title,
            'description' => $this->gateway->description,
            'icon'        => $this->gateway->icon,
            'supports'    => array_filter($this->gateway->supports, array($this->gateway, 'supports')),
        );
    }
}
