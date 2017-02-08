<?php

namespace Analogic\AlertBundle\Alerter;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
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

    public function requestException(Request $request, \Exception $exception)
    {
        $subject = "Exception: ".$exception->getMessage();

        $message = $this->templating->render(
            "AnalogicAlertBundle::requestException.html.twig", [
                'url' => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                'request' => $request,
                'exception' => $exception,
                'exception_class' => \get_class($exception)
            ]
        );

        $this->mail($subject, $message);
    }

    public function javascriptException(Request $request, array $exceptionData = [])
    {
        $subject = !empty($exceptionData['message']) ? 'Javascript: '.$exceptionData['message'] : "Javascript error";

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
}