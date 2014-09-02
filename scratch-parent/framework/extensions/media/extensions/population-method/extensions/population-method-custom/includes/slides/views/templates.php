<?php if (!defined('FW')) die('Forbidden'); ?>
<script class="default-slide" type="text/template">
	<div class="fw-slide default slide-{{=i}}"  data-order="{{=i}}">
		<?php
		echo fw()->backend->render_options($slides_options, $values, array(
			'id_prefix' => $data['id_prefix'] . $id . '-' . '{{=i}}' . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . '][' . '{{=i}}' . ']',
		));?>
	</div>
</script>
<script class="default-thumb" type="text/template">
	<li data-order="{{=i}}">
		<div class="delete-btn"></div>
	<img src="{{=src}}" height="<?php echo $thumb_size['height'] ?>" width="<?php echo $thumb_size['width']?>"/>
		<?php echo fw()->backend->option_type('hidden')->render('thumb', array('value' => '{{=src}}'), array(
			'id_prefix' => $data['id_prefix'] . $id . '-' . '{{=i}}' . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . '][' . '{{=i}}' . ']',
		));?>
	</li>
</script>
