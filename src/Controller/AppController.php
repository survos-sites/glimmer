<?php

namespace App\Controller;

use App\Entity\User;
use Http\Discovery\Psr18ClientDiscovery;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Samwilson\PhpFlickr\PhpFlickr;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Survos\FlickrBundle\Services\FlickrService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppController extends AbstractController
{
    public function __construct(
        private readonly FlickrService $flickrService)
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
    #[Template('homepage.html.twig')]
    public function homepage()
    {
        return ['users' => [
            '26016159@N00'
        ]];
    }

    #[Route('/albums/{userId}', name: 'app_albums')]
    public function albums(FlickrService $flickr,
        string $userId='26016159@N00',
    ): Response
    {
        try {
            $result = $flickr->photosets()->getList($userId);
        } catch (\Exception $e) {
            $result = null;
        }

        return $this->render('albums.html.twig', [
            'albums' => $result ? $result['photoset'] : []
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(
        UrlGeneratorInterface $urlGenerator
    ): Response
    {
        $flickr = $this->flickrService;
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getFlickrKey()) {
            return $this->redirect($flickr->getAuthUrl([
                'callbackUrl' => $urlGenerator->generate('app_profile',
                    referenceType:  UrlGeneratorInterface::ABSOLUTE_URL),
            ]));
        }
        // for profile, this MUST be the user profile


        $flickr->authenticate();
//        $userId = $flickr->test()->login();
        $nsId = $user->getFlickrUserId();
        $info = $flickr->people()->getInfo($nsId);
        $limits = $flickr->people()->getLimits();

        $groups = $flickr->people()->getGroups($nsId);
        // @todo: pagination, etc.
        $albums = $flickr->photosets()->getList($nsId);

//        $authUrl = $flickr->getAuthUrl(); // for sopme reason, needs to happen before ->authenticate()

//        $token = new StdOAuth1Token();
//        $token->setAccessToken($user->getFlickrKey());
//        $token->setAccessTokenSecret($user->getFlickrSecret());
//        $storage = new Session();
//        $storage->storeAccessToken('Flickr', $token);
//        $flickr->setOauthStorage($storage);


        return $this->render('profile.html.twig', [
            'groups' => $groups,
            'albums' => $albums,
            'collections' => $flickr->collections()->getTree(userId: $nsId),
            'info' => $info,
            'limits' => $limits,
            'flickr_auth_url' => $authUrl??false,
            'user' => $user]);
    }

    }
