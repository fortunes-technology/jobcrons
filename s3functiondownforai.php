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

if(isset($argv[1])) {
  $order = $argv[1]; //for feedid=$argv[1] and status = 'Checking' or status = 'Ready'
}
else {
  $order = 0 ; 
}

if($order != 0) {
  $can = $crud->getRunningAICheckingReady($order);
  if($can) {
    $feedAll = $crud->getAllAI($order);
  }
  else {
    $feedAll = [];
  }
}
else {
  $feedAll = [];
}

// Remove specific value from url
function strip_param_from_url($url, $params)
{
  $paramArray = [];
  $params = explode("&", $params);
  foreach ($params as $key => $value) {
    $paramArray[] = explode("=", $value)[0];
  }
  foreach ($paramArray as $key => $value) {
    $base_url = strtok($url, '?');              // Get the base url
    $parsed_url = parse_url($url);              // Parse it 
    $query = $parsed_url['query'];              // Get the query string
    parse_str($query, $parameters);           // Convert Parameters into array
    unset($parameters[$value]);               // Delete the one you want
    $new_query = http_build_query($parameters); // Rebuilt query string
    $url = $base_url . '?' . $new_query;
  }
  return $base_url . '?' . $new_query;            // Finally url is ready
}

function get_key_from_node($node, $baseTagCurrent, $cdataTagCurrent)
{
  foreach ($node as $minikey => $child) {
    $status = false;
    foreach ($child as $miniChild) {
      $tagName = $child->getName();
      if (!empty($miniChild->getName())) {

        $childStatus = false;
        foreach ($miniChild as $miminiChild) {
          if (!empty($miminiChild->getName())) {
            $addTagName = $tagName . ":" . $miniChild->getName() . ":" . $miminiChild->getName();
            if (!in_array($addTagName, $baseTagCurrent)) {
              $baseTagCurrent[] = $addTagName;
            }
            if (strpos($miminiChild->__toString(), 'cdata') !== false) {
              if (!in_array($addTagName, $cdataTagCurrent)) {
                $cdataTagCurrent[] = $addTagName;
              }
            } else {
              $baseValue[] = htmlspecialchars($miminiChild->__toString());
            }
            // echo $miminiChild->__toString(); exit;
          }
        }
        if ($childStatus) {
          continue;
        }

        $status = true;
        $addTagName = $tagName . ":" . $miniChild->getName();
        if (!in_array($addTagName, $baseTagCurrent)) {
          $baseTagCurrent[] = $addTagName;
        }
        if (strpos($miniChild->__toString(), 'cdata') !== false) {
          if (!in_array($addTagName, $cdataTagCurrent)) {
            $cdataTagCurrent[] = $addTagName;
          }
        } else {
        }
      }
    }

    if ($status) {
      $is_child = "4";
      continue;
    }

    $tagName = $child->getName();
    if (!in_array($tagName, $baseTagCurrent)) {
      $baseTagCurrent[] = $tagName;
    }
    if (strpos($child->__toString(), 'cdata') !== false) {
      if (!in_array($tagName, $cdataTagCurrent)) {
        $cdataTagCurrent[] = $tagName;
      }
    } else {
    }
  }
  return array("baseTagCurrent" => $baseTagCurrent, "cdataTagCurrent" => $cdataTagCurrent);
}

function dateHandle($value)
{
  $dateFormatArray = array(
    "Y:m:d", "d:m:Y", "Y/m/d", "d/m/Y", "Y.m.d", "d.m.Y"
  );
  if (!empty($strtotime = strtotime($value))) {
    $year = date('Y', $strtotime);
    if ($year == "2020" || $year == "2021" || $year == "2022") {
      $insertedDate = date('Y-m-d', $strtotime);
    } else {
      foreach ($dateFormatArray as $fordate) {
        if (DateTime::createFromFormat($fordate, $value) !== FALSE) {
          $insertedDate = DateTime::createFromFormat($fordate, $value)->format('Y-m-d');
          break;
        } else {
          $insertedDate = $value;
        }
      }
    }
  } else {
    $arraySplit = explode(" ", $value);
    if (count($arraySplit) > 0) {

      foreach ($arraySplit as $splitValue) {
        foreach ($dateFormatArray as $fordate) {
          if (DateTime::createFromFormat($fordate, $splitValue) !== FALSE) {
            $insertedDate = DateTime::createFromFormat($fordate, $splitValue)->format('Y-m-d');
            $year = DateTime::createFromFormat($fordate, $splitValue)->format('Y');
            if ($year == "2020" || $year == "2021" || $year == "2022") {
              break 2;
            } else {
              $insertedDate = $value;
            }
            break;
          } else {
            $insertedDate = $value;
          }
        }
      }
    } else {
      $insertedDate = $value;
    }
  }

  return $insertedDate;
}

function getChatGptContent($input)
{

  $curl = curl_init();
  $endpoint = "https://api.openai.com/v1/completions";
  $params = array(
    'model' => 'text-curie-001',
    'prompt' => $input,
    'temperature' => 0,
    'max_tokens' => 300,
  );

  curl_setopt_array($curl, array(
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Authorization: Bearer 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'
    ),
  ));

  $response = curl_exec($curl);

  if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    echo 'Error: ' . $error_msg;
  }

  curl_close($curl);

  $result = json_decode($response, true);
  $midresult = $result['choices'] ?? '';
  $output = $midresult['0']['text'] ?? '';

  return $output;
}

if (count($feedAll) > 0) {
  // $_SESSION['bigCron'] == "Valid";

  foreach ($feedAll as $value) {

    $changeStatus = $crud->changeRunningAIStatus($value['id'], "Progressing");
    $industryFlag = 0;
    $estimatedSalaryFlag = 0;
    $specialCaseFlag = 0;

    $titleForAI = '';
    $preCount = $value['totalcount'];
    $preRepeat = $value['repeats'];
    if (empty($preCount)) {
      $preRepeat = 0;
    }
    $cdatatagpiece = [];
    $baseTagCurrent = [];
    $cdataTagCurrent = [];
    $updatetag = $value['updatetag'];
    $basetag = $value['basetag'];
    $updatetagpiece = explode(",", $updatetag);
    $basetagpiece = explode(",", $basetag);
    array_pop($basetagpiece);
    $defaultcountry = $value['defaultcountry'];
    $industry = $value['industry'];
    $company = $value['company'];
    $joblocationtype = $value['joblocationtype'];
    if ($value['cdatatag'] != "") {
      $cdatatag = $value['cdatatag'];
      $cdatatagpiece = explode(",", $value['cdatatag']);
    } else {
      $cdatatag = "";
      $cdatatagpiece = [];
    }
    if (strpos($updatetag, 'industry') !== false) {
      $industryFlag = 1;
    }
    if (strpos($updatetag, 'estimatedSalary') !== false) {
      $estimatedSalaryFlag = 1;
    }
    $reader1 = new XMLReader();

    $realHandleUrl = $value['url'];
    if ($value['url'] == "https://gateway.harri.com/dispatcher/api/v2/brands/914618/jobs/feed") {
      $realHandleUrl = "/var/www/html/cf/xmldir/file.xml";
    }
    if ($value['url'] == "https://files.channable.com/ZKWkKXye0GkHX8R0rM_xYw==.xml") {
      $realHandleUrl = "/var/www/html/cf/xmldir/file_channable.xml";
    }
    if($value['url'] == "https://xml.jobswipe.net/CLICKTH-DE/xmlfeed.xml") {
      $specialCaseFlag = 1;
    }
    if (strpos($realHandleUrl, '.zip') !== false || strpos($realHandleUrl, '.gz') !== false) {
      $isReady = $crud->getIsReady($realHandleUrl);
      if ($isReady) {
        $realHandleUrl = S3ZIP . $isReady['name'] . '.xml';
      }
    } else {
      $isReady = $crud->getIsReady($realHandleUrl);
      if ($isReady) {
        $realHandleUrl = S3ZIP . $isReady['name'] . '.xml';
      }
    }

    $saveName1 = str_replace(" ", "___", strtolower($value['name'])) . ".xml";
    $saveName1 = XMLDIR . $saveName1;
    $xml = file_get_contents($realHandleUrl);
    file_put_contents($saveName1, $xml);
    echo "file is saved";

    $reader2 = new XMLReader();
    if ($reader2->open($saveName1)) {

      $key = 0;

      $saveName2 = str_replace(" ", "_", strtolower($value['name'])) . "_rewritten.xml";
      // $saveNameOld = str_replace(" ", "_", strtolower($value['name'])).".xml";
      // $saveNameAlt = str_replace(" ", "_", strtolower($value['name']))."_2.xml";
      $s3xml = S3XML . $saveName2;
      // $s3xmlOld = S3XML.$saveNameOld;
      // $s3xmlAlt = S3XML.$saveNameAlt;
      $s3key = $saveName2;
      $saveName2 = XMLDIR . $saveName2;

      //remove if the file is exist in server
      if (file_exists($saveName2)) {
        $deleted2 = unlink($saveName2);
      }

      //remove if the file is exist in s3
      // unlink($s3xml);

      $xmlWriter = new XMLWriter();
      $xmlWriter->openMemory();
      $xmlWriter->startDocument('1.0', 'UTF-8');
      $xmlWriter->setIndent(TRUE);
      $xmlWriter->startElement('bebee');

      while ($reader2->read()) {

        if ($reader2->nodeType == XMLReader::ELEMENT) $nodeName = $reader2->name;

        if ($nodeName == "job" || $nodeName == "row" || $nodeName == "JOB" || $nodeName == "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job" || $nodeName == "post" || $nodeName == "Product" || ($specialCaseFlag == 1 && $nodeName == "Jobs")) {

          libxml_use_internal_errors(true);
          $readerForNodeForTag = str_replace("<![CDATA[", "<![CDATA[cdata", $reader2->readOuterXML());

          try {
            $readerForNode = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $reader2->readOuterXML());
            $node = new SimpleXMLElement($readerForNode);
            $nodeForTag = new SimpleXMLElement($readerForNodeForTag);
          } catch (Exception $e) {
            continue;
          }

          if (!empty($node)) {

            $processCurrent = get_key_from_node($nodeForTag, $baseTagCurrent, $cdataTagCurrent);
            $baseTagCurrent = $processCurrent['baseTagCurrent'];
            $cdataTagCurrent = $processCurrent['cdataTagCurrent'];

            $xmlWriter->startElement('item');

            // this is for normal xml handling
            if ($value['cronid'] != "4") {
              for ($i = 0; $i < count($updatetagpiece) - 1; $i++) {

                if ($updatetagpiece[$i] != "discard") {
                  if ($updatetagpiece[$i] != "Default") {
                    $updatetagReal = $updatetagpiece[$i];
                  } else {
                    $updatetagReal = $basetagpiece[$i];
                  }

                  if (isset($node->{$basetagpiece[$i]})) {
                    $xmlString = $node->{$basetagpiece[$i]};

                    // This is for CDATA case
                    if (in_array($basetagpiece[$i], $cdatatagpiece)) {
                      $xmlWriter->startElement($updatetagReal);
                      if ($updatetagReal == "datePosted") {
                        $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                        $xmlWriter->writeCdata(htmlspecialchars($insertedDate));
                      } elseif ($updatetagReal == "title") {
                        $insertedTitle = $xmlString->__toString();
                        $titleForAI = $insertedTitle;
                        $preinsertedTitle = " Rewrite this job title with similar words : " . $insertedTitle . " ";
                        $rewrittedTitle = getChatGptContent($preinsertedTitle);
                        $xmlWriter->writeCdata(htmlspecialchars($rewrittedTitle));
                      } elseif ($updatetagReal == "url") {
                        // UTM adding here
                        if (!empty($value['utm'])) {
                          if (strpos($xmlString->__toString(), '?') !== false) {
                            $xmlWriter->writeCdata(strip_param_from_url($xmlString->__toString(), $value['utm']) . "&" . $value['utm']);
                          } else {
                            $xmlWriter->writeCdata($xmlString->__toString() . "?" . $value['utm']);
                          }
                        } else {
                          // This is for indeed aws case, just for replace replaceme to 6544253382309580
                          if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                            $xmlWriter->writeCdata(str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString()));
                          } elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                            $xmlWriter->writeCdata(str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString()));
                          } else {
                            $xmlWriter->writeCdata($xmlString->__toString());
                          }
                        }
                      } elseif ($updatetagReal == "industry") {
                        $insertedIndustry = $xmlString->__toString();
                        $preinsertedIndustry = " According to linkedin's categorisation of jobs, tell me to which category this job belongs. Just the name of the category itself, without more text that the category name. Output has to be just the name of the category. Just put the text that is into the '', but without the '' :  " . $insertedIndustry . " ";
                        $rewrittedIndustry = getChatGptContent($preinsertedIndustry);
                        $xmlWriter->writeCdata(htmlspecialchars($rewrittedIndustry));
                      } elseif ($basetagpiece[$i] == 'description' || $updatetagReal == 'content') {
                        $insertedDescription = $xmlString->__toString();
                        $decordedDescription = htmlspecialchars_decode($insertedDescription);
                        $decordedDescription = strip_tags($decordedDescription);
                        if(str_word_count($decordedDescription) > 50){
                          $decordedDescription = implode(' ', array_slice(explode(' ', $decordedDescription), 0, 50));
                        }
                        echo $decordedDescription;
                        $preinsertedDescription = " Summarise the following job description in a much shorter job description but with a more commerical tone to make it more attractive to the candidate and adding the skills needed to apply for the position in the original language: \n" . $decordedDescription . "\n";
                        $rewrittedDescription = getChatGptContent($preinsertedDescription);
                        echo $rewrittedDescription;
                        $xmlWriter->writeCdata(htmlspecialchars($rewrittedDescription));
                      } elseif ($updatetagReal == "estimatedSalary") {
                        $preInsertSalary = " Give me just one range per the next job title. But only print price. Job title: " . $insertedTitle . " ";
                        $rewrittedSalary = getChatGptContent($preInsertSalary);
                        $xmlWriter->writeCdata(htmlspecialchars($rewrittedSalary));
                      } else {
                        $xmlWriter->writeCdata(htmlspecialchars($xmlString->__toString()));
                      }
                      $xmlWriter->endElement();
                    }

                    // This is not for CDATA case
                    else {

                      if ($updatetagReal == "datePosted") {
                        $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($insertedDate));
                      } elseif ($updatetagReal == "title") {
                        $insertedTitle = $xmlString->__toString();
                        $titleForAI = $insertedTitle;
                        $preinsertedTitle = " Rewrite this job title with similar words : " . $insertedTitle . " ";
                        $rewrittedTitle = getChatGptContent($preinsertedTitle);
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($rewrittedTitle));
                      } elseif ($updatetagReal == "url") {
                        // UTM adding here
                        if (!empty($value['utm'])) {
                          if (strpos($xmlString->__toString(), '?') !== false) {
                            $xmlWriter->writeElement($updatetagReal, strip_param_from_url($xmlString->__toString(), $value['utm']) . "&" . $value['utm']);
                          } else {
                            $xmlWriter->writeElement($updatetagReal, $xmlString->__toString() . "?" . $value['utm']);
                          }
                        } else {
                          // This is for indeed aws case, just for replace replaceme to 6544253382309580
                          if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                            $xmlWriter->writeElement($updatetagReal, str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString()));
                          } elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                            $xmlWriter->writeElement($updatetagReal, str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString()));
                          } else {
                            $xmlWriter->writeElement($updatetagReal, $xmlString->__toString());
                          }
                        }
                      } elseif ($updatetagReal == "industry") {
                        $insertedIndustry = $xmlString->__toString();
                        $preinsertedIndustry = " According to linkedin's categorisation of jobs, tell me to which category this job belongs. Just the name of the category itself, without more text that the category name. Output has to be just the name of the category. Just put the text that is into the '', but without the '' :  " . $insertedIndustry . " ";
                        $rewrittedIndustry = getChatGptContent($preinsertedIndustry);
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($rewrittedIndustry));
                      } elseif ($basetagpiece[$i] == 'description' || $updatetagReal == 'content') {
                        $insertedDescription = $xmlString->__toString();
                        $decordedDescription = htmlspecialchars_decode($insertedDescription);
                        $decordedDescription = strip_tags($decordedDescription);
                        if(str_word_count($decordedDescription) > 50){
                          $decordedDescription = implode(' ', array_slice(explode(' ', $decordedDescription), 0, 50));
                        }
                        echo $decordedDescription;
                        $preinsertedDescription = " Summarise the following job description in a much shorter job description but with a more commerical tone to make it more attractive to the candidate and adding the skills needed to apply for the position in the original language: \n" . $decordedDescription . "\n";
                        $rewrittedDescription = getChatGptContent($preinsertedDescription);
                        echo $rewrittedDescription;
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($rewrittedDescription));
                      } elseif ($updatetagReal == "estimatedSalary") {
                        $preInsertSalary = " Give me just one range per the next job title. But only print price. Job title: " . $insertedTitle . " ";
                        $rewrittedSalary = getChatGptContent($preInsertSalary);
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($rewrittedSalary));
                      } else {
                        $xmlWriter->writeElement($updatetagReal, htmlspecialchars($xmlString->__toString()));
                      }
                    }
                  }
                }
              }
            }


            if ($value['cronid'] == "4") {
              for ($i = 0; $i < count($updatetagpiece) - 1; $i++) {

                if ($updatetagpiece[$i] != "discard") {
                  // If update tag
                  if ($updatetagpiece[$i] != "Default") {
                    $updatetagReal = $updatetagpiece[$i];
                  } else {
                    if (strpos($basetagpiece[$i], ':') !== false) {
                      $updatetagRealKey = explode(":", $basetagpiece[$i]);
                      $updatetagReal = end($updatetagRealKey);
                    } else {
                      $updatetagReal = $basetagpiece[$i];
                    }
                  }

                  // if contains child tag
                  if (strpos($basetagpiece[$i], ':') !== false) {
                    $minitag = explode(":", $basetagpiece[$i]);
                    $xmlString = $node;
                    foreach ($minitag as $rkey) {
                      if (isset($xmlString->{$rkey})) {
                        $xmlString = $xmlString->{$rkey};
                      }
                    }
                    // if value is not exist then empty value
                    if (!empty($xmlString)) {
                      $insertedDate = "";
                    }
                    // handle of dateposted tag
                    if ($updatetagReal == "datePosted") {
                      $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                    }
                    //utm handle
                    elseif ($updatetagReal == "url") {
                      if (!empty($value['utm'])) {
                        if (strpos($xmlString->__toString(), '?') !== false) {
                          $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm']) . "&" . $value['utm'];
                        } else {
                          $insertedDate = $xmlString->__toString() . "?" . $value['utm'];
                        }
                      } else {
                        // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                        if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                          $insertedDate = str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString());
                        } elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                          $insertedDate = str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString());
                        } else {
                          $insertedDate = $xmlString->__toString();
                        }
                      }
                    } elseif ($updatetagReal == "title") {
                      $insertedDate = $xmlString->__toString();
                      $insertedTitle = $insertedDate;
                      $preinsertedTitle = " Rewrite this job title with similar words : " . $insertedDate . " ";
                      $insertedDate = getChatGptContent($preinsertedTitle);
                    } elseif ($updatetagReal == "industry") {
                      $insertedDate = $xmlString->__toString();
                      $preinsertedIndustry = " According to linkedin's categorisation of jobs, tell me to which category this job belongs. Just the name of the category itself, without more text that the category name. Output has to be just the name of the category. Just put the text that is into the '', but without the '' :  " . $insertedDate . " ";
                      $insertedDate = getChatGptContent($preinsertedIndustry);
                    } elseif ($updatetagReal == 'description' || $updatetagReal == 'content') {
                      $insertedDate = $xmlString->__toString();
                      $decordedDescription = htmlspecialchars_decode($insertedDate);
                      $decordedDescription = strip_tags($decordedDescription);
                      if(str_word_count($decordedDescription) > 50){
                        $decordedDescription = implode(' ', array_slice(explode(' ', $decordedDescription), 0, 50));
                      }
                      $preinsertedDescription = " Summarise the following job description in a much shorter job description but with a more commerical tone to make it more attractive to the candidate and adding the skills needed to apply for the position in the original language: \n" . $decordedDescription . "\n";
                      $insertedDate = getChatGptContent($preinsertedDescription);
                    } elseif ($updatetagReal == "estimatedSalary") {
                      $preInsertSalary = " Give me just one range per the next job title. But only print price. Job title: " . $insertedTitle . " ";
                      $insertedDate = getChatGptContent($preInsertSalary);
                    } else {
                      $insertedDate = $xmlString->__toString();
                    }

                    if (in_array($basetagpiece[$i], $cdatatagpiece)) {
                      if ($updatetagReal != "url") {
                        $insertedDate = htmlspecialchars($insertedDate);
                      }
                      $xmlWriter->startElement($updatetagReal);
                      $xmlWriter->writeCdata($insertedDate);
                      $xmlWriter->endElement();
                    } else {
                      if ($updatetagReal != "url") {
                        $insertedDate = htmlspecialchars($insertedDate);
                      }
                      $xmlWriter->writeElement($updatetagReal, $insertedDate);
                    }
                  }
                  // if not contain child tag
                  else {
                    if (isset($node->{$basetagpiece[$i]})) {
                      $xmlString = $node->{$basetagpiece[$i]};
                      // handle of dateposted tag
                      if ($updatetagReal == "datePosted") {
                        $insertedDate = dateHandle(htmlspecialchars($xmlString->__toString()));
                      }
                      //utm handle
                      elseif ($updatetagReal == "url") {
                        if (!empty($value['utm'])) {
                          if (strpos($xmlString->__toString(), '?') !== false) {
                            $insertedDate = strip_param_from_url($xmlString->__toString(), $value['utm']) . "&" . $value['utm'];
                          } else {
                            $insertedDate = $xmlString->__toString() . "?" . $value['utm'];
                          }
                        } else {
                          // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                          if (strpos($xmlString->__toString(), 'pubnum=REPLACEME') !== false) {
                            $insertedDate = str_replace("pubnum=REPLACEME", "pubnum=6544253382309580", $xmlString->__toString());
                          } elseif (strpos($xmlString->__toString(), '/REPLACEME') !== false) {
                            $insertedDate = str_replace("/REPLACEME", "/6544253382309580", $xmlString->__toString());
                          } else {
                            $insertedDate = $xmlString->__toString();
                          }
                        }
                      } elseif ($updatetagReal == "title") {
                        $insertedDate = $xmlString->__toString();
                        $insertedTitle = $insertedDate;
                        $preinsertedTitle = " Rewrite this job title with similar words : " . $insertedDate . " ";
                        $insertedDate = getChatGptContent($preinsertedTitle);
                      } elseif ($updatetagReal == "industry") {
                        $insertedDate = $xmlString->__toString();
                        $preinsertedIndustry = " According to linkedin's categorisation of jobs, tell me to which category this job belongs. Just the name of the category itself, without more text that the category name. Output has to be just the name of the category. Just put the text that is into the '', but without the '' :  " . $insertedDate . " ";
                        $insertedDate = getChatGptContent($preinsertedIndustry);
                      } elseif ($updatetagReal == 'description' || $updatetagReal == 'content') {
                        $insertedDate = $xmlString->__toString();
                        $decordedDescription = htmlspecialchars_decode($insertedDate);
                        $decordedDescription = strip_tags($decordedDescription);
                        if(str_word_count($decordedDescription) > 50){
                          $decordedDescription = implode(' ', array_slice(explode(' ', $decordedDescription), 0, 50));
                        }
                        $preinsertedDescription = " Summarise the following job description in a much shorter job description but with a more commerical tone to make it more attractive to the candidate and adding the skills needed to apply for the position in the original language: \n" . $decordedDescription . "\n";
                        $insertedDate = getChatGptContent($preinsertedDescription);
                      } elseif ($updatetagReal == "estimatedSalary") {
                        $preInsertSalary = " Give me just one range per the next job title. But only print price. Job title: " . $insertedTitle . " ";
                        $insertedDate = getChatGptContent($preInsertSalary);
                      } else {
                        // this is for special case. https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
                        $insertedDate = $xmlString->__toString();
                      }

                      if (in_array($basetagpiece[$i], $cdatatagpiece)) {
                        if ($updatetagReal != "url") {
                          $insertedDate = htmlspecialchars($insertedDate);
                        }
                        $xmlWriter->startElement($updatetagReal);
                        $xmlWriter->writeCdata($insertedDate);
                        $xmlWriter->endElement();
                      } else {
                        if ($updatetagReal != "url") {
                          $insertedDate = htmlspecialchars($insertedDate);
                        }
                        $xmlWriter->writeElement($updatetagReal, $insertedDate);
                      }
                    }
                  }
                }
              }
            }

            if ($industryFlag == 0) {
              $preinsertedIndustry = " According to linkedin's categorisation of jobs, tell me to which category this job belongs. Just the name of the category itself, without more text that the category name. Output has to be just the name of the category. Just put the text that is into the '', but without the '' :  " . $titleForAI . " ";
              $rewrittedIndustry = getChatGptContent($preinsertedIndustry);
              $xmlWriter->writeElement("industry", htmlspecialchars($rewrittedIndustry));
            }

            if ($estimatedSalaryFlag == 0) {
              $preInsertSalary = " Give me just one range per the next job title. But only print price. Job title: " . $titleForAI . " ";
              $rewrittedSalary = getChatGptContent($preInsertSalary);
              $xmlWriter->writeElement("estimatedSalary", htmlspecialchars($rewrittedSalary));
            }

            if (!empty($defaultcountry)) {
              $xmlWriter->writeElement("addressCountry", $defaultcountry);
            }
            if (!empty($industry)) {
              $xmlWriter->writeElement("industry", $industry);
            }
            if (!empty($company)) {
              $xmlWriter->writeElement("company", $company);
            }
            if (!empty($joblocationtype)) {
              $xmlWriter->writeElement("jobLocationType", $joblocationtype);
            }
            $xmlWriter->endElement();
          }

          $key++;
        }

        if (0 == $key % 1000) {
          file_put_contents($saveName2, $xmlWriter->flush(true), FILE_APPEND);
        }

        // if( $key == 8 ) break;
        // echo '#';
        if( $key > 30000 ) break;

      }
      $xmlWriter->endElement();
      file_put_contents($saveName2, $xmlWriter->flush(true), FILE_APPEND);

      // file upload to s3 and update file name
      $uploader = new MultipartUploader($s3, $saveName2, [
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

      if (file_exists($saveName2)) {
        $deleted1 = unlink($saveName2);
      }

      $tagChanged = false;
      array_pop($updatetagpiece);

      if (!empty($baseTagCurrent)) {
        // This is the disappeard key
        $baseArrayNone = array_diff($basetagpiece, $baseTagCurrent);
        if (!empty($baseArrayNone)) {
          $updatetag = "";
          $tagChanged = true;
          foreach ($baseArrayNone as $tagValue) {
            $cdatatag .= str_replace($tagValue . ",", "", $cdatatag);
            $basetag = str_replace($tagValue . ",", "", $basetag);
            $basekey = array_search($tagValue, $basetagpiece);
            if (isset($updatetagpiece[$basekey])) {
              unset($updatetagpiece[$basekey]);
            }
          }
          foreach ($updatetagpiece as $tagValue) {
            $updatetag .= $tagValue . ",";
          }
        }

        // This is the new key
        $baseArrayNew = array_diff($baseTagCurrent, $basetagpiece);
        if (!empty($baseArrayNew)) {
          $tagChanged = true;
          foreach ($baseArrayNew as $tagValue) {
            if (in_array($tagValue, $cdataTagCurrent)) {
              $cdatatag .= $tagValue . ",";
            }
            $basetag .= $tagValue . ",";
            $updatetag .= "Default,";
          }
        }
      }
    }

    $reader2->close();
    if (file_exists($saveName1)) {
      $deleted2 = unlink($saveName1);
    }

    $changeStatus = $crud->changeRunningAIStatus($value['id'], "Ready");
    echo $value['url'];
    echo "<br>";
  }
  echo "success";
}
