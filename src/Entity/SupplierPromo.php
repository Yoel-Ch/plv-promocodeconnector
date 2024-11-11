<?php

class SupplierPromo extends ObjectModel
{
    public $id_order;
    public $id_product;
    public $id_product_attribute;
    public $value_tax_excluded;

    public static $definition = [
        'table' => 'promo_code_connector_supplier',
        'primary' => 'id_promo_code_connector_supplier',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'value_tax_excluded' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
        ]
    ];

    protected $webserviceParameters = [
        'objectNodeName' => 'promo_code_supplier',
        'objectsNodeName' => 'promo_code_suppliers',
        'fields' => array(
            'id_order' => array('required' => true),
            'id_product' => array('required' => false),
            'id_product_attribute' => array('required' => false),
            'value_tax_excluded' => array('required' => false),
        )
    ];
}