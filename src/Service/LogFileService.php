<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Service;

use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\FileDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class LogFileService
{
    public function __construct(
        #[Autowire('%kernel.logs_dir%')]
        private string $logDir,
    ) {
    }

    public function getLogFiles(): array
    {
        $finder = new Finder();
        $finder->files()->name('*.log')->in($this->logDir)->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
            return $b->getMTime() - $a->getMTime();
        });
        $files = [];

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $files[] = (new FileDto())
                    ->setName($file->getFilename())
                    ->setPath($file->getRealPath())
                    ->setSize($file->getSize())
                    ->setLastUpdatedAt((new \DateTime())->setTimestamp($file->getMTime()));
            }
        }

        return $files;
    }

    public function getFileDataForAbsolutePath(string $path): FileDto
    {
        $this->validateLogFilePath($path);
        $file = new \SplFileInfo($path);

        return (new FileDto())
            ->setName($file->getFilename())
            ->setPath($file->getRealPath())
            ->setSize($file->getSize())
            ->setLastUpdatedAt((new \DateTime())->setTimestamp($file->getMTime()));
    }

    public function validateLogFilePath(string $path): void
    {
        // Check that the path strictly starts with the log dir and does not contain any back steps like ../../
        if (!str_starts_with($path, $this->logDir) || str_contains($path, '..')) {
            throw new AccessDeniedHttpException('Invalid file path.');
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException('File does not exist.');
        }
    }

    public function getLogFileContent(string $path): string
    {
        $this->validateLogFilePath($path);

        return file_get_contents($path);
    }

    public function getLogFileContentArray(string $path, ?string $levelFilter = null, ?string $typeFilter = null): array
    {
        $content = $this->getLogFileContent($path);
        $lines = explode("\n", $content);
        $lines = array_reverse($lines);
        $formatted = [];
        $pattern = '/\[(?P<date>.*?)\]\s(?P<type>.*?)\.(?P<level>.*?):\s(?P<message>.*)/';
        $levels = [];
        $types = [];
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            preg_match($pattern, $line, $matches);

            $level = $matches['level'] ?? null;
            $date = $matches['date'] ?? null;
            $type = $matches['type'] ?? null;

            if ($type && !in_array($type, $types, true)) {
                $types[] = $type;
            }

            if ($level && !in_array($level, $levels, true)) {
                $levels[] = $level;
            }

            if ($levelFilter && $level !== $levelFilter) {
                continue;
            }

            if ($typeFilter && $type !== $typeFilter) {
                continue;
            }

            $badgeLevel = self::getBadgeLevel($level);

            $formatted[] =
                [
                    'date' => $date ? new \DateTime($date) : null,
                    'type' => $type,
                    'level' => $level,
                    'badgeLevel' => $badgeLevel,
                    'message' => $matches['message'] ?? null,
                ];
        }

        return ['content' => $formatted, 'types' => $types, 'levels' => $levels];
    }

    public static function getBadgeLevel(string $level): ?string
    {
        return match ($level) {
            'INFO' => 'info',
            'CRITICAL', 'ERROR' => 'danger',
            'DEBUG' => 'secondary',
            default => null,
        };
    }

    public function deleteLogFile(string $path): string
    {
        $this->validateLogFilePath($path);

        if (unlink($path)) {
            return 'File deleted successfully.';
        }

        return 'An error occurred during file deletion.';
    }

    public static function humanFilesize($size, $precision = 2, $space = true): string
    {
        static $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;

        while (($size / $step) > 0.9) {
            $size /= $step;
            ++$i;
        }

        return round($size, $precision).($space ? ' ' : '').$units[$i];
    }
}
