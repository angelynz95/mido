<?php

namespace App\Http\Controllers;

use Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Log;

class FacebookController extends Controller {
	private $dataHelper;
	private $fb;
	public function __construct() {
		$dataHelper = new PersistentDataHandler();
		$config = [
			'app_id' => env('FB_APP_ID'),
			'app_secret' => env('FB_APP_SECRET'),
			'default_graph_version' => env('FB_DEFAULT_GRAPH_VERSION'),
			'persistent_data_handler' => $dataHelper,
		];
		$this->fb = new Facebook\Facebook($config);
	}

	public function login(Request $req) {
		$helper = $this->fb->getRedirectLoginHelper();

		$currentPath= env('PUBLIC_URL') . '/fb/callback';
		$permissions = ['email', 'manage_pages', 'publish_pages'];
		$loginUrl = $helper->getLoginUrl($currentPath, $permissions);

		echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
	}

	public function callback() {
		$helper = $this->fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
		 	echo 'Graph returned an error: ' . $e->getMessage();
		  	exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		if (isset($accessToken)) {
			// Logged in!
			// $_SESSION['facebook_access_token'] = (string) $accessToken;
			echo (string) $accessToken;
			Log::info($accessToken);

			// OAuth 2.0 client handler
			$oAuth2Client = $this->fb->getOAuth2Client();

			// Exchanges a short-lived for a long-lived one
			$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			echo (string) $longLivedAccessToken;
			Log::info($longLivedAccessToken);

			// Now you can redirect to another page and use the
			// access token from $_SESSION['facebook_access_token']
		}
	}

	public function test() {
		$this->fb->setDefaultAccessToken(env('FB_LONG_TOKEN'));

		try {
			$response = $this->fb->get('/me');
			$userNode = $response->getGraphUser();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$plainOldArray = $response->getDecodedBody();
		dd($plainOldArray);
		echo 'Logged in as ' . $userNode->getName();
	}

	public function getPages() {
		$this->fb->setDefaultAccessToken(env('FB_LONG_TOKEN'));

		try {
			$response = $this->fb->get('/me/accounts');
			$userNode = $response->getGraphEdge();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$plainOldArray = $response->getDecodedBody();
		dd($plainOldArray);
	}

	public function getPosts() {
		$this->fb->setDefaultAccessToken(env('FB_PAGE_ACCESS_TOKEN'));
		try {
			$response = $this->fb->post('/' . env('FB_PAGE_ID') . '/feed?message=This is a test');
			$pageNode = $response->getGraphNode();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		$plainOldArray = $response->getDecodedBody();
		dd($plainOldArray);
	}
}