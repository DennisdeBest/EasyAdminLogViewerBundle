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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LogFileController extends AbstractController
{
    public function __construct(
        private readonly LogFileService $logFileService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
		private readonly string $routePrefix
    ) {
    }

    #[Route(path: '/admin/log-files', name: 'admin_log_files')]
    public function list(): Response
    {
        $files = $this->logFileService->getLogFiles();

        return $this->render('admin/log_files/list.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route(path: '/admin/log-files/download', name: 'admin_log_files_download')]
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

    #[Route(path: '/admin/log-files/show', name: 'admin_log_files_show')]
    public function show(Request $request): Response
    {
        $routeParams = $request->query->all()[EA::ROUTE_PARAMS];
        $path = $routeParams['path'];

        $file = $this->logFileService->getFileDataForAbsolutePath($path);

        $level = $routeParams['level'] ?? null;
        $type = $routeParams['type'] ?? null;

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/log_files/show.html.twig', [
            'file' => $file,
        ]);
    }

    #[Route(path: '/admin/log-files/delete', name: 'admin_log_files_delete')]
    public function delete(Request $request): Response
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

        $url = $this->adminUrlGenerator->setRoute('admin_log_files');

		if (!str_starts_with($url, $this->routePrefix)) {
			$url = $this->routePrefix . $url;
		}

        return new RedirectResponse($url);
    }
}
