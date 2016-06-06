(function ($, fwe) {

	var init = function () {
		var $option = $(this),
			$textarea = $option.find('.wp-editor-area:first'),
			id = $textarea.attr('id');

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
		{
			if (typeof tinyMCEPreInit.mceInit[ id ] == 'undefined') {
				console.error('Can\'t find "'+ id +'" in tinyMCEPreInit.mceInit');
				return;
			}

			tinymce.execCommand('mceRemoveEditor', false, id);

			tinyMCEPreInit.mceInit[ id ].setup = function(ed) {
				ed.once('init', function (e) {
					var editor = e.target,
						id = editor.id,
						$wrap = $textarea.closest('.wp-editor-wrap'),
						visualMode = (typeof $option.attr('data-mode') != 'undefined')
							? ($option.attr('data-mode') == 'tinymce')
							: $wrap.hasClass('tmce-active');

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


						if (!editor.getParam('wpautop') && !visualMode) {
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
			} catch(e){
				console.error('wp-editor init error', id, e);
				return;
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

