(function ($) {
	var $rootClass = '.fw-option-type-icon-v2';

	/**
	 * We'll have this HTML structure
	 *
	 * <div class="fw-icon-v2-preview-wrapper>
	 *   <div class="fw-icon-v2-preview">
	 *     <i></i>
	 *     <button class="fw-icon-v2-remove-icon"></button>
	 *   </div>
	 *
	 *   <button class="fw-icon-v2-trigger-modal">Add Icon</div>
	 * </div>
	 */

	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find($rootClass).toArray().map(renderSinglePreview);
	});

	$(document).on('click', '.fw-icon-v2-remove-icon', removeIcon);
	$(document).on('click', '.fw-icon-v2-trigger-modal', getNewIcon);
	$(document).on('click', '.fw-icon-v2-preview', getNewIcon);

	/**
	 * For debugging purposes
	 */
	function refreshEachIcon () { $($rootClass).toArray().map(refreshSinglePreview); }

	function getNewIcon (event) {
		event.preventDefault();

		var $root = $(this).closest($rootClass);
		var modalSize = $root.attr('data-fw-modal-size');

		/**
		 * fw.OptionsModal should execute it's change:values callbacks
		 * only if the picker was changed. That's why we introduce unique-id
		 * for each picker.
		 */
		if (! $root.data('unique-id')) {
			$root.data('unique-id', fw.randomMD5());
		}

		fwOptionTypeIconV2Instance.set('size', modalSize);

		fwOptionTypeIconV2Instance.open(getDataForRoot($root))
			.then(function (data) {
				setDataForRoot($root, data);
			})
			.fail(function () {
				// modal closed without save
			});

        /*
		fwOptionTypeIconV2Picker.pick(
			getDataForRoot($root),
			$root.data('unique-id'),
			function (data) {
				setDataForRoot(
					$root,
					data
				);
			},
			modalSize
		);
        */
	}

	function removeIcon (event) {
		event.preventDefault();
		event.stopPropagation();

		var $root = $(this).closest($rootClass);

		if (getDataForRoot($root)['type'] === 'icon-font') {
			setDataForRoot($root, {
				'icon-class': ''
			});
		}

		if (getDataForRoot($root)['type'] === 'custom-upload') {
			setDataForRoot($root, {
				'attachment-id': '',
				'url': ''
			});
		}
	}

	function renderSinglePreview ($root) {
		$root = $($root);

		/**
		* Skip element if it's already activated
		*/
		if ( $root.hasClass('fw-activated') ) {
			return;
		}

		$root.addClass('fw-activated');

		var $wrapper = $('<div>', {
			class: 'fw-icon-v2-preview-wrapper',
			'data-icon-type': getDataForRoot($root)['type']
		});

		var $preview = $('<div>', {
			class: 'fw-icon-v2-preview',
		}).append(
			$('<i>')
		).append(
			$('<a>', {
				class: 'fw-icon-v2-remove-icon dashicons fw-x',
				html: ''
			})
		);

		$wrapper.append(
			$preview
		).append(
			$('<button>', {
				class: 'fw-icon-v2-trigger-modal button-secondary button-large',
				type: 'button',
				html: fw_icon_v2_data.add_icon_label
			})
		);

		$wrapper.appendTo( $root );

		refreshSinglePreview( $root );
	}

	function refreshSinglePreview ($root) {
		$root = $($root);

		var data = getDataForRoot( $root );

		$root.find('.fw-icon-v2-trigger-modal').text(
			fw_icon_v2_data[
				hasIcon(data) ? 'edit_icon_label' : 'add_icon_label'
			]
		);

		$root.find('.fw-icon-v2-preview-wrapper')
			.removeClass('fw-has-icon')
			.addClass(
				hasIcon(data) ? 'fw-has-icon' : ''
			);

		$root.find('.fw-icon-v2-preview-wrapper').attr(
			'data-icon-type',
			data['type']
		);

		if (data.type === 'icon-font') {
			$root.find('i').attr('class', data['icon-class']);
			$root.find('i').attr('style', '');
		}

		if (data.type === 'custom-upload') {
			if (hasIcon(data)) {
				$root.find('i').attr(
					'style',
					'background-image: url("' + data['url'] + '");'
				);

				$root.find('i').attr('class', '');
			} else {
				$root.find('i').attr(
					'style',
					''
				);

				$root.find('i').attr('class', '');
			}
		}

		function hasIcon (data) {
			if (data.type === 'icon-font') {
				if (data['icon-class'] && data['icon-class'].trim() !== '') {
					return true;
				}
			}

			if (data.type === 'custom-upload') {
				if (data['url'].trim() !== '') {
					return true;
				}
			}

			return false;
		}
	}

	function getDataForRoot ($root) {
		return JSON.parse($root.find('input').val());
	}

	function setDataForRoot ($root, data) {
		var currentData = getDataForRoot($root);

		$root.find('input').val(
			JSON.stringify(
				_.omit(
					_.extend(
						{},
						currentData,
						data
					),

					'attachment'
				)
			)
		).trigger('change');

		refreshSinglePreview($root);
	}

}(jQuery));

