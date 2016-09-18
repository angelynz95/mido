<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yangqi\Htmldom\Htmldom;

class NewsController extends Controller {
	public function getPages(Request $request) {
		$htmldom = new Htmldom();
		$html = $htmldom->file_get_html('http://www.depkop.go.id/berita-informasi/berita-media/');

		$newsList = [];
		foreach($html->find('.news-list') as $news) {
			$imageUrl = $news->find('img', 0)->src;
			$time = $news->find('.muted', 0)->innertext;
			$title = $news->find('.new-title', 0)->innertext;
			$header = $news->find('.news-subheader p', 0)->innertext;
			$readMore = $news->find('a[title="' . $title . '"]', 0)->href;
			
			$news = [
				'image_url' => strip_tags($imageUrl),
				'time' => strip_tags($time),
				'title' => strip_tags($title),
				'header' => strip_tags($header),
				'read_more' => 'http://www.depkop.go.id/' . strip_tags($readMore),
			];
			array_push($newsList, $news);
		}

		return response()->json([
			'news' => $newsList
		]);
	}
}