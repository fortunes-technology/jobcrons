<?php

include_once('dbconfig.php');
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3MultipartUploadException;

// error_reporting(E_ALL);

// Set s3 configuration
$s3 = new S3Client([
	'version' => 'latest',
	'region'  => 'us-east-2',
	'credentials' => [
		'key' => 'AKIAQLRVICZQM2MQJHZM',
		'secret' => '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',
	]
]);
$s3->registerStreamWrapper();

// Parse XML file
if (isset($_POST['parse']) && isset($_POST['url'])) {
	$specialCaseFlag = 0;
	$url_real = $_POST['url'];
	if ($_POST['url'] == "https://gateway.harri.com/dispatcher/api/v2/brands/914618/jobs/feed") {
		$url_real = "https://converter.bebee.com/cf/xmldir/file.xml";
	}
	if ($_POST['url'] == "https://gateway.harri.com/dispatcher/api/v2/brands/914618/jobs/feed") {
		$url_real = "https://converter.bebee.com/cf/xmldir/file_channable.xml";
	}
	if (
		$_POST['url'] == "https://xml.jobswipe.net/CLICKTH-DE/xmlfeed.xml"
		|| $_POST['url'] == "http://xml.jobswipe.net/CLICKTH-GB-PRE/xmlfeed.xml"
		|| $_POST['url'] == "http://xml.jobswipe.net/CLICKTH-US/xmlfeed.xml"
	) {
		$specialCaseFlag = 1;
	}
	$is_child = "2";

	// try
	// {
	// 	$h = fopen($url_real, 'r');
	// 	$first5000Bytes = fread($h, 50000);
	// 	fclose($h);
	// }
	// catch(\Exception $e)
	// {
	// 	echo "false"; exit();
	// }
	// var_dump($first5000Bytes); exit;

	$reader = new XMLReader();
	$is_exist = 0;

	if (strpos($url_real, '.zip') !== false || strpos($url_real, '.gz') !== false) {
		$isReady = $crud->getIsReady($url_real);
		if ($isReady) {
			$is_exist = 1;
			$url_real = S3ZIP . $isReady['name'] . '.xml';
		} else {
			$res = json_encode(['data' => "false"]);
			echo $res;
			exit();
		}
	}

	if ($is_exist == 0) {
		// This is for gz/zip special case. so the url doesn't contain gz extension
		$headers = get_headers($url_real);
		// Check if the headers are empty.
		if (empty($headers)) {
			$res = json_encode(['data' => "error"]);
			echo $res;
			exit();
		}
		// Use a regex to see if the response code is 200.
		preg_match('/\b404\b/', $headers[0], $matches);

		if (!empty($matches)) {
			$res = json_encode(['data' => "error"]);
			echo $res;
			exit();
		} else {
			$is_download = 0;
			foreach ($headers as $h_key => $h_value) {
				if (strpos(str_replace(" ", "", $h_value), 'gzip') !== false || strpos(str_replace(" ", "", $h_value), 'gz') !== false) {
					$is_download = 1;
				}
			}
			if ($is_download == 1) {
				$isReady = $crud->getIsReady($url_real);
				if ($isReady) {
					$url_real = S3ZIP . $isReady['name'] . '.xml';
				} else {
					$res = json_encode(['data' => "false"]);
					echo $res;
					exit();
				}
			}
		}
	}

	if ($reader->open($url_real)) {
		$i = 0;
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) $nodeName = $reader->name;
			if ($nodeName == "job" || $nodeName == "row" || $nodeName == "JOB" || $nodeName == "ad" || $nodeName == "item" || $nodeName == "vacancy" || $nodeName == "Job" || $nodeName == "post" || $nodeName == "Product" || $nodeName == "advertisement" || ($specialCaseFlag == 1 && $nodeName == "Jobs")) {
				$baseTag = [];
				$baseValue = [];
				$cdataTag = [];
				libxml_use_internal_errors(true);
				$readerForNode = str_replace("<![CDATA[", "<![CDATA[cdata", $reader->readOuterXML());
				// var_dump($readerForNode); exit;

				try {
					$readerForNode = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $readerForNode);
					$node = new SimpleXMLElement($readerForNode);
				} catch (Exception $e) {
					echo "false";
					exit();
				}

				// var_dump($node); exit;

				if (!empty($node)) {
					foreach ($node as $minikey => $child) {

						$status = false;
						$tagName = $child->getName();
						if ($tagName != "description") {
							foreach ($child as $miniChild) {
								if (!empty($miniChild->getName())) {

									$childStatus = false;
									foreach ($miniChild as $miminiChild) {
										if (!empty($miminiChild->getName())) {
											$addTagName = $tagName . ":" . $miniChild->getName() . ":" . $miminiChild->getName();
											$baseTag[] = $addTagName;
											if (strpos($miminiChild->__toString(), 'cdata') !== false) {
												$cdataTag[] = $addTagName;
												$realString = $xmlString = str_replace("cdata", "", $miminiChild->__toString());
												$realString = "<![CDATA[" . $realString . "]]>";
												$baseValue[] = htmlspecialchars($realString);
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
									$baseTag[] = $addTagName;
									if (strpos($miniChild->__toString(), 'cdata') !== false) {
										$cdataTag[] = $addTagName;
										$realString = $xmlString = str_replace("cdata", "", $miniChild->__toString());
										$realString = "<![CDATA[" . $realString . "]]>";
										$baseValue[] = htmlspecialchars($realString);
									} else {
										$baseValue[] = htmlspecialchars($miniChild->__toString());
									}
								}
							}
						}

						if ($status) {
							$is_child = "4";
							continue;
						}

						$baseTag[] = $tagName;
						if (strpos($child->__toString(), 'cdata') !== false) {
							$cdataTag[] = $tagName;
							$realString = $xmlString = str_replace("cdata", "", $child->__toString());
							$realString = "<![CDATA[" . $realString . "]]>";
							$baseValue[] = htmlspecialchars($realString);
						} else {
							// This is for special case ? https://account.jobsinnetwork.com/feeds/c81476f7-8fd8-434a-958a-675388d67516.xml
							if ($tagName == "description") {
								$realString = htmlspecialchars(htmlspecialchars($child->asXML()));
							} else {
								$realString = htmlspecialchars($child->__toString());
							}
							$baseValue[] = $realString;
						}
					}
					$result = ['baseTag' => $baseTag, 'baseValue' => $baseValue, 'cdataTag' => $cdataTag, 'is_child' => $is_child];
					echo json_encode($result);
					exit();
				} else {
					echo "false";
					exit();
				}
			}
			$i++;
			if ($i > 100) {
				echo $i;
				exit;
			}
		}
		if ($i > 1) {
			$res = json_encode(['data' => "empty"]);
			echo $res;
			exit();
		}
	}
}

// Save XML information
if (isset($_POST['saveFeed'])) {
	$name = $_POST['feedName'];
	$xmlurl = $_POST['xmlurl'];
	$isChild = $_POST['isChild'];
	$utmValue = $_POST['utmValue'];
	$isReady = $crud->isxml($name, $xmlurl);
	if ($isReady) {
		echo json_encode(['data' => "duplicate"]);
		exit;
	}
	$basetag = $_POST['basetag'];
	$updatetag = $_POST['updatetag'];
	$cdatatag = $_POST['cdatatag'];
	$willAddCountry = $_POST['willAddCountry'];
	$willAddIndustry = $_POST['willAddIndustry'];
	$willAddCompany = $_POST['willAddCompany'];
	$jobLocationType = $_POST['jobLocationType'];
	$defaultCountry = ($willAddCountry == "invalid") ? null : $willAddCountry;
	$industry = ($willAddIndustry == "invalid") ? null : $willAddIndustry;
	$company = ($willAddCompany == "invalid") ? null : $willAddCompany;
	$joblocationtype = ($jobLocationType == "invalid") ? null : $jobLocationType;
	$crudResult = $crud->create($name, $xmlurl, $basetag, $updatetag, $cdatatag, $defaultCountry, $industry, $company, $joblocationtype, $isChild, $utmValue);
	if ($crudResult == true) {
		$res = json_encode(['data' => "true"]);
	} else {
		$res = json_encode(['data' => "false"]);
	}
	echo $res;
	exit();
}


// Update xml information
if (isset($_POST['updateFeed'])) {
	$id = $_POST['id'];
	$name = $_POST['feedName'];
	$orgin_name = $_POST['feedOrigin'];
	$updatetag = $_POST['updatetag'];
	$xmlurl = $_POST['xmlurl'];
	$willAddCountry = $_POST['willAddCountry'];
	$willAddIndustry = $_POST['willAddIndustry'];
	$willAddCompany = $_POST['willAddCompany'];
	$jobLocationType = $_POST['jobLocationType'];
	$utmValue = $_POST['utmValue'];
	$defaultCountry = ($willAddCountry == "invalid") ? null : $willAddCountry;
	$industry = ($willAddIndustry == "invalid") ? null : $willAddIndustry;
	$company = ($willAddCompany == "invalid") ? null : $willAddCompany;
	$joblocationtype = ($jobLocationType == "invalid") ? null : $jobLocationType;
	$crudResult = $crud->update($id, $name, $updatetag, $xmlurl, $defaultCountry, $industry, $company, $joblocationtype, $utmValue);
	if ($crudResult == true) {

		$res = json_encode(['data' => "true"]);

		$originName = str_replace(" ", "_", strtolower($orgin_name)) . ".xml";
		$originName = S3XML . $originName;
		$updateName = str_replace(" ", "_", strtolower($name)) . ".xml";
		$updateName = S3XML . $updateName;
		if (file_exists($originName)) {
			$updateRes = rename($originName, $updateName);
		}
	} else {
		$res = json_encode(['data' => "false"]);
	}
	echo $res;
	exit();
}

// Remove XML information
if (isset($_POST["removeItem"])) {
	$data_id = $_POST['data_id'];
	$remove_name = $_POST['remove_name'];
	$crudResult = $crud->delete($data_id);
	if ($crudResult == true) {
		$saveName = str_replace(" ", "_", strtolower($remove_name)) . ".xml";
		$saveName = S3XML . $saveName;
		if (file_exists($saveName)) {
			$deleted = unlink($saveName);
		}
		echo "true";
		exit();
	}
}

// running XML directly
if (isset($_POST["runningItem"])) {
	$data_id = $_POST['data_id'];
	$crudResult = $crud->createRunning($data_id);
	if ($crudResult === true) {
		$res = json_encode(['data' => "true"]);
	} else if ($crudResult === "warning") {
		$res = json_encode(['data' => "warning"]);
	} else {
		$res = json_encode(['data' => "false"]);
	}
	echo $res;
	exit();
}

//save downloadfile information
if (isset($_POST["downloadfile"])) {
	$xmlurl = $_POST['xmlurl'];
	$crudResult = $crud->createDownloading($xmlurl);

	if ($crudResult === "warning") {
		$res = json_encode(['data' => "warning"]);
	} elseif ($crudResult === false) {
		$res = json_encode(['data' => "false"]);
	} else {
		$res = json_encode(['data' => "true"]);
	}
	echo $res;
	exit();
}

// Remove XML information
if (isset($_POST["removeFile"])) {
	$data_id = $_POST['data_id'];
	$crudResult = $crud->deleteFile($data_id);
	if ($crudResult == true) {
		echo "true";
		exit();
	}
}

// Remove User
if (isset($_POST['removeUser']) && $_POST['removeUser'] == "removeOne") {
	$userId = $_POST['userId'];
	$crud->removeUser($userId);
	echo true;
	exit();
}

// Create user
if (isset($_POST['createUser'])) {
	$userName = $_POST['userName'];
	$userEmail = $_POST['userEmail'];
	$userPwd = $_POST['userPwd'];
	$userRole = $_POST['userRole'];
	$userId = $_POST['userId'];
	$crudResult = false;
	if (empty($userId)) {
		$is_url = $crud->isUser($userEmail);
		if ($is_url) {
			echo json_encode(['data' => "duplicate"]);
			exit;
		}
		$crudResult = $crud->createUser($userName, $userEmail, $userPwd, $userRole);
	} else
		$crudResult = $crud->updateUser($userName, $userEmail, $userPwd, $userRole, $userId);
	if ($crudResult == true) {
		$res = json_encode(['data' => "true"]);
	} else {
		$res = json_encode(['data' => "false"]);
	}
	echo $res;
	exit();
}

// active AI generation
if (isset($_POST["activeAIGenerate"])) {
	$feedinfoID = $_POST['feedinfo'];
	$crudResult = $crud->feedInfoAISwitch($feedinfoID);
	if ($crudResult == true) {
		return true;
	}
}
