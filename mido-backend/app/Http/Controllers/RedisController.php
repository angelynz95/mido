<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Redis;
use Log;

class RedisController extends Controller {
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