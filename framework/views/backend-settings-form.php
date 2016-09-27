<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $options
 * @var array $values
 * @var string $reset_input_name
 * @var bool $ajax_submit
 * @var bool $side_tabs
 */
?>

<?php
if (!function_exists('_action_fw_theme_settings_footer_scripts')):

function _action_fw_theme_settings_footer_scripts() {
	?>
	<script type="text/javascript">
		(function ($) {
			var fwLoadingId = 'fw-theme-settings';

			<?php if (wp_script_is('fw-option-types')): ?>
			// there are options on the page. show loading now and hide it after the options were initialized
			{
				fw.loading.show(fwLoadingId);

				fwEvents.one('fw:options:init', function (data) {
					fw.loading.hide(fwLoadingId);
				});
			}
			<?php endif; ?>

			$(function ($) {
				$(document.body).on({
					'fw:settings-form:before-html-reset': function () {
						fw.loading.show(fwLoadingId);
					},
					'fw:settings-form:reset': function () {
						fw.loading.hide(fwLoadingId);
					}
				});
			});
		})(jQuery);
	</script>
<?php
}
add_action('admin_print_footer_scripts', '_action_fw_theme_settings_footer_scripts', 20);

endif;

$texts = apply_filters('fw_settings_form_texts', array(
	'save_button' => __('Save Changes', 'fw'),
	'reset_button' => __('Reset Options', 'fw'),
));
?>

<?php if ($side_tabs): ?>
	<div class="fw-settings-form-header fw-row">
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
				/**
				 * Make sure firs submit button is Save button
				 * because the first button is "clicked" when you press enter in some input
				 * and the form is submitted.
				 * So to prevent form Reset on input Enter, make Save button first in html
				 */

				echo fw_html_tag('input', array(
					'type' => 'submit',
					'name' => '_fw_save_options',
					'class' => 'fw-hidden',
				));
				?>
				<?php
				echo implode(
					'<i class="submit-button-separator"></i>',
					apply_filters('fw_settings_form_header_buttons', array(
						fw_html_tag('input', array(
							'type' => 'submit',
							'name' => '_fw_reset_options',
							'value' => $texts['reset_button'],
							'class' => 'button-secondary button-large submit-button-reset',
						)),
						fw_html_tag('input', array(
							'type' => 'submit',
							'name' => '_fw_save_options',
							'value' => $texts['save_button'],
							'class' => 'button-primary button-large submit-button-save',
						))
					))
				);
				?>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			fwEvents.on('fw:options:init', function(data){
				data.$elements.find('.fw-settings-form-header:not(.initialized)').addClass('initialized');
			});
		});
	</script>
<?php endif; ?>

<?php echo fw()->backend->render_options($options, $values); ?>

<div class="form-footer-buttons">
<!-- This div is required to follow after options in order to have special styles in case options will contain tabs (css adjacent selector + ) -->
<?php echo implode(
	$side_tabs ? ' ' : ' &nbsp;&nbsp; ',
	apply_filters('fw_settings_form_footer_buttons', array(
		fw_html_tag('input', array(
			'type' => 'submit',
			'name' => '_fw_save_options',
			'value' => $texts['save_button'],
			'class' => 'button-primary button-large',
		)),
		fw_html_tag('input', array(
			'type' => 'submit',
			'name' => '_fw_reset_options',
			'value' => $texts['reset_button'],
			'class' => 'button-secondary button-large',
		))
	))
); ?>
</div>

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
				$(this).closest('form').find('[clicked]:submit').removeAttr('clicked');
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

<script type="text/javascript">
	jQuery(function($){
		var $form = $('form[data-fw-form-id="fw_settings"]:first'),
			timeoutId = 0;

		$form.on('change.fw_settings_form_delayed_change', function(){
			clearTimeout(timeoutId);
			/**
			 * Run on timeout to prevent too often trigger (and cpu load) when a bunch of changes will happen at once
			 */
			timeoutId = setTimeout(function () {
				$form.trigger('fw:settings-form:delayed-change');
			}, 333);
		});
	});
</script>

<?php if ($ajax_submit): ?>
<!-- ajax submit -->
<div id="fw-settings-form-ajax-save-extra-message"
     data-html="<?php echo fw_htmlspecialchars(apply_filters('fw_settings_form_ajax_save_loading_extra_message', '')) ?>"></div>
<script type="text/javascript">
	jQuery(function ($) {
		function isReset($submitButton) {
			return $submitButton.length && $submitButton.attr('name') == '<?php echo esc_js($reset_input_name) ?>';
		}

		var formSelector = 'form[data-fw-form-id="fw_settings"]',
			loadingExtraMessage = $('#fw-settings-form-ajax-save-extra-message').attr('data-html');

		$(formSelector).addClass('prevent-all-tabs-init'); // fixes https://github.com/ThemeFuse/Unyson/issues/1491

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
							'<?php echo esc_js(__('This may take a few moments.', 'fw')); ?>';
					}

					fw.soleModal.show(
						'fw-options-ajax-save-loading',
						'<h2 class="fw-text-muted">'+
							'<img src="'+ fw.img.loadingSpinner +'" alt="Loading" class="wp-spinner" /> '+
							title +
						'</h2>'+
						'<p class="fw-text-muted"><em>'+ description +'</em></p>'+ loadingExtraMessage,
						{
							autoHide: 60000,
							allowClose: false
						}
					);

					return 500; // fixes https://github.com/ThemeFuse/Unyson/issues/1491
				} else {
					// fw.soleModal.hide('fw-options-ajax-save-loading'); // we need to show loading until the form reset ajax will finish
				}
			},
			afterSubmitDelay: function (elements) {
				fwEvents.trigger('fw:options:init:tabs', {$elements: elements.$form});
			},
			onErrors: function() {
				fw.soleModal.hide('fw-options-ajax-save-loading');
			},
			onAjaxError: function(elements, data) {
				{
					var message = String(data.errorThrown);

					if (data.jqXHR.responseText && data.jqXHR.responseText.indexOf('Fatal error') > -1) {
						message = $(data.jqXHR.responseText).text().split(' in ').shift();
					}
				}

				fw.soleModal.hide('fw-options-ajax-save-loading');
				fw.soleModal.show(
					'fw-options-ajax-save-error',
					'<p class="fw-text-danger">'+ message +'</p>'
				);
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
							elements.$form.trigger('fw:settings-form:before-html-reset');

							setTimeout(function() {
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

								jQuery(window).scrollTop(scrollTop);

								// fadeIn
								{
									elements.$form.css('opacity', '');
									setTimeout(function(){
										elements.$form.css('transition', '');
										elements.$form.css('visibility', '');
									}, 300);
								}

								elements.$form.trigger('fw:settings-form:reset');
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
					elements.$form.trigger('fw:settings-form:saved');
				}
			}
		});
	});
</script>
<!-- end: ajax submit -->
<?php endif; ?>

<?php if ($side_tabs && apply_filters('fw:settings-form:side-tabs:open-all-boxes', true)): ?>
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

<?php if (!empty($_GET['_focus_tab'])): ?>
<script type="text/javascript">
	jQuery(function($){
		fwEvents.one('fw:options:init', function(){
			setTimeout(function(){
				$('a[href="#<?php echo esc_js($_GET['_focus_tab']); ?>"]').trigger('click');
			}, 90);
		});
	});
</script>
<?php endif; ?>

<?php do_action('fw_settings_form_footer'); ?>
