<?php
include_once 'dbconfigcron.php';
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3MultipartUploadException;
set_time_limit(0);
ini_set('memory_limit', '-1');

if(isset($argv[1])) {
  $order = $argv[1];
}
else {
  $order = 1;
}

$getRunning = $crud->getDownloading($order);
$runningList = $getRunning['runningList'];
$downloadingId = $getRunning['downloadingId'];

$cronStatus = $crud->getFilexmlCronStatus($order);
if($cronStatus != "Finished") {
  echo "Cron is Running";
  exit();
}
echo "Cron status is changed and it is running";

$cronStart = $crud->filexmlCronStatus($order, "Running");

if(empty($_SESSION['bigCron'])) {
  if(count($runningList) > 0) {
    
    // make db "downloading" to "progressing"
    $progressCrud = $crud->setDownToProgress($downloadingId);
    foreach ($runningList as $value) {
      // This is url contains gz/zip case
      if (strpos($value['inputurl'], '.zip') !== false || strpos($value['inputurl'], '.gz') !== false) {
        if (strpos($value['inputurl'], '.zip') !== false) {
          $file = $value['inputurl'];
          $newfile = TEMPZIP.$value['name'].'.zip';
          // if (!copy($file, $newfile)) {
          //     echo "failed to copy $file...\n";
          // }

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $file);
          curl_setopt($ch, CURLOPT_USERAGENT, "TETRA 4.0");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_HEADER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
          $response = curl_exec($ch);
          $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
          // $header = substr($response, 0, $header_size);
          curl_close($ch);

          if($httpcode == 200) {
            $body = substr($response, $header_size);
            $fp = fopen($newfile, 'wb');
            fwrite($fp, $body);
            fclose($fp);

            // file extract
            $zip = new ZipArchive;
            $res = $zip->open($newfile);
            if ($res === TRUE) {
              $inName = $zip->getNameIndex(0);
              $zip->extractTo(TEMPZIP);
              $zip->close();
              rename(TEMPZIP.$inName, TEMPZIP.$value['name'].'.xml');
              // upload to s3 bucket
              $s3key = "zipgz/".$value['name'].'.xml';
              $uploader = new MultipartUploader($s3, TEMPZIP.$value['name'].'.xml', [
                  'bucket' => "convertedfeed",
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
              // make db "downloading" to "ready"
              $readyCrud = $crud->setDownToReady($value['id']);
              unlink($newfile);
              unlink(TEMPZIP.$value['name'].'.xml');
            }
            else {
              $readyCrud = $crud->setDownToError($value['id']);
            }
          }
          else {
            $readyCrud = $crud->setDownToError($value['id']);
          }
        }
    
        if (strpos($value['inputurl'], '.gz') !== false) {
          $file = $value['inputurl'];
          $newfile = TEMPZIP.$value['name'].'.gz';

          if($order == 2) {
            // large file case
            $buffer_size = 4194304; 
            $ret = 0;
            // $fin = fopen($file, "rb");
            $fout = fopen($newfile, "w");
            if(($fin = fopen($file, "r"))) {
              while(!feof($fin)) {
                $ret += fwrite($fout, fread($fin, $buffer_size));
              }
              fclose($fin);
              fclose($fout);

              $file = gzopen($newfile, 'rb'); //Opening the file in binary mode
              if($file) {
                $out_file_name = str_replace('.gz', '.xml', $newfile);
                $out_file = fopen($out_file_name, 'wb');
                // Keep repeating until the end of the input file
                while (!gzeof($file)) {
                  fwrite($out_file, gzread($file, $buffer_size)); //Read buffer-size bytes.
                }
                fclose($out_file); //Close the files once they are done with
                gzclose($file);

                // upload to s3 bucket
                $s3key = "zipgz/".$value['name'].'.xml';
                $uploader = new MultipartUploader($s3, $out_file_name, [
                    'bucket' => "convertedfeed",
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

                // make db "downloading" to "ready"
                $readyCrud = $crud->setDownToReady($value['id']);
                unlink($newfile);
                unlink($out_file_name);
              }
              else {
                $readyCrud = $crud->setDownToError($value['id']);
              }
            }
            else {
              $readyCrud = $crud->setDownToError($value['id']);
            }

          }
          else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_USERAGENT, "TETRA 4.0");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            // $header = substr($response, 0, $header_size);
            curl_close($ch);

            if($httpcode == 200) {
              $body = substr($response, $header_size);
              $fp = fopen($newfile, 'wb');
              fwrite($fp, $body);
              fclose($fp);

              $buffer_size = 8096; // The number of bytes that needs to be read at a specific time, 4KB here
              $out_file_name = str_replace('.gz', '.xml', $newfile);
              $file = gzopen($newfile, 'rb'); //Opening the file in binary mode
              if($file) {
                $out_file = fopen($out_file_name, 'wb');
                // Keep repeating until the end of the input file
                while (!gzeof($file)) {
                  fwrite($out_file, gzread($file, $buffer_size)); //Read buffer-size bytes.
                }
                fclose($out_file); //Close the files once they are done with
                gzclose($file);

                // upload to s3 bucket
                $s3key = "zipgz/".$value['name'].'.xml';
                $uploader = new MultipartUploader($s3, $out_file_name, [
                    'bucket' => "convertedfeed",
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

                // make db "downloading" to "ready"
                $readyCrud = $crud->setDownToReady($value['id']);
                unlink($newfile);
                unlink($out_file_name);
              }
              else {
                $readyCrud = $crud->setDownToError($value['id']);
              }
            }
            else {
              $readyCrud = $crud->setDownToError($value['id']);
            }
          }
          
        }
      }

      // This is for gz/zip special case. so the url doesn't contain gz extension
      else {
        $headers = get_headers($value['inputurl']);
        if(!empty($headers)){
          // Use a regex to see if the response code is 200.
          preg_match('/\b404\b/', $headers[0], $matches);
          if(empty($matches)){
            $is_download = 0;
            foreach ($headers as $h_key => $h_value) {
              if (strpos(str_replace(" ", "", $h_value), 'gzip') !== false || strpos(str_replace(" ", "", $h_value), 'gz') !== false) {
                $is_download = 1;
              }
            }
            if($is_download == 1) {
              // download file
              $newfile = TEMPZIP.$value['name'].'.gz';
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $value['inputurl']);
              curl_setopt($ch, CURLOPT_USERAGENT, "TETRA 4.0");
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_HEADER, 1);
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
              $response = curl_exec($ch);
              $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
              curl_close($ch);

              if($httpcode == 200) {
                $body = substr($response, $header_size);
                $fp = fopen($newfile, 'wb');
                fwrite($fp, $body);
                fclose($fp);

                $buffer_size = 8096; // The number of bytes that needs to be read at a specific time, 4KB here
                $out_file_name = str_replace('.gz', '.xml', $newfile);
                $file = gzopen($newfile, 'rb'); //Opening the file in binary mode
                if($file) {
                  $out_file = fopen($out_file_name, 'wb');
                  // Keep repeating until the end of the input file
                  while (!gzeof($file)) {
                    fwrite($out_file, gzread($file, $buffer_size)); //Read buffer-size bytes.
                  }
                  fclose($out_file); //Close the files once they are done with
                  gzclose($file);

                  // upload to s3 bucket
                  $s3key = "zipgz/".$value['name'].'.xml';
                  $uploader = new MultipartUploader($s3, $out_file_name, [
                      'bucket' => "convertedfeed",
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

                  // make db "downloading" to "ready"
                  $readyCrud = $crud->setDownToReady($value['id']);
                  unlink($newfile);
                  unlink($out_file_name);
                }
                else {
                  $readyCrud = $crud->setDownToError($value['id']);
                }
              }
              else {
                $readyCrud = $crud->setDownToError($value['id']);
              }
            }
            else {
              $readyCrud = $crud->setDownToCheck($value['id']);
            }
          }
        }
        
      }
    }
    echo "success";
    $cronStart = $crud->filexmlCronStatus($order, "Finished");
  }
}

?>