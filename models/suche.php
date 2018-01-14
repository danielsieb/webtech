<?php

class Suche {

	private $such_ID;
	private $zeitstempel;
	private $suchbegriff;
	private $sozialesMedium;
	private $analyseergebnisse;



//Datenbankabfrage des $zeitstempel
public function getZeitstempel(){
	$stmt = $dbh->prepare("SELECT FROM Suche WHERE zeitstempel=:zeitstempel");   
        $stmt->bindValue(":zeitstempel", $this->zeitstempel);                          	
	$stmt->execute();
        return $this->zeitstempel;
}



//Datenbankabfrage des $such_ID
public function getSuchID(){
	$stmt = $dbh->prepare("SELECT FROM Suche WHERE such_ID=:such_ID");   
        $stmt->bindValue(":such_ID", $this->such_ID);                                  
        $stmt->execute();
        return $this->such_ID;
}



//Datenbankabfrage des $suchbegriff
public function getSuchbegriff(){
	$stmt = $dbh->prepare("SELECT FROM Suche WHERE suchbegriff=:suchbegriff");   
        $stmt->bindValue(":suchbegriff", $this->suchbegriff);                                  
        $stmt->execute();
        return $this->suchbegriff;
}



//Speichern von Bildurl nach suchbegriff in Datenbank
public function catchImageUrls($suchbegriff){

    $db = Db::getInstance();
    $suchId = uniqid();
    $db->query("INSERT INTO Suche (Such_Id, Suchbegriff, Soziales_Medium) VALUES ('$suchId', '$suchbegriff', 'Twitter')");

	//Such nach dem Suchbegriff auf den Seite von Twitter
    $keyword = urlencode($suchbegriff);  
    $url = "https://twitter.com/search?q=" . $keyword;  

	//Erhalten von Seiteninhalt
    $html = file_get_contents($url);  

	//Erhalten von Bildurl 
    preg_match_all('!http[s]?:\/\/pbs\.twimg\.com\/media\/[^:]+\.(jpg|png|gif)!i', $html, $matches);  
    $res = array_values(array_unique($matches[0]));
    
	//speichern von Bildurl in Datenbank
    foreach ($res as $key => $value) {   
        $db = Db::getInstance();
        $bildId = uniqid();
        $db->query("INSERT INTO Bild (Bild_Id, Link) VALUES ('$bildId', '$value')");
        $db->query("INSERT INTO Bild_has_Suche (Bild_Bild_Id, Suche_Such_Id) VALUES ('$bildId', '$suchId')");
    }

	return $res;
}
	 
/*

//schicken einer Abfrage und abholen von Analyseergebnisse
public function Analyseergebnisse(){

//sending the request(Ich glaube,diese Teil ist falsch...)(https://cloud.google.com/vision/docs/using-curl) 
curl -v -s -H "Content-Type: application/json" \ 
 https://vision.googleapis.com/v1/images:annotate?key=AIzaSyB16FhpClXmeCSz1wbKudN2pvtYv-UtQE4 \
    --data-binary @<request class="json"></request>
	header('Content-type:text/json');
	$body = {
  	"requests":[{
 		     "image":{
 		       "source":{
         		 "imageUri":$link
       			}
		      },
      		"features":[{
          		"type":"LABEL_DETECTION",
         		 "maxResults":1
	      	}]
	  }]
	}

//abholen von Analyseergebnisse(die unteren code ist aus die api  https://cloud.google.com/vision/docs/detecting-labels#vision-label-detection-gcs-php)
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\VisionClient;
use Google\Cloud\Storage\StorageClient;


//hab keine passende variable von unseren projekt gefunden, um die unteren variable zu wechseln... 
// $projectId = 'YOUR_PROJECT_ID';
// $bucketName = 'your-bucket-name'
// $objectName = 'your-object-name'

function detect_label_gcs($projectId, $bucketName, $objectName)
{
    $vision = new VisionClient([
        'projectId' => $projectId,
    ]);
    $storage = new StorageClient([
        'projectId' => $projectId,
    ]);
    // fetch the storage object and annotate the image
    $object = $storage->bucket($bucketName)->object($objectName);
    $image = $vision->image($object, ['LABEL_DETECTION']);
    $result = $vision->annotate($image);

    // print the response
    print("LABELS:\n");
    foreach ($result->labels() as $label) {
        print($label->description() . PHP_EOL);
    }
}


  
}

*/

}

