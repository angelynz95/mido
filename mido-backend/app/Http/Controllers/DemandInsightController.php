<?php

	namespace App\Http\Controllers;
    use Yangqi\Htmldom\Htmldom;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use Log;

	class DemandInsightController extends Controller {
		public function getResep(string $keyword) {
			$this->Htmldom = new Htmldom();
			$keyword_video = "video cara membuat ".$keyword;

		    // Create DOM from URL or file
		    $videohtml = $this->Htmldom->file_get_html('https://www.google.com/search?q='.urlencode($keyword_video).'&ie=utf-8&oe=utf-8&client=firefox-b');
		    $videolinks = array();

		    foreach($videohtml->find('cite.kv') as $element) {
	          array_push($videolinks, $element->innertext);
	        }

	        $recipelink = $this->Htmldom->file_get_html('https://cookpad.com/id/cari/'.$keyword);

			foreach($recipelink->find('li.recipe a') as $element) {
				$recipe = 'https://cookpad.com'.$element->href.'?ref=search';
				break;
	        }

	        $recipe = $this->Htmldom->file_get_html($recipe);

	        $bahan = array();
	        foreach($recipe->find('div.ingredient__details') as $element) {
				array_push($bahan, strip_tags($element->innertext));
	        }

	        $langkah = array();
	        foreach($recipe->find('p.step__text') as $element) {
				array_push($langkah, $element->innertext);
	        }

	        $arrayData = array(
				'link_video' => $videolinks, 		
				'bahan' => $bahan, 
				'langkah' => $langkah
			);

			return json_encode($arrayData);

		}

		public function getInfoBahan(string $keyword) {
			$this->Htmldom = new Htmldom();
			$recipelink = $this->Htmldom->file_get_html('https://cookpad.com/id/cari/'.$keyword);
			
			foreach($recipelink->find('div.recipe__ingredients') as $element) {
				$bahan_bahan = $element->innertext;
				break;
	        }

	        $bahan = explode(", ", $bahan_bahan);

	        for($i = 0; $i < count($bahan); $i++) {
	        	if (strpos($bahan[$i], '(') !== false) {
	        		$bahan[$i] = substr($bahan[$i], 0, strpos($bahan[$i], '('));	
	        	}
	        }
	        

	        echo print_r($bahan);
		}
	}
?>