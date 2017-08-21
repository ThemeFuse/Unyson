(function($) {
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

	fwEvents.on('fw:options:init', function(data) {
		data.$elements.find($rootClass).toArray().map(renderSinglePreview);
	});

	$(document).on('click', '.fw-icon-v2-remove-icon', removeIcon);
	$(document).on('click', '.fw-icon-v2-trigger-modal', getNewIcon);
	$(document).on('click', '.fw-icon-v2-preview', getNewIcon);

	/**
	 * For debugging purposes
	 */
	function refreshEachIcon() {
		$($rootClass).toArray().map(refreshSinglePreview);
	}

	function getNewIcon(event) {
		event.preventDefault();

		var $root = $(this).closest($rootClass);
		var modalSize = $root.attr('data-fw-modal-size');

		/**
		 * fw.OptionsModal should execute it's change:values callbacks
		 * only if the picker was changed. That's why we introduce unique-id
		 * for each picker.
		 */
		if (!$root.data('unique-id')) {
			$root.data('unique-id', fw.randomMD5());
		}

		fwOptionTypeIconV2Instance.set('size', modalSize);

		fwOptionTypeIconV2Instance
			.open(getDataForRoot($root))
			.then(function(data) {
				setDataForRoot($root, data);
			})
			.fail(function() {
				// modal closed without save
			});
	}

	function removeIcon(event) {
		event.preventDefault();
		event.stopPropagation();

		setDataForRoot($(this).closest($rootClass), {
			type: 'none',
			'icon-class': '',
			'url': '',
			'attachment-id': ''
		});
	}

	function renderSinglePreview($root) {
		$root = $($root);

		/**
		* Skip element if it's already activated
		*/
		if ($root.hasClass('fw-activated')) {
			return;
		}

		$root.addClass('fw-activated');

		var $wrapper = $('<div>', {
			class: 'fw-icon-v2-preview-wrapper',
			'data-icon-type': getDataForRoot($root)['type'],
		});

		var $preview = $('<div>', {
			class: 'fw-icon-v2-preview',
		})
			.append($('<i>'))
			.append(
				$('<a>', {
					class: 'fw-icon-v2-remove-icon dashicons fw-x',
					html: '',
				})
			);

		$wrapper.append($preview).append(
			$('<button>', {
				class: 'fw-icon-v2-trigger-modal button-secondary button-large',
				type: 'button',
				html: fw_icon_v2_data.add_icon_label,
			})
		);

		$wrapper.appendTo($root);

		if (getDataForRoot($root)['type'] === 'custom-upload') {
			var media = wp.media.attachment(
				getDataForRoot($root)['attachment-id']
			);
			
			if (! media.get('url')) {
				media.fetch().then(function () {
					refreshSinglePreview($root);
				});
			}
		}

		refreshSinglePreview($root);
	}

	function refreshSinglePreview($root) {
		$root = $($root);

		var data = getDataForRoot($root);

		$root
			.find('.fw-icon-v2-trigger-modal')
			.text(
				fw_icon_v2_data[
					hasIcon(data) ? 'edit_icon_label' : 'add_icon_label'
				]
			);

		$root
			.find('.fw-icon-v2-preview-wrapper')
			.removeClass('fw-has-icon')
			.addClass(hasIcon(data) ? 'fw-has-icon' : '');

		$root
			.find('.fw-icon-v2-preview-wrapper')
			.attr('data-icon-type', data['type']);

		$root.find('i').attr('class', '');
		$root.find('i').attr('style', '');

		if (data.type === 'icon-font') {
			$root.find('i').attr('class', data['icon-class']);
		}

		if (data.type === 'custom-upload') {
			if (hasIcon(data)) {
				$root
					.find('i')
					.attr(
						'style',
						'background-image: url("' +
						// Insert the smallest possible image in the preview
						_.min(
							_.values(wp.media.attachment(
								data['attachment-id']
							).get('sizes')),
							function (size) {return size.width}
						).url +
						'");'
					);
			}
		}

		function hasIcon(data) {
			return data.type !== 'none';
		}
	}

	function getDataForRoot($root) {
		return JSON.parse($root.find('input').val());
	}

	function setDataForRoot($root, data) {
		var currentData = getDataForRoot($root);

		var actualValue = _.omit(_.extend({}, currentData, data), 'attachment');

		if (actualValue.type === 'icon-font') {
			if ((actualValue['icon-class'] || "").trim() === '') {
				actualValue.type = 'none';
			}
		}

		if (actualValue.type === 'custom-upload') {
			if (! actualValue['attachment-id']) {
				actualValue.type = 'none';
			}
		}

		$root.find('input').val(JSON.stringify(actualValue)).trigger('change');

		fw.options.trigger.changeForEl($root, {
			value: actualValue,
		});

		refreshSinglePreview($root);
	}

	fw.options.register('icon-v2', {
		startListeningForChanges: $.noop,
		getValue: function(optionDescriptor) {
			return {
				value: JSON.parse($(optionDescriptor.el).find('input').val()),

				optionDescriptor: optionDescriptor,
			};
		},
	});
})(jQuery);
