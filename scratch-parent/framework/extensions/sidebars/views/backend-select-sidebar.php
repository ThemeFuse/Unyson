<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="fw-ext-sidebars-location empty <?php echo $id?> <?php echo $color;?>" data-color="<?php echo $color;?>">
	<select class="sidebar-selectize <?php echo $id?>-select">
		<?php if (isset($sidebars) and is_array($sidebars)) :?>
			<?php foreach($sidebars as $sidebar):?>
				<option value="<?php echo $sidebar->get_id() ?>"><?php echo $sidebar->get_name(); ?></option>
			<?php endforeach;?>
		<?php endif;?>
	</select>
</div>

