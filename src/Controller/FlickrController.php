<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OAuth\Common\Storage\Memory;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Token\StdOAuth1Token;
use Survos\FlickrBundle\Services\FlickrService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FlickrController extends AbstractController
{
    public function __construct(
        private FlickrService         $flickrService,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator)
    {

    }

    #[Route('/flickr_login', name: 'flickr_login')]
    #[IsGranted('ROLE_USER')]
    public function login(
        Request                      $request,
        #[MapQueryParameter] ?string $oauth_token = null,
        #[MapQueryParameter] ?string $oauth_verifier = null,
    ): Response
    {
        $accessToken = new StdOAuth1Token();
        $flickr = $this->flickrService;
        $storage = new Session();
        $flickr->setOauthStorage($storage);

        /** @var User $user */
        $user = $this->getUser();
        if ($key = $user->getFlickrKey()) {
            $accessToken->setAccessToken($key);
            $accessToken->setAccessTokenSecret($user->getFlickrSecret());
            // A storage object has already been created at this point because we called testEcho above.
            $flickr->getOauthTokenStorage()->storeAccessToken('Flickr', $accessToken);
        } else {

        }


        if (!$oauth_token) {
            $callbackHere = $request->getUri();
            $perm = 'write';
            $url = $flickr->getAuthUrl($perm, $callbackHere);
            return $this->render('flickr/index.html.twig', [
                'url' => $url,
            ]);
        }

        if ($oauth_token) {
            $accessToken = $flickr->retrieveAccessToken($oauth_verifier, $oauth_token);
            // we need the userid for future calls
            $user
                ->setFlickrUsername($accessToken->getExtraParams()['username'])
                ->setFlickrUserId($accessToken->getExtraParams()['user_nsid'])
                ->setFlickrKey($accessToken->getAccessToken())
                ->setFlickrSecret($accessToken->getAccessTokenSecret());
            $this->entityManager->flush();
            return $this->redirectToRoute('app_profile');
        }

//        $accessToken->setAccessToken($config['access_key']);
//        $accessToken->setAccessTokenSecret($config['access_secret']);
//        // A storage object has already been created at this point because we called testEcho above.
//        $flickr->getOauthTokenStorage()->storeAccessToken('Flickr', $accessToken);


////        dd($flickr->test()->testEcho());
//        $storage = new Session();
//// Create the access token from the strings you acquired before.
//        $token = new StdOAuth1Token();
////// Add the token to the storage.
//        $storage->storeAccessToken('Flickr', $token);

        return $this->render('flickr/index.html.twig', [
//            'url' => $url,
        ]);
    }

    #[Route('/flickr', name: 'app_flickr')]
    public function index(Request $request, FlickrService $flickr): Response
    {
        //        $this->flickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);
//        $storage = new Memory();
        dump($request->query->all());
        if ($verifier = $request->get('oauth_verifier', false)) {
            $accessToken = $flickr->retrieveAccessToken($verifier, $request->get('oauth_token'));
            $userId = $this->flickrService->test()->login();
            dd($accessToken, $userId);
        }
        return $this->redirectToRoute('flickr_login');

    }
}
