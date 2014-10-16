<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $title
 * @var $subtitle
 */

?>
<div id="backup-restore-container" class="backup-restore-container" style="display: none;">
	<div class="backup-restore-overlay"></div>
	<div class="backup-restore-modal">
		<div class="backup-restore-modal-vertical">
			<h2><i class="backup-spinner spinner"></i> <?php echo $title ?></h2>
			<p><i><?php echo str_replace("\n", '<br>', $subtitle) ?></i></p>
		</div>
	</div>
</div>
