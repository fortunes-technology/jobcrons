<?php
$realHandleUrl = "https://storage.googleapis.com/jr-databox-syndication-public/syndication/aggregators/Bebee/jobrapido_Bebee_UK.xml";
// $xml = simplexml_load_file($realHandleUrl);
     
// echo $xml->job->count(); // Output is 0

$dom = new DOMDocument;
$dom->loadXml($realHandleUrl);

// count all OfferName elements
echo $dom->getElementsByTagName('job')->length, PHP_EOL; // 6

?>