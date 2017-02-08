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

        /*
        if(isset($data['message'])) {
            if(preg_match('~(FoxbrowserToolsLoaded|DealPly)~i', $data['message'])) return new JsonResponse([]);
        }

        if(!empty($data['file'])) {
            if(!preg_match('~(satoshibox|onion|satoshicrypt|undefined)~i', $data['file'])) return new JsonResponse([]);
        }*/

        $this->alerter->javascriptException($request, $data);

        return new JsonResponse([]);
    }
}