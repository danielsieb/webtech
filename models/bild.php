<?php

class Bild{
	
	private $id;
	private $link;

	public function __construct($id, $link) {
      $this->id    = $id;
      $this->link  = $link;
    }

//Datenbankabfrage des $link
public function getLink(){
	$list = [];
	$db = Db::getInstance();
  $req = $db->query('SELECT Link FROM Bild');

    foreach($req->fetchAll() as $post) {
    	$list[] = $post['Link'];
    }

    return $list;

}

}