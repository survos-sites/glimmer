<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Survos\FlickrBundle\Services\FlickrService;

class AppController extends AbstractController
{
    public function __construct(private FlickrService $flickrService)
    {

    }
    #[Route('/list/{userId}/{photosetId}', name: 'flickr_list')]
    public function photoSet(string $userId, string $photosetId): Response
    {

        $result = $this->flickrService->photosets()->getPhotos(
            $photosetId,
            $userId,
            ['media' => 'photos, url_o, tags']
        );

        return $this->render('app.html.twig', [
            'photos' => $result
        ]);
    }

    #[Route('/', name: 'app_homepage')]
    public function albums(FlickrService $flickr): Response
    {

        $userId = '26016159@N00';
        $result = $flickr->photosets()->getList($userId);

        return $this->render('albums.html.twig', [
            'albums' => $result['photoset']
        ]);
    }

}
