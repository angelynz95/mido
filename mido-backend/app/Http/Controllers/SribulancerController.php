<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Yangqi\Htmldom\Htmldom;

class SribulancerController extends Controller {
	public function getPages(Request $req) {
		$keyword = urlencode($req->get('keyword'));

		$htmldom = new Htmldom();
		$html = $htmldom->file_get_html('https://www.sribulancer.com/id/bf/freelancer/v3?utf8=%E2%9C%93&by_keyword=' . $keyword);

		$freelancerInfos = [];
		foreach($html->find('div.col-sm-10') as $user) {
			$nameLink = $user->find('a[title="Lihat Profil"]', 0);
			$link = $nameLink->href;

			$name = $nameLink->find('h4.real-name', 0);
			$name = str_replace($name->find('small.ml-5', 0), '', $name->innertext);

			$job = $user->find('.applicant-title', 0);
			$job = str_replace($job->find('i.fa-hand-o-up', 0), '', $job->innertext);

			$bio = $user->find('.applicant-bio', 0)->innertext;
			$bio = str_replace('<span class="highlight-search">', '', $bio);
			$bio = str_replace('</span>', '', $bio);

			$freelancerInfo = [
				'name' => $name,
				'link' => 'https://www.sribulancer.com/' . $link,
				'job' => $job,
				'bio' => $bio,
			];
			array_push($freelancerInfos, $freelancerInfo);
		}
		
		return $freelancerInfos;
	}
}