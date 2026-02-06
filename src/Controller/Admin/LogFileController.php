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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LogFileController extends AbstractController
{
    public function __construct(
        private readonly LogFileService $logFileService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function list(): Response
    {
        return $this->render('@EasyAdminLogViewer/list.html.twig', [
            'files' => $this->logFileService->getLogFiles(),
        ]);
    }

    public function download(Request $request): Response
    {
        $path = $this->getPathFromRequest($request);
        $this->logFileService->validateLogFilePath($path);

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($path));

        return $response;
    }

    public function show(Request $request): Response
    {
        $path = $this->getPathFromRequest($request);
        $file = $this->logFileService->getFileDataForAbsolutePath($path);

        return $this->render('@EasyAdminLogViewer/show.html.twig', [
            'file' => $file,
        ]);
    }

    public function delete(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete-log-file', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToList();
        }

        $path = $request->request->get('path', '');
        $type = 'success';

        try {
            $message = $this->logFileService->deleteLogFile($path);
        } catch (\Exception $exception) {
            $type = 'error';
            $message = $exception->getMessage();
        }

        $this->addFlash($type, $message);

        return $this->redirectToList();
    }

    private function getPathFromRequest(Request $request): string
    {
        $routeParams = $request->query->all()[EA::ROUTE_PARAMS] ?? [];

        return $routeParams['path'] ?? '';
    }

    private function redirectToList(): RedirectResponse
    {
        $url = $this->adminUrlGenerator->setRoute('easy_admin_log_viewer_list')->generateUrl();

        return new RedirectResponse($url);
    }
}
