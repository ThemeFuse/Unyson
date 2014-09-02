jQuery(function ($) {

    // Screen Options: Show advanced menu properties: Icon Checkbox
    (function () {

        var container = '#menu-to-edit';
        var selector = '#icon-hide';

        $(document).on('change', selector, function () {
            $(container).toggleClass('screen-options-icon', $(this).is(':checked'));
        });

        $(selector).change();

    })();

    // Mega Menu Column Title: input
    (function (selector) {

        $(document).on('change', selector, function () {
            $(this).closest('li').find(selector).val($(this).val());
        });
        // $(selector).change() is not necessary since those two fields
        // are populated by wordpress with the same value (title)

    })('.mega-menu-title, .edit-menu-item-title');

    // Mega Menu Column Title: checkbox
    (function (selector) {

        $(document).on('change', selector, function () {
            var checkbox = $(this);
            checkbox.closest('p').find('.mega-menu-title').prop('disabled', checkbox.is(':checked'));
        });
        $(selector).change();

    })('.mega-menu-title-off');

    // Use as Mega Menu Checkbox
    (function () {

        var menu = $('#menu-to-edit');

        function update()
        {
            menu.children().removeClass('mega-menu');
            menu.children('.menu-item-depth-0:has(.mega-menu-enabled:checked)').each(function () {
                var item = $(this);
                item.addClass('mega-menu');
                item.nextUntil('.menu-item-depth-0').addClass('mega-menu');
            });
        }

        $(document).on('change', '.menu-item-depth-0 .mega-menu-enabled', update);
        // FIXME our handler should be called after WP handler
        menu.on('sortstop', function () {
            setTimeout(update, 1);
        });

        update();

    })();

    // Monitor icon state and reflect it with dependent fields
    (function (selector) {

        $(document).on('change', selector, function (event) {
            var field = $(this).closest('.field-mega-menu-icon');
            var value = $(this).val();
            field.toggleClass('empty', value == '');
            field.find('[data-subject=mega-menu-icon-i]').attr('class', 'fa fa-lg ' + value);
        });
        $(selector).change();

    })('.field-mega-menu-icon [data-subject=mega-menu-icon-input]');

    // Add/Edit Icon Buttons
    $(document).on('click', '[data-action=mega-menu-pick-icon]', function (event) {

        event.preventDefault();

        var modal = new fw.OptionsModal({
            title: 'Select Icon',
            options: [{
                icon: {
                    type: 'icon',
                    label: 'Select Icon'
                }
            }],
            values: {
                icon: $(event.target).closest('.field-mega-menu-icon').find('input').val()
            },
            size: 'small'
        });

        // Listen for values change
        modal.on('change:values', function(modal, values) {
            $(event.target).closest('.field-mega-menu-icon').find('input').val(values.icon).change();
        });

        // Immediately close dialog after clicking on icon
        $(modal.frame.$el).on('click', '.fa', function () {
            modal.set('values', {
                icon: $(this).data('value')
            });
            modal.frame.close();
        });

        // Resize icon list to fit entire window
        function resizeIconList()
        {
            var option = modal.frame.$el.find('#fw-edit-options-modal-icon');
            var frame_content = option.closest('.media-frame-content');
            var icon_list = option.find('.fontawesome-icon-list');

            // get rid of bottom border
            option.closest('.fw-row').css('border-bottom', 'none');

            // resize icon list to fit entire window
            icon_list.css('max-height', 'none').height(1000000);
            frame_content.scrollTop(1000000);
            icon_list.height(icon_list.height() - frame_content.scrollTop());
        }

        modal.on('change:html', resizeIconList);
        $(window).resize(resizeIconList);

        modal.open();

        // Replace [Save] button by [Cancel]
        $(modal.frame.$el).find('.media-toolbar-primary').html('<a href="#" class="button media-button button-large">Cancel</a>').find('a').click(function (event) {
            event.preventDefault();
            modal.frame.close();
        });

    });

    // Remove Icon Button
    $(document).on('click', '[data-action=mega-menu-remove-icon]', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).closest('.field-mega-menu-icon').find('input').val('').change();
    });

	// The problem is in using **change** event for initialization.
	//
	// Internally WordPress listen this inputs for **change** event
	// and sets **menuChanged** flag. It also sets window.onbeforeunload handler
	// which decides whether or not display
	//
	//     "The changes you made will be lost if you navigate away from this page."
	//
	// dialog based on this flag.
	wpNavMenu.menusChanged = false;

});
