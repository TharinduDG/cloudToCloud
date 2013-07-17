<?php

namespace OCA\CloudToCloud;

class CloudToCloudShare
{

    public static function c2cShare($sharedTo, $host, $filename, $email,$dataType, $dataId)
    {
        $current_user = \OC_User::getUser();
        $query = \OC_DB::prepare("INSERT INTO *PREFIX*c2c_share (`share_with`,`file_target`,`uid_owner`,`host`,`email`,`stime`,`item_type`,`item_source`) VALUES (?,?,?,?,?,?,?,?)");
        $result = $query->execute(array($sharedTo, $filename, $current_user, $host, $email, time(),$dataType,$dataId));
        return $result;
    }


    public static function findEmail($id)
    {
        $query = \OC_DB::prepare("SELECT `email`,`stime` FROM *PREFIX*c2c_share");
        $result = $query->execute();
        $shared_email = false;

        while ($row = $result->fetchRow()) {
            $encrypted = sha1((string)$row['email']);
            if ($encrypted == $id) {
                $shared_email = $row['email'];
                break;
            }
        }
        return $shared_email;
    }

    public static function getOwner($key){

        $query = \OC_DB::prepare("SELECT `uid_owner` FROM *PREFIX*c2c_share");
        $result = $query->execute();
        $owner = false;

        while ($row = $result->fetchRow()) {
            $encrypted = sha1((string)$row['uid_owner']);
            if ($encrypted == $key) {
                $shared_email = $row['uid_owner'];
                break;
            }
        }
        return $owner;
    }

    public static function getFileId($name){
        $query = \OC_DB::prepare("SELECT `item_source` FROM *PREFIX*c2c_share WHERE `file_target`=?");
        $result = $query->execute(array($name));
        $fileId = $result->fetchRow();
        return $fileId['item_source'];
    }


    public static function sendMail($to_address, $to_name, $from_name,$filename)
    {
        $subject = "Cloud To Cloud : A filed has been shared for you.";
        $text = "Dear ".$to_name.",<br/> ".$from_name." has shared ".$filename." with you.<br/> Use the following link
         to access the file : ".CloudToCloudShare::getSharedUrl($to_address);

        try {
            \OCP\Util::sendMail($to_address, $to_name, $subject, $text, "noreply@owncloud.com", $from_name );
        } catch (Exception $exception) {
        }
    }

    public static function getSharedUrl($email)
    {
        return CloudToCloudShare::getCurrentPage().'?id='.sha1($email);
    }

    public static function getCurrentPage(){
        $pageURL = 'http';
        if (isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
    }

    public static function getFiles($email,$dir){
        $query = \OC_DB::prepare("SELECT * FROM *PREFIX*c2c_share WHERE `email`=? ORDER BY `stime`");
        $result = $query->execute(array($email));
        $files = array();
        $owners = array();

        if($dir == ""){
            while ($aFile = $result->fetchRow()) {
                if($aFile['item_type'] == "file"){
                   $name = $aFile['file_target'];
                    \OC_Util::setupFS($aFile['uid_owner']);
                   $owners[$aFile['item_source']][] = $aFile['uid_owner'];
                   $path = \OC\Files\Filesystem::getPath($aFile['item_source']);
                   $parent_path = substr($path,0,strrpos($path,'/')+1);
                   $dir_content = \OC\Files\Filesystem::getDirectoryContent($parent_path);

                    foreach($dir_content as $i){
                        if($i['name'] == $name && $i['type']=="file"){
                            $files[] = $i;
                            break;
                        }
                    }
                }else{
                    $name = $aFile['file_target'];
                    \OC_Util::setupFS($aFile['uid_owner']);
                    $owners[$aFile['item_source']][] = $aFile['uid_owner'];
                    $path = \OC\Files\Filesystem::getPath($aFile['item_source']);
                    $parent_path = substr($path,0,strrpos($path,'/')+1);
                    $dir_content = \OC\Files\Filesystem::getDirectoryContent($parent_path);
                    foreach($dir_content as $i){
                        if($i['name'] == $name && $i['type']=="dir"){
                            $files[] = $i;
                            break;
                        }
                    }
                }
            }
        }else{
            while($aFile = $result->fetchRow()){
                if($aFile['item_type'] == "dir" && $aFile['file_target'] == $dir){
                    \OC_Util::setupFS($aFile['uid_owner']);
                    $owners[$aFile['item_source']][] = $aFile['uid_owner'];
                    $path = \OC\Files\Filesystem::getPath($aFile['item_source']);
                    $dir_content = \OC\Files\Filesystem::getDirectoryContent($path);
                    foreach($dir_content as $i){
                            $files[] = $i;
                    }
                }
            }

        }
        $owners = array_unique($owners);
        $_SESSION['owners'] = $owners;
        return $files;
    }

    public static function isSharedContent($file){
        $query = \OC_DB::prepare("SELECT COUNT(*) FROM *PREFIX*c2c_share WHERE `file_target`=?");
        $result = $query->execute(array($file));
        $count = sizeof($result);
        return $count;
    }

    public static function getHost($fileId,$email){
        $query = \OC_DB::prepare("SELECT `host` FROM *PREFIX*c2c_share WHERE `item_source`=? AND `email`=?");
        $result = $query->execute(array($fileId,$email));
        $host = $result->fetchRow();

        \OC_Util::setupFS();
        if($host[`host`] == ""){
            $path = \OC\Files\Filesystem::getPath($fileId);
            $path = explode("/",$path);
            $i =  1;
            foreach($path as $filename){
                if($filename == ""){
                    continue;
                }
                $query = \OC_DB::prepare("SELECT `host` FROM *PREFIX*c2c_share WHERE `file_target`=? AND `email`=?");
                $result = $query->execute(array($filename,$email));
                $host = $result->fetchRow();

                if($host[`host`] != ""){
                    return $host['host'];
                }
                $i++;
            }
        }else{
            return $host['host'];
        }
    }

}