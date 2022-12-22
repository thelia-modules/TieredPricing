<?php

namespace TieredPricing\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Tools\URL;
use TieredPricing\Form\ProductTierDiscountForm;
use TieredPricing\Model\ProductTierDiscount;
use TieredPricing\Model\ProductTierDiscountQuery;

#[Route(path: "/admin/module/TieredPricing/product_tier", name: "tiered_pricing_product_tier_")]
class ProductTierDiscountController extends BaseAdminController
{
    #[Route(path: "/add", name: "add", methods: ['POST'])]
    public function add()
    {
        $form = $this->validateForm($this->createForm(ProductTierDiscountForm::getName()));
        $data = $form->getData();

        $productSaleElementsId = $data['product_sale_elements_id'] !== 'all' ? $data['product_sale_elements_id'] : null;

        $productTierDiscount = ProductTierDiscountQuery::create()
            ->filterByProductId($data['product_id'])
            ->filterByProductSaleElementsId($productSaleElementsId)
            ->filterByTierQuantity($data['tier_quantity'])
            ->findOneOrCreate();

        $productTierDiscount->setDiscountPercent($data['discount_percent'])
            ->save();

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl(
                '/admin/products/update',
                [
                    'product_id' => $data['product_id'],
                    'current_tab' => 'tiered_pricing'
                ]
            )
        );
    }

    #[Route(path: "/delete/{id}", name: "delete", methods: ['GET'])]
    public function delete($id)
    {
        $productTierDiscount = ProductTierDiscountQuery::create()
            ->findOneById($id);

        $productTierDiscount->delete();

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl(
                '/admin/products/update',
                [
                    'product_id' => $productTierDiscount->getProductId(),
                    'current_tab' => 'tiered_pricing'
                ]
            )
        );
    }

    #[Route(path: "/{id}", name: "update", methods: ['POST'])]
    public function update($id)
    {
        $form = $this->validateForm($this->createForm(ProductTierDiscountForm::getName()));
        $data = $form->getData();

        $productSaleElementsId = $data['product_sale_elements_id'] !== 'all' ? $data['product_sale_elements_id'] : null;

        $productTierDiscount = ProductTierDiscountQuery::create()
            ->findOneById($id);

        $productTierDiscount
            ->setProductId($data['product_id'])
            ->setProductSaleElementsId($productSaleElementsId)
            ->setTierQuantity($data['tier_quantity'])
            ->setDiscountPercent($data['discount_percent'])
            ->save();

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl(
                '/admin/products/update',
                [
                    'product_id' => $data['product_id'],
                    'current_tab' => 'tiered_pricing'
                ]
            )
        );
    }
}