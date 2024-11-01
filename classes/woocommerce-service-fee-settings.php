<?php

class WC_Service_Fee_Settings {
    	/**
	* get_fields function.
	*
	* Returns an array of available admin settings fields
	*
	* @access public static
	* @return array
	*/
	public static function get_fields() {
        $fields = array(
			'option_enabled' => array(
                'title'             => __( 'Enable', 'woocommerce-service-fee' ),
                'type'              => 'checkbox',
                'desc_tip'          => false,
                'default'           => ''
            ),
            'option_label' => array(
                'title'             => __( 'Label', 'woocommerce-service-fee' ),
                'type'              => 'text',
                'description'       => __( 'The label of the service fee, used on orders', 'woocommerce-service-fee' ),
                'desc_tip'          => true,
                'default'           => __( 'Service Fee', 'woocommerce-service-fee' ),
            ),
            'option_cost' => array(
                'title'             => __( 'Amount or %', 'woocommerce-service-fee' ),
                'type'              => 'text',
                'description'       => __( 'The fee, a fixed amount or percentage (add % after the number) that you want to apply as service fee on the order', 'woocommerce-service-fee' ),
                'desc_tip'          => true,
                'default'           => '0'
            ),
            'option_tax_class' => array(
                'title'             => __( 'Tax class', 'woocommerce-service-fee' ),
                'type'              => 'select',
                'css'               => 'min-width:150px;',
                'class'             => 'wc-enhanced-select',
                'description'       => __( 'The tax class for the fee if taxable', 'woocommerce-service-fee' ),
                'options'           => array_merge( array( 'no_tax' => __( 'Do not add tax on the fee', 'woocommerce-service-fee' ) ), wc_get_product_tax_class_options() ),
                'desc_tip'          => true,
                'default'           => 'no_tax'
            ),
			'option_min_order' => array(
                'title'             => __( 'Order value', 'woocommerce-service-fee' ),
                'type'              => 'text',
                'description'       => __( 'Optional, apply extra fee when cart total is related as below to this amount', 'woocommerce-service-fee' ),
                'desc_tip'          => true,
                'default'           => '0'
            ),
            'option_mix_max' => array(
                'title'             => __( 'Min/Max order', 'woocommerce-service-fee' ),
                'type'              => 'select',
                'description'       => __( 'Select if the fee should be applied on orders below or above the Order Value', 'woocommerce-service-fee' ),
                'desc_tip'          => true,
                'options' => array(
                    'minimum' => __('Apply fee when the order is below or equal to the Order value','woocommerce-service-fee' ),
                    'maximum' => __('Apply fee when the order is above or equal to the Order value','woocommerce-service-fee' ),
                ),
			),
        );
        
        return $fields;
    }		
    
}