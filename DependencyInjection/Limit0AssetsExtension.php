<?php

namespace Limit0\AssetsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class Limit0AssetsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('limit0_assets.http_prefix', $config['http_prefix']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Configure storage engine
        $engineKey = sprintf('limit0_assets.engine.%s', $config['engine']);
        $engine = $container->getDefinition($engineKey);

        switch ($config['engine']) {
            case 'aws_s3':
                $engine->addMethodCall('setRegion', [$config['aws_s3']['region']]);
                $engine->addMethodCall('setAcl', [$config['aws_s3']['acl']]);
                $engine->addMethodCall('setBucket', [$config['aws_s3']['bucket']]);
                break;

            case 'local_storage':
                $engine->addMethodCall('setPath', [$config['local_storage']['path']]);
                break;
        }

        // Assign the storage engine
        $processor = $container->getDefinition('limit0_assets.manager');
        $processor->addMethodCall('setStorageEngine', [new Reference($engineKey)]);
    }
}
