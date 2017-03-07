<?php

namespace Limit0\AssetsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('limit0_assets');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
        ->children()
            ->enumNode('engine')
                ->values(['aws_s3', 'local_storage'])
            ->end()
            ->scalarNode('http_prefix')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('aws_s3')
                ->children()
                    ->scalarNode('region')->defaultValue('us-east-1')->end()
                    ->scalarNode('acl')->defaultValue('public-read')->end()
                    ->scalarNode('bucket')->end()
                ->end()
            ->end()
            ->arrayNode('local_storage')
                ->children()
                    ->scalarNode('path')->end()
                ->end()
            ->end()
        ->end()
        ->validate()
            ->always()
            ->then(function ($v) {
                if ('aws_s3' === $v['engine']) {
                    if (!array_key_exists('bucket', $v['aws_s3']) || empty($v['aws_s3']['bucket'])) {
                        throw new InvalidConfigurationException('Bucket must be set when using AWS S3 asset storage.');
                    }
                }
                if ('local_storage' === $v['engine']) {
                    if (!array_key_exists('bucket', $v['local_storage']) || empty($v['local_storage']['path'])) {
                        throw new InvalidConfigurationException('Upload path must be set when using local asset storage.');
                    }
                }
                return $v;
            })
        ->end()
        ;

        return $treeBuilder;
    }
}
