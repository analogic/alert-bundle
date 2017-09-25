<?php

namespace Analogic\AlertBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Analogic\AlertBundle\Alerter\Alerter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends Controller
{
    private $alerter;
    private $javascriptIgnoreRegex;

    public function __construct(Alerter $alerter, ?string $javascriptIgnoreRegex)
    {
        $this->alerter = $alerter;
        $this->javascriptIgnoreRegex;
    }

    /**
     * @Route("/_js_error", name="analogic_alert_js_error")
     */
    public function error(Request $request)
    {
        if(empty($request->getContent())) return new JsonResponse([]);

        $data = json_decode($request->getContent(), true);
        if(empty($data) || !is_array($data)) return new JsonResponse([]);

        if(is_array($data['message'])) {
            $subject = json_encode($data['message']);
        } else {
            $subject = $data['message'];
        }

        if(preg_match('~(FoxbrowserToolsLoaded|vid_mate_check)~', $subject)) {
            // we are ignoring this message
            return new JsonResponse([]);
        }

        if (!empty($subject) && !empty($this->javascriptIgnoreRegex) && preg_match($this->javascriptIgnoreRegex, $subject)) {
            // we are ignoring this message
            return new JsonResponse([]);
        }

        $this->alerter->javascriptException($request, $data);

        return new JsonResponse([]);
    }
}