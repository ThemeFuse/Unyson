<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $options
 * @var array $values
 * @var string $focus_tab_input_name
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

<!-- ajax submit -->
<script type="text/javascript">
	jQuery(function ($) { return;
		fwForm.initAjaxSubmit({
			selector: 'form[data-fw-form-id="fw_settings"]',
			ajaxUrl: ajaxurl
		});
	});
</script>
<!-- end: ajax submit -->
