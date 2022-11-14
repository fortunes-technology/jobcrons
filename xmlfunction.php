<?php
include_once 'dbconfigcron.php';

$url_w3 = "https://www.w3schools.com/Xml/note.xml";
$url_test = "http://cloud.tfl.gov.uk/TrackerNet/PredictionSummary/V";
$url_real = "https://neuvoo.com/services/feeds/generatesV3/generate.php?partner=bebee&country=sg&page=1&of=1";


$feedAll = $crud->getAll();

if(count($feedAll) > 0) {
  
  $xml = new DOMDocument();
  $xml->formatOutput=true;  
  $xml_bebee = $xml->createElement("bebee");
  $xml->appendChild( $xml_bebee );
  
  
  foreach ($feedAll as $value) {
    $updatetag = $value['updatetag'];
    $updatetagpiece = explode(",", $updatetag);
    if($value['cdatatag'] != "") {
      $cdatatagpiece = explode(",", $value['cdatatag']);
    }
    // print_r($updatetagpiece); exit;

    $xmlContent = simplexml_load_file($value['url']);
    if(!empty($xmlContent)) {
      $jobXmlContent = $xmlContent->xpath("//job");
      $itemXmlContent = $xmlContent->xpath("//item");

      switch (true) {
        case (!empty($jobXmlContent)):
            $xmlHandleContent = $jobXmlContent;
            break;
      
        case (!empty($itemXmlContent)):
            $xmlHandleContent = $itemXmlContent;
            break;
      
        default:
            $xmlHandleContent = [];
            break;
      }

      if(!empty($xmlHandleContent)) {
        foreach($xmlHandleContent as $key => $child) {
  
          $xml_item = $xml->createElement("item");
          $xml_bebee->appendChild( $xml_item );
    
          // print_r($jobXmlContent[2]); exit;
          $i = 0;
          foreach($child as $minikey => $minichild) {
            if($updatetagpiece[$i] != "discard") {
              if($updatetagpiece[$i] != "Default") {
                $updatetagReal = $updatetagpiece[$i];
              }
              else {
                $updatetagReal = $minichild->getName();
              }
              if(in_array($minichild->getName(), $cdatatagpiece)) {
                $inner_item = $xml->createElement($updatetagReal);
                $inner_item_cdata = $xml->createCDATASection(htmlspecialchars($minichild->__toString()));
                $inner_item->appendChild($inner_item_cdata);
              }
              else {
                $inner_item = $xml->createElement($updatetagReal, htmlspecialchars($minichild->__toString()));
              }
              $xml_item->appendChild($inner_item);
            }
            $i ++ ;
          }
        }
        $saveName = str_replace(" ", "_", strtolower($value['name'])).".xml";
        $xml->save("/var/www/html/cf/xmldir/".$saveName);
      }
    }
  }
  echo "success";
}

?>