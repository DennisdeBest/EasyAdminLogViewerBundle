<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Twig\Components\AdminLogViewer;

use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\FileDto;
use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\LogFileLine;
use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent(name: 'EasyAdminLogViewer:Show', template: '@EasyAdminLogViewer/components/Show.html.twig')]
class Show
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'type'))]
    public string $type = '';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'level'))]
    public string $level = '';

    /** @var LogFileLine[] */
    public array $contentArray = [];

    #[LiveProp]
    public int $lineCount = 0;

    #[LiveProp]
    public array $typeFilters = [];

    #[LiveProp]
    public array $levelFilters = [];

    #[LiveProp(writable: false, useSerializerForHydration: true)]
    public ?FileDto $file = null;

    public function __construct(private readonly LogFileService $logFileService)
    {
    }

    public function mount(FileDto $file): void
    {
        $this->file = $file;
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->loadData();
    }

    #[LiveAction]
    public function changeFilter(): void
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $data = $this->logFileService->getLogFileContentArray(
            $this->file->path,
            $this->level ?: null,
            $this->type ?: null,
        );

        $this->contentArray = $data['content'];
        $this->typeFilters = $data['types'];
        $this->levelFilters = $data['levels'];
        $this->lineCount = \count($this->contentArray);
    }
}
