<?php

require_once dirname(__FILE__).'/openid.php';

$data = "";
$email = "";
$owner = "";
$id = isset($_GET['id']) ?  $_GET['id'] : false;
$owner_hash = isset($_GET['owner']) ? $_GET['owner'] : false;

if(!$id){
   // echo "<br/>You don't have any files here ";
   // exit();
}else{
    $email = \OCA\CloudToCloud\CloudToCloudShare::findEmail($id);
    $owner = \OCA\CloudToCloud\CloudToCloudShare::getOwner($owner_hash);
    $_SESSION['owner'] = $owner;
}


try {
    # Change 'localhost' to your domain name.
    $openid = new LightOpenID($_SERVER['SERVER_NAME']);
    if(!$openid->mode) {

        if(isset($_GET['provider']) && strcmp($_GET['provider'], 'google') == 0) {
            $openid->identity = 'https://www.google.com/accounts/o8/id';
            $openid->required = array('contact/email','namePerson/first','namePerson/last');
            header('Location: ' . $openid->authUrl());

        }else if(isset($_GET['provider']) && strcmp($_GET['provider'], 'yahoo') == 0) {
            $openid->identity = 'https://me.yahoo.com';
            $openid->required = array('contact/email','namePerson/first','namePerson/last');
            header('Location: ' . $openid->authUrl());

        }else if(isset($_POST['openid_identifier'])) {
            $openid->identity = $_POST['openid_identifier'];
            $openid->required = array('contact/email','namePerson/first','namePerson/last');
            header('Location: ' . $openid->authUrl());
        }

    } elseif($openid->mode == 'cancel') {
        echo 'User has canceled authentication!';
    } else {

        if($openid->validate())
        {
            //User logged in
            $d = $openid->getAttributes();

            $first_name = $d['namePerson/first'];
            $last_name = $d['namePerson/last'];
            $email = $d['contact/email'];

            $data = array(
                'first_name' => $first_name ,
                'last_name' => $last_name ,
                'email' => $email ,
            );

            //now signup/login the user.
        }
        else
        {
            $tmpl = new OCP\Template('cloud_to_cloud', 'error', 'user');
            $tmpl->assign( 'data' , $data , false );
            $tmpl->printPage();
        }
    }
} catch(ErrorException $e) {
    echo $e->getMessage();
}

//********************************************

// Load the files
$dir = isset($_GET['dir']) ? stripslashes($_GET['dir']) : '';

if($dir != ""){
    $dir = str_replace("/","",$dir);

    \OC_Util::setupFS('admin5');
    $_SESSION['owners'] = array('admin5');

    $dir_path = OC\Files\Filesystem::getPath($dir);
    $path = explode("/",$dir_path);
    $clicked_dir = $path[sizeof($path) - 1];
    $dir = $clicked_dir;

    if ( !\OC\Files\Filesystem::is_dir($dir_path) || \OCA\CloudToCloud\CloudToCloudShare::isSharedContent($clicked_dir) < 1) {
         header('Location: ' . \OCA\CloudToCloud\CloudToCloudShare::getCurrentPage() ."?id=".$id. '');
         exit();
    }
}

// Load the files we need
OCP\Util::addStyle('files', 'files');
OCP\Util::addStyle('cloud_to_cloud','custom');
OCP\Util::addscript('files', 'jquery.iframe-transport');
OCP\Util::addscript('files', 'jquery-visibility');
OCP\Util::addscript('files', 'filelist');


function fileCmp($a, $b) {
    if ($a['type'] == 'dir' and $b['type'] != 'dir') {
        return -1;
    } elseif ($a['type'] != 'dir' and $b['type'] == 'dir') {
        return 1;
    } else {
        return strnatcasecmp($a['name'], $b['name']);
    }
}

$files = array();
$email = $email == "" ? $data['email'] : $email;
$_SESSION['shared_email'] = $email;

    //aeded68cdb71970f15c74de957bf361fbbd205ca

$files = \OCA\CloudToCloud\CloudToCloudShare::getFiles($email,$dir);
$files_list = array();

foreach ($files as $i) {
    $i['date'] = OCP\Util::formatDate($i['mtime']);
    if ($i['type'] == 'file') {
        $fileinfo = pathinfo($i['name']);
        $i['basename'] = $fileinfo['filename'];
        if (!empty($fileinfo['extension'])) {
            $i['extension'] = '.' . $fileinfo['extension'];
        } else {
            $i['extension'] = '';
        }
    }
    $i['directory'] = $dir;
    $files_list[] = $i;
}

// Make breadcrumb
$breadcrumb = array();
$pathtohere = '';
foreach (explode('/', $dir) as $i) {
    if ($i != '') {
        $pathtohere .= '/' . $i;
        $breadcrumb[] = array('dir' => \OCA\CloudToCloud\CloudToCloudShare::getFileId($i), 'name' =>$i );
    }
}

// make breadcrumb und filelist markup
$list = new OCP\Template('cloud_to_cloud', 'myFiles.part.list', '');
$list->assign('files', $files_list);
$list->assign('baseURL', \OCA\CloudToCloud\CloudToCloudShare::getSharedUrl($email) . '&dir=');
$list->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$list->assign('disableSharing', false);
$breadcrumbNav = new OCP\Template('cloud_to_cloud', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', \OCA\CloudToCloud\CloudToCloudShare::getSharedUrl($email) . '&dir=');

$permissions = OCP\PERMISSION_READ;
if (\OC\Files\Filesystem::isCreatable($dir . '/')) {
    $permissions |= OCP\PERMISSION_CREATE;
}
if (\OC\Files\Filesystem::isUpdatable($dir . '/')) {
    $permissions |= OCP\PERMISSION_UPDATE;
}
if (\OC\Files\Filesystem::isDeletable($dir . '/')) {
    $permissions |= OCP\PERMISSION_DELETE;
}
if (\OC\Files\Filesystem::isSharable($dir . '/')) {
    $permissions |= OCP\PERMISSION_SHARE;
}

    $storageInfo=OC_Helper::getStorageInfo();
    $maxUploadFilesize=OCP\Util::maxUploadFilesize($dir);

    OCP\Util::addscript('cloud_to_cloud', 'myFile.fileactions');
    OCP\Util::addscript('cloud_to_cloud', 'files');
    OCP\Util::addscript('files', 'keyboardshortcuts');
    $tmpl = new OCP\Template('cloud_to_cloud', 'myFiles.index', 'user');
    $tmpl->assign('fileList', $list->fetchPage());
    $tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
    $tmpl->assign('dir', \OC\Files\Filesystem::normalizePath($dir));

    $tmpl->assign('permissions', $permissions);
    $tmpl->assign('files', $files);
    $tmpl->assign('allowZipDownload', true);
    //$tmpl->printGuestPage('cloud_to_cloud','myFiles.index',array('breadcrumb'=>$breadcrumbNav->fetchPage(),'fileList'=>$list->fetchPage(),'dir'=> \OC\Files\Filesystem::normalizePath($dir),'files'=>$files,'permissions'=>$permissions));
    $tmpl->printPage();