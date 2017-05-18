<?php

namespace Analogic\AlertBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class AnalogicAlertExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTreeBuilder()->buildTree(), $configs);

        $container->setParameter($this->getAlias().'.enabled', $config['enabled']);
        $container->setParameter($this->getAlias().'.prefix', $config['prefix']);
        $container->setParameter($this->getAlias().'.ignore', $config['ignore']);
        $container->setParameter($this->getAlias().'.javascript_ignore_regex', $config['javascript_ignore_regex']);
        $container->setParameter($this->getAlias().'.from', $config['from']);
        $container->setParameter($this->getAlias().'.to', $config['to']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
