<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Twig\Components\AdminLogViewer;

use CodeBuds\EasyAdminLogViewerBundle\Entity\Dto\LogFileLine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'EasyAdminLogViewer:Lines', template: '@EasyAdminLogViewer/components/Lines.html.twig')]
class Lines
{
    /** @var LogFileLine[] */
    public array $lines = [];
}
