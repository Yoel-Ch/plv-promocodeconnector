<?php


class CustomPromoService
{
    public const ID_SUPPLIER_PROMO = 3;

    public function calculatePromoPerProductPerCartRule(array $orderCartRuleArray, array $promosPerProduct, Cart $cart): array
    {
        $orderCartRule = new OrderCartRule($orderCartRuleArray['id_order_cart_rule']);
        $cartRule = new CartRule($orderCartRule->id_cart_rule);
        $numberOfProducts = count($promosPerProduct);
        $eligibleProductsForCartRule = $this->formatEligibleProducts($cartRule->checkProductRestrictionsFromCart($cart, true));
        $numberOfProductsEligible = count($eligibleProductsForCartRule);
        if ($orderCartRule->value_tax_excl == 0 && !$orderCartRule->free_shipping) {
            return $promosPerProduct;
        }
        $valuePerProduct = $this->calculateValuePerProduct($orderCartRule, $numberOfProductsEligible, $numberOfProducts);
        $isFreeShipping = $orderCartRule->free_shipping;
        if (empty($eligibleProductsForCartRule)) {
            return $this->applyEqualReductionToAllProducts($promosPerProduct, $valuePerProduct, $isFreeShipping);
        }
        return $this->applyReductionToEligibleProducts($eligibleProductsForCartRule, $promosPerProduct, $valuePerProduct, $isFreeShipping);

    }

    public function generatePromoCodes(array $orderCartRules): string
    {
        $codes = '';
        foreach ($orderCartRules as $orderCartRule) {
            if($orderCartRule['id_cart_rule']==self::ID_SUPPLIER_PROMO){
                continue;
            }
            $codes .= ((new CartRule($orderCartRule['id_cart_rule']))->code ?: '') . ' ';
        }
        return trim($codes);
    }

    public function calculateValuePerProduct(OrderCartRule $orderCartRule, int $numberOfProductsEligible, int $numberOfProducts): int|float
    {
        return match (true) {
            $orderCartRule->value_tax_excl > 0 && $numberOfProductsEligible === 0 => $orderCartRule->value_tax_excl / $numberOfProducts,
            $orderCartRule->value_tax_excl > 0 && $numberOfProductsEligible > 0 => $orderCartRule->value_tax_excl / $numberOfProductsEligible,
            default => 0,
        };
    }

    public function applyEqualReductionToAllProducts(array $promosPerProduct, int|float $valuePerProduct, bool $isFreeShipping): array
    {
        foreach ($promosPerProduct as $key => $product) {
            $promosPerProduct[$key]['value_tax_excluded'] += $valuePerProduct;
            if ($promosPerProduct[$key]['is_free_shipping'] === 0) {
                $promosPerProduct[$key]['is_free_shipping'] = $isFreeShipping;
            }
        }
        return $promosPerProduct;
    }

    public function applyReductionToEligibleProducts(array $allEligibleProducts, array $promosPerProduct, int|float $valuePerProduct, bool $isFreeShipping): array
    {
        foreach ($allEligibleProducts as $eligibleProduct) {
            foreach ($promosPerProduct as $key => $product) {
                if (($product['id_product'] !== $eligibleProduct['id_product']) && ($product['id_product_attribute'] !== $eligibleProduct['id_product_attribute'])) {
                    continue;
                }
                $promosPerProduct[$key]['value_tax_excluded'] += $valuePerProduct;
                if ($promosPerProduct[$key]['is_free_shipping'] === 0) {
                    $promosPerProduct[$key]['is_free_shipping'] = $isFreeShipping;
                }
            }
        }
        return $promosPerProduct;
    }

    public function generateInitialPromoArray(int $idOrder, array $orderProducts): array
    {
        return array_map(
            fn($orderProduct) => [
                'id_order' => $idOrder,
                'id_product' => $orderProduct['id_product'],
                'id_product_attribute' => $orderProduct['product_attribute_id'],
                'value_tax_excluded' => 0.00,
                'is_free_shipping' => 0,
            ],
            $orderProducts
        );
    }

    private function formatEligibleProducts(array $eligibleProducts): array
    {
        $simpleArrayOfEligibleProducts = array_map(
            fn($productAndAttribute) => explode('-', $productAndAttribute),
            $eligibleProducts
        );
        return array_map(
            fn($eligibleProduct) => [
                'id_product' => (int)$eligibleProduct[0],
                'id_product_attribute' => (int)$eligibleProduct[1]
            ],
            $simpleArrayOfEligibleProducts
        );
    }
}