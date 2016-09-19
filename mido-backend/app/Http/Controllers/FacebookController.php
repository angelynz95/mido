<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Constanta;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\RedisController;
use Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Log;

class FacebookController extends Controller {
	private $const;
	private $db;
	private $fb;
	private $redis;
	public function __construct() {
		$this->const = new Constanta();
		$this->db = new DatabaseController();
		$dataHelper = new PersistentDataHandler();
		$config = [
			'app_id' => '197603340658783',
			'app_secret' => '137c77f8f885620a1b3377612901d569',
			'default_graph_version' => 'v2.7',
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

	public function storeUserData(Request $req) {
		$accessToken = $req->get('access_token');
		$this->fb->setDefaultAccessToken($accessToken);

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
		Log::info('Logged in as ' . $userNode->getName() . $userNode->getEmail());

		$name = $userNode->getName();
		$email = '';
		if (isset($email)) {
			$email = $userNode->getEmail();
		}
		$data = [
			'full_name' => $name, 
			'email' => $email, 
			'api' => $this->const->FACEBOOK_API];
		$userId = $this->db->storeUserData($data);

		// Set access token in redis
		$key = $this->redis->getAccessTokenKey($userId);
		$this->redis->save($key, $accessToken);

		return response()->json([
			'user_id' => $userId,
			'status_code' => '200',
			'status_message' => 'You have login!',
		]);
	}

	public function getPages(int $userId) {
		$key = $this->redis->getAccessTokenKey($userId, $this->const->FACEBOOK_API);
		$accessToken = $this->redis->get($key);
		$this->fb->setDefaultAccessToken($accessToken);

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

		$graphObject = $response->getDecodedBody();
		$pages = $graphObject['data'];
		$responsePages = [];
		foreach($pages as $page) {
			$isPermitted = $this->validatePagePermission($page['perms']);
			if ($isPermitted) {
				$accessToken = $page['access_token'];
				$pageId = $page['id'];
				$pageName = $page['name'];

				// Store to DB
				$data = [
					'user_id' => $userId,
					'real_id' => $pageId,
					'page_name' => $pageName,
				];
				$shadowPageId = $this->db->storePagesData($data);

				// Store to redis
				$key = $this->redis->getPageAccessTokenKey($userId, $shadowPageId);
				$this->redis->save($key, $accessToken);
			}

			$responsePage = [
				'id' => $shadowPageId,
				'name' => $pageName,
			];
			array_push($responsePages, $responsePage);
		}

		return response()->json([
			'pages' => $responsePages,
			'status_code' => '200',
			'status_message' => 'OK',
		]);
	}

	public function validatePagePermission(array $permissions) {
		$i = 0;
		$isFound = false;
		while ($i < sizeOf($permissions) && !$isFound) {
			if ($permissions[$i] == 'CREATE_CONTENT') {
				$isFound = true;
			}
			$i++;
		}
		return $isFound;
	}

	public function createPost(int $userId, int $pageId, Request $req) {
		$key = $this->redis->getPageAccessTokenKey($userId, $pageId);
		$accessToken = $this->redis->get($key);
		$this->fb->setDefaultAccessToken($accessToken);

		try {
			$response = $this->fb->post('/' . $this->db->getPageId($pageId) . '/feed?message=' . $req->get('message'));
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
		$graphObject = $response->getDecodedBody();
		if (isset($graphObject['error'])) {
			return $graphObject;
		}
		
		return response()->json([
			'status_code' => '200',
			'status_message' => 'OK',
		]);
	}

	public function getPosts(int $userId, int $pageId) {
		$key = $this->redis->getPageAccessTokenKey($userId, $pageId);
		$accessToken = $this->redis->get($key);
		$this->fb->setDefaultAccessToken($accessToken);

		try {
			$response = $this->fb->get('/' . $this->db->getPageId($pageId) . '/posts');
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

		$graphObject = $response->getDecodedBody();
		
		if (isset($graphObject['error'])) {
			return $graphObject;
		}
		
		return response()->json([
			'status_code' => '200',
			'status_message' => 'OK',
			'posts' => $graphObject['data'],
		]);
	}

	public function getInsight(int $userId, int $pageId) {
		$key = $this->redis->getAccessTokenKey($userId, $pageId);
		$accessToken = $this->redis->get($key);
		$this->fb->setDefaultAccessToken($accessToken);

		try {
			// dd('/' . $this->db->getPageId($pageId) . '/insights/page_fans?period=lifetime');
			$response = $this->fb->post('/' . $this->db->getPageId($pageId) . '/insights/page_views_total?period=week');
			$graphObject = $response->getGraphObject();
			dd($graphObject);
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
		$graphObject = $response->getDecodedBody();
		if (isset($graphObject['error'])) {
			return $graphObject;
		}
		
		return response()->json([
			'status_code' => '200',
			'status_message' => 'OK',
		]);
	}

	public function uploadPicture(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $request->file('image');
        $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/images');
        $image->move($destinationPath, $input['imagename']);

        $this->postImage->add($input);

        return back()->with('success','Image Upload successful');
    }
}