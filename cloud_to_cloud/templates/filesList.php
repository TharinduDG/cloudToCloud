<?php 

$dir = $_['directory'];
// Redirect if directory does not exist
if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
	header('Location: ' . OCP\Util::linkTo('cloud_to_cloud', 'viewShared.php') . '');
	exit();
}

function fileCmp($a, $b) {
	if ($a['type'] == 'dir' and $b['type'] != 'dir') {
		return -1;
	} elseif ($a['type'] != 'dir' and $b['type'] == 'dir') {
		return 1;
	} else {
		return strnatcasecmp($a['name'], $b['name']);
	}
}

$content = \OC\Files\Filesystem::getDirectoryContent($dir);
$files = array();

foreach ($content as $i) {
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
	$files[] = $i;
}

usort($files, "fileCmp");

// Make breadcrumb
$breadcrumb = array();
$pathtohere = '';
foreach (explode('/', $dir) as $i) {
	if ($i != '') {
		$pathtohere .= '/' . $i;
		$breadcrumb[] = array('dir' => $pathtohere, 'name' => $i);
	}
}

$breadcrumbNav = new OCP\Template('files', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('cloud_to_cloud', 'viewShared.php') . '?dir=');

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

// Load the files we need
OCP\Util::addStyle('files', 'files');
OCP\Util::addStyle('cloud_to_cloud', 'custom');
OCP\Util::addscript('files', 'jquery.iframe-transport');
OCP\Util::addscript('files', 'jquery.fileupload');
OCP\Util::addscript('files', 'jquery-visibility');
OCP\Util::addscript('files', 'filelist');
OCP\Util::addscript('files', 'fileactions');
OCP\Util::addscript('files', 'files');
OCP\Util::addscript('files', 'keyboardshortcuts');

OCP\App::setActiveNavigationEntry('files_index');

$tmpl = new OCP\Template('cloud_to_cloud', 'part.list', '');
$tmpl->assign('files', $files);
$tmpl->assign('permissions', $permissions);
$tmpl->assign('baseURL', OCP\Util::linkTo('cloud_to_cloud', 'viewShared.php') . '?dir=');
$tmpl->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$tmpl->assign('disableSharing', false);
$files_list = $tmpl->fetchPage();



?>
<div id="controls">
	<?php print_unescaped($breadcrumbNav->fetchPage()); ?>
	<div id="moto">
		Cloud to Cloud : Share files between clouds
	</div>
</div>
<table id="filestable">
	<thead>
		<tr>
			<th id='headerName'>
				<span class='name'><?php p($l->t( 'Name' )); ?></span>
			</th>
			<th id="headerSize"><?php p($l->t( 'Size' )); ?></th>
			<th id="headerDate">
				<span id="modified"><?php p($l->t( 'Modified' )); ?></span>
			</th>
		</tr>
	</thead>
	<tbody id="fileList">
		<?php print_unescaped($files_list); ?>
	</tbody>
</table>