<?php

namespace Analogic\AlertBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Helper extends \Twig_Extension
{
    private $router;
    private $enabled;

    public function __construct($enabled, Router $router)
    {
        $this->enabled = $enabled;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('javascript_error_listener', [$this, 'javascriptErrorListener'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('aDump', [$this, 'analogicDump'], ['is_safe' => ['html']])
        );
    }

    public function javascriptErrorListener()
    {
        if(!$this->enabled) return '';

        return 'window.onerror=function(c,b,a){var d=new XMLHttpRequest();d.open("POST","'.$this->router->generate('analogic_alert_js_error').'");d.setRequestHeader("Content-Type","application/json;charset=UTF-8");d.send(JSON.stringify({message:c,lineNumber:a,file:b,location:window.location}))};';
    }

    public function analogicDump($vars)
    {
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();

        return $dumper->dump($cloner->cloneVar($vars));
    }
}
