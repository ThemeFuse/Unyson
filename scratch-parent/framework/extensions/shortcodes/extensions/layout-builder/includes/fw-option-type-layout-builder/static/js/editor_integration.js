(function($, fwe, data) {
	var gui = {
		elements: {
			$showButton: $('<a href="#" class="button button-primary">' + data.l10n.showButton + '</a>'),
			$hideButton: $('<a href="#" class="button button-primary layout-builder-hide-button">' + data.l10n.hideButton + '</a>'),
			$builderBox: $('#' + data.optionId).closest('.postbox'),
			$builderActiveHidden: $('<input name="layout-builder-active" type="hidden">'),
			$wpPostBodyContent: $('#post-body-content'),
			$wpPostDivRich: $('#postdivrich'),
			$wpContentWrap: $('#wp-content-wrap')
		},
		getWPEditorContent: function() {

			/*
			 * WordPress works with tinyMCE for its WYSIWYG editor
			 * depending on the current editor tab (visual or text)
			 * we need to ask tinyMCE to get the content (in the case of visual tab)
			 * of get the value from the #content textarea (in the case of text tab)
			 */
			if (this.elements.$wpContentWrap.hasClass('tmce-active')) {
				return tinyMCE.get('content').getContent();
			} else {
				return this.elements.$wpContentWrap.find('#content').val();
			}
		},
		clearWPEditorContent: function() {

			/*
			 * WordPress works with tinyMCE for its WYSIWYG editor
			 * depending on the current editor tab (visual or text)
			 * we need to clear tinyMCE instance (in the case of visual tab)
			 * of the value from the #content textarea (in the case of text tab)
			 */
			if (this.elements.$wpContentWrap.hasClass('tmce-active')) {
				return tinyMCE.get('content').setContent('');
			} else {
				return this.elements.$wpContentWrap.find('#content').val('');
			}
		},
		showBuilder: function() {
			this.elements.$wpPostBodyContent.addClass('layout-builder-visible');

			this.elements.$wpPostDivRich.hide();
			this.elements.$hideButton.show();
			this.elements.$builderBox.show();

			window.editorExpand && window.editorExpand.off && window.editorExpand.off();

			// set the hidden to store that the builder is active
			this.elements.$builderActiveHidden.val('true');
		},
		hideBuilder: function() {
			this.elements.$wpPostBodyContent.removeClass('layout-builder-visible');

			this.elements.$hideButton.hide();
			this.elements.$builderBox.hide();
			this.elements.$wpPostDivRich.show();

			window.editorExpand && window.editorExpand.on && window.editorExpand.on();

			// set the hidden to store that the builder is inactive
			this.elements.$builderActiveHidden.val('false');
		},
		initButtons: function() {
			// insert the show button
			$('#wp-content-media-buttons').prepend(this.elements.$showButton);

			// insert the hide button
			this.elements.$wpPostDivRich.before(this.elements.$hideButton);

			if (data.renderInBuilderMode) {
				this.showBuilder();
			} else {
				this.hideBuilder();
			}
		},
		insertHidden: function() {

			/*
			 * whether or not to display the builder at render depends
			 * on a value that is stored in the $builderActiveHidden hidden input
			 */
			this.elements.$builderBox.prepend(this.elements.$builderActiveHidden);
		},
		bindEvents: function() {
			var self = this;

			if (data.renderInBuilderMode) {

				/*
				 * If the page has to render with the builder being active
				 * a one time click event is attached to the hide button
				 * that when clicked will clear the wp editor textarea
				 * (which, at first, holds the shortcode notation generated from the buider)
				 */
				this.elements.$hideButton.one('click', function(e) {
					self.clearWPEditorContent();
					self.elements.$wpContentWrap.find('#content-tmce').trigger('click');
					e.preventDefault();
				});
			} else {

				/*
				 * If the page has to render with wp editor active
				 * a one time click event is attached to the show button
				 * that when clicked will get the content from the wp editor textarea
				 * and create a text_block in the builder that contains that content
				 */
				this.elements.$showButton.one('click', function(e) {
					var wpEditorContent = self.getWPEditorContent();
					if (wpEditorContent) {
						optionTypeLayoutBuilder.initWithTextBlock(self.getWPEditorContent());
					}
					e.preventDefault();
				});
			}

			this.elements.$showButton.on('click', function(e) {
				self.showBuilder();
				e.preventDefault();
			});
			this.elements.$hideButton.on('click', function(e) {
				self.hideBuilder();
				e.preventDefault();
			});
		},
		init: function() {
			this.initButtons();
			this.insertHidden();
			this.bindEvents();
		}
	};
	gui.init();

	/*
	 * The global variable optionTypeLayoutBuilder is created intentionally
	 * to allow creating a text_block shortcode when switching from
	 * the default editor into the visual one for the first time
	 */
	fwe.one('fw-builder:' + 'layout-builder' + ':register-items', function(builder) {
		optionTypeLayoutBuilder = builder;
		optionTypeLayoutBuilder.initWithTextBlock = function(content) {
			this.rootItems.reset([
				{
					type: 'simple',
					subtype: 'text_block',
					optionValues: {text: content}
				}
			]);
		}
	});
})(jQuery, fwEvents, fw_option_type_layout_builder_editor_integration_data);
