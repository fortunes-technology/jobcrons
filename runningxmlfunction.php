<?php
include_once 'dbconfigcron.php';
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3MultipartUploadException;

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

// Update or add specific value from url
function update_param_from_url( $url, $params ) {
  $paramArray = [];
  parse_str($params, $parsed_params);
  $params = explode("&", $params);
  foreach ($params as $key => $value) {
    $paramArray[] = explode("=", $value)[0];
  }
  foreach ($paramArray as $key => $value) {
    $base_url = strtok($url, '?');              // Get the base url
    $parsed_url = parse_url($url);              // Parse it 
    $query = $parsed_url['query'];              // Get the query string
    parse_str( $query, $parameters );           // Convert Parameters into array
    $parameters[$value] = $parsed_params[$value];
    $new_query = http_build_query($parameters); // Rebuilt query string
    $url = $base_url.'?'.$new_query;
  }  
  return $base_url.'?'.$new_query;            // Finally url is ready
}

$getRunning = $crud->getRunningChecking();

if(empty($_SESSION['bigCron'])) {
  if(count($getRunning) > 0) {

    $makingProgress = $crud->setCheckToProgress($getRunning);
  
    if($makingProgress) {
      $feedAll = $crud->getRunningItem($getRunning);
    
      if(count($feedAll) > 0) {
          
        foreach ($feedAll as $value) {
      
          $changeStatus = $crud->changeStatus($value['id'], "Progressing");
      
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
          if($value['url'] == "https://gateway.harri.com/dispatcher/api/v2/brands/914618/jobs/feed") {
            $realHandleUrl = "/var/www/html/cf/xmldir/file.xml";
          }
          if($value['url'] == "https://files.channable.com/ZKWkKXye0GkHX8R0rM_xYw==.xml") {
            $realHandleUrl = "/var/www/html/cf/xmldir/file_channable.xml";
          }
          if (strpos($realHandleUrl, '.zip') !== false || strpos($realHandleUrl, '.gz') !== false) {
            $isReady = $crud->getIsReady($realHandleUrl);
            if($isReady) {
              $realHandleUrl = S3ZIP.$isReady['name'].'.xml';
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
            $s3xml = S3XML.$saveName;
            $s3key = $saveName;
            $saveName = XMLDIR.$saveName;
      
            //remove if the file is exist in server
            if (file_exists($saveName)) {
              $deleted = unlink($saveName);
            }
      
            //remove if the file is exist in s3
            unlink($s3xml);
      
            $xmlWriter = new XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->setIndent(TRUE);
            $xmlWriter->startElement('bebee');
      
            while($reader->read()) {
      
              if($reader->nodeType == XMLReader::ELEMENT) $nodeName = $reader->name;
        
              if($nodeName == "job" || $nodeName == "row" || $nodeName == "JOB" || $nodeName == "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job" || $nodeName == "post" || $nodeName == "Product") {
        
                libxml_use_internal_errors(true);
                
                try{
                  $readerForNode = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $reader->readOuterXML()); 
                  $node = new SimpleXMLElement($readerForNode);
                } catch (Exception $e){
                  continue;
                }
        
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
                                  $xmlWriter->writeCdata(update_param_from_url($xmlString->__toString(), $value['utm']));
                                }
                                else {
                                  $xmlWriter->writeCdata($xmlString->__toString()."?".$value['utm']);
                                }
                              }                        
                              else {
                                // This is for indeed aws case, just for replace replaceme to 6544253382309580
                                if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                                  $xmlWriter->writeCdata(str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString()));  
                                }
                                elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                                  $xmlWriter->writeCdata(str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString()));  
                                }
                                else {
                                  $xmlWriter->writeCdata($xmlString->__toString());
                                }
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
                                  $xmlWriter->writeElement($updatetagReal, update_param_from_url($xmlString->__toString(), $value['utm']));
                                }
                                else {
                                  $xmlWriter->writeElement($updatetagReal, $xmlString->__toString()."?".$value['utm']);
                                }
                              }                        
                              else {
                                // This is for indeed aws case, just for replace replaceme to 6544253382309580
                                if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                                  $xmlWriter->writeElement($updatetagReal, str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString()));
                                }
                                elseif(strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                                  $xmlWriter->writeElement($updatetagReal, str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString()));
                                }
                                else {
                                  $xmlWriter->writeElement($updatetagReal, $xmlString->__toString());
                                }
                              }
                            }
                            else {
                              // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                              if($basetagpiece[$i] == 'description') {
                                $xmlWriter->writeElement($updatetagReal, htmlspecialchars($xmlString->asXML()));
                              }
                              else {
                                $xmlWriter->writeElement($updatetagReal, htmlspecialchars($xmlString->__toString()));
                              }
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
                                $insertedDate = update_param_from_url($xmlString->__toString(), $value['utm']);
                              }
                              else {
                                $insertedDate = $xmlString->__toString()."?".$value['utm'];
                              }
                            }                        
                            else {
                              // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                              if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                                $insertedDate = str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString());
                              }
                              elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                                $insertedDate = str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString());
                              }
                              else {
                                $insertedDate = $xmlString->__toString();
                              }
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
                                  $insertedDate = update_param_from_url($xmlString->__toString(), $value['utm']);
                                }
                                else {
                                  $insertedDate = $xmlString->__toString()."?".$value['utm'];
                                }
                              }                        
                              else {
                                // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                                if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                                  $insertedDate = str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString());
                                }
                                elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                                  $insertedDate = str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString());
                                }
                                else {
                                  $insertedDate = $xmlString->__toString();
                                }
                              }
                            }
                            else {
                              // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                              if($basetagpiece[$i] == 'description') {
                                $insertedDate = $xmlString->asXML();
                              }
                              else {
                                $insertedDate = $xmlString->__toString();
                              }
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
                
              }
      
              $key ++ ;
              if (0 == $key%1000) {
                  file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);
              }
      
            }
            $xmlWriter->endElement();
            file_put_contents($saveName, $xmlWriter->flush(true), FILE_APPEND);
      
            // file upload to s3
            $uploader = new MultipartUploader($s3, $saveName, [
                'bucket' => "converted.bebee.com",
                'key'    => $s3key,
                'ACL'    => 'public-read'
            ]);
      
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
            $deleteResult = $crud->deleteChecking($value['id']);
            $changeStatus = $crud->changeStatus($value['id'], "Ready");
          }
      
          else {
            $changeStatus = $crud->changeStatus($value['id'], "Error");
          }
        }    
        echo "success";
      }
    }
  }
}


?>