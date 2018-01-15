<?php
  class ViewController {

    public function home() {
      $searched = False;
      require_once('views/index.php');
    }

    public function search() {
      $searched = True;
      $searchKey = strip_tags(htmlspecialchars($_POST['searchKey']));
      $tupel = Suche::catchImageUrls($searchKey);
      $image_urls = $tupel[0];
      $suchId = $tupel[1];

      foreach ($image_urls as $key => $value) {
        $jsons[$key] = json_decode(Suche::catchGoogleVisionData($value));
      }

      $cumulativeArray = Suche::cumulateJsons($jsons, $suchId); // Berechnet kumulierte Analyseergebnisse
      
      require_once('views/index.php'); 
    }

    public function error() {
      require_once('views/error.php');
    }
  }
?>