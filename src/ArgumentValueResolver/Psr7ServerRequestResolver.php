<?php

namespace Sergiors\Silex\Provider;

use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class Psr7ServerRequestResolver implements ArgumentValueResolverInterface
{
    private static $supportedTypes = [
        'Psr\Http\Message\ServerRequestInterface' => true,
        'Psr\Http\Message\RequestInterface' => true,
        'Psr\Http\Message\MessageInterface' => true,
    ];

    private $httpMessageFactory;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->httpMessageFactory = $httpMessageFactory;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return isset(self::$supportedTypes[$argument->getType()]);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->httpMessageFactory->createRequest($request);
    }
}
