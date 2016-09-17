<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller {
	public function storeUserData(array $data) {
		// Check previous occurences
		$shadowUserId = DB::table('user')
			->where($data)
			->first()->id;

		if (!count($shadowUserId)) {
			DB::table('user')->insert($data);
			$shadowUserId = DB::getPdo()->lastInsertId();
		}
		return $shadowUserId;
	}

	public function storePagesData(array $data) {
		// Check previous occurences
		$shadowPageId = DB::table('user_page')
			->where($data)
			->first()->id;

		if (!count($shadowPageId)) {
			DB::table('user_page')->insert($data);
			$shadowPageId = DB::getPdo()->lastInsertId;
		}

		return $shadowPageId;
	}
}