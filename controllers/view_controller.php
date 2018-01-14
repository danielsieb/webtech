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
      
      require_once('views/index.php'); 
    }

    public function error() {
      require_once('views/error.php');
    }
  }
?>