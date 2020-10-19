<?php
namespace Relevanz\Providers;

use Plenty\Plugin\RouteServiceProvider as PlentyRouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;

/**
 * Class RouteServiceProvider
 * @package Relevanz\Providers
 */
class RouteServiceProvider extends PlentyRouteServiceProvider
{
    public function map(ApiRouter $apiRouter)
    {
        $apiRouter->version(['v1'], ['namespace' => 'Relevanz\Controllers', 'middleware' => 'oauth'],
            function ($api) {
                $api->get('relevanz/settings/', 'Settings@index');
            }
        );
    }
}