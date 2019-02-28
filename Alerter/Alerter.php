<?php

namespace Analogic\AlertBundle\Alerter;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\HttpFoundation\Request;

class Alerter
{
    private $mailer;
    private $templating;
    private $logger;

    protected $from;
    protected $recipients;
    protected $prefix;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, LoggerInterface $logger, array $from, array $recipients, string $prefix = '')
    {
        $this->mailer = $mailer;

        $this->from = $from;
        $this->recipients = $recipients;
        $this->prefix = $prefix;
        $this->logger = $logger;
        $this->templating = $templating;
    }

    public function customException(string $subject, \Exception $exception, $dump_data = null)
    {
        $message = $this->templating->render(
            "AnalogicAlertBundle::customException.html.twig", [
                'exception' => $exception,
                'exception_class' => \get_class($exception),
                'dump_data' => $dump_data
            ]
        );

        $this->mail($subject, $message);
    }

    public function commandException(ConsoleExceptionEvent $event)
    {
        $exception = $event->getException();
        $subject = "Command exception: ".mb_substr($event->getException()->getMessage(), 0, 32);

        $message = $this->templating->render(
            "AnalogicAlertBundle::commandException.html.twig", [
                'event' => $event,
                'exception' => $exception,
                'exception_class' => \get_class($exception)
            ]
        );

        $this->mail($subject, $message);
    }
    
    public function commandError(ConsoleErrorEvent $event)
    {
        $error = $event->getError();
        $subject = "Command error: ".mb_substr($error->getMessage(), 0, 32);

        $message = $this->templating->render(
            "AnalogicAlertBundle::commandException.html.twig", [
                'event' => $event,
                'exception' => $error,
                'exception_class' => \get_class($error)
            ]
        );

        $this->mail($subject, $message);
    }

    public function customCommandException(Command $command, \Exception $exception, $dump_data = null)
    {
        $subject = "Command exception: ".mb_substr($exception->getMessage(), 0, 32);

        $message = $this->templating->render(
            "AnalogicAlertBundle::customCommandException.html.twig", [
                'command' => $command,
                'exception' => $exception,
                'exception_class' => \get_class($exception),
                'dump_data' => $dump_data
            ]
        );

        $this->mail($subject, $message);
    }

    public function requestException(Request $request, \Exception $exception)
    {
        $subject = "Exception: ".$exception->getMessage();

        $message = $this->templating->render(
            "AnalogicAlertBundle::requestException.html.twig", [
                'url' => isset($_SERVER['HTTP_HOST']) ? 
                (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") :
                'console',
                'request' => $request,
                'exception' => $exception,
                'exception_class' => \get_class($exception)
            ]
        );

        $this->mail($subject, $message);
    }

    public function javascriptException(Request $request, array $exceptionData = [])
    {
        $subject = !empty($exceptionData['message']) ? 'Javascript: '.$this->truncate($exceptionData['message'], 45) : "Javascript error";

        $message = $this->templating->render(
            "AnalogicAlertBundle::javascriptException.html.twig", [
                'url' => (isset($exceptionData['location']) && isset($exceptionData['location']['href'])) ? $exceptionData['location']['href'] : '',
                'request' => $request,
                'exceptionData' => $exceptionData
            ]
        );

        $this->mail($subject, $message);
    }

    private function mail(string $subject, string $htmlMessage)
    {
        $letter = $this->mailer->createMessage();

        $letter
            ->setSubject($this->prefix . $subject)
            ->setFrom(array($this->from['email'] => $this->from['name']))
            ->setBody(strip_tags($htmlMessage));

        /** @var \Swift_Message $letter */
        $letter
            ->addPart($htmlMessage, 'text/html');

        if(!is_array($this->recipients)) {
            $letter->addTo($this->recipients);
        } else if(count($this->recipients) == 1) {
            $letter->addTo($this->recipients[0]);
        } else if(count($this->recipients) > 0) {
            foreach($this->recipients as $recipient) {
                $letter->addCc($recipient);
            }
        }

        try {
            return $this->mailer->send($letter);
        } catch (\Exception $e) {
            $this->logger->error('Mailer error: '.$e->getMessage());
        }
    }

    private function truncate($value, $length = 30, $preserve = false, $separator = '...')
    {
        if(is_array($value)) {
            $value = implode(', ', $value);
        }

        if (mb_strlen($value) > $length) {
            if ($preserve) {
                // If breakpoint is on the last word, return the value without separator.
                if (false === ($breakpoint = mb_strpos($value, ' ', $length))) {
                    return $value;
                }
                $length = $breakpoint;
            }
            return rtrim(mb_substr($value, 0, $length)).$separator;
        }

        return $value;
    }
}
