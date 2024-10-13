<?php

class PromoCode extends ObjectModel
{
    public $id_order;
    public $id_product;
    public $id_product_attribute;
    public $promo_code;
    public $value_tax_excluded;
    public $is_free_shipping;

    public static $definition = [
        'table' => 'promo_code_connector',
        'primary' => 'id_promo_code_connector',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'promo_code' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'value_tax_excluded' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'is_free_shipping' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true]
        ]
    ];

    protected $webserviceParameters = [
        'objectNodeName' => 'promo_code',
        'objectsNodeName' => 'promo_codes',
        'fields' => array(
            'id_order' => array('required' => true),
            'id_product' => array('required' => false),
            'id_product_attribute' => array('required' => false),
            'promo_code' => array('required' => false),
            'value_tax_excluded' => array('required' => false),
            'is_free_shipping' => array('required' => true),
        )
    ];
}