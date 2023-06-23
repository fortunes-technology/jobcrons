<?php
include_once 'dbconfigcron.php';

if(isset($argv[1])) {
  $order = $argv[1];
}
else {
  $order = 1;
}

if($order > 100) {
  $feedAll = $crud->getLarge($order);
}
else {
  $feedAll = $crud->getAll($order);
}

$cronStatus = $crud->getCronStatus($order);
if($cronStatus != "Finished") {
  echo "Cron is Running";
  exit();
}
echo "Cron status is changed and it is running";

$cronStart = $crud->cronStatus($order, "Running");

if(count($feedAll) > 0) {
    
  foreach ($feedAll as $value) {

    $changeCount =  $crud->changeCount($value['id'], "Reset");  

    echo $value['url'];
    echo "<br>";

  }
  echo "reset";

$cronStart = $crud->cronStatus($order, "Finished");
}
