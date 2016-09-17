<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller {
	public function storeUserData(array $data) {
		// Check previous occurences
		$result = DB::table('user')
			->where($data)
			->first();

		$shadowUserId;
		if (count($result)) {
			$shadowUserId = $result->id;
		} else {
			DB::table('user')->insert($data);
			$shadowUserId = DB::getPdo()->lastInsertId();
		}
		
		return $shadowUserId;
	}

	public function storePagesData(array $data) {
		// Check previous occurences
		$result = DB::table('user_page')
			->where($data)
			->first();

		$shadowPageId;
		if (count($result)) {
			$shadowPageId = $result->id;
		} else {
			DB::table('user_page')->insert($data);
			$shadowPageId = DB::getPdo()->lastInsertId();
		}

		return $shadowPageId;
	}

	public function getPageId(int $shadowPageId) {
		$pageId = DB::table('user_page')
			->where('id', $shadowPageId)
			->first();

		if (count($pageId)) {
			$pageId = $pageId->real_id;
		} else {
			$pageId = 0;
		}
		return $pageId;
	}
}