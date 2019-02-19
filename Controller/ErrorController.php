<?php

namespace Analogic\AlertBundle\Controller;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Annotation\Route;
use Analogic\AlertBundle\Alerter\Alerter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends AbstractController
{
    private $alerter;
    private $javascriptIgnoreRegex;
    private $apcuAdapter;

    public function __construct(Alerter $alerter, ?string $javascriptIgnoreRegex, CacheItemPoolInterface $apcuAdapter)
    {
        $this->alerter = $alerter;
        $this->javascriptIgnoreRegex;
        $this->apcuAdapter = $apcuAdapter;
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

        $hash = md5(serialize($data));

        if(!$this->apcuAdapter->hasItem($hash)) {

            $this->alerter->javascriptException($request, $data);

            $item = $this->apcuAdapter->getItem($hash);
            $item->expiresAt(new \DateTime('+1hour'));
            $item->set(true);

            $this->apcuAdapter->save($item);
        }

        return new JsonResponse([]);
    }
}
