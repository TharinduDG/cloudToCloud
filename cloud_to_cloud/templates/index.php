<!--[if IE 8]><style>input[type="checkbox"]{padding:0;}table td{position:static !important;}</style><![endif]-->
<div id="controls">
    <?php print_unescaped($_['breadcrumb']); ?>
    <input type="hidden" name="permissions" value="<?php p($_['permissions']); ?>" id="permissions">
</div>


<table id="filestable">
	<thead>
		<tr>
			<th id='headerName'>
				<!--input type="checkbox" id="select_all" /-->
				<span class='name'><?php p($l->t( 'Name' )); ?></span>
				<span class='selectedActions'>
						<a href="" class="download">
							<img class="svg" alt="Download"
								 src="<?php print_unescaped(OCP\image_path("core", "actions/download.svg")); ?>" />
							<?php p($l->t('Download'))?>
						</a>
				</span>
			</th>
			<th id="headerSize"><?php p($l->t( 'Size' )); ?></th>
			<th id="headerDate">
				<span id="modified"><?php p($l->t( 'Modified' )); ?></span>

			</th>
		</tr>
	</thead>
	<tbody id="fileList">
		<?php print_unescaped($_['fileList']); ?>
	</tbody>
</table>
<div id="editor"></div>
<div id="uploadsize-message" title="<?php p($l->t('Upload too large'))?>">
	<p>
	<?php p($l->t('The files you are trying to upload exceed the maximum size for file uploads on this server.'));?>
	</p>
</div>
<div id="scanning-message">
	<h3>
		<?php p($l->t('Files are being scanned, please wait.'));?> <span id='scan-count'></span>
	</h3>
	<p>
		<?php p($l->t('Current scanning'));?> <span id='scan-current'></span>
	</p>
</div>

