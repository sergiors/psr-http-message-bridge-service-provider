<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sergiors\Silex\EventListener\PsrResponseListener;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Sergiors\Silex\ArgumentValueResolver\Psr7ServerRequestResolver;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
final class PsrHttpMessageBridgeServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $app)
    {
        $app['psr7.http_foundation_factory'] = function () {
            return new HttpFoundationFactory();
        };

        $app['psr7.diactoros_factory'] = function () {
            return new DiactorosFactory();
        };

        $app['psr7.listener.response'] = function (Container $app) {
            return new PsrResponseListener($app['psr7.http_foundation_factory']);
        };

        $app['argument_value_resolvers'] = $app->extend('argument_value_resolvers', function (array $resolvers) use ($app) {
            return array_merge($resolvers, [
                new Psr7ServerRequestResolver($app['psr7.diactoros_factory'])
            ]);
        });
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['psr7.listener.response']);
    }
}
