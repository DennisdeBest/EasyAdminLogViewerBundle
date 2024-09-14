<?php

namespace CodeBuds\EasyAdminLogViewerBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class EasyAdminLogViewerBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('levels')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('level')->end()
                            ->scalarNode('class')->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        ['level' => 'INFO', 'class' => 'info'],
                        ['level' => 'ERROR', 'class' => 'danger'],
                        ['level' => 'CRITICAL', 'class' => 'danger'],
                        ['level' => 'DEBUG', 'class' => 'secondary'],
                    ])
                ->end()
                ->scalarNode('route_prefix')
                    ->defaultValue('/admin')
                ->end()
            ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->parameters()
            ->set('easy_admin_log_viewer.levels', $config['levels']);

        $container->parameters()
            ->set('easy_admin_log_viewer.route_prefix', $config['route_prefix']);


        $bundles = $builder->getParameter('kernel.bundles');
        if (isset($bundles['TwigComponentBundle'])) {
            $builder->prependExtensionConfig('twig_component', ['defaults' => ['CodeBuds\\EasyAdminLogViewerBundle\\Twig\\Components\\' => '@EasyAdminLogViewer/components/']]);
        }

        if (isset($bundles['TwigBundle'])) {
            $builder->prependExtensionConfig('twig', ['paths' => [ dirname(__DIR__).'/../templates' => 'EasyAdminLogViewer',]]);
        }
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/config/routes.yaml')
            ->prefix('%easy_admin_log_viewer.route_prefix%');
    }
}
