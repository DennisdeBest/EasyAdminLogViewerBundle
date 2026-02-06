<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Command;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'codebuds:log:files',
    description: 'List all available log files',
)]
class LogFilesCommand extends Command
{
    public function __construct(private readonly LogFileService $logFileService)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $files = $this->logFileService->getLogFiles();

        if (empty($files)) {
            $io->info('No log files found.');

            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($files as $file) {
            $rows[] = [
                $file->name,
                $file->getHumanSize(),
                $file->path,
                $file->lastUpdatedAt?->format(\DateTimeInterface::ATOM) ?? '-',
            ];
        }

        $io->table(['Name', 'Size', 'Path', 'Updated'], $rows);

        return Command::SUCCESS;
    }
}
