<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Twig\Components\AdminLogViewer;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'EasyAdminLogViewer:Lines', template: '@EasyAdminLogViewer/components/Lines.html.twig')]
class Lines
{
    use DefaultActionTrait;

    public array $lines = [];
}
