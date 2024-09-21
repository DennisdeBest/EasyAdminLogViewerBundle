<?php

namespace CodeBuds\EasyAdminLogViewerBundle\Controller\Admin;

use CodeBuds\EasyAdminLogViewerBundle\Service\LogFileService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LogFileController extends AbstractController
{
    public function __construct(
        private readonly LogFileService    $logFileService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function list(): Response
    {
        $files = $this->logFileService->getLogFiles();

        return $this->render('@EasyAdminLogViewer/list.html.twig', [
            'files' => $files,
        ]);
    }

    public function download(Request $request): Response
    {
        $routeParams = $request->query->all()[EA::ROUTE_PARAMS];
        $path = $routeParams['path'];

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $this->logFileService->validateLogFilePath($path);

        // Validate that the file exists before creating the response
        if (!file_exists($path)) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        $response = new BinaryFileResponse($path);
        // You can set whatever Content-Type you like, depending on the type of file you're sending
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($path),  // This will set the downloaded file's name
        );

        return $response;
    }

    public function show(Request $request): Response
    {
        $routeParams = $request->query->all()[EA::ROUTE_PARAMS];
        $path = $routeParams['path'];

        $file = $this->logFileService->getFileDataForAbsolutePath($path);

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $this->render('@EasyAdminLogViewer/show.html.twig', [
            'file' => $file,
        ]);
    }

    public function delete(Request $request): RedirectResponse
    {
        $routeParams = $request->query->all()[EA::ROUTE_PARAMS];
        $path = $routeParams['path'];

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $type = 'success';

        try {
            $this->logFileService->validateLogFilePath($path);
            $message = $this->logFileService->deleteLogFile($path);
        } catch (\Exception $exception) {
            $type = 'error';
            $message = $exception->getMessage();
        }

        $this->addFlash($type, $message);

        $url = $this->adminUrlGenerator->setRoute('easy_admin_log_viewer_list')->generateUrl();

        return new RedirectResponse($url);
    }
}
