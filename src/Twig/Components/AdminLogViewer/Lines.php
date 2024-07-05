<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Twig\Components\AdminLogViewer;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/admin_log_viewer/Lines.html.twig')]
class Lines
{
    use DefaultActionTrait;

    public array $lines = [];
}
