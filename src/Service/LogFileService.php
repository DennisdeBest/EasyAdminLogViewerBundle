<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Service;

use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\FileDto;
use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\LogFileLine;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

readonly class LogFileService
{
    private const int DEFAULT_MAX_LINES = 5000;
    private const string LOG_LINE_PATTERN = '/^\[(?P<date>[^\]]+)]\s+(?P<type>[^.]+)\.(?P<level>[^:]+):\s?(?P<message>.*)$/';

    private string $resolvedLogDir;

    public function __construct(
        #[Autowire('%kernel.logs_dir%')]
        private string $logDir,
        #[Autowire('%easy_admin_log_viewer.levels%')]
        private array $levels,
    ) {
        $resolvedLogDir = realpath($this->logDir);
        $this->resolvedLogDir = false !== $resolvedLogDir ? rtrim($resolvedLogDir, DIRECTORY_SEPARATOR) : rtrim($this->logDir, DIRECTORY_SEPARATOR);
    }

    /**
     * @return FileDto[]
     */
    public function getLogFiles(): array
    {
        $finder = new Finder();
        $finder->files()->name('*.log')->in($this->resolvedLogDir)->sort(
            static fn (\SplFileInfo $a, \SplFileInfo $b): int => $b->getMTime() - $a->getMTime(),
        );

        $files = [];

        foreach ($finder as $file) {
            $files[] = $this->createFileDto($file);
        }

        return $files;
    }

    public function getFileDataForAbsolutePath(string $path): FileDto
    {
        $this->validateLogFilePath($path);

        return $this->createFileDto(new \SplFileInfo($path));
    }

    public function validateLogFilePath(string $path): void
    {
        if ('' === trim($path)) {
            throw new \InvalidArgumentException('Invalid file path.');
        }

        $resolvedPath = realpath($path);
        if (false === $resolvedPath) {
            throw new \InvalidArgumentException('File does not exist.');
        }

        if (!is_file($resolvedPath)) {
            throw new \InvalidArgumentException('Invalid file path.');
        }

        $expectedPrefix = $this->resolvedLogDir . DIRECTORY_SEPARATOR;
        if ($resolvedPath !== $this->resolvedLogDir && !str_starts_with($resolvedPath, $expectedPrefix)) {
            throw new \InvalidArgumentException('Invalid file path.');
        }
    }

    /**
     * Parse log file into structured entries with filtering.
     *
     * @return array{content: LogFileLine[], types: string[], levels: string[]}
     */
    public function getLogFileContentArray(
        string $path,
        ?string $levelFilter = null,
        ?string $typeFilter = null,
        int $maxLines = self::DEFAULT_MAX_LINES,
    ): array {
        $rawLines = $this->tailFile($path, $maxLines);
        $entries = $this->parseLogEntries($rawLines);

        $levels = [];
        $types = [];
        $filtered = [];

        foreach ($entries as $entry) {
            if (!in_array($entry->type, $types, true)) {
                $types[] = $entry->type;
            }
            if (!in_array($entry->level, $levels, true)) {
                $levels[] = $entry->level;
            }

            if ($levelFilter && $entry->level !== $levelFilter) {
                continue;
            }
            if ($typeFilter && $entry->type !== $typeFilter) {
                continue;
            }

            $filtered[] = $entry;
        }

        sort($types);
        sort($levels);

        return ['content' => $filtered, 'types' => $types, 'levels' => $levels];
    }

    public function deleteLogFile(string $path): string
    {
        $this->validateLogFilePath($path);

        if (unlink($path)) {
            return 'File deleted successfully.';
        }

        return 'An error occurred during file deletion.';
    }

    /**
     * Read the last N lines from a file without loading the entire file into memory.
     *
     * @return string[]
     */
    private function tailFile(string $path, int $maxLines): array
    {
        $this->validateLogFilePath($path);

        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        if ($totalLines === 0) {
            return [];
        }

        $startLine = max(0, $totalLines - $maxLines);
        $lines = [];

        $file->seek($startLine);
        while (!$file->eof()) {
            $line = $file->current();
            if ($line !== false && trim($line) !== '') {
                $lines[] = rtrim($line);
            }
            $file->next();
        }

        return array_reverse($lines);
    }

    /**
     * Parse raw log lines into LogFileLine entries, handling multiline entries (stack traces).
     * Lines that don't match the log pattern are appended to the previous entry's message.
     *
     * @param string[] $lines Lines in reverse chronological order
     * @return LogFileLine[]
     */
    private function parseLogEntries(array $lines): array
    {
        $entries = [];
        $pendingExtraLines = [];

        foreach ($lines as $line) {
            if (preg_match(self::LOG_LINE_PATTERN, $line, $matches)) {
                $message = $matches['message'];

                // Prepend any accumulated extra lines (stack traces) to this entry
                if (!empty($pendingExtraLines)) {
                    $message .= "\n" . implode("\n", array_reverse($pendingExtraLines));
                    $pendingExtraLines = [];
                }

                $entries[] = new LogFileLine(
                    type: $matches['type'],
                    level: $matches['level'],
                    badgeLevel: self::getBadgeLevel($matches['level'], $this->levels),
                    date: $this->createDate($matches['date']),
                    message: $message,
                );
            } else {
                // Non-matching line (stack trace, context, etc.) — collect for the next matching entry
                $pendingExtraLines[] = $line;
            }
        }

        return $entries;
    }

    public static function getBadgeLevel(string $level, array $levels): string
    {
        foreach ($levels as $configLevel) {
            if ($level === $configLevel['level']) {
                return $configLevel['class'];
            }
        }

        return 'secondary';
    }

    private function createDate(string $date): ?\DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($date);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function humanFilesize(?int $size, int $precision = 2): string
    {
        if ($size === null || $size === 0) {
            return '0 B';
        }

        $units = ['B', 'kB', 'MB', 'GB', 'TB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9 && $i < \count($units) - 1) {
            $size /= $step;
            ++$i;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    private function createFileDto(\SplFileInfo $file): FileDto
    {
        return new FileDto(
            name: $file->getFilename(),
            path: $file->getRealPath(),
            size: $file->getSize(),
            lastUpdatedAt: (new \DateTimeImmutable())->setTimestamp($file->getMTime()),
        );
    }
}
