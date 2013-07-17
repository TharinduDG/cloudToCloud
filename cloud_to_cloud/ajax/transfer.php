<?php

$file_id = isset($_POST['dataId']) ? $_POST['dataId'] : false;
$owner = $_SESSION['owner'];

if($file_id){

  //  OC_JSON::error(array('data' => $owner." "));

    \OC_Util::setupFS('admin5');
     $path = \OC\Files\Filesystem::getPath($file_id);
     $files = explode("/",$path);
     $files = $files[sizeof($files) - 1];

     if(\OC\Files\Filesystem::is_dir($path)){
        $zip_file = new ZipArchive();
     }else{
         // store to temporary location
        file_put_contents('/var/www/owncloud5/apps/cloud_to_cloud/downloads/'.$files,\OC\Files\Filesystem::file_get_contents($path));
        $url = $_SERVER['SERVER_NAME'].'/owncloud5/apps/cloud_to_cloud/downloads/'.$files;
        $host = \OCA\CloudToCloud\CloudToCloudShare::getHost($file_id,$_SESSION['shared_email']);

       //  OC_JSON::error(array('data' => $url));

         $ch = curl_init(); // initialize curl handle
        // owncloud5/index.php/apps/cloud_to_cloud/processDownload.php
         $host = "http://cloud.projects.uom.lk/owncloud_B/index.php/apps/cloud_to_cloud/processDownload.php";
         //OC_JSON::error(array('data' =>$host));
         curl_setopt($ch, CURLOPT_URL,$host);
         curl_setopt($ch, CURLOPT_FAILONERROR, 1);
         curl_setopt($ch, CURLOPT_TIMEOUT, 10); // times out after 10s
         curl_setopt($ch, CURLOPT_POST, 1); // set POST method
         curl_setopt($ch, CURLOPT_POSTFIELDS, "url=".$url); // post fields
         $data = curl_exec($ch); // run the whole process
         curl_close($ch);
      }

    OC_JSON::success(array('data' => "path:".$path."url:".$url));
}else{
    OC_JSON::error(array('data' => 'error occurred'));
}
