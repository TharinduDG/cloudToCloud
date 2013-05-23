<?php

/**
 * ownCloud - internal_messages
 *
 * @author Tharindu Galappaththi,Sampath Basnagoda, Visitha Baddegama, Buddhiprabha Erabadda
 * @copyright 2013 Tharindu Galappaththi,Sampath Basnagoda, Visitha Baddegama, Buddhiprabha Erabadda <tdgalappaththi@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once dirname(__FILE__).'/openid.php';


OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('cloud_to_cloud');

OCP\Util::addStyle('cloud_to_cloud', 'tabs');

OCP\Util::addScript('cloud_to_cloud', 'jquery');
OCP\Util::addScript('cloud_to_cloud', 'tabs');



try {
	# Change 'localhost' to your domain name.
	$openid = new LightOpenID('localhost');
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
			$tmpl = new OCP\Template('cloud_to_cloud', 'filesList', 'user');
			$tmpl->assign( 'data' , $data , false );
			$tmpl->printPage();
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

//$tmpl = new OCP\Template('cloud_to_cloud', 'login', 'user');
//$tmpl->printPage();


$tmpl = new OCP\Template('cloud_to_cloud', 'filesList', 'user');
$tmpl->printPage();