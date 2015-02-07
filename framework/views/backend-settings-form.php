<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $options
 * @var array $values
 * @var string $focus_tab_input_name
 * @var string $reset_input_name
 */
?>
<!--
wp moves flash message error with js after first h2
if there are no h2 on the page it shows them wrong
-->
<h2 class="fw-hidden"></h2>

<?php echo fw()->backend->render_options($options, $values); ?>

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
				$(this).attr("href").replace(/^\\#/, "") // tab id
			);
		});

		/* "wait" after tabs initialized */
		setTimeout(function(){
			var focusTabId = $.trim("<?php echo esc_js($focus_tab_id) ?>");

			if (!focusTabId.length) {
				return;
			}

			var $tabLink = $(".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a[href=\'#"+ focusTabId +"\']");

			while ($tabLink.length) {
				$tabLink.trigger("click");
				$tabLink = $tabLink
					.closest(".fw-options-tabs-wrapper").parent().closest(".fw-options-tabs-wrapper")
					.find("> .fw-options-tabs-list > ul > li > a[href=\'#"+ $tabLink.closest(".fw-options-tab").attr("id") +"\']");
			}

			// click again on focus tab to update the input value
			$(".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a[href=\'#"+ focusTabId +"\']").trigger("click");;
		}, 200);
	});
});
</script>
<!-- end: focus tab -->

<!-- reset warning -->
<script type="text/javascript">
	jQuery(function($){
		$('form[data-fw-form-id="fw_settings"] input[name="<?php echo esc_js($reset_input_name) ?>"]').on('click.fw-reset-warning', function(e){
			/**
			 * on confirm() the submit input looses focus
			 * fwForm.isAdminPage() must be able to select the input to send it in _POST
			 * so use alternative solution http://stackoverflow.com/a/5721762
			 */
			{
				$(this).closest('form').find('input[type="submit"]').removeAttr('clicked');
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

<!-- ajax submit -->
<script type="text/javascript">
	jQuery(function ($) {
		fwForm.initAjaxSubmit({
			selector: 'form[data-fw-form-id="fw_settings"]',
			loading: function(show, $form, $submitButton) {
				if (show) {
					var title, description;

					if ($submitButton.length && $submitButton.attr('name') == '<?php echo esc_js($reset_input_name) ?>') {
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
							autoHide: 30000,
							allowClose: false
						}
					);
				} else {
					fw.soleModal.hide('fw-options-ajax-save-loading');
				}
			},
			onSuccess: function($form, ajaxData) {
				/**
				 * do not display the "Done" message
				 * (users will click often on the save button, it's obvious it was saved if no error is shown)
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
					// nothing to display
					return;
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
						hidePrevious: noErrors ? false : true
					}
				);
			}
		});
	});
</script>
<!-- end: ajax submit -->
