<?php
namespace Relevanz\DataProviders;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Plugin\Templates\Twig;

/**
 * Class TrackingPixel
 * @package Relevanz\DataProviders
 */
class TrackingPixel
{
    public static $variationId = '';

    /**
     * @param Twig $twig
     * @param BasketRepositoryContract $basketRepositoryContract
     * @param FrontendSessionStorageFactoryContract $frontendSessionStorageFactoryContract
     * @return string
     */
    public function call(Twig $twig, BasketRepositoryContract $basketRepositoryContract, FrontendSessionStorageFactoryContract $frontendSessionStorageFactoryContract)
    {
        $basketItems = $frontendSessionStorageFactoryContract->getPlugin()->getValue('Relevanz::basketItemIds');
        if (!is_array($basketItems)) {
            $basketItems = [];
        }

        $currentBasketItems  = $basketRepositoryContract->load()->basketItems;
        $existingBasketItems = [];
        $newItems            = [];
        $removedItems        = [];

        foreach ($currentBasketItems as $basketItem) {
            if ($basketItem instanceof BasketItem) {
                $existingBasketItems[] = $basketItem->variationId;

                if (!in_array($basketItem->variationId, $basketItems)) {
                    $basketItems[] = $basketItem->variationId;
                    $newItems[]    = $basketItem->variationId;
                }
            }
        }

        foreach ($basketItems as $index => $basketItemId) {
            if(!in_array($basketItemId, $existingBasketItems)){
                $removedItems[] = $basketItemId;
                unset($basketItems[$index]);
            }
        }

        $frontendSessionStorageFactoryContract->getPlugin()->setValue('Relevanz::basketItemIds', $basketItems);

        return $twig->render('Relevanz::tracking', ['variationId' => self::$variationId, 'newBasketItems' => $newItems, 'removedBasketItems' => $removedItems]);
    }
}