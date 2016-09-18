<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Constanta;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\RedisController;
use InstagramAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Log;

class InstagramController extends Controller {
	private $const;
	private $db;
	private $ig;
	private $redis;
	public function __construct() {
		$this->const = new Constanta();
		$this->db = new DatabaseController();
		$this->redis = new RedisController();
	}

	private function login(string $usernameId) {
		$key = $this->redis->getUsernameKey($usernameId);
		$username = $this->redis->get($key);

		$key = $this->redis->getUserPasswordKey($usernameId);
		$password = $this->redis->get($key);

		$debugMode = false;
		$this->ig = new \InstagramAPI\Instagram($username, $password, $debugMode);
		try {
			$this->ig->login();
		} catch(Exception $e) {
			Log::info('Instagram returned an error: ' . $e->getMessage());
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			exit;
		}
	}

	public function storeUserData(Request $req) {
		$username = $req->get('username');
		$password = $req->get('password');
		$debugMode = false;

		$this->ig = new \InstagramAPI\Instagram($username, $password, $debugMode);
		try {
			$this->ig->login();
		} catch(Exception $e) {
			Log::info('Instagram returned an error: ' . $e->getMessage());
			return response()->json([
				'error' => [
					'status_code' => '500', 
					'status_message' => $e->getMessage()
				],
			]);
			exit;
		}
		Log::info('Logged in Instagram as ' . $username);

		$usernameId = $this->ig->getUsernameId($username);
		$userInfo = $this->ig->getUsernameInfo($usernameId);
		$fullName = $userInfo->getFullname();
		$data = [
			'full_name' => $username,
			'email' => $username, // API do not provide email
			'api' => $this->const->INSTAGRAM_API
		];
		$userId = $this->db->storeUserData($data);

		// Set username id to redis
		$key = $this->redis->getAccessTokenKey($userId);
		$this->redis->save($key, $usernameId);

		$key = $this->redis->getUsernameKey($usernameId);
		$this->redis->save($key, $username);

		$key = $this->redis->getUserPasswordKey($usernameId);
		$this->redis->save($key, $password);

		return response()->json([
			'user_id' => $userId,
			'status_code' => '200',
			'status_message' => 'You have login!',
		]);
	}

	public function getPosts(int $userId) {
		// Get username id from redis
		$key = $this->redis->getAccessTokenKey($userId);
		$usernameId = $this->redis->get($key);
		$this->login($usernameId);

		// $result = $this->ig->getUserFeed($usernameId, null, 1);
		// $dom = new DOMDocument();
	    // $dom->loadHTML($html);
	    // return element_to_obj($dom->documentElement);
	 //    $res = [];
		// $res = array_merge($res, $result->getItems());
		// dd($res);
		// foreach($result->getItems() as $image) {
		// 	dd($image['id']);
		// 	dd($result->getImageVersion2());
		// }

		try {
		    $helper = null;
		    $followers = [];
		    $helper = $this->ig->getSelfUserFeed();

		    $followers = array_merge($followers, $helper->getItems());
		    // } while(!is_null($helper->getNextMaxId()));
		    echo "My followers: \n";
		    foreach ($followers as $follower) {
		    	dd($follower->getImageVersions());
		        // echo '- '. $follower . "\n";
		    }
		} catch (Exception $e) {
		  echo $e->getMessage();
		}
	}

	public function uploadPhoto(int $userId, Request $req) {
		// Get username id from redis
		$key = $this->redis->getAccessTokenKey($userId);
		$usernameId = $this->redis->get($key);
		$this->login($usernameId);

		$path = $req->get('path');
		$caption = $req->get('caption');
		$this->ig->uploadPhoto($path, $caption);
	}
}