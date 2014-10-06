(function ($) {

	$(document).ready(function () {
		//Rating stars
		$('.wrap-rating.edit-backend .fa.fa-star').hover(
			function () {
				$(this).addClass('over').prevAll().addClass('over');
			}, function () {
				$(this).removeClass('over').prevAll().removeClass('over');
			}
		);

		$('.wrap-rating.edit-backend .fa.fa-star').on('click', function () {
			var $this = $(this),
				value = $this.data('vote');

			$this.parent().children('.fa.fa-star').removeClass('voted');
			$this.addClass('voted').prevAll().addClass('voted');
			$this.parents('.wrap-rating.edit-backend').find('input[type="hidden"]').val(value);
		});
	});
})(jQuery);

