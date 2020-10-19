<?php
namespace Relevanz\Providers;

use IO\Helper\TemplateContainer;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;
use Plenty\Plugin\Events\Dispatcher;
use Relevanz\DataProviders\TrackingPixel;
use Relevanz\Generators\Items;
use Relevanz\ResultFields\Items as ResultFieldsItems;

/**
 * Class ServiceProvider
 * @package Relevanz\Providers
 */
class ServiceProvider extends DataExchangeServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(RouteServiceProvider::class);
    }

    /**
     * @param ExportPresetContainer $exportPresetContainer
     * @param Dispatcher $eventDispatcher
     */
    public function exports(ExportPresetContainer $exportPresetContainer, Dispatcher $eventDispatcher){

        $eventDispatcher->listen('IO.tpl.item', function (TemplateContainer $templateContainer, $tplData) {
            TrackingPixel::$variationId = $tplData['variationId'];
        }, 99);

        $exportPresetContainer->add(
            'releva.nz',
            ResultFieldsItems::class,
            Items::class,
            '',
            true,
            true
        );
    }
}