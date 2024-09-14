<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Tests;

use CodeBuds\EasyAdminLogViewerBundle\EasyAdminLogViewerBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class EasyAdminLogViewerTestingKernel extends Kernel
{
	public function __construct(
		protected string $environment = 'test',
		protected bool $debug = true,
		private array $easyAdminLogViewerConfig = [],
	)
	{
		parent::__construct($environment, $debug);
	}

	public function registerBundles(): iterable
	{
		return [
			new FrameworkBundle(),
			new SecurityBundle(),
			new DoctrineBundle(),
			new EasyAdminBundle(),
			new EasyAdminLogViewerBundle(),
			new TwigBundle()
		];
	}

	public function registerContainerConfiguration(LoaderInterface $loader): void
	{
		$loader->load(function (ContainerBuilder $container) {
			if (!empty($this->easyAdminLogViewerConfig)) {
				$container->loadFromExtension('easy_admin_log_viewer', $this->easyAdminLogViewerConfig);
			}

			$container->loadFromExtension('framework', [
				'secret' => 'ASecret',
				'test' => true,
				'session' => [
					'storage_factory_id' => 'session.storage.factory.mock_file',
				],
				'router' => [
					'resource' => '%kernel.project_dir%/config/routes.yaml',
					'type' => 'yaml',
					'strict_requirements' => null,
				],
			]);

			$container->loadFromExtension('security', [
				'providers' => [
					'in_memory' => [
						'memory' => null,
					],
				],
				'firewalls' => [
					'main' => [
						'lazy' => true,
						'logout' => [
							'path' => 'app_logout',
							'target' => '/',
						],
					],
				],
			]);

			$container->loadFromExtension('doctrine', [
				'dbal' => [
					'driver' => 'pdo_sqlite',
					'path' => '%kernel.project_dir%/var/test.db',
				],
				'orm' => [
					'auto_generate_proxy_classes' => '%kernel.debug%',
					'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
					'auto_mapping' => false,
					'controller_resolver' => [
						'auto_mapping' => false, // Explicitly configure auto_mapping to true
					],
				],
			]);
			$container->loadFromExtension('twig', [
				'paths' => [
					'%kernel.project_dir%/templates' => 'templates',  // Set paths relatively
				],
				'default_path' => '%kernel.project_dir%/templates',  // Set default path
			]);
		});
	}

	public function getCacheDir(): string
	{
		return __DIR__ . '/../var/cache/' . spl_object_hash($this);
	}

}
