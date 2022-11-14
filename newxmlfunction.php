<?php
include_once 'dbconfigcron.php';
error_reporting(E_ALL);
$feedAll = $crud->getAll();

if(count($feedAll) > 0) {

  $_SESSION['bigCron'] == "Valid";
  
  // $xml = new DOMDocument();
  // $xml->formatOutput=true;  
  // $xml_bebee = $xml->createElement("bebee");
  // $xml->appendChild( $xml_bebee );
  
  foreach ($feedAll as $value) {

    $cdatatagpiece = [];
    $updatetag = $value['updatetag'];
    $basetag = $value['basetag'];
    $updatetagpiece = explode(",", $updatetag);
    $basetagpiece = explode(",", $basetag);
    $defaultcountry = $value['defaultcountry'];
    $joblocationtype = $value['joblocationtype'];
    if($value['cdatatag'] != "") {
      $cdatatagpiece = explode(",", $value['cdatatag']);
    }

    $reader = new XMLReader();

    $realHandleUrl = $value['url'];
    if (strpos($realHandleUrl, '.zip') !== false || strpos($realHandleUrl, '.gz') !== false) {
      $isReady = $crud->getIsReady($realHandleUrl);
      if($isReady) {
        $realHandleUrl = "/var/www/html/cf/tempzip/".$isReady['name'].'.xml';
      }
    }
    else {
      $isReady = $crud->getIsReady($realHandleUrl);
      if($isReady) {
        $realHandleUrl = S3ZIP.$isReady['name'].'.xml';
      }
    }

    if($reader->open($realHandleUrl)) {

      $key = 0;

      $saveName = str_replace(" ", "_", strtolower($value['name'])).".xml";
      $saveName = "/var/www/html/cf/xmldir/".$saveName;

      //remove if the file is exist
      if (file_exists($saveName)) {
        $deleted = unlink($saveName);
      }


      $xmlWriter = new XMLWriter();
      $xmlWriter->openMemory();
      $xmlWriter->startDocument('1.0', 'UTF-8');
      $xmlWriter->setIndent(TRUE);
      $xmlWriter->startElement('bebee');

      while($reader->read()) {

        if($reader->nodeType == XMLReader::ELEMENT) $nodeName = $reader->name;
  
        if($nodeName === "job" || $nodeName === "JOB" || $nodeName === "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job") {
  
          libxml_use_internal_errors(true);
          
          try{
              $node = new SimpleXMLElement($reader->readOuterXML());
          } catch (Exception $e){
            continue;
          }
  
          if(!empty($node)) {
            
            $xmlWriter->startElement('item');
  
            $i = 0;
            foreach($node as $minikey => $minichild) {
              if(!empty($updatetagpiece[$i])) {
                if($updatetagpiece[$i] != "discard") {
                  if($updatetagpiece[$i] != "Default") {
                    $updatetagReal = $updatetagpiece[$i];
                  }
                  else {
                    $updatetagReal = $minichild->getName();
                  }
  
                  if(in_array($minichild->getName(), $cdatatagpiece)) {
                    $xmlWriter->startElement($updatetagReal);
                    $xmlWriter->writeCdata(htmlspecialchars($minichild->__toString()));
                    $xmlWriter->endElement();
                    // $inner_item = $xml->createElement($updatetagReal);  
                    // $inner_item_cdata = $xml->createCDATASection(htmlspecialchars($minichild->__toString()));
                    // $inner_item->appendChild($inner_item_cdata);
                  }
                  else {
                    $xmlWriter->writeElement($updatetagReal, htmlspecialchars($minichild->__toString()));
                    // $inner_item = $xml->createElement($updatetagReal, htmlspecialchars($minichild->__toString()));
                  }
                  // $xml_item->appendChild($inner_item);
                }
                $i ++ ;
              }
            }
            if(!empty($defaultcountry)) {
              $xmlWriter->writeElement("addressCountry", $defaultcountry);
            }
            if(!empty($joblocationtype)) {
              $xmlWriter->writeElement("jobLocationType", $joblocationtype);
            }
            $xmlWriter->endElement();
          }
          
        }
        $key ++ ;
        if (0 == $key%1000) {
            // $xmlWriter->endElement();
            file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);
        }

      }
      $xmlWriter->endElement();
      file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);
      // $xml->save("xmldir/".$saveName); 
    }
  }
  echo "success";
  unset($_SESSION['bigCron']);
}

?>