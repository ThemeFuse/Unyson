(function ($, fwe, _) {

	fwe.one('fw:option-type:builder:init', function (data) {

		if (!data.$elements.length) {
			return;
		}

		var $builders = data.$elements;

		$builders.each(function () {

			var elements = {$builder: $(this)},
				saveStateFlag = true,
				type = $(this).attr('data-builder-option-type');

			elements.$navigation = elements.$builder.find('.builder-root-items .navigation');


			if (elements.$navigation.length === 0) {
				elements.$builder.find('.builder-root-items').append('<div class="navigation"></div>');
				elements.$navigation = elements.$builder.find('.builder-root-items .navigation');
			}
			elements.$navigation.append('<div class="history-container"><a class="disabled undo" href="#">Undo</a><a class="disabled redo" href="#">Redo</a></div>');
			elements.$undo = elements.$navigation.find('.undo');
			elements.$redo = elements.$navigation.find('.redo');

			var utils = {
				disableUndo: function () {
					elements.$undo.addClass('disabled');
				},
				enableUndo: function () {
					elements.$undo.removeClass('disabled');
				},
				disableRedo: function () {
					elements.$redo.addClass('disabled');
				},
				enableRedo: function () {
					elements.$redo.removeClass('disabled');
				}
			};

			var history = {
				storage: [],
				activeIndex: 0,
				undo: function () {
					--this.activeIndex;

					if ((this.activeIndex === 0)) {
						utils.disableUndo();
					}

					return this.storage[this.activeIndex];
				},
				redo: function () {
					++this.activeIndex;
					if (this.activeIndex === this.storage.length - 1) {
						utils.disableRedo();
					}

					return this.storage[this.activeIndex];
				},
				saveState: function (item) {
					this.storage = _.initial(this.storage, (this.storage.length-1) - this.activeIndex);
					this.storage.push(item);
					this.activeIndex = this.storage.length - 1;
				}
			};

			fwe.one('fw-builder:' + type + ':register-items', function (builder) {

				//For first time;
				history.saveState(builder.$input.val());

				builder.$input.on('fw-builder:input:change', function () {

					if (true === saveStateFlag) {
						history.saveState($(this).val());
						utils.enableUndo();
						utils.disableRedo();
					} else {
						saveStateFlag = true;
					}
				});

				elements.$undo.on('click', function (e) {

					e.preventDefault();

					if ($(this).hasClass('disabled')) {
						return;
					}
					utils.enableUndo();
					utils.enableRedo();

					saveStateFlag = false;

					var undoSnapshot = history.undo();


					if (undoSnapshot !== undefined) {
						builder.rootItems.reset(JSON.parse(undoSnapshot));
					} else {
						utils.disableUndo();
					}
				});

				elements.$redo.on('click', function (e) {
					e.preventDefault();

					if ($(this).hasClass('disabled')) {
						return;
					}

					utils.enableRedo();
					utils.enableUndo();

					saveStateFlag = false;

					var redoSnapshot = history.redo();

					if (redoSnapshot !== undefined) {
						builder.rootItems.reset(JSON.parse(redoSnapshot));
					} else {
						utils.disableRedo();
					}
				});
			});
		});
	});
})(jQuery, fwEvents, _);