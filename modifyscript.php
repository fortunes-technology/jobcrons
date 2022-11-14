<?php
include_once 'dbconfigcron.php';

$feedAll = $crud->getAll();

if(count($feedAll) > 0) {
  
  foreach ($feedAll as $value) {
    $reader = new XMLReader();

    $url_real = $value['url'];

    if (strpos($url_real, '.zip') !== false || strpos($url_real, '.gz') !== false) {
      $isReady = $crud->getIsReady($url_real);
      if($isReady) {
        $url_real = S3ZIP.$isReady['name'].'.xml';
      }
    }

    if($reader->open($url_real)) {
      while($reader->read()) {
        if($reader->nodeType == XMLReader::ELEMENT) $nodeName = $reader->name;
        if($nodeName == "job" || $nodeName == "JOB" || $nodeName == "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job") {
          
          $readerForNode = str_replace("<![CDATA[", "<![CDATA[cdata", $reader->readOuterXML());

          try{
            $readerForNode = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $readerForNode); 
            $node = new SimpleXMLElement($readerForNode);
          } catch (Exception $e){
            break;
          }

          if(!empty($node)) {
            foreach($node as $minikey => $child) {
              $status = false;
              foreach($child as $miniChild) {
                if(!empty($miniChild->getName())) {
                    file_put_contents("/var/www/html/cf/childxmlurllist.txt", $value['url']."\n", FILE_APPEND);
                    break 3;
                }
              }

            }

            break;

          }

        }        

      }
    }

  }
    
}
?>