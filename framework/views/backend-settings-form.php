<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $options
 * @var array $values
 * @var string $focus_tab_input_name
 * @var string $reset_input_name
 * @var bool $ajax_submit
 * @var bool $side_tabs
 */
?>

<?php if ($side_tabs): ?>
	<div class="fw-settings-form-header fw-row" style="opacity:0;">
		<div class="fw-col-xs-12 fw-col-sm-6">
			<h2><?php echo fw()->theme->manifest->get_name() ?>
				<?php if (fw()->theme->manifest->get('author')): ?>
					<?php
					if (fw()->theme->manifest->get('author_uri')) {
						echo fw_html_tag('a', array(
							'href' => fw()->theme->manifest->get('author_uri'),
							'target' => '_blank'
						), '<small>'. __('by', 'fw') .' '. fw()->theme->manifest->get('author') .'</small>');
					} else {
						echo '<small>'. fw()->theme->manifest->get('author') .'</small>';
					}
					?>
				<?php endif; ?>
			</h2>
		</div>
		<div class="fw-col-xs-12 fw-col-sm-6">
			<div class="form-header-buttons">
				<?php
				echo fw_html_tag('input', array(
					'type' => 'submit',
					'name' => '_fw_reset_options',
					'value' => __('Reset Options', 'fw'),
					'class' => 'button-secondary button-large submit-button-reset',
				))
				?>
				<i class="submit-button-separator"></i>
				<?php
				echo fw_html_tag('input', array(
					'type' => 'submit',
					'name' => '_fw_save_options',
					'value' => __('Save Changes', 'fw'),
					'class' => 'button-primary button-large submit-button-save',
				))
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			fwEvents.on("fw:options:init", function(data){
				// styles are loaded in footer and are applied after page load
				data.$elements.find('.fw-settings-form-header').fadeTo('fast', 1, function(){ $(this).css('opacity', ''); });
			}, 300);
		});
	</script>
<?php endif; ?>

<?php echo fw()->backend->render_options($options, $values); ?>

<div class="form-footer-buttons">
<!-- This div is required to follow after options in order to have special styles in case options will contain tabs (css adjacent selector + ) -->
<?php
	echo fw_html_tag('input', array(
		'type' => 'submit',
		'name' => '_fw_save_options',
		'value' => __('Save Changes', 'fw'),
		'class' => 'button-primary button-large',
	));
	echo ($side_tabs ? '' : ' &nbsp;&nbsp; ');
	echo fw_html_tag('input', array(
		'type' => 'submit',
		'name' => '_fw_reset_options',
		'value' => __('Reset Options', 'fw'),
		'class' => 'button-secondary button-large',
	));
?>
</div>

<!-- focus tab -->
<?php
$focus_tab_id = trim( FW_Request::POST($focus_tab_input_name, FW_Request::GET($focus_tab_input_name, '')) );
echo fw_html_tag('input', array(
	'type'  => 'hidden',
	'name'  => $focus_tab_input_name,
	'value' => $focus_tab_id,
));
?>
<script type="text/javascript">
jQuery(function($){
	fwEvents.one("fw:options:init", function(){
		var $form = $('form[data-fw-form-id="fw_settings"]:first');

		$form.on("click", ".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a", function(){
			$form.find("input[name='<?php echo esc_js($focus_tab_input_name); ?>']").val(
				$(this).attr("href").replace(/^#/, "") // tab id
			);
		});

		/* "wait" after tabs initialized */
		setTimeout(function(){
			fwBackendOptions.openTab($.trim("<?php echo esc_js($focus_tab_id) ?>"));
		}, 200);
	});
});
</script>
<!-- end: focus tab -->

<!-- reset warning -->
<script type="text/javascript">
	jQuery(function($){
		$(document.body).on('click.fw-reset-warning', 'form[data-fw-form-id="fw_settings"] input[name="<?php echo esc_js($reset_input_name) ?>"]', function(e){
			/**
			 * on confirm() the submit input looses focus
			 * fwForm.isAdminPage() must be able to select the input to send it in _POST
			 * so use alternative solution http://stackoverflow.com/a/5721762
			 */
			{
				$(this).closest('form').find('input[type="submit"][clicked]').removeAttr('clicked');
				$(this).attr('clicked', '');
			}

			if (!confirm('<?php
				echo esc_js(__("Click OK to reset.\nAll settings will be lost and replaced with default settings!", 'fw'))
			?>')) {
				e.preventDefault();
				$(this).removeAttr('clicked');
			}
		});
	});
</script>
<!-- end: reset warning -->

<?php if ($ajax_submit): ?>
<!-- ajax submit -->
<script type="text/javascript">
	jQuery(function ($) {
		function isReset($submitButton) {
			return $submitButton.length && $submitButton.attr('name') == '<?php echo esc_js($reset_input_name) ?>';
		}

		var formSelector = 'form[data-fw-form-id="fw_settings"]';

		fwForm.initAjaxSubmit({
			selector: formSelector,
			loading: function(elements, show) {
				if (show) {
					var title, description;

					if (isReset(elements.$submitButton)) {
						title = '<?php echo esc_js(__('Resetting', 'fw')) ?>';
						description =
							'<?php echo esc_js(__('We are currently resetting your settings.', 'fw')) ?>'+
							'<br/>'+
							'<?php echo esc_js(__('This may take a few moments.', 'fw')) ?>';
					} else {
						title = '<?php echo esc_js(__('Saving', 'fw')) ?>';
						description =
							'<?php echo esc_js(__('We are currently saving your settings.', 'fw')) ?>'+
							'<br/>'+
							'<?php echo esc_js(__('This may take a few moments.', 'fw')) ?>';
					}

					fw.soleModal.show(
						'fw-options-ajax-save-loading',
						'<h2 class="fw-text-muted">'+
							'<img src="'+ fw.img.loadingSpinner +'" style="vertical-align: bottom;" /> '+
							title +
						'</h2>'+
						'<p class="fw-text-muted"><em>'+ description +'</em></p>',
						{
							autoHide: 60000,
							allowClose: false
						}
					);
				} else {
					// fw.soleModal.hide('fw-options-ajax-save-loading'); // we need to show loading until the form reset ajax will finish
				}
			},
			onErrors: function() {
				fw.soleModal.hide('fw-options-ajax-save-loading');
			},
			onAjaxError: function() {
				fw.soleModal.hide('fw-options-ajax-save-loading');
			},
			onSuccess: function(elements, ajaxData) {
				/**
				 * Display messages
				 */
				do {
					/**
					 * Don't display the "Settings successfully saved" message
					 * users will click often on the Save button, it's obvious it was saved if no error is shown.
					 */
					delete ajaxData.flash_messages.success.fw_settings_form_save;

					if (
						_.isEmpty(ajaxData.flash_messages.error)
						&&
						_.isEmpty(ajaxData.flash_messages.warning)
						&&
						_.isEmpty(ajaxData.flash_messages.info)
						&&
						_.isEmpty(ajaxData.flash_messages.success)
					) {
						// no messages to display
						break;
					}

					var noErrors = _.isEmpty(ajaxData.flash_messages.error) && _.isEmpty(ajaxData.flash_messages.warning);

					fw.soleModal.show(
						'fw-options-ajax-save-success',
						'<div style="margin: 0 35px;">'+ fw.soleModal.renderFlashMessages(ajaxData.flash_messages) +'</div>',
						{
							autoHide: noErrors
								? 1000 // hide fast the message if everything went fine
								: 10000,
							showCloseButton: false,
							hidePrevious: noErrors ? false : true // close and open popup when there are errors
						}
					);
				} while(false);

				/**
				 * Refresh form html on Reset
				 */
				if (isReset(elements.$submitButton)) {
					jQuery.ajax({
						type: "GET",
						dataType: 'text'
					}).done(function(html){
						fw.soleModal.hide('fw-options-ajax-save-loading');

						var $form = jQuery(formSelector, html);
						html = undefined; // not needed anymore

						if (!$form.length) {
							alert('Can\'t find the form in the ajax response');
							return;
						}

						// waitSoleModalFadeOut -> formFadeOut -> formReplace -> formFadeIn
						setTimeout(function(){
							elements.$form.css('transition', 'opacity ease .3s');
							elements.$form.css('opacity', '0');
							setTimeout(function() {
								var focusTabId = elements.$form.find("input[name='<?php echo esc_js($focus_tab_input_name); ?>']").val();
								var scrollTop = jQuery(window).scrollTop();

								// replace form html
								{
									elements.$form.css({
										'display': 'block',
										'height': elements.$form.height() +'px'
									});
									elements.$form.get(0).innerHTML = $form.get(0).innerHTML;
									$form = undefined; // not needed anymore
									elements.$form.css({
										'display': '',
										'height': ''
									});
								}

								fwEvents.trigger('fw:options:init', {$elements: elements.$form});

								fwBackendOptions.openTab(focusTabId);

								jQuery(window).scrollTop(scrollTop);

								// fadeIn
								{
									elements.$form.css('opacity', '');
									setTimeout(function(){
										elements.$form.css('transition', '');
										elements.$form.css('visibility', '');
									}, 300);
								}
							}, 300);
						}, 300);
					}).fail(function(jqXHR, textStatus, errorThrown){
						fw.soleModal.hide('fw-options-ajax-save-loading');
						elements.$form.css({
							'opacity': '',
							'transition': '',
							'visibility': ''
						});
						console.error(jqXHR, textStatus, errorThrown);
						alert('Ajax error (more details in console)');
					});
				} else {
					fw.soleModal.hide('fw-options-ajax-save-loading');
				}
			}
		});
	});
</script>
<!-- end: ajax submit -->
<?php endif; ?>

<?php if ($side_tabs): ?>
<!-- open all postboxes -->
<script type="text/javascript">
	jQuery(function ($) {
		var execTimeoutId = 0;

		fwEvents.on('fw:options:init', function(data){
			// use timeout to be executed after the script from backend-options.js
			clearTimeout(execTimeoutId);
			execTimeoutId = setTimeout(function(){
				// undo not first boxes auto close
				data.$elements.find('.fw-backend-postboxes > .fw-postbox:not(:first-child)').removeClass('closed');
			}, 10);
		});
	});
</script>
<?php endif; ?>
