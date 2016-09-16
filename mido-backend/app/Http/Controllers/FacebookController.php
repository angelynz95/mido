<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RedisController;
use Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Log;

class FacebookController extends Controller {
	private $dataHelper;
	private $fb;
	private $redis;
	public function __construct() {
		$dataHelper = new PersistentDataHandler();
		$config = [
			'app_id' => env('FB_APP_ID'),
			'app_secret' => env('FB_APP_SECRET'),
			'default_graph_version' => env('FB_DEFAULT_GRAPH_VERSION'),
			'persistent_data_handler' => $dataHelper,
		];
		$this->fb = new Facebook\Facebook($config);
		$this->redis = new RedisController();
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
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Graph returned an error: ' . $e->getMessage());
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			return response()->json([
				'error'=> [
					'status_code' => '500',
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Facebook SDK returned an error: ' . $e->getMessage());
		}

		if (isset($accessToken)) {
			// Exchanges a short-lived for a long-lived one
			Log::info('Access token: ' . (string)$accessToken);

			$oAuth2Client = $this->fb->getOAuth2Client();
			$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			echo (string) $longLivedAccessToken;
			Log::info('Long life access token: ' . (string)$longLivedAccessToken);

			return response()->json([
				'status_code' => '200',
				'status_message' => 'You have login!',
			]);
		}

		return response()->json([
			'error'=> [
				'status_code' => '500',
				'status_message' => 'Sorry, you can\'t login right now. Try again later.'
			],
		]);
	}

	public function test() {
		$this->fb->setDefaultAccessToken(env('FB_LONG_TOKEN'));

		try {
			$response = $this->fb->get('/me?fields=id,name,email');
			$userNode = $response->getGraphUser();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Graph returned an error: ' . $e->getMessage());
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			return response()->json([
				'error'=> [
					'status_code' => '500',
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Facebook SDK returned an error: ' . $e->getMessage());
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
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Graph returned an error: ' . $e->getMessage());
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			return response()->json([
				'error'=> [
					'status_code' => '500',
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Facebook SDK returned an error: ' . $e->getMessage());
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
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Graph returned an error: ' . $e->getMessage());
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			return response()->json([
				'error'=> [
					'status_code' => '500',
					'status_message' => $e->getMessage()
				],
			]);
			Log::info('Facebook SDK returned an error: ' . $e->getMessage());
		}
		$plainOldArray = $response->getDecodedBody();
		dd($plainOldArray);
	}

	public function redis() {
		$this->redis->save('test', 'testvalue');
	}
}