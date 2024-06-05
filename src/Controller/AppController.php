<?php

namespace App\Controller;

//use Bunny\Storage\Client;
//use Bunny\Storage\Region;
use Http\Discovery\Psr18ClientDiscovery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Survos\FlickrBundle\Services\FlickrService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\EdgeStorageAPI;
use ToshY\BunnyNet\Enum\Region;

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
    public function albums(FlickrService $flickr,
    #[Autowire('%env(BUNNY_API_KEY)%')] string $apiKey,
    HttpClientInterface $httpClient,
    ): Response
    {


//        $httpClient =  Psr18ClientDiscovery::find();
        $httpClient = new \Symfony\Component\HttpClient\Psr18Client();
// Create a BunnyClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        $bunnyClient = new BunnyClient(
            client: $httpClient
        );

// Provide the API key available at the "Account Settings > API" section.
        $baseApi = new BaseAPI(
            apiKey: $apiKey,
            client: $bunnyClient,
        );
//        dd($baseApi->listCountries());
        $storageZoneName = 'museado';
        foreach ($baseApi->listStorageZones()->getContents() as $zone) {
            $accessKey = $zone['ReadOnlyPassword'];

// Provide the "(Read-Only) Password" available at the "FTP & API Access" section of your specific storage zone.
            $edgeStorageApi = new EdgeStorageAPI(
                apiKey: $accessKey,
                client: $bunnyClient,
                region: Region::NY
            );
            $list = $edgeStorageApi->listFiles(
                storageZoneName: $storageZoneName,
                path: '/'
            );

//            $client = new Client($accessKey, 'museado', Region::NEW_YORK);
//            $list = $client->listFiles('/');
            foreach ($list->getContents() as $fileInfo) {
                $subList = $edgeStorageApi->listFiles(
                    $storageZoneName,
                    path: $fileInfo['ObjectName']
                );
                dd($subList);
//                dd($client->getContents('/'));

            }
        };
        dd();


        $userId = '26016159@N00';
        $result = $flickr->photosets()->getList($userId);

        return $this->render('albums.html.twig', [
            'albums' => $result['photoset']
        ]);
    }

    #[Route('/flickr', name: 'flickr_homepage')]
    public function flickr(FlickrService $flickr): Response
    {


        $userId = '26016159@N00';
        $result = $flickr->photosets()->getList($userId);

        return $this->render('albums.html.twig', [
            'albums' => $result['photoset']
        ]);
    }


}
