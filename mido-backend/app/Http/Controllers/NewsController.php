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
				'image_url' => $imageUrl,
				'time' => $time,
				'title' => $title,
				'header' => $header,
				'read_more' => $readMore,
			];
			array_push($newsList, $news);
		}

		return response()->json([
			'news' => $newsList
		]);
	}
}