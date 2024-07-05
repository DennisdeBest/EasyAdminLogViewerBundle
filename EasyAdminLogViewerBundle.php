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
		// the "$config" variable is already merged and processed so you can
		// use it directly to configure the service container (when defining an
		// extension class, you also have to do this merging and processing)
		$container->services()
			->get('easy_admin_log_viewer.log_viewer')
			->arg(0, $config['levels'])
		;

		$container->parameters()
			->set('easy_admin_log_viewer.route_prefix', $config['route_prefix']);

	}

	public function configureRoutes(RoutingConfigurator $routes): void
	{
		$routes->import(__DIR__.'/config/routes.yaml')
			->prefix('%easy_admin_log_viewer.route_prefix%');
	}
}
