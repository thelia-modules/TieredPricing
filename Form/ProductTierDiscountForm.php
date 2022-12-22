<?php


namespace TieredPricing\Form;


use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Form\BaseForm;

class ProductTierDiscountForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add('product_id', HiddenType::class)
            ->add('product_sale_elements_id', TextType::class)
            ->add('tier_quantity', IntegerType::class)
            ->add('discount_percent', IntegerType::class)
            ;
    }
}