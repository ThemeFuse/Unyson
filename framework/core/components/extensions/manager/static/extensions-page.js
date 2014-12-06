jQuery(function ($) {
	fw.qtip( $('.fw-extensions-list .fw-extensions-list-item .fw-extension-tip') );

	setTimeout(function(){
		$('#fw-extensions-list-wrapper').fadeTo('fast', 1);
	}, 300);
});

/**
 * Install/Remove/... via popup if has direct filesystem access (no ftp credentials required)
 */
jQuery(function($){
	var inst = {
		isBusy: false,
		eventNamespace: '.fw-extension',
		$wrapper: $('.wrap'),
		/**
		 * @param {string} html 'x<tag...>y</tag>z'
		 * @param {string} tag
		 * @returns {string} 'y'
		 */
		extractFirstTagContents: function(html, tag) {
			// 'x<tag...>y' -> 'y'
			$.each(['<'+ tag, '>'], function(i, x){
				html = html.split(x);
				html.shift();
				html = html.join(x);
			});

			// 'x</tag>y' -> 'x'
			{
				var tagEnd = '</'+ tag +'>';

				html = html.split(tagEnd);
				html.pop();
				html = html.join(tagEnd);
			}

			return html;
		},
		listenSubmit: function() {
			this.$wrapper.on('submit'+ this.eventNamespace, 'form.fw-extension-ajax-form', this.onSubmit);
		},
		stopListeningSubmit: function() {
			this.$wrapper.off('submit'+ this.eventNamespace, 'form.fw-extension-ajax-form');
		},
		onSubmit: function(e) {
			e.preventDefault();

			if (inst.isBusy) {
				alert('Working... Please try again later');
				return;
			}

			var $form = $(this);

			var confirmMessage = $form.attr('data-confirm-message');

			if (confirmMessage) {
				if (!confirm(confirmMessage)) {
					return;
				}
			}

			inst.isBusy = true;
			inst.loading($form, true);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'fw_extensions_check_direct_fs_access'
				},
				dataType: 'json'
			}).done(function(data){
				if (data.success) {
					inst.isBusy = true;
					inst.loading($form, true);

					$.ajax({
						url: $form.attr('action') +'&is-ajax-submit',
						type: 'POST',
						data: $form.serialize(),
						dataType: 'html'
					}).done(function(html){
						html = inst.extractFirstTagContents(html, 'body');
						html = '<div>'+ html +'</div>';

						var $content = $(html).find('#wpcontent .wrap').last();
						var success = $content.find('[success]').length != 0;

						if (success) {
							window.location.reload();
						} else {
							if (true) {
								var $lastMessage = $content.find('> p').last();

								if ($lastMessage.find('a').length) {
									// this is not message, these are link printed by WP_Upgrader_Skin::after()
									$lastMessage = $lastMessage.prev();
								}

								alert($lastMessage.text());

								window.location.reload();
							} else {
								inst.stopListeningSubmit();
								$form.submit();
							}
						}
					}).fail(function(jqXHR, textStatus, errorThrown){
						console.log(textStatus);
						inst.isBusy = false;
						inst.loading($form, false);
					});
				} else {
					inst.stopListeningSubmit();
					$form.submit();
				}
			}).fail(function(jqXHR, textStatus, errorThrown){
				console.log(textStatus);
				inst.isBusy = false;
				inst.loading($form, false);
			});
		},
		loading: function($form, show) {
			var $loadingContainer = $form.closest('.fw-extensions-list-item').find('.fw-extensions-list-item-title').first();
			var $loading = $loadingContainer.find('.ajax-form-loading');

			if (!$loading.length) {
				$loadingContainer.append(
					'<span class="ajax-form-loading fw-text-center fw-hidden">'+
						'<img src="'+ fw.img.loadingSpinner +'" />'+
					'</span>'
				);
				$loading = $loadingContainer.find('.ajax-form-loading');
			}

			if (show) {
				$loading.removeClass('fw-hidden');
			} else {
				$loading.addClass('fw-hidden');
			}
		}
	};

	inst.listenSubmit();
});