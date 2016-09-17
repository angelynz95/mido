<?php
    namespace App\Http\Controllers;
    use Yangqi\Htmldom\Htmldom;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use Log;
    

    class TokopediaController extends Controller {
      public function getPages(string $keyword) {
        $number_of_product = 0;
        $total_price = 0;
        $max = 0;
        $min = 100000000;
        $page_number = 0;
        $is_last_page = false;
        do {
            $page_number++;
            $jsonurl = 'https://ace.tokopedia.com/search/v1/product?st=product&q='.urlencode($keyword).'&full_domain=www.tokopedia.com&scheme=https&device=desktop&source=search&page='.$page_number.'&fshop=1&rows=25&unique_id=6af0d4bd93df4e4ba4346909011b70af&start='.(25*($page_number-1)).'&ob=23&full_domain=www.tokopedia.com&callback=angular.callbacks._1';
            $json = file_get_contents($jsonurl,0,null,null);
            $json = substr($json, 21, strlen($json)-22);
            $json_output = json_decode($json);

            foreach ( $json_output->data as $data )
            {
              $number_of_product++;
              $price = filter_var($data->price, FILTER_SANITIZE_NUMBER_INT);
              $total_price += $price;
              if ($max < $price) {
                $max = $price;
              }
              if ($min > $price) {
                $min = $price;
              }
            }


            if (count($json_output->data) != 25) {
                $is_last_page = true;
            }

          } while (($page_number < 20)&&!($is_last_page));

        $arrayData = array(
            'banyak_produk' => $number_of_product, 
            'total_harga' => $total_price, 
            'harga_tertinggi' => $max, 
            'harga_terendah' => $min, 
            'harga_rata' => $total_price/$number_of_product
        );

        echo json_encode($arrayData);

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