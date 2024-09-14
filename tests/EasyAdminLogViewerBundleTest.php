<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EasyAdminLogViewerBundleTest extends KernelTestCase
{
	public function testDefaultConfiguration(): void
	{
		$kernel = new EasyAdminLogViewerTestingKernel();
		$kernel->boot();

		$container = $kernel->getContainer();
		$levels = $container->getParameter('easy_admin_log_viewer.levels');
		$prefix = $container->getParameter('easy_admin_log_viewer.route_prefix');

		$this->assertCount(4, $levels);
		$this->assertEquals('/admin', $prefix);
	}

	public function testCustomConfiguration(): void
	{
		$config = [
			'levels' => [
				['level' => 'CUSTOM', 'class' => 'custom-class'],
			],
			'route_prefix' => '/custom-admin',
		];

		$kernel = new EasyAdminLogViewerTestingKernel(easyAdminLogViewerConfig: $config);
		$kernel->boot();

		$container = $kernel->getContainer();
		$levels = $container->getParameter('easy_admin_log_viewer.levels');
		$prefix = $container->getParameter('easy_admin_log_viewer.route_prefix');

		$this->assertCount(1, $levels);
		$this->assertEquals('CUSTOM', $levels[0]['level']);
		$this->assertEquals('/custom-admin', $prefix);
	}
}
