<?php
include_once 'dbconfigcron.php';
$curl = curl_init();
// $myfile = fopen("/xmldir/file.xml", "w") or die("Unable to open file!");
//   fwrite($myfile, $response_xml);
//   fclose($myfile);
//   exit;
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://oauth.harri.com/oauth2/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'client_id=7ai4ikprhi7r9pqug742qr7dlg&client_secret=atph6ptp8co4d2htik2omoon3tnemrcmtq99utt9tjj5o8e6gjh&grant_type=client_credentials',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded',
    'Cookie: XSRF-TOKEN=a1a6c8f6-0c4e-458f-a3a3-584108e87866'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = json_decode($response);
$token = $response->access_token;

if(!empty($token)) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://gateway.harri.com/dispatcher/api/v2/brands/914618/jobs/feed?include_children=true',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer '.$token
    ),
  ));

  $response_xml = curl_exec($curl);

  curl_close($curl);

  $myfile = fopen("/var/www/html/cf/xmldir/file.xml", "w") or die("Unable to open file!");
  fwrite($myfile, $response_xml);
  fclose($myfile);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://files.channable.com/ZKWkKXye0GkHX8R0rM_xYw==.xml");
curl_setopt($ch, CURLOPT_USERAGENT, "TETRA 4.0");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$newfile = '/var/www/html/cf/xmldir/file_channable.xml';

if($httpcode == 200) {
  $body = substr($response, $header_size);
  $fp = fopen($newfile, 'wb');
  fwrite($fp, $body);
  fclose($fp);
}

?>