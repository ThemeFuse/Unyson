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
				'upload-custom-icon-recents' => array(
					'type' => 'html-full',
					'label' => false,
					'html' => '{{{data.recently_used_custom_uploads_html}}}'
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
	<select class="fw-selectize">
		<# _.each(data.packs, function (pack, index) { #>
			<option {{ index === 0 ? 'selected' : '' }} value="{{pack.name}}">
				{{pack.title}}
			</option>
		<# }) #>
	</select>
	
	<input 
		type="text"
		placeholder="<?php echo __('Search Icon', 'fw'); ?>"
		class="fw-option fw-option-type-text">
</div>

<div class="fw-icon-v2-library-packs-wrapper">
	<# if (data.packs.length > 0) { #>
		<# var template = wp.template('fw-icon-v2-packs'); #>
		<# data.packs = data.pack_to_select #>
		<# data.should_have_headings = false #>

		{{{ template(data) }}}
	<# } #>
</div>

</script>

<script type="text/html" id="tmpl-fw-icon-v2-packs">
	<# _.each(data.packs, function (pack) { #>
		<# if (pack.icons.length === 0) { return; } #>

		<# if (data.should_have_headings) { #>
			<h2>
				<span>{{pack.title}}</span>
			</h2>
		<# } #>

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

<div class="fw-icon-v2-library-packs-wrapper fw-favorite-icons-wrapper">
	<# var favorites = _.filter(data.favorites, _.compose(_.isNaN, _.partial(parseInt, _, 10))) #>

	<# if (favorites.length === 0) { #>

		<div class="fw-icon-v2-note">
			<h3>You have no favorite icons yet.</h3>

			<p>
				To add icons here, simply click on the star 
				(<i class="fw-icon-v2-info dashicons dashicons-star-filled"></i>)
				button that's on top right corner of each icon.
			</p>
		</div>

	<# } else { #>

		{{{
			wp.template('fw-icon-v2-icons-collection')(
				_.extend({}, {icons: favorites, current_state: data.current_state})
			)
		}}}

	<# } #>
</div>

</script>

<script type="text/html" id="tmpl-fw-icon-v2-recent-custom-icon-uploads">
<# var recent_uploads = _.filter(data.favorites, _.compose(_.negate(_.isNaN), _.partial(parseInt, _, 10))) #>

<div class="fw-icon-v2-toolbar">
	<h3>Upload an icon</h3>

	<button type="button" class="fw-icon-v2-custom-upload-perform button primary">
		Upload
	</button>
</div>

<# if (recent_uploads.length === 0) { #>

	<h4>You have no uploaded icons.</h4>

	<p>
		You can simply click on the Upload button to upload more icons and
		use them right away.
	</p>

<# } else { #>
	
	<div class="fw-icon-v2-library-packs-wrapper">
		<ul class="fw-icon-v2-library-pack">

		<# _.each(recent_uploads, function (attachment_id) { #>
			<# var selectedClass = data.current_state['attachment-id'] === attachment_id ? 'selected' : ''; #>
			<# url = _.min(_.values(wp.media.attachment(attachment_id).get('sizes')), function (size) {
				return size.width;
			}).url; #>

			<li
				data-fw-icon-v2="{{ attachment_id }}"
				class="fw-icon-v2-library-icon {{selectedClass}}">

				<div class="fw-icon-inner">
					<img src="{{ url }}" style="max-width: 100%" alt="">

					<a
						title="<?php echo __('Add to Favorites', 'fw') ?>"
						class="fw-icon-v2-favorite dashicons dashicons-no">
					</a>
				</div>
			</li>

		<# }) #>

			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			
		</ul>
	</div>

<# } #>

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

				<div class="fw-icon-inner">
					<i class="{{iconClass}}"></i>

					<a
						title="<?php echo __('Add to Favorites', 'fw') ?>"
						class="fw-icon-v2-favorite dashicons dashicons-star-filled">
					</a>
				</div>
			</li>

		<# }) #>

			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			<li class="fw-ghost-item"></li>
			
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
