<?php

namespace App\Controller;

//use Bunny\Storage\Client;
//use Bunny\Storage\Region;
use App\Entity\User;
use Http\Discovery\Psr18ClientDiscovery;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhpFlickr;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Survos\FlickrBundle\Services\FlickrService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ToshY\BunnyNet\BaseAPI;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\EdgeStorageAPI;
use ToshY\BunnyNet\Enum\Region;

class AppController extends AbstractController
{
    public function __construct(private readonly FlickrService $flickrService)
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
            'photosetId' => $photosetId,
            'photos' => $result
        ]);
    }

    public function upload()
    {

        $this->flickr = new PhpFlickr($config['consumer_key'], $config['consumer_secret']);
        $accessToken = new StdOAuth1Token();
        $accessToken->setAccessToken($config['access_key']);
        $accessToken->setAccessTokenSecret($config['access_secret']);
        $this->flickr->getOauthTokenStorage()->storeAccessToken('Flickr', $accessToken);


    }

    #[Route('/', name: 'app_homepage')]
    public function home(FlickrService $flickr,
    ): Response
    {
        $userId = '26016159@N00';
        $result = $flickr->photosets()->getList($userId);

        return $this->render('albums.html.twig', [
            'albums' => $result['photoset']
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(
    ): Response
    {
        $flickr = $this->flickrService;
        /** @var User $user */
        $user = $this->getUser();

        dd($flickr->authenticate()->test()->login());

        $token = new StdOAuth1Token();
        $token->setAccessToken($user->getFlickrKey());
        $token->setAccessTokenSecret($user->getFlickrSecret());
        $storage = new Session();
        $storage->storeAccessToken('Flickr', $token);
        $flickr->setOauthStorage($storage);


        $user = $this->getUser();
        return $this->render('profile.html.twig', [
            'flickr_auth_url' => $flickr->getAuthUrl(),
            'user' => $user]);
    }

    }
