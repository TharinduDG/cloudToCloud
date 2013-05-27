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
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=');

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
OCP\Util::addscript('files', 'jquery.iframe-transport');
OCP\Util::addscript('files', 'jquery.fileupload');
OCP\Util::addscript('files', 'jquery-visibility');
OCP\Util::addscript('files', 'filelist');
OCP\Util::addscript('files', 'fileactions');
OCP\Util::addscript('files', 'files');
OCP\Util::addscript('files', 'keyboardshortcuts');

OCP\App::setActiveNavigationEntry('files_index');

$tmpl = new OCP\Template('files', 'part.list', '');
$tmpl->assign('files', $files);
$tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
$tmpl->assign('permissions', $permissions);
$tmpl->assign('baseURL', OCP\Util::linkTo('cloud_to_cloud', 'viewShared.php') . '?dir=');
$tmpl->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$tmpl->assign('disableSharing', false);
$files_list = $tmpl->fetchPage();

$allowZipDownload = intval(OCP\Config::getSystemValue('allowZipDownload', true));


?>

<?php print_unescaped($_['breadcrumb']); ?>

<table id="filestable">
	<thead>
		<tr>
			<th id='headerName'>
				<input type="checkbox" id="select_all" />
				<span class='name'><?php p($l->t( 'Name' )); ?></span>
				<span class='selectedActions'>
					<?php if($allowZipDownload) : ?>
						<a href="" class="download">
							<img class="svg" alt="Download"
								 src="<?php print_unescaped(OCP\image_path("core", "actions/download.svg")); ?>" />
							<?php p($l->t('Download'))?>
						</a>
					<?php endif; ?>
				</span>
			</th>
			<th id="headerSize"><?php p($l->t( 'Size' )); ?></th>
			<th id="headerDate">
				<span id="modified"><?php p($l->t( 'Modified' )); ?></span>
				<?php if ($permissions & OCP\PERMISSION_DELETE): ?>
<!-- 					NOTE: Temporary fix to allow unsharing of files in root of Shared folder -->
					<?php if ($_['dir'] == '/Shared'): ?>
						<span class="selectedActions"><a href="" class="delete-selected">
							<?php p($l->t('Unshare'))?>
							<img class="svg" alt="<?php p($l->t('Unshare'))?>"
								 src="<?php print_unescaped(OCP\image_path("core", "actions/delete.svg")); ?>" />
						</a></span>
					<?php else: ?>
						<span class="selectedActions"><a href="" class="delete-selected">
							<?php p($l->t('Delete'))?>
							<img class="svg" alt="<?php p($l->t('Delete'))?>"
								 src="<?php print_unescaped(OCP\image_path("core", "actions/delete.svg")); ?>" />
						</a></span>
					<?php endif; ?>
				<?php endif; ?>
			</th>
		</tr>
	</thead>
	<tbody id="fileList">
		<?php print_unescaped($files_list); ?>
	</tbody>
</table>