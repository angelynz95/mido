<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Log;

class RedisController extends Controller {
	// Instagram only
	public function getUsernameKey(int $usernameId) {
		return "ig_username:" . $usernameId;
	}
	public function getUserPasswordKey(int $usernameId) {
		return "ig_password:" . $usernameId;
	}

	public function getAccessTokenKey(int $userId) {
		return "access_token:" . $userId;
	}

	public function getPageAccessTokenKey(int $userId, int $pageId) {
		return "page_access_token:" . $userId . ":" . $pageId;
	}

	public function get(string $key) {
		$value = Redis::get($key);
		Log::info('Get redis key: ' . $key . ' value: ' . $value);
		
		return $value;
	}

	public function save(string $key, string $value) {
		Redis::set($key, $value);
		Log::info('Save redis key: ' . $key . ' value: ' . $value);
	}

	public function delete(string $key) {
		Redis::del($key);
		Log::info('Delete redis key: ' . $key);
	}
}