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

	        //$bahan isinya bahan yang mau dicari harganya
	        echo print_r($bahan);

	        $arrayData = array(); //ini buat nampung semua hasil

	        //cari di kemendag dulu
	        $harga_kemendag = $this->Htmldom->file_get_html('http://ews.kemendag.go.id/');
	        $arrKomoditas = array(); //terdiri dari nama dan harga
			$komoditas = array(); //terdiri dari nama dan harga

			foreach($harga_kemendag->find('div.sp2kpindexharga') as $element) {
				$harga = array();
				$nama = $element->find('div div.titleharga',0)->innertext;
				array_push($harga, filter_var($element->find('div div.hargaskg',0)->innertext, FILTER_SANITIZE_NUMBER_INT));
				array_push($harga, filter_var($element->find('div.sp2kpindexhargacontentkemarin',0)->innertext, FILTER_SANITIZE_NUMBER_INT));
				array_push($harga, (boolean)((filter_var($element->find('div div.hargaskg',0)->innertext, FILTER_SANITIZE_NUMBER_INT))>(filter_var($element->find('div.sp2kpindexhargacontentkemarin',0)->innertext, FILTER_SANITIZE_NUMBER_INT))));

				array_push($komoditas, $nama);
				array_push($komoditas, $harga);
				array_push($arrKomoditas, $komoditas); //ini masih salah loopingnyaaa.... pusingg
	        }

	        echo "<br><br><br>";
				echo print_r($arrKomoditas); //ini dapat komoditas yang dari kemendag

			for ($i = 0; $i < count($bahan); $i++) {
				for ($j = 0; $j < count($arrKomoditas); $j++) {
					if (strcmp($bahan[$i], $arrKomoditas[$j][0]) != 0) {
						$nama = $bahan[$i];
						$harga = $arrKomoditas[$j][1][0];
						$kenaikan = ($arrKomoditas[$j][1][2] == 1);
						$sumber = "kemendag";

						$subArrayData = array();
						array_push($subArrayData, $nama);
						array_push($subArrayData, $harga);
						array_push($subArrayData, $kenaikan);
						array_push($subArrayData, $sumber);

						array_push($arrayData, $subArrayData);
					}

				}
			}

			echo print_r($arrayData);



	        //kalo ga ada cari di tokopedia
		}
	}
?>