<?php

$tabs = fw()->backend->render_options(
	array(
		'icon-fonts' => array(
			'type' => 'tab',
			'title' => __('Icons', 'fw'),
			'lazy_tabs' => false,
			'options' => array(
				'icon-font' => array(
					'type' => 'html-full',
					'attr' => array('class' => 'fw-icon-v2-icons-library'),
					'label' => false,
					'html' => '{{{data.icons_library_html}}}',
				)
			)
		),

		'favorites' => array(
			'type' => 'tab',
			'attr' => array('class' => '.fw-icon-v2-favorites'),
			'title' => __('Favorites', 'fw'),
			'lazy_tabs' => false,
			'options' => array(
				'icon-font-favorites' => array(
					'type' => 'html-full',
					'label' => false,
					'html' => '{{{data.favorites_list_html}}}'
				)
			)
		),

		'custom-upload' => array(
			'type' => 'tab',
			'lazy_tabs' => false,
			'title' => __('Upload', 'fw'),
			'options' => array(
				'custom-upload' => array(
					'type' => 'upload',
					'label' => __('Upload Icon', 'fw')
				)
			)
		)
	),

	/** $values */
	array(),

	array(
		'id_prefix' => 'fw-option-type-iconv2-',
		'name_prefix' => 'fw_option_type_iconv2'
	)
);

?>

<script type="text/html" id="tmpl-fw-icon-v2-tabs">

<?php echo $tabs; ?>

</script>

<script type="text/html" id="tmpl-fw-icon-v2-library">

<div class="fw-icon-v2-toolbar">
	<input 
		type="text"
		placeholder="<?php echo __('Search Icon', 'fw'); ?>"
		class="fw-option fw-option-type-text">

	<select class="fw-selectize">
		<option selected value="all">
			<?php echo __('All Packs', 'fw'); ?>
		</option>

		<# _.each(data.packs, function (pack) { #>
			<option value="{{pack.name}}">{{pack.title}}</option>
		<# }) #>
	</select>
</div>

<div class="fw-icon-v2-library-packs-wrapper">
	<# if (data.packs.length > 0) { #>
		<# var template = wp.template('fw-icon-v2-packs'); #>

		{{{ template(data) }}}
	<# } #>
</div>

</script>

<script type="text/html" id="tmpl-fw-icon-v2-packs">
	<# _.each(data.packs, function (pack) { #>
		<# if (pack.icons.length === 0) { return; } #>

		<h2>
			<span>{{pack.title}}</span>
		</h2>

		{{{
			wp.template('fw-icon-v2-icons-collection')(
				_.extend({}, pack, {
					current_state: data.current_state,
					favorites: data.favorites
				})
			)
		}}}
	<# }) #>
</script>

<script type="text/html" id="tmpl-fw-icon-v2-favorites">

<div class="fw-icon-v2-icon-favorites">
	<# if (data.favorites.length === 0) { #>

		<h4>You have no favorite icons yet.</h4>
		<p>
			To add icons here, simply click on the star 
			(<i class="fw-icon-v2-info dashicons dashicons-star-filled"></i>)
			button that's on top of each icon.
		</p>

	<# } else { #>

		{{{
			wp.template('fw-icon-v2-icons-collection')(
				_.extend({}, {icons: data.favorites, current_state: data.current_state})
			)
		}}}

	<# } #>
</div>

</script>

<script type="text/html" id="tmpl-fw-icon-v2-icons-collection">

	<# if (data.icons.length > 0) { #>
		<ul class="fw-icon-v2-library-pack">

		<# _.each(data.icons, function (icon) { #>
			<# var iconClass = data.css_class_prefix ? data.css_class_prefix + ' ' + icon : icon; #>
			<# var selectedClass = data.current_state['icon-class'] === iconClass ? 'selected' : ''; #>
			<# var favoriteClass = _.contains(data.favorites, iconClass) ? 'fw-icon-v2-favorite' : '' #>

			<li
				data-fw-icon-v2="{{data.css_class_prefix}} {{icon}}"
				class="fw-icon-v2-library-icon {{selectedClass}} {{favoriteClass}}">

				<i class="{{iconClass}}"></i>

				<a
					title="<?php echo __('Add to Favorites', 'fw') ?>"
					class="fw-icon-v2-favorite">

					<i class="dashicons dashicons-star-filled"></i>
				</a>
			</li>

		<# }) #>
		</ul>
	<# } #>

</script>

<?php

/* 			<li class="fw-icon-v2-library-icon" data-fw-icon-v2="{{icon}}"> */
/* 				<i class="{{icon}}"> */
/* 				<a title="<?php __('Add To Favorites', 'fw'); ?>" */
/* 					class="fw-icon-v2-favorite"> */
/* 					<i class="dashicons dashicons-star-filled"></i> */
/* 				</a> */
/* 			</li> */

?>
