<?php

namespace Analogic\AlertBundle\EventListener;

use Analogic\AlertBundle\Alerter\Alerter;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    public $alerter;
    public $ignores;
    public $enabled;

    public function __construct($enabled, Alerter $alerter, $ignores = [])
    {
        $this->enabled = $enabled;
        $this->alerter = $alerter;
        $this->ignores = $ignores;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if(!$this->enabled) return;

        $e = $event->getException();

        foreach($this->ignores as $ignore) {
            if(is_a($e, $ignore)) {
                return;
            }
        }

        $this->alerter->requestException($event->getRequest(), $event->getException());
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        if(!$this->enabled) return;

        $e = $event->getException();

        foreach($this->ignores as $ignore) {
            if(is_a($e, $ignore)) {
                return;
            }
        }

        $this->alerter->commandException($event);
    }
}