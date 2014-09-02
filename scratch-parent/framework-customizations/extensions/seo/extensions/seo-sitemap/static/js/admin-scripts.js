function fw_ext_seo_sitemap_update(element) {
	if (fw_ext_seo_sitemap_update.active) {
		return;
	}

	fw_ext_seo_sitemap_update.active = true;

	jQuery(element).addClass('button-primary-disabled').css('vertical-align', 'inherit').next('.spinner').show();

	var data = {
		action: 'fw_update_sitemap'
	};
	jQuery.post(ajaxurl, data, function (response) {
		jQuery(element).removeClass('button-primary-disabled').next('.spinner').hide();
		if (response)
		    jQuery('.sitemap-successfully').fadeIn(300);
		else
		    jQuery('.sitemap-unsuccessfully').fadeIn(300);
		setTimeout(function () {
		    jQuery('.sitemap-update-response').fadeOut(300);
		}, 3000);
		fw_ext_seo_sitemap_update.active = false;
	});
	return false;
}