<?php
$url = isset($_POST['url']) ? $_POST['url'] : false;
error_log($url,0);

if($url){


    \OC_Util::setupFS('admin_B');
	//get data directory path
	//$datadir = \OC_User::getHome(\OC_User::getUser()) . '/files/';

    $datadir = \OC_User::getHome('admin_B').'/files/';

	$file_base_name = basename($url);

    error_log($datadir,0);


	function download_file($sourceUrl, $pathTodataFolder, $file_base_name)
    {
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_FILE, $out);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $sourceUrl);

        $data=curl_exec($ch);
        file_put_contents($pathTodataFolder.$file_base_name, $data);

        curl_close($ch);
    }

	$wav_file = download_file($url, $datadir, $file_base_name);

    error_log($wav_file,0);



    function retrieve_remote_file_size($url){
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    $size = retrieve_remote_file_size($url);
    error_log($size,0);


	$zip = new ZipArchive;
	if ($zip->open($datadir.$file_base_name) === TRUE) {
        $zip->extractTo($datadir);
        $zip->close();
        //echo "<br>"."File unziped successfully!!!";


        if (!unlink($datadir.$file_base_name))
        {
            //echo ("<br>"." Error deleting $file_base_name");
        }
        else
        {
            //echo ("<br>"."Deleted $file_base_name");
        }


    } else {
        //echo "<br>"."Unzipping failed";
    }
}
