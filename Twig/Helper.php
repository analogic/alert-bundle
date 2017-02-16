<?php

namespace Analogic\AlertBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

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
        // Copied from \Twig_Extension_Debug
        // dump is safe if var_dump is overridden by xdebug
        $isDumpOutputHtmlSafe = extension_loaded('xdebug')
            // false means that it was not set (and the default is on) or it explicitly enabled
            && (false === ini_get('xdebug.overload_var_dump') || ini_get('xdebug.overload_var_dump'))
            // false means that it was not set (and the default is on) or it explicitly enabled
            // xdebug.overload_var_dump produces HTML only when html_errors is also enabled
            && (false === ini_get('html_errors') || ini_get('html_errors'))
            || 'cli' === php_sapi_name()
        ;

        return array(
            new \Twig_SimpleFunction('javascript_error_listener', [$this, 'javascriptErrorListener'], ['is_safe' => ['html']]),
            new \Twig_Function('aDump', 'analogic_dump', array('is_safe' => $isDumpOutputHtmlSafe ? array('html') : array(), 'needs_context' => true, 'needs_environment' => true)),
        );
    }

    public function javascriptErrorListener()
    {
        if(!$this->enabled) return '';

        return 'window.onerror=function(c,b,a){var d=new XMLHttpRequest();d.open("POST","'.$this->router->generate('analogic_alert_js_error').'");d.setRequestHeader("Content-Type","application/json;charset=UTF-8");d.send(JSON.stringify({message:c,lineNumber:a,file:b,location:window.location}))};';
    }
}

function analogic_dump(\Twig_Environment $env, $context, ...$vars)
{
    // copied from \Twig_Extension_Debug
    ob_start();

    if (!$vars) {
        $vars = array();
        foreach ($context as $key => $value) {
            if (!$value instanceof \Twig_Template) {
                $vars[$key] = $value;
            }
        }

        var_dump($vars);
    } else {
        var_dump(...$vars);
    }

    return ob_get_clean();
}