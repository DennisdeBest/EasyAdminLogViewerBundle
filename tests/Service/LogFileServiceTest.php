<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Tests\Service;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use PHPUnit\Framework\TestCase;

class LogFileServiceTest extends TestCase
{
	private LogFileService $logFileService;

	protected function setUp(): void
	{

		$levels = [
			['level' => 'INFO', 'class' => 'info'],
			['level' => 'ERROR', 'class' => 'danger'],
			['level' => 'CRITICAL', 'class' => 'danger'],
			['level' => 'DEBUG', 'class' => 'secondary'],
		];

		$logDir = sys_get_temp_dir().'/EasyAdminViewerBundle/';
		mkdir($logDir);

		$this->logFileService = new LogFileService($logDir, $levels);
	}

	public function testGetLogFiles(): void
	{
		$files = $this->logFileService->getLogFiles();
		$this->assertIsArray($files);
	}

	public function testValidateLogFilePath(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->logFileService->validateLogFilePath('/invalid/path');
	}
}
