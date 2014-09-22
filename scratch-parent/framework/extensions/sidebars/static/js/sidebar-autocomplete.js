(function($,fwe){
	$(document).ready(function(){
		/**
		 * clear autocomplete when unfocused
		 */
		$('#specific-field-id').on('blur', function(){
			$(this).val('');
		});

		$( "#specific-field-id" ).autocomplete({
			minLength: 2,
			source: function( request, response ) {
				var searchTypeSlug = $('#fw-option-sidebars-for-specific').val();
				$.ajax({
					url: ajaxurl,
					dataType: "json",
					data: {
						action: 'sidebar_autocomplete_ajax',
						searchTerm: request.term,
						searchType: searchTypeSlug
					},
					type: 'POST',
					success: function( data ) {
						$('#specific-field-id').removeClass('ui-autocomplete-loading');
						if (data.success === false || typeof data.data.items === 'undefined') {
							return false;
						}

						if (data.data.items.length === 0) {
							response({
								label: noMatchesFoundMsg
							});
						} else {
							response( $.map( data.data.items, function( val, index) {
							return {
								label: val,
								value: val,
								id: index,
								slug: searchTypeSlug
							}
						}));
						}
					},
					error: function (e) {
						$('#specific-field-id').removeClass('ui-autocomplete-loading');
						return false;
					}
				});
			},

			select: function( event, ui ) {
				$('#specific-field-id').removeClass('fw-ext-sidebars-error');
				onItemSelected(ui.item);
				$(this).val('');
				return event.preventDefault();
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
				$(this).data("uiAutocomplete").menu.element.addClass("fw-ext-sidebars-autocomplete-menu");
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});

		$('.sidebars-specific-pages').on('click','a.fw-sidebars-remove-page',function(e){
			e.preventDefault();
			$(this).parent().remove();
		});

		/**
		 * Highlighting for autocomplete results
		 */
		$.extend( $.ui.autocomplete.prototype, {
			_renderItem: function( ul, item ) {
				var term = this.element.val(),
					html = item.label.replace(new RegExp(term, "i"), "<span class='fw-search-term'>$&</span>" );
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( $("<a></a>").html(html) )
					.appendTo( ul );
			}
		});
	});

	/**
	 * @param item.value, item.id, item.label
	 */
	function onItemSelected( item ) {
		var addedItems = [];
		var slug = $('#fw-option-sidebars-for-specific').val();
		var name = $('#fw-option-sidebars-for-specific option:selected').text();

		if (typeof item.id === 'undefined' ) {
			return false;
		}

		$('.fw-sidebars-remove-page').each(function(){
			var item = {
						id: parseInt($(this).data('id')),
						slug: $(this).data('slug')
						};
			addedItems[addedItems.length] = item;
		});

		//if item exists exit
		for (var  i=0; i<addedItems.length; i++){
			if ( addedItems[i].id === parseInt(item.id) && addedItems[i].slug === item.slug ) {
				return false;
			}
		}

		fwSidebars.addRemovableItem(item.id, item.value, slug, name);
	}
})(jQuery,fwEvents);


