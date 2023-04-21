<?php

define("XMLDIR", "xmldir/");
define("TEMPZIP", "tempzip/");
define("S3XML", "s3://converted.bebee.com/");
define("S3ZIP", "s3://convertedfeed/zipgz/");

require 'vendor/autoload.php';
use Aws\S3\S3Client;

$s3 = new S3Client([
	'version' => 'latest',
	'region'  => 'us-east-2',
	'credentials' => [
			'key' => 'AKIAQLRVICZQM2MQJHZM',
        	'secret' => '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',
	]
]);
$s3->registerStreamWrapper();

$debug = new \bdk\Debug(array(
	'collect' => true,
	'output' => true,
));

if(session_status() == PHP_SESSION_NONE){
    //session has not started
    session_start();
}


$DB_host = "localhost";
$DB_user = "root";
$DB_pass = "";
$DB_name = "dbpdo";

// $DB_host = "localhost";
// $DB_user = "root";
// $DB_pass = "Innerpeace628!";
// $DB_name = "dbpdo";

try
{
	$DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name}",$DB_user,$DB_pass);
	$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
	echo $e->getMessage();
}
include_once 'class.crud.php';
$crud = new crud($DB_con);

?>
