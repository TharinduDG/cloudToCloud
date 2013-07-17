<?php

OC_JSON::checkLoggedIn();
OCP\JSON::callCheck();

if(isset($_POST['username']) && isset($_POST['url']) && isset($_POST['filename']) && isset($_POST['email_id']) && isset($_POST['type']) && isset($_POST['dataId'])){
    try{
        $result =\OCA\CloudToCloud\CloudToCloudShare::c2cShare($_POST['username'],$_POST['url'],$_POST['filename'], $_POST['email_id'],$_POST['type'],$_POST['dataId']);

    }catch (Exception $e){
        OC_JSON::error(array('data' => 'error occurred'));
        exit();
    }
//  \OCA\CloudToCloud\CloudToCloudShare::sendMail($_POST['email_id'],$_POST['username'],OC_User::getUser(),$_POST['filename']);

   OC_JSON::success(array('data' => 'success'));
}else{
    OC_JSON::error(array('data' => 'error occurred'));
}




