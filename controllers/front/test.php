<?php

class promocodeconnectortestModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();


        die;
//        foreach ($promosPerProduct as $item) {
//            if ($item['value_tax_excluded'] === 0) {
//                continue;
//            }
//            Db::getInstance()->insert('promo_code_connector', [
//                'id_order' => $item['id_order'],
//                'id_product' => $item['id_product'],
//                'id_product_attribute' => $item['id_product_attribute'],
//                'promo_code' => $promoCodes,
//                'value_tax_excluded' => round($item['value_tax_excluded'], 2),
//                'is_free_shipping' => $item['is_free_shipping']
//            ]);
//        }

    }
}