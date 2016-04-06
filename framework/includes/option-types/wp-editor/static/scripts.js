(function ($, fwe) {

	var init = function () {
		var $option = $(this),
			$textarea = $option.find('.wp-editor-area:first'),
			id = $textarea.attr('id'),
			editor;

		/**
		 * width-type-fixed, width-type-auto, width-type-full
		 */
		$option.closest('.fw-backend-option-input-type-wp-editor').addClass('width-type-fixed');

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

			try {
				tinymce.init( tinyMCEPreInit.mceInit[ id ] );
			} catch(e){
				console.log('wp-editor init error', e);
				return;
			}

			editor = tinymce.get(id);

			editor.on('change', function(){ editor.save(); });
		}

		/**
		 * Quick Tags
		 * http://stackoverflow.com/a/21519323/1794248
		 */
		{
			$( '[id="wp-' + id + '-wrap"]' ).unbind( 'onmousedown' );
			$( '[id="wp-' + id + '-wrap"]' ).bind( 'onmousedown', function(){
				wpActiveEditor = id;
			});

			QTags( tinyMCEPreInit.qtInit[ id ] );
			QTags._buttonsInit();

			$textarea.closest('.fw-option-type-wp-editor').data('editor-type') === 'tinymce'
				? $textarea.closest('.wp-editor-wrap').find('.switch-tmce')
				: $textarea.closest('.wp-editor-wrap').find('.switch-html');
		}
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-wp-editor:not(.fw-option-initialized)')
			.each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);

