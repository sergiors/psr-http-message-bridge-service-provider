<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

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

        $app['argument_value_resolvers'] = $app->extend('argument_value_resolvers', function (array $resolvers) use ($app) {
            return array_merge($resolvers, [
                new Psr7ServerRequestResolver($app['psr7.diactoros_factory'])
            ]);
        });
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(KernelEvents::VIEW,
            function (GetResponseForControllerResultEvent $event) use ($app) {
                $controllerResult = $event->getControllerResult();

                if (!$controllerResult instanceof ResponseInterface) {
                    return;
                }

                $event->setResponse($app['psr7.http_foundation_factory']->createResponse($controllerResult));
            }
        );
    }
}
