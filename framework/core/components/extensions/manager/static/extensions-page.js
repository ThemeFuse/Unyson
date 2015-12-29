jQuery(function ($) {
	fw.qtip( $('.fw-extensions-list .fw-extensions-list-item .fw-extension-tip') );
});

/**
 * Install/Remove/... via popup if has direct filesystem access (no ftp credentials required)
 */
jQuery(function($){
	var inst = {
		isBusy: false,
		eventNamespace: '.fw-extension',
		$wrapper: $('.wrap'),
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
					if (confirmMessage) {
						if (!confirm(confirmMessage)) {
							inst.isBusy = false;
							inst.loading($form, false);
						}
					}

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'fw_extensions_'+ $form.attr('data-extension-action'),
							extension: $form.attr('data-extension-name')
						},
						dataType: 'json'
					}).done(function(r) {
						if (r.success) {
							window.location.reload();
						} else {
							var error = r.data ? r.data.pop().message : 'Error';

							fw.soleModal.show(
								'fw-extension-install-error',
								'<p class="fw-text-danger">'+ error +'</p>'
							);
						}
					}).fail(function(jqXHR, textStatus, errorThrown){
						fw.soleModal.show(
							'fw-extension-install-error',
							'<p class="fw-text-danger">'+ String(errorThrown) +'</p>'
						);
						inst.isBusy = false;
						inst.loading($form, false);
					});
				} else {
					inst.stopListeningSubmit();
					$form.submit();
				}
			}).fail(function(jqXHR, textStatus, errorThrown){
				inst.stopListeningSubmit();
				$form.submit();
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