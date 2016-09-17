<?php
    namespace App\Http\Controllers;
    use Yangqi\Htmldom\Htmldom;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use Log;
    

    class BukalapakController extends Controller {
      public function getPages(string $keyword) {
        $this->Htmldom = new Htmldom();

        // Create DOM from URL or file
        $html = $this->Htmldom->file_get_html('https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bkeywords%5D=' . $keyword);

        $number_of_page = 0;
        foreach($html->find('span.last-page') as $element) {
          $number_of_page = $element->innertext;
        }

        if ($number_of_page > 20) {
          $number_of_page = 20;
        }
        
        $total_price = 0;
        $mean = 0;
        $total_product = 0;
        $max = 0;
        $min = 100000000;
        $page_number = 0;

        // Find all links 
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
          // Create DOM from URL or file
        $html = $this->Htmldom->file_get_html('https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&page='.$page_number.'&search_source=omnisearch_organic&search%5Bkeywords%5D=' . $keyword);
        } while ($page_number < $number_of_page);
        


        echo 'Banyak produk : '.$total_product;
        echo '<br><br>Total harga : '.$total_price;
        echo '<br><br>Harga tertinggi : '.$max;
        echo '<br><br>Harga terendah : '.$min;
        echo '<br><br>Rata-rata harga : '.($total_price/$total_product);

    }
  }
?>