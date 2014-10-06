(function ($) {

	$(document).ready(function () {
		//Rating stars
		$('.wrap-rating.in-post .fa.fa-star').hover(
			function () {
				$(this).addClass('over').prevAll().addClass('over');
			}, function () {
				$(this).removeClass('over').prevAll().removeClass('over');
			}
		);

		$('.wrap-rating.in-post .fa.fa-star').on('click', function () {
			var $this = $(this),
				value = $this.data('vote');

			$this.parent().children('.fa.fa-star').removeClass('voted');
			$this.addClass('voted').prevAll().addClass('voted');
			$this.parents('.wrap-rating.in-post').find('input[type="hidden"]').val(value);
		});

		//Rating qTip
		$('.wrap-rating.header.qtip-rating').each(function () { // Notice the .each() loop, discussed below
			$(this).qtip({
				content: {
					text: $(this).next('div') // Use the "div" element next to this for the content
				},
				style: {
					classes: 'rating-tip'
				},
				position: {
					my: 'top center',
					at: 'bottom center'
				}
			});
		});
	});
})(jQuery);
