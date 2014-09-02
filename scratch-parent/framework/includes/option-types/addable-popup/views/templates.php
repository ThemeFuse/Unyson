<?php if (!defined('FW')) die('Forbidden');?>
<script class="default-slide" type="text/template">
	<div class=" default slide-{{=i}}"  data-order="{{=i}}">
		<?php
		echo fw()->backend->render_options($slides_options, $values, array(
			'id_prefix' => $data['id_prefix'] . $id . '-' . '{{=i}}' . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . '][' . '{{=i}}' . ']',
		));?>
</div>
</script>
