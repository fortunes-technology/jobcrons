<?php 
include_once 'dbconfigcron.php';
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3MultipartUploadException;
$realHandleUrl = "https://convertedfeed.s3.us-east-2.amazonaws.com/zipgz/WS9M48Tl.xml";

$reader = new XMLReader();
if($reader->open($realHandleUrl)) {
      
    $key = 0;

    while($reader->read()) {

      if($reader->nodeType == XMLReader::ELEMENT) $nodeName = $reader->name;

      if($nodeName === "job" || $nodeName === "JOB" || $nodeName === "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job") {

        libxml_use_internal_errors(true);
        
        try{
          $readerForNode = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $reader->readOuterXML()); 
          $node = new SimpleXMLElement($readerForNode);
        } catch (Exception $e){
          continue;
        }

        // if(!empty($node)) {
          
        //   $xmlWriter->startElement('item');
          
        //   // this is for normal xml handling
        //   if($value['cronid'] != "4") {

        //     for($i = 0; $i < count($updatetagpiece) - 1; $i++) {
        //       if($updatetagpiece[$i] != "discard") {
        //         if($updatetagpiece[$i] != "Default") {
        //           $updatetagReal = $updatetagpiece[$i];
        //         }
        //         else {
        //           $updatetagReal = $basetagpiece[$i];
        //         }

        //         if(isset($node->{$basetagpiece[$i]})) {

        //           $xmlString = $node->{$basetagpiece[$i]};
        //           if(in_array($basetagpiece[$i], $cdatatagpiece)) {
        //             $xmlWriter->startElement($updatetagReal);
        //             if($updatetagReal == "datePosted") {
        //               $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
        //               $xmlWriter->writeCdata(htmlspecialchars($insertedDate));
        //             }
        //             elseif($updatetagReal == "url") {
        //               // UTM adding here
        //               if(!empty($value['utm'])) {
        //                 if (strpos($xmlString->__toString(), '?') !== false) {
        //                   $xmlWriter->writeCdata(strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm']);
        //                 }
        //                 else {
        //                   $xmlWriter->writeCdata($xmlString->__toString()."?".$value['utm']);
        //                 }
        //               }                        
        //               else {
        //                 $xmlWriter->writeCdata($xmlString->__toString());
        //               }
        //             }
        //             else {
        //               $xmlWriter->writeCdata(htmlspecialchars($xmlString->__toString()));
        //             }
        //             $xmlWriter->endElement();
        //           }
        //           else {
        //             if($updatetagReal == "datePosted") {
        //               $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
        //               $xmlWriter->writeElement($updatetagReal, htmlspecialchars($insertedDate));
        //             }
        //             elseif($updatetagReal == "url") {
        //               // UTM adding here
        //               if(!empty($value['utm'])) {
        //                 if (strpos($xmlString->__toString(), '?') !== false) {
        //                   $xmlWriter->writeElement($updatetagReal, strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm']);
        //                 }
        //                 else {
        //                   $xmlWriter->writeElement($updatetagReal, $xmlString->__toString()."?".$value['utm']);
        //                 }
        //               }                        
        //               else {
        //                 $xmlWriter->writeElement($updatetagReal, $xmlString->__toString());
        //               }
        //             }
        //             else {
        //               $xmlWriter->writeElement($updatetagReal, htmlspecialchars($xmlString->__toString()));
        //             }

        //           }
        //         }                 

        //       }
        //     }
        //   }           


        //   if($value['cronid'] == "4") {
        //     for($i = 0; $i < count($updatetagpiece) - 1; $i++) {

        //       if($updatetagpiece[$i] != "discard") {
        //         // If update tag
        //         if($updatetagpiece[$i] != "Default") {
        //           $updatetagReal = $updatetagpiece[$i];
        //         }
        //         else {
        //           if (strpos($basetagpiece[$i], ':') !== false) {
        //             $updatetagRealKey = explode(":", $basetagpiece[$i]);
        //             $updatetagReal = end($updatetagRealKey);
        //           }
        //           else {
        //             $updatetagReal = $basetagpiece[$i];
        //           }
        //         }

        //         // if contains child tag
        //         if (strpos($basetagpiece[$i], ':') !== false) {
        //           $minitag = explode(":", $basetagpiece[$i]);
        //           $xmlString = $node;
        //           foreach($minitag as $rkey) {
        //             if(isset($xmlString -> {$rkey})) {
        //               $xmlString = $xmlString -> {$rkey};
        //             }
        //           }
        //           // if value is not exist then empty value
        //           if(!empty($xmlString)) {
        //             $insertedDate = "";
        //           }
        //           // handle of dateposted tag
        //           if($updatetagReal == "datePosted") {
        //             $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
        //           }
        //           //utm handle
        //           elseif($updatetagReal == "url")  {
        //             if(!empty($value['utm'])) {
        //               if (strpos($xmlString->__toString(), '?') !== false) {
        //                 $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm'];
        //               }
        //               else {
        //                 $insertedDate = $xmlString->__toString()."?".$value['utm'];
        //               }
        //             }                        
        //             else {
        //               $insertedDate = $xmlString->__toString();
        //             }
        //           }
        //           else {
        //             $insertedDate = $xmlString->__toString();
        //           }

        //           if(in_array($basetagpiece[$i], $cdatatagpiece)) {
        //             if($updatetagReal != "url") {
        //               $insertedDate = htmlspecialchars($insertedDate);
        //             }
        //             $xmlWriter->startElement($updatetagReal);
        //             $xmlWriter->writeCdata($insertedDate);
        //             $xmlWriter->endElement();
        //           }
        //           else {
        //             if($updatetagReal != "url") {
        //               $insertedDate = htmlspecialchars($insertedDate);
        //             }
        //             $xmlWriter->writeElement($updatetagReal, $insertedDate);
        //           }
        //         }
        //         // if not contain child tag
        //         else {
        //           if(isset($node->{$basetagpiece[$i]})) {
        //             $xmlString = $node->{$basetagpiece[$i]};
        //             // handle of dateposted tag
        //             if($updatetagReal == "datePosted") {
        //               $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
        //             }
        //             //utm handle
        //             elseif($updatetagReal == "url")  {
        //               if(!empty($value['utm'])) {
        //                 if (strpos($xmlString->__toString(), '?') !== false) {
        //                   $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm'];
        //                 }
        //                 else {
        //                   $insertedDate = $xmlString->__toString()."?".$value['utm'];
        //                 }
        //               }                        
        //               else {
        //                 $insertedDate = $xmlString->__toString();
        //               }
        //             }
        //             else {
        //               $insertedDate = $xmlString->__toString();
        //             }

        //             if(in_array($basetagpiece[$i], $cdatatagpiece)) {
        //               if($updatetagReal != "url") {
        //                 $insertedDate = htmlspecialchars($insertedDate);
        //               }
        //               $xmlWriter->startElement($updatetagReal);
        //               $xmlWriter->writeCdata($insertedDate);
        //               $xmlWriter->endElement();
        //             }
        //             else {
        //               if($updatetagReal != "url") {
        //                 $insertedDate = htmlspecialchars($insertedDate);
        //               }
        //               $xmlWriter->writeElement($updatetagReal, $insertedDate);
        //             }
        //           }
                  
        //         }

        //       }
        //     }
        //   }           


        //   if(!empty($defaultcountry)) {
        //     $xmlWriter->writeElement("addressCountry", $defaultcountry);
        //   }
        //   if(!empty($joblocationtype)) {
        //     $xmlWriter->writeElement("jobLocationType", $joblocationtype);
        //   }
        //   $xmlWriter->endElement();
        // }
        
      }

      $key ++ ;
      echo $key;
      echo "\n";

    }
    // $xmlWriter->endElement();
    // file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);

    // file upload to s3
     
  }
  echo $key; exit;
?>