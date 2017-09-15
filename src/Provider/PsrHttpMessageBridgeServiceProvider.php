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

final class PsrHttpMessageBridgeServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $app)
    {
        $app['psr7.http_foundation_factory'] = function () {
            return new HttpFoundationFactory();
        };
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
