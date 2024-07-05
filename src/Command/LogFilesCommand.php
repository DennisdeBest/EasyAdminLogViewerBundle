<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Command;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:log:files',
    description: 'Add a short description for your command',
)]
class LogFilesCommand extends Command
{
    public function __construct(private LogFileService $logFileService)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = $this->logFileService->getLogFiles();
        if ($files) {
            $rows = [];
            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'Size', 'Path', 'UpdatedOn'])
            ;
            foreach ($files as $file) {
                $rows[] = [$file['name'], $file['size'], $file['path'], (new \DateTime())->setTimestamp((int) $file['updatedOn'])->format(DATE_ATOM)];
            }
            $table->setRows($rows);
            $table->render();
        }

        return Command::SUCCESS;
    }
}
