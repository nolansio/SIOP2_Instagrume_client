<?php

namespace App\Controller;

use App\Service\PublicationService;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicationController extends AbstractController
{
    private PublicationService $publicationService;
    private ApiService $apiService;

    public function __construct(PublicationService $publicationService, ApiService $apiService)
    {
        $this->publicationService = $publicationService;
        $this->apiService = $apiService;
    }

    #[Route('/publications/creer', name: 'app_publication_create')]
    public function create(Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');

            // Récupérer les fichiers
            $uploadedFiles = $request->files->get('images');

            $images = [];
            if ($uploadedFiles) {
                if (is_array($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $file->isValid()) {
                            $images[] = $file;
                        }
                    }
                } elseif ($uploadedFiles instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && $uploadedFiles->isValid()) {
                    $images[] = $uploadedFiles;
                }
            }

            // Validation
            if (empty($description) && empty($images)) {
                $error = "Vous devez fournir au moins une description ou une image.";
            } else {
                // Valider les extensions des images
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
                $invalidImages = [];

                foreach ($images as $image) {
                    $extension = strtolower($image->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        $invalidImages[] = $image->getClientOriginalName();
                    }
                }

                if (!empty($invalidImages)) {
                    $error = "Format(s) d'image non supporté(s) : " . implode(', ', $invalidImages) . ". Formats acceptés : " . implode(', ', $allowedExtensions);
                } else {
                    try {
                        // Préparer les données pour l'API
                        $formData = [
                            'description' => $description
                        ];

                        // Appel API avec multipart/form-data pour l'upload d'images
                        $response = $this->apiService->postMultipart('/publications', $formData, $images, $token);

                        $this->addFlash('success', 'Publication créée avec succès !');
                        return $this->redirectToRoute('app_home');
                    } catch (\Exception $e) {
                        $error = "Erreur lors de la création de la publication. Veuillez réessayer.";
                    }
                }
            }
        }

        return $this->render('publication/create.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/publications/{id}/modifier', name: 'app_publication_edit')]
    public function edit(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $publication = $this->publicationService->getPublicationById($id, $token);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Publication non trouvée.');
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $description = $request->request->get('description');

            try {
                $this->publicationService->updatePublication([
                    'id' => $id,
                    'description' => $description
                ], $token);

                $this->addFlash('success', 'Publication modifiée avec succès !');
                return $this->redirectToRoute('app_home');
            } catch (\Exception $e) {
                $error = "Erreur lors de la modification : " . $e->getMessage();
            }
        }

        return $this->render('publication/edit.html.twig', [
            'publication' => $publication,
            'error' => $error
        ]);
    }

    #[Route('/publications/{id}/supprimer', name: 'app_publication_delete')]
    public function delete(int $id, Request $request): Response
    {
        $token = $request->getSession()->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->publicationService->deletePublication($id, $token);
            $this->addFlash('success', 'Publication supprimée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la suppression.');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
