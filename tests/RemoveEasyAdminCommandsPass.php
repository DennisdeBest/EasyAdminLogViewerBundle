<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Tests;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveEasyAdminCommandsPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		$commandsToRemove = [
			'EasyCorp\Bundle\EasyAdminBundle\Command\MakeCrudControllerCommand',
			'EasyCorp\Bundle\EasyAdminBundle\Command\MakeAdminDashboardCommand',
		];

		foreach ($commandsToRemove as $command) {
			if ($container->hasDefinition($command)) {
				$container->removeDefinition($command);
			}
		}
	}
}
