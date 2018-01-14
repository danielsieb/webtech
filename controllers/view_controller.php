<?php
  class ViewController {

    public function home() {
      $searched = False;
      require_once('views/index.php');
    }

    public function search() {
      $searched = True;
      $searchKey = strip_tags(htmlspecialchars($_POST['searchKey']));
      $image_urls = Suche::catchImageUrls($searchKey);

      $test = json_decode(Suche::catchGoogleVisionData($image_urls[0]));

      foreach ($image_urls as $key => $value) {
        $json[$key] = json_decode(Suche::catchGoogleVisionData($value));
      }

      $cumulativeArray = Suche::cumulateJson($json);
      
      require_once('views/index.php'); 
    }

    public function error() {
      require_once('views/error.php');
    }
  }
?>