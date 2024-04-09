<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;


class JWTCreatedListener {
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $expiration = new \DateTime('+365 day');
        $expiration->setTime(2, 0, 0);


        $payload       = $event->getData();
        $payload['sub'] = $event->getUser()->getId();
        $payload['exp'] = $expiration->getTimestamp();

        $event->setData($payload);

        $header        = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}
