<?php
    namespace App\Http\Controllers;
    use Yangqi\Htmldom\Htmldom;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use Log;
    

    class BukalapakController extends Controller {
      public function getPages(Request $req) {
      	$keyword = $req->get('keyword');
        $this->Htmldom = new Htmldom();

        // Create DOM from URL or file
        $html = $this->Htmldom->file_get_html('https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bkeywords%5D=' . urlencode($keyword));

        $number_of_page = 0;
        foreach($html->find('span.last-page') as $element) {
          $number_of_page = $element->innertext;
        }

        if ($number_of_page > 5) {
          $number_of_page = 5;
        }
        
        $total_price = 0;
        $total_product = 0;
        $max = 0;
        $min = 100000000;
        $page_number = 0;

        do {
          $page_number++;
          foreach($html->find('span.amount') as $element) {
            $total_product += 1;
            $total_price += str_replace(".", "", $element->innertext);
            if ($max < str_replace(".", "", $element->innertext)) {
              $max = str_replace(".", "", $element->innertext);
            }
            if ($min > str_replace(".", "", $element->innertext)) {
              $min = str_replace(".", "", $element->innertext);
            }
          }

          $html = $this->Htmldom->file_get_html('https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&page='.$page_number.'&search_source=omnisearch_organic&search%5Bkeywords%5D=' . urlencode($keyword));
        } while ($page_number < $number_of_page);
        
        $arrayData = [
          'banyak_produk' => $total_product, 
          'harga_tertinggi' => $max, 
          'harga_terendah' => $min, 
          'harga_rata' => $total_price/$total_product
          ];

        return response()->json($arrayData);
    }

    // public function postProduct(int $product_id) {
    //   $data = "{'id': ".$product_id." }";
    //   $url = "https://api.bukalapak.com/v2/products/".$product_id."/push.json";
    //   $headers = array('Content-Type: application/json');
    //   $curl = curl_init();
    //   curl_setopt($curl, CURLOPT_URL, $url);
    //   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //   curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    //   curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //   $response = curl_exec($curl);
    //   curl_close($curl);
    //   echo $response;
    // }
  }
?>
