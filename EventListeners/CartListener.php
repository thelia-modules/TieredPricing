<?php

namespace TieredPricing\EventListeners;

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartItem;
use Thelia\Model\ProductSaleElements;
use TieredPricing\Model\ProductTierDiscount;
use TieredPricing\Model\ProductTierDiscountQuery;

class CartListener implements EventSubscriberInterface
{
    public function onCartItemUpdate(CartEvent $cartEvent): void
    {
        $cart = $cartEvent->getCart();
        $customer = $cart->getCustomer();
        $currency = $cart->getCurrency();

        $productId = $cartEvent->getCartItem()?->getProductId();

        // Check for all cartItems for the same product
        $cartItemToChecks = array_filter(
            iterator_to_array($cart->getCartItems()),
            function (CartItem $cartItem) use ($productId) {
                return null === $productId || $cartItem->getProductId() === $productId;
            }
        );

        foreach ($cartItemToChecks as $cartItemToCheck) {
            $tierDiscount = $this->getProductTierDiscountToApply($cartEvent, $cartItemToCheck);
            $tierDiscountPercent = $tierDiscount ? $tierDiscount->getDiscountPercent() : 0;

            $discount = 0;
            if (null !== $customer && $customer->getDiscount() > 0) {
                $discount = $customer->getDiscount();
            }

            /** @var ProductSaleElements $productSaleElements */
            $productSaleElements = $cartItemToCheck->getProductSaleElements();
            $productPrices = $productSaleElements->getPricesByCurrency($currency, $discount);
            $cartItemToCheck->setPrice($productPrices->getPrice() * ((100 - $tierDiscountPercent)/100))
                ->setPromoPrice($productPrices->getPromoPrice() * ((100 - $tierDiscountPercent)/100))
                ->save();
        }
    }

    private function getProductTierDiscountToApply(CartEvent $cartEvent, CartItem $cartItem): ?ProductTierDiscount
    {
        $productId = $cartItem->getProductId();
        $productSaleElementsId = $cartItem->getProductSaleElementsId();

        $tierDiscounts = iterator_to_array(ProductTierDiscountQuery::create()
            ->filterByProductId($productId)
            ->filterByProductSaleElementsId(null, Criteria::ISNULL)
            ->_or()
            ->filterByProductSaleElementsId($productSaleElementsId)
            // Firstly apply tier by specific pse then by product
            ->orderByProductSaleElementsId(Criteria::DESC)
            // Apply tier from bigger quantity to lower
            ->orderByTierQuantity(Criteria::DESC)
            ->find());

        if (empty($tierDiscounts)) {
            return  null;
        }

        $cart = $cartEvent->getCart();
        $productQuantityInCart = array_reduce(
            iterator_to_array($cart->getCartItems()),
            function ($carry, CartItem $cartItem) use ($productId) {
                if ($cartItem->getProductId() === $productId) {
                    $carry = $carry + $cartItem->getQuantity();
                }
                return $carry;
            },
            0
        );

        /** @var ProductTierDiscount $tierDiscount */
        foreach ($tierDiscounts as $tierDiscount) {
            if (null !== $tierDiscount->getProductSaleElementsId()) {
                if ($cartItem->getQuantity() < $tierDiscount->getTierQuantity()) {
                    continue;
                }

                return  $tierDiscount;
            }

            if ($productQuantityInCart < $tierDiscount->getTierQuantity()) {
                continue;
            }

            return $tierDiscount;
        }

        return null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::CART_ADDITEM => ['onCartItemUpdate', 100],
            TheliaEvents::CART_UPDATEITEM => ['onCartItemUpdate', 100],
            TheliaEvents::CART_DELETEITEM => ['onCartItemUpdate', 100]
        ];
    }
}
