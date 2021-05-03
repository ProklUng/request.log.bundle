<?php

namespace Prokl\RequestLogBundle\EventListener;

use Prokl\RequestLogBundle\Service\ResponseLogger;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

/**
 * Class ResponseLogSubscriber
 * @package Prokl\RequestLogBundle\EventListener
 */
class ResponseLogSubscriber
{
    /**
     * @var ResponseLogger $responseLogger Логгер ответов.
     */
    private $responseLogger;

    /**
     * @const string GENERATE_LOG_HEADER Заголовок, при наличии которого будет сгенерирован мок.
     */
    private const GENERATE_LOG_HEADER = 'x-generate-response-mock';

    /**
     * ResponseLogSubscriber constructor.
     *
     * @param ResponseLogger $responseLogger Логгер ответов.
     */
    public function __construct(ResponseLogger $responseLogger)
    {
        $this->responseLogger = $responseLogger;
    }

    /**
     * @param TerminateEvent $event Событие.
     *
     * @return void
     */
    public function handle(TerminateEvent $event) : void
    {
        if (!$event->isMasterRequest() || !$event->getRequest()->headers->has(self::GENERATE_LOG_HEADER)) {
            return;
        }

        // Восстановленный из мока Response.
        if (strlen($event->getResponse()->headers->get('x-generated-response-mock', '')) > 0) {
            return;
        }

        $this->responseLogger->logResponse($event->getRequest(), $event->getResponse());
    }
}
