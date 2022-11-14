<?php
include_once 'dbconfigcron.php';

use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3MultipartUploadException;
use Aws\S3\S3Client;

$client = new S3Client(array(
    'credentials' => [
        'key' => 'AKIAQLRVICZQM2MQJHZM',
        'secret' => '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',
    ],
    'region' => 'us-east-2',
    'version'  => 'latest',
));

$client->registerStreamWrapper();

$feedAll = $crud->getSpecial();

$cronStatus = $crud->getCronStatus(1000);
if($cronStatus != "Finished") {
  echo "Cron is Running";
  exit();
}
echo "Cron status is changed and it is running";

$cronStart = $crud->cronStatus("1000", "Running");

// Remove specific value from url
function strip_param_from_url( $url, $params ) {
  $paramArray = [];
  $params = explode("&", $params);
  foreach ($params as $key => $value) {
    $paramArray[] = explode("=", $value)[0];
  }
  foreach ($paramArray as $key => $value) {
    $base_url = strtok($url, '?');              // Get the base url
    $parsed_url = parse_url($url);              // Parse it 
    $query = $parsed_url['query'];              // Get the query string
    parse_str( $query, $parameters );           // Convert Parameters into array
    unset( $parameters[$value] );               // Delete the one you want
    $new_query = http_build_query($parameters); // Rebuilt query string
    $url = $base_url.'?'.$new_query;
  }  
  return $base_url.'?'.$new_query;            // Finally url is ready
}

function stripInvalidXml($value)
{
    $ret = "";
    $current;
    if (empty($value)) 
    {
        return $ret;
    }
 
    $length = strlen($value);
    for ($i=0; $i < $length; $i++)
    {
        $current = ord($value[$i]);
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF)))
        {
            $ret .= chr($current);
        }
        else
        {
            $ret .= " ";
        }
    }
    return $ret;
}

function dateHandle($value) {
  $dateFormatArray = array(
            "Y:m:d","d:m:Y","Y/m/d","d/m/Y","Y.m.d","d.m.Y"
    );
    if(!empty($strtotime = strtotime($value))) {
      $year = date('Y', $strtotime);
      if($year == "2020" || $year == "2021" || $year == "2022") {
        $insertedDate = date('Y-m-d', $strtotime);
      }
      else {
        foreach ($dateFormatArray as $fordate) {
            if (DateTime::createFromFormat($fordate, $value) !== FALSE) {
              $insertedDate = DateTime::createFromFormat($fordate, $value)->format('Y-m-d');
              break;
            }
            else {
              $insertedDate = $value;
            }
          }        
      }
    }
    else {
      $arraySplit = explode(" ",$value);
        if(count($arraySplit) > 0) {
          
        foreach ($arraySplit as $splitValue) {
            foreach ($dateFormatArray as $fordate) {
              if (DateTime::createFromFormat($fordate, $splitValue) !== FALSE) {
                  $insertedDate = DateTime::createFromFormat($fordate, $splitValue)->format('Y-m-d');
                  $year = DateTime::createFromFormat($fordate, $splitValue)->format('Y');
                  if($year == "2020" || $year == "2021" || $year == "2022") {
                    break 2;
                  }
                  else {
                    $insertedDate = $value;
                  }
                  break;
              }
              else {
                $insertedDate = $value;
              }
            }
          }
          
        }
        else {
            $insertedDate = $value;
        }
    }

  return $insertedDate;
}

if(count($feedAll) > 0) {
  // $_SESSION['bigCron'] == "Valid";
    
  foreach ($feedAll as $value) {

    $changeStatus = $crud->changeStatus($value['id'], "Progressing");

    $preCount = $value['totalcount'];
    $preRepeat = $value['repeats'];
    if(empty($preCount)) {
      $preRepeat = 0;
    }
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

    $realHandleUrl = $value['url'];
    if (strpos($realHandleUrl, '.zip') !== false || strpos($realHandleUrl, '.gz') !== false) {
      $isReady = $crud->getIsReady($realHandleUrl);
      if($isReady) {
        $realHandleUrl = "https://convertedfeed.s3.us-east-2.amazonaws.com/zipgz/".$isReady['name'].'.xml';
      }
      if($realHandleUrl == $value['url']) {
        continue;
      }
    }

    // $handle = fopen('compress.zlib://'.$realHandleUrl, 'r');
    $handle = fopen($realHandleUrl, 'r');
    $xml_source = '';
    $record = false;

    if($handle){
      $key = 0;

      $saveName = str_replace(" ", "_", strtolower($value['name'])).".xml";
      $s3xml = S3XML.$saveName;
      $s3key = $saveName;
      $saveName = XMLDIR.$saveName;

      //remove if the file is exist in server
      if (file_exists($saveName)) {
        $deleted = unlink($saveName);
      }

      //remove if the file is exist in s3
      // unlink($s3xml);

      $xmlWriter = new XMLWriter();
      $xmlWriter->openMemory();
      $xmlWriter->startDocument('1.0', 'UTF-8');
      $xmlWriter->setIndent(TRUE);
      $xmlWriter->startElement('bebee');

    while(($buffer = fgets($handle, 4096)) !== false){

        if(strpos($buffer, '<job>') > -1){
            $xml_source = '<?xml version="1.0" encoding="UTF-8"?>';
            $record = true;
        }
        if(strpos($buffer, '</job>') > -1){
            $xml_source .= $buffer;
            $record = false;
            $node = simplexml_load_string(stripInvalidXml($xml_source));
            if(!empty($node)) {
            
                $xmlWriter->startElement('item');
                
                // this is for normal xml handling
                if($value['cronid'] != "4") {
                  for($i = 0; $i < count($updatetagpiece) - 1; $i++) {

                    if($updatetagpiece[$i] != "discard") {
                      if($updatetagpiece[$i] != "Default") {
                        $updatetagReal = $updatetagpiece[$i];
                      }
                      else {
                        $updatetagReal = $basetagpiece[$i];
                      }

                      if(isset($node->{$basetagpiece[$i]})) {
                        $xmlString = $node->{$basetagpiece[$i]};
                        if(in_array($basetagpiece[$i], $cdatatagpiece)) {
                          $xmlWriter->startElement($updatetagReal);
                          if($updatetagReal == "datePosted") {
                            $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                            $xmlWriter->writeCdata(htmlspecialchars($insertedDate));
                          }
                          elseif($updatetagReal == "url") {
                            // UTM adding here
                            if(!empty($value['utm'])) {
                              if (strpos($xmlString->__toString(), '?') !== false) {
                                $xmlWriter->writeCdata(strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm']);
                              }
                              else {
                                $xmlWriter->writeCdata($xmlString->__toString()."?".$value['utm']);
                              }
                            }                        
                            else {
                              $xmlWriter->writeCdata($xmlString->__toString());
                            }
                          }
                          else {
                            $xmlWriter->writeCdata(htmlspecialchars($xmlString->__toString()));
                          }
                          $xmlWriter->endElement();
                        }
                        else {
                          if($updatetagReal == "datePosted") {
                            $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                            $xmlWriter->writeElement($updatetagReal, htmlspecialchars($insertedDate));
                          }
                          elseif($updatetagReal == "url") {
                            // UTM adding here
                            if(!empty($value['utm'])) {
                              if (strpos($xmlString->__toString(), '?') !== false) {
                                $xmlWriter->writeElement($updatetagReal, strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm']);
                              }
                              else {
                                $xmlWriter->writeElement($updatetagReal, $xmlString->__toString()."?".$value['utm']);
                              }
                            }                        
                            else {
                              $xmlWriter->writeElement($updatetagReal, $xmlString->__toString());
                            }
                          }
                          else {
                            $xmlWriter->writeElement($updatetagReal, htmlspecialchars($xmlString->__toString()));
                          }

                        }
                      }                 

                    }
                  }
                }


                if($value['cronid'] == "4") {
                  for($i = 0; $i < count($updatetagpiece) - 1; $i++) {

                    if($updatetagpiece[$i] != "discard") {
                      // If update tag
                      if($updatetagpiece[$i] != "Default") {
                        $updatetagReal = $updatetagpiece[$i];
                      }
                      else {
                        if (strpos($basetagpiece[$i], ':') !== false) {
                          $updatetagRealKey = explode(":", $basetagpiece[$i]);
                          $updatetagReal = end($updatetagRealKey);
                        }
                        else {
                          $updatetagReal = $basetagpiece[$i];
                        }
                      }

                      // if contains child tag
                      if (strpos($basetagpiece[$i], ':') !== false) {
                        $minitag = explode(":", $basetagpiece[$i]);
                        $xmlString = $node;
                        foreach($minitag as $rkey) {
                          if(isset($xmlString -> {$rkey})) {
                            $xmlString = $xmlString -> {$rkey};
                          }
                        }
                        // if value is not exist then empty value
                        if(!empty($xmlString)) {
                          $insertedDate = "";
                        }
                        // handle of dateposted tag
                        if($updatetagReal == "datePosted") {
                          $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                        }
                        //utm handle
                        elseif($updatetagReal == "url")  {
                          if(!empty($value['utm'])) {
                            if (strpos($xmlString->__toString(), '?') !== false) {
                              $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm'];
                            }
                            else {
                              $insertedDate = $xmlString->__toString()."?".$value['utm'];
                            }
                          }                        
                          else {
                            $insertedDate = $xmlString->__toString();
                          }
                        }
                        else {
                          $insertedDate = $xmlString->__toString();
                        }

                        if(in_array($basetagpiece[$i], $cdatatagpiece)) {
                          if($updatetagReal != "url") {
                            $insertedDate = htmlspecialchars($insertedDate);
                          }
                          $xmlWriter->startElement($updatetagReal);
                          $xmlWriter->writeCdata($insertedDate);
                          $xmlWriter->endElement();
                        }
                        else {
                          if($updatetagReal != "url") {
                            $insertedDate = htmlspecialchars($insertedDate);
                          }
                          $xmlWriter->writeElement($updatetagReal, $insertedDate);
                        }
                      }
                      // if not contain child tag
                      else {
                        if(isset($node->{$basetagpiece[$i]})) {
                          $xmlString = $node->{$basetagpiece[$i]};
                          // handle of dateposted tag
                          if($updatetagReal == "datePosted") {
                            $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                          }
                          //utm handle
                          elseif($updatetagReal == "url")  {
                            if(!empty($value['utm'])) {
                              if (strpos($xmlString->__toString(), '?') !== false) {
                                $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm'])."&".$value['utm'];
                              }
                              else {
                                $insertedDate = $xmlString->__toString()."?".$value['utm'];
                              }
                            }                        
                            else {
                              $insertedDate = $xmlString->__toString();
                            }
                          }
                          else {
                            $insertedDate = $xmlString->__toString();
                          }

                          if(in_array($basetagpiece[$i], $cdatatagpiece)) {
                            if($updatetagReal != "url") {
                              $insertedDate = htmlspecialchars($insertedDate);
                            }
                            $xmlWriter->startElement($updatetagReal);
                            $xmlWriter->writeCdata($insertedDate);
                            $xmlWriter->endElement();
                          }
                          else {
                            if($updatetagReal != "url") {
                              $insertedDate = htmlspecialchars($insertedDate);
                            }
                            $xmlWriter->writeElement($updatetagReal, $insertedDate);
                          }
                        }
                        
                      }

                    }
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

            $key ++ ;
            if (0 == $key%1000) {
                file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);
            }
            // ... do stuff here with the xml element
        }
        if($record){
            $xml_source .= $buffer;
        }
    }

    $xmlWriter->endElement();
    file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);

    // file upload to s3 and update file name
      $uploader = new MultipartUploader($s3, $saveName, [
          'bucket' => "converted.bebee.com",
          'key'    => $s3key,
          'ACL'    => 'public-read'
      ]);

      // $keyExists = file_exists($s3xmlOld);
      // if ($keyExists) {
      //     rename($s3xmlOld, $s3xmlAlt);
      // }
      // sleep(2);
      // rename($s3xml, $s3xmlOld);
      // sleep(2);

      // unlink($s3xml);
      // unlink($s3xmlAlt);

      // Perform the upload.
      try {
          $result = $uploader->upload();
          echo "Upload complete: {$result['ObjectURL']}" . PHP_EOL;
      } catch (S3MultipartUploadException $e) {
          echo $e->getMessage() . PHP_EOL;
      }

      // Unlink the file in the server

      if (file_exists($saveName)) {
        $deleted = unlink($saveName);
      }

      // Setting repeat configuration
      if($key != $preCount) {
        $preRepeat = $preRepeat - 1;
      }
      else {
        $preRepeat = $preRepeat + 1;
      }

      if($key == 0) {
        $preRepeat = 3;
      }

      if($preRepeat > 3) {
        $preRepeat = 3;
      }
      elseif($preRepeat < 1) {
        $preRepeat = 1;
      }
      else {
        $preRepeat = $preRepeat;
      }

      $changeStatus = $crud->changeStatusFinal($value['id'], "Ready", $key, $preRepeat);
  }
  echo "success";
  // unset($_SESSION['bigCron']);
  $cronStart = $crud->cronStatus("1000", "Finished");
  }
}
