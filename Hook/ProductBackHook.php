<?php

namespace TieredPricing\Hook;

use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Hook\BaseHook;
use TieredPricing\TieredPricing;

class ProductBackHook extends BaseHook
{
    public function addProductDataTab(HookRenderBlockEvent $event): void
    {
        $event->add(
            [
                'id' => 'tiered_pricing',
                'title' => $this->trans('Tiered pricing', [], TieredPricing::DOMAIN_NAME),
                'content' => $this->render(
                    'tieredPricing/hook-product-data-tab.html',
                    [
                        'productId' => $event->getArgument('id')
                    ]
                ),
            ]
        );
    }

    public static function getSubscribedHooks()
    {
        return [
            'product.tab' => [
                [
                    'type' => "back",
                    'method' => "addProductDataTab"
                ]
            ],
        ];
    }
}