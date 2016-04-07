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
						id = editor.id;

					editor.on('change', function(){ editor.save(); });

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

						(function($wrap) {
							$wrap.find('.switch-'+ ($wrap.hasClass('tmce-active') ? 'tmce' : 'html')).trigger('click');
						})($textarea.closest('.wp-editor-wrap'));
					}
				});
			};

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

