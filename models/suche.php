<?php

class Suche {

    //Datenbankabfrage des $zeitstempel
    public function getZeitstempel(){
    	$stmt = $dbh->prepare("SELECT FROM Suche WHERE zeitstempel=:zeitstempel");   
            $stmt->bindValue(":zeitstempel", $this->zeitstempel);                          	
    	$stmt->execute();
            return $this->zeitstempel;
    }
    
    
    
    //Datenbankabfrage des $such_ID
    public function getSuchID(){
        $db = Db::getInstance();
        $db->query("SELECT FROM Suche WHERE Such_Id=:such_ID");
    	$stmt = $dbh->prepare("SELECT FROM Suche WHERE Such_Id=:such_ID");   
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
    public function catchImageUrls($suchbegriff) {
    
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
        
        $tupel = [$res, $suchId];

    	return $tupel;
    }
    	 
    //schicken einer Abfrage und abholen von Analyseergebnisse
    //abholen von Analyseergebnisse(die unteren code ist aus die api  https://cloud.google.com/vision/docs/ detecting-labels#vision-label-detection-gcs-php)
    public function catchGoogleVisionData($link) {

        $db = Db::getInstance();
        $req = $db->query("SELECT Analyseergebnis FROM Bild WHERE Link = '$link'");
        $result = $req->fetch(PDO::FETCH_ASSOC);

        if ($result['Analyseergebnis'] == NULL) {

            $json = '{"requests":  [{ "features":  [ {"type": "LABEL_DETECTION"}], "image": {"source": { "imageUri": "'. $link .'"}}}]}';
    
            $ch = curl_init('https://vision.googleapis.com/v1/images:annotate?key=AIzaSyA8o4W6wHolFeOEHD0ZdSiFS2S5TcytlIY');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($json))                                                                       
            );         
    
            $result = curl_exec($ch);
    
            $db->query("UPDATE Bild SET Analyseergebnis = '$result' WHERE Link = '$link'");

            return $result;

        } else {
            $json = $result['Analyseergebnis'];
            $db->query("UPDATE Bild SET Analyseergebnis = '$json' WHERE Link = '$link'");
            return $json;
        }
    } 

    public function cumulateJsons($jsons, $suchId) {

        for ($i=0; $i < (count($jsons)-1) ; $i++) { 

            foreach ($jsons[$i]->responses[0]->labelAnnotations as $label) {
                $labelArray[] = $label->{'description'};                        // Erstellt Array aus in $jsons enthaltenen Labeln 
            };
            
            foreach ($jsons[($i)]->responses[0]->labelAnnotations as $percent) {
                $labelPercent[] = $percent->{'score'};                          // // Erstellt Array aus in $jsons enthaltenen Scores
            }
        }


        for ($i=0; $i < (count($labelArray)) ; $i++) {
            $sum = 0.0;
            if ($labelPercent[$i] != 0)  {
                $z = array_keys($labelArray,"$labelArray[$i]");             // Erstellt Array mit Schlüsseln, welche gleiche Labels in $labelArray adressieren 
                $arrayLength = count($z);                                   // gibt Länge des mit Schlüsseln gefüllten Arrays aus = Anzahl gleicher Labels 

                for ($j=0; $j < $arrayLength; $j++) { 
                    $sum += (double)$labelPercent[($z[$j])];                // Summiert Scores der gleichen Label auf 
                    $labelPercent[($z[$j])] = 0;                            // Ersetzt jeweils aufsummierten Score mit 0 --> erkennen was bereits bearbeitet wurde 
                }

                $sum = $sum / $arrayLength;
                if ($sum > 0.7) {                                           // Score Filter 
                    $cumLabelArray["$labelArray[$i]"] = $sum;               // cumLabelArray mit Labeln und dazugehlrigen ckumulierten Scores füllen 
                }
            }         
        }


        $cumJson = json_encode($cumLabelArray);                                     // Wandelt cumLabelArray in Json um 
        $db = Db::getInstance();                                               
        $db->query("UPDATE Suche SET Analyseergebnis = '$cumJson' WHERE Such_Id = '$suchId'");

        return $cumLabelArray;
    }

}








































