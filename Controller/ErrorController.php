<?php

namespace Analogic\AlertBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Analogic\AlertBundle\Alerter\Alerter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="analogic_alert_error_controller")
 */
class ErrorController extends Controller
{
    private $alerter;

    public function __construct(Alerter $alerter)
    {
        $this->alerter = $alerter;
    }

    /**
     * @Route("/_js_error", name="analogic_alert_js_error")
     */
    public function error(Request $request)
    {
        if(empty($request->getContent())) return new JsonResponse([]);

        $data = json_decode($request->getContent(), true);
        if(empty($data) || !is_array($data)) return new JsonResponse([]);

        // TODO message filters

        $this->alerter->javascriptException($request, $data);

        return new JsonResponse([]);
    }
}