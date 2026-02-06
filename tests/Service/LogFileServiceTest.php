<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Tests\Service;

use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\FileDto;
use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\LogFileLine;
use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use PHPUnit\Framework\TestCase;

class LogFileServiceTest extends TestCase
{
    private LogFileService $logFileService;
    private string $logDir;

    private const array DEFAULT_LEVELS = [
        ['level' => 'EMERGENCY', 'class' => 'danger'],
        ['level' => 'CRITICAL', 'class' => 'danger'],
        ['level' => 'ERROR', 'class' => 'danger'],
        ['level' => 'ALERT', 'class' => 'danger'],
        ['level' => 'WARNING', 'class' => 'warning'],
        ['level' => 'NOTICE', 'class' => 'info'],
        ['level' => 'INFO', 'class' => 'info'],
        ['level' => 'DEBUG', 'class' => 'secondary'],
    ];

    protected function setUp(): void
    {
        $this->logDir = sys_get_temp_dir() . '/EasyAdminViewerBundle_' . uniqid() . '/';
        mkdir($this->logDir, 0777, true);

        $this->logFileService = new LogFileService($this->logDir, self::DEFAULT_LEVELS);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->logDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->logDir)) {
            rmdir($this->logDir);
        }
    }

    // --- File listing ---

    public function testGetLogFilesReturnsEmptyArrayWhenNoFiles(): void
    {
        $files = $this->logFileService->getLogFiles();
        $this->assertSame([], $files);
    }

    public function testGetLogFilesFindsLogFiles(): void
    {
        file_put_contents($this->logDir . 'dev.log', 'test content');
        file_put_contents($this->logDir . 'prod.log', 'prod content');

        $files = $this->logFileService->getLogFiles();

        $this->assertCount(2, $files);
    }

    public function testGetLogFilesIgnoresNonLogFiles(): void
    {
        file_put_contents($this->logDir . 'dev.log', 'log content');
        file_put_contents($this->logDir . 'notes.txt', 'not a log');

        $files = $this->logFileService->getLogFiles();

        $this->assertCount(1, $files);
        $this->assertSame('dev.log', $files[0]->name);
    }

    public function testGetLogFilesSortsByNewestFirst(): void
    {
        file_put_contents($this->logDir . 'old.log', 'old');
        sleep(1);
        file_put_contents($this->logDir . 'new.log', 'new');

        $files = $this->logFileService->getLogFiles();

        $this->assertSame('new.log', $files[0]->name);
        $this->assertSame('old.log', $files[1]->name);
    }

    // --- Path validation ---

    public function testValidateLogFilePathRejectsPathOutsideLogDir(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file path.');
        $this->logFileService->validateLogFilePath('/etc/passwd');
    }

    public function testValidateLogFilePathRejectsDirectoryTraversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file path.');
        $this->logFileService->validateLogFilePath($this->logDir . '../../../etc/passwd');
    }

    public function testValidateLogFilePathRejectsNonExistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not exist.');
        $this->logFileService->validateLogFilePath($this->logDir . 'nonexistent.log');
    }

    public function testValidateLogFilePathAcceptsValidFile(): void
    {
        $path = $this->logDir . 'valid.log';
        file_put_contents($path, 'content');

        // Should not throw
        $this->logFileService->validateLogFilePath($path);
        $this->addToAssertionCount(1);
    }

    // --- File data ---

    public function testGetFileDataForAbsolutePath(): void
    {
        $path = $this->logDir . 'test.log';
        file_put_contents($path, 'some content');

        $dto = $this->logFileService->getFileDataForAbsolutePath($path);

        $this->assertInstanceOf(FileDto::class, $dto);
        $this->assertSame('test.log', $dto->name);
        $this->assertSame(12, $dto->size); // "some content" = 12 bytes
    }

    // --- Log parsing ---

    public function testParsesSingleLogEntry(): void
    {
        $path = $this->logDir . 'test.log';
        file_put_contents($path, "[2024-06-15 10:30:45] app.ERROR: Something went wrong\n");

        $result = $this->logFileService->getLogFileContentArray($path);

        $this->assertCount(1, $result['content']);
        $this->assertInstanceOf(LogFileLine::class, $result['content'][0]);
        $this->assertSame('app', $result['content'][0]->type);
        $this->assertSame('ERROR', $result['content'][0]->level);
        $this->assertSame('danger', $result['content'][0]->badgeLevel);
        $this->assertStringContainsString('Something went wrong', $result['content'][0]->message);
    }

    public function testParsesMultipleEntries(): void
    {
        $path = $this->logDir . 'test.log';
        $content = <<<'LOG'
[2024-06-15 10:30:00] app.INFO: First entry
[2024-06-15 10:30:01] app.ERROR: Second entry
[2024-06-15 10:30:02] security.WARNING: Third entry
LOG;
        file_put_contents($path, $content);

        $result = $this->logFileService->getLogFileContentArray($path);

        $this->assertCount(3, $result['content']);
        // Entries are in reverse chronological order (newest first)
        $this->assertSame('WARNING', $result['content'][0]->level);
        $this->assertSame('ERROR', $result['content'][1]->level);
        $this->assertSame('INFO', $result['content'][2]->level);
    }

    public function testCollectsAvailableTypesAndLevels(): void
    {
        $path = $this->logDir . 'test.log';
        $content = <<<'LOG'
[2024-06-15 10:30:00] app.INFO: Info message
[2024-06-15 10:30:01] security.ERROR: Error message
[2024-06-15 10:30:02] app.WARNING: Warning message
LOG;
        file_put_contents($path, $content);

        $result = $this->logFileService->getLogFileContentArray($path);

        $this->assertContains('app', $result['types']);
        $this->assertContains('security', $result['types']);
        $this->assertContains('INFO', $result['levels']);
        $this->assertContains('ERROR', $result['levels']);
        $this->assertContains('WARNING', $result['levels']);
    }

    // --- Filtering ---

    public function testFilterByLevel(): void
    {
        $path = $this->logDir . 'test.log';
        $content = <<<'LOG'
[2024-06-15 10:30:00] app.INFO: Info message
[2024-06-15 10:30:01] app.ERROR: Error message
[2024-06-15 10:30:02] app.INFO: Another info
LOG;
        file_put_contents($path, $content);

        $result = $this->logFileService->getLogFileContentArray($path, levelFilter: 'ERROR');

        $this->assertCount(1, $result['content']);
        $this->assertSame('ERROR', $result['content'][0]->level);
        // Types and levels should still contain all options (unfiltered)
        $this->assertContains('INFO', $result['levels']);
        $this->assertContains('ERROR', $result['levels']);
    }

    public function testFilterByType(): void
    {
        $path = $this->logDir . 'test.log';
        $content = <<<'LOG'
[2024-06-15 10:30:00] app.INFO: App message
[2024-06-15 10:30:01] security.ERROR: Security message
[2024-06-15 10:30:02] app.ERROR: App error
LOG;
        file_put_contents($path, $content);

        $result = $this->logFileService->getLogFileContentArray($path, typeFilter: 'security');

        $this->assertCount(1, $result['content']);
        $this->assertSame('security', $result['content'][0]->type);
    }

    // --- Multiline entries (stack traces) ---

    public function testParsesMultilineStackTraces(): void
    {
        $path = $this->logDir . 'test.log';
        $content = <<<'LOG'
[2024-06-15 10:30:00] app.ERROR: Uncaught exception
#0 /var/www/src/Controller.php(42): doSomething()
#1 /var/www/vendor/framework.php(100): handleRequest()
[2024-06-15 10:30:01] app.INFO: Normal entry after
LOG;
        file_put_contents($path, $content);

        $result = $this->logFileService->getLogFileContentArray($path);

        $this->assertCount(2, $result['content']);
        // The error entry (second in reversed order) should contain the stack trace
        $errorEntry = $result['content'][1];
        $this->assertSame('ERROR', $errorEntry->level);
        $this->assertStringContainsString('Uncaught exception', $errorEntry->message);
        $this->assertStringContainsString('#0 /var/www/src/Controller.php', $errorEntry->message);
        $this->assertStringContainsString('#1 /var/www/vendor/framework.php', $errorEntry->message);
    }

    // --- Badge level mapping ---

    public function testGetBadgeLevelReturnsConfiguredClass(): void
    {
        $this->assertSame('danger', LogFileService::getBadgeLevel('ERROR', self::DEFAULT_LEVELS));
        $this->assertSame('warning', LogFileService::getBadgeLevel('WARNING', self::DEFAULT_LEVELS));
        $this->assertSame('info', LogFileService::getBadgeLevel('INFO', self::DEFAULT_LEVELS));
        $this->assertSame('secondary', LogFileService::getBadgeLevel('DEBUG', self::DEFAULT_LEVELS));
    }

    public function testGetBadgeLevelReturnsSecondaryForUnknownLevel(): void
    {
        $this->assertSame('secondary', LogFileService::getBadgeLevel('CUSTOM', self::DEFAULT_LEVELS));
    }

    // --- Human file size ---

    public function testHumanFilesizeFormatsCorrectly(): void
    {
        $this->assertSame('0 B', LogFileService::humanFilesize(0));
        $this->assertSame('0 B', LogFileService::humanFilesize(null));
        $this->assertSame('500 B', LogFileService::humanFilesize(500));
        $this->assertSame('1 kB', LogFileService::humanFilesize(1024));
        $this->assertSame('1.5 kB', LogFileService::humanFilesize(1536));
        $this->assertSame('1 MB', LogFileService::humanFilesize(1048576));
    }

    // --- Delete ---

    public function testDeleteLogFile(): void
    {
        $path = $this->logDir . 'deleteme.log';
        file_put_contents($path, 'content');

        $result = $this->logFileService->deleteLogFile($path);

        $this->assertSame('File deleted successfully.', $result);
        $this->assertFileDoesNotExist($path);
    }

    public function testDeleteLogFileRejectsInvalidPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->logFileService->deleteLogFile('/etc/passwd');
    }
}
