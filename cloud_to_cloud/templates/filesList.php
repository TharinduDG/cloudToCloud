<?php 


$content = \OC\Files\Filesystem::getDirectoryContent('');
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
OCP\Util::addStyle('files', 'files');
OCP\Util::addscript('files', 'jquery.iframe-transport');
OCP\Util::addscript('files', 'jquery.fileupload');
OCP\Util::addscript('files', 'jquery-visibility');
OCP\Util::addscript('files', 'filelist');


OCP\Util::addscript('files', 'fileactions');
OCP\Util::addscript('files', 'files');
OCP\Util::addscript('files', 'keyboardshortcuts');
$tmpl = new OCP\Template('files', 'part.list', '');
$tmpl->assign('files', $files);
$tmpl->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=');
$tmpl->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$tmpl->assign('disableSharing', false);
$files_list = $tmpl->fetchPage()
?>
<table id="filestable">
	<thead>
		<tr>
			<th id='headerName'>
				<input type="checkbox" id="select_all" />
				<span class='name'><?php p($l->t( 'Name' )); ?></span>
				<span class='selectedActions'>
					<?php if($_['allowZipDownload']) : ?>
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
				<?php if ($_['permissions'] & OCP\PERMISSION_DELETE): ?>
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