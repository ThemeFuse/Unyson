<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $request_filesystem_credentials
 */

echo $request_filesystem_credentials;

?>

<div id="backup-restore-container" class="backup-restore-container" style="display: none;">
	<div class="backup-restore-overlay"></div>
	<div class="backup-restore-modal">
		<div class="backup-restore-modal-vertical">
			<h2><i class="backup-spinner spinner"></i> Restore in Progress</h2>
			<p><i>We are currently restoring your backup.<br>This may take up to a few minutes.</i></p>
		</div>
	</div>
</div>
