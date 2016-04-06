(function ($, fwe) {

	var init = function () {
		var id = $(this).find('.wp-editor-area').attr('id');

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
		}
	};

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-wp-editor:not(.fw-option-initialized)')
			.each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, fwEvents);

