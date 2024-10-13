<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'promocodeconnector/src/Entity/PromoCode.php';
require_once _PS_MODULE_DIR_ . 'promocodeconnector/Service/CustomPromoService.php';

class promocodeconnector extends Module
{
    public function __construct()
    {
        $this->name = 'promocodeconnector';
        $this->tab = 'checkout';
        $this->version = '1.0.0';
        $this->author = 'Yoel CHICHEPORTICHE';
        $this->need_instance = 0;
        parent::__construct();

        $this->displayName = $this->l('Permet la récupération des codes promos produit par produit');
        $this->description = $this->l('Permet la récupération des codes promos produit par produit dans l\'ERP');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('addWebserviceResources') &&
            $this->installDb();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDb();
    }

    private function installDb()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "promo_code_connector` (
            `id_promo_code_connector` INT(11) NOT NULL AUTO_INCREMENT,
            `id_order` INT(11) NOT NULL,
            `id_product` INT(11),
            `id_product_attribute` INT(11),
            `promo_code` VARCHAR(255),
            `value_tax_excluded` DECIMAL(20,2) DEFAULT '0.000000',
            `is_free_shipping` TINYINT(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id_promo_code_connector`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        return Db::getInstance()->execute($sql);
    }

    private function uninstallDb()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "order_custom_field`;";
        return Db::getInstance()->execute($sql);
    }

    public function hookActionValidateOrder($params)
    {
        /** @var Order $idOrder */
        $order = $params['order'];
        $orderCartRules = $order->getCartRules();
        $customPromoService = new CustomPromoService();
        if(empty($orderCartRules)){
            return;
        }
        $promoCodes = $customPromoService->generatePromoCodes($orderCartRules);
        $promosPerProduct = $customPromoService->generateInitialPromoArray($order->id, $order->getProducts());
        foreach ($orderCartRules as $orderCartRule) {
            $promosPerProduct = $customPromoService->calculatePromoPerProductPerCartRule($orderCartRule, $promosPerProduct, new Cart($order->id_cart));
        }
        foreach ($promosPerProduct as $item) {
            if ($item['value_tax_excluded'] === 0) {
                continue;
            }
            Db::getInstance()->insert('promo_code_connector', [
                'id_order' => $item['id_order'],
                'id_product' => $item['id_product'],
                'id_product_attribute' => $item['id_product_attribute'],
                'promo_code' => $promoCodes,
                'value_tax_excluded' => round($item['value_tax_excluded'], 2),
                'is_free_shipping' => $item['is_free_shipping']
            ]);
        }
    }

    public function hookAddWebserviceResources($params)
    {
        return [
            'promo_codes' => array(
                'description' => 'Codes promos de la commande',
                'class' => 'PromoCode',
                'forbidden_method' => array('DELETE')
            )
        ];

    }
}