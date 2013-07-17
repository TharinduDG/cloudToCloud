<?php
$l=new OC_L10N('cloud to cloud');
OC::$CLASSPATH['OC_Cloud_To_Cloud_App'] = 'apps/cloud_to_cloud/lib/cloud_to_cloud.php';

OC::$CLASSPATH['OCA\CloudToCloud\CloudToCloudShare'] = 'cloud_to_cloud/lib/cloud_to_cloud.php';

OCP\App::addNavigationEntry( array(
  'id' => 'cloud_to_cloud_index',
  'order' => 14,
  'href' => OCP\Util::linkTo( 'cloud_to_cloud', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'cloud_to_cloud', 'cloudToCloud.svg' ),
  'name' => $l->t('Cloud2Cloud')));
