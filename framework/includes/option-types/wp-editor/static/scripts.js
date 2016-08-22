(function ($, fwe) {

	var init = function () {
		var $option = $(this),
			$textarea = $option.find('.wp-editor-area:first'),
			id = $option.attr('data-fw-editor-id'),
			$wrap = $textarea.closest('.wp-editor-wrap'),
			visualMode = (typeof $option.attr('data-mode') != 'undefined')
				? ($option.attr('data-mode') == 'tinymce')
				: $wrap.hasClass('tmce-active'),
			hasAutoP = $option.attr('data-fw-has-autop') === 'true';

		/**
		 * Dynamically set tinyMCEPreInit.mceInit and tinyMCEPreInit.qtInit
		 * based on the data-fw-mce-settings and data-fw-qt-settings
		 */
		var mceSettings = JSON.parse($option.attr('data-fw-mce-settings'));
		var qtSettings = JSON.parse($option.attr('data-fw-qt-settings'));

		tinyMCEPreInit.mceInit[ id ] = mceSettings;
		tinyMCEPreInit.qtInit[ id ] = qtSettings;

		/**
		 * Set width
		 */
		$option.closest('.fw-backend-option-input-type-wp-editor').addClass(
			'width-type-'+ ($option.attr('data-size') == 'large' ? 'full' : 'fixed')
		);

		/**
		 * TinyMCE Editor
		 * http://stackoverflow.com/a/21519323/1794248
		 */
		if (mceSettings) {
			if (typeof tinyMCEPreInit.mceInit[ id ] == 'undefined') {
				console.error('Can\'t find "'+ id +'" in tinyMCEPreInit.mceInit');
				return;
			}

			tinymce.execCommand('mceRemoveEditor', false, id);

			tinyMCEPreInit.mceInit[ id ].setup = function(ed) {
				ed.once('init', function (e) {
					var editor = e.target,
						id = editor.id;

					editor.on('change', function(){ editor.save(); });

					/**
					 * Fixes when wpautop is false
					 */
					if (!editor.getParam('wpautop')) {
						editor
							.on('SaveContent', function(event){
								// Remove <p> in Visual mode
								if (event.content && $wrap.hasClass('tmce-active')) {
									event.content = wp.editor.removep(event.content);
								}
							})
							.on('BeforeSetContent', function(event){
								// Prevent inline all content when switching from Text to Visual mode
								if (event.content && !$wrap.hasClass('tmce-active')) {
									event.content = wp.editor.autop(event.content);
								}
							});
					}

					/**
					 * Quick Tags
					 * http://stackoverflow.com/a/21519323/1794248
					 */
					{
						new QTags( tinyMCEPreInit.qtInit[ id ] );

						QTags._buttonsInit();

						if ($wrap.hasClass('html-active')) {
							$wrap.find('.switch-html').trigger('click');
						}

						$wrap.find('.switch-'+ (visualMode ? 'tmce' : 'html')).trigger('click');

						if (!hasAutoP && !visualMode) {
							$textarea.val(wp.editor.removep(editor.getContent()));
						}
					}
				});
			};

			if (!tinyMCEPreInit.mceInit[ id ].wpautop) {
				$textarea.val( wp.editor.autop($textarea.val()) );
			}

			try {
				tinymce.init( tinyMCEPreInit.mceInit[ id ] );

				// Remove garbage. This caused lag on page scroll after OptionsModal with wp-editor close
				$option.on('remove', function(){ tinymce.execCommand('mceRemoveEditor', false, id); });
			} catch(e){
				console.error('wp-editor init error', id, e);
				return;
			}

			// fixes https://github.com/ThemeFuse/Unyson/issues/1615
			if (typeof window.wpLink != 'undefined') {
				try {
					// do not do .open() // fixes https://github.com/ThemeFuse/Unyson/issues/1901

					window.wpLink.close();

					/**
					 * hide link edit toolbar on wp-editor destroy (on options modal close)
					 */
					$option.one('remove', function () {
						window.wpLink.close();
					});
				} catch (e) {
					$('#wp-link-wrap,#wp-link-backdrop').css('display', '');
				}
			}
		} else {
			/**
			 * Quick Tags
			 * http://stackoverflow.com/a/21519323/1794248
			 */
			{
				new QTags( tinyMCEPreInit.qtInit[ id ] );

				QTags._buttonsInit();
			}
		}
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-wp-editor:not(.fw-option-initialized)')
			.each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);

/**
 * Find all wp-editor option types from container
 * and give them new IDs (random MD5).
 *
 * Copy their preinit data from currentId.
 *
 * The main callback we have below will take care about populating
 * tinyMCEPreInit.mceInit and tinyMCEPreInit.qtInit for them.
 */
function fwWpEditorRefreshIds(currentId, container) {
	_.map(
		jQuery(container).find('.fw-option-type-wp-editor').toArray(),
		refreshEditor
	);

	function refreshEditor (editor) {
		var html = jQuery(editor).clone().wrap('<p>').parent().html();

		var regexp = new RegExp(currentId, 'g');
		html = html.replace(regexp, fw.randomMD5());

		jQuery(editor).replaceWith(html);
	}
}

