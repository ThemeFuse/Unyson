/**
 * Script file that will manage the "map" option
 */

"use strict";
(function($, _, fwe, localized){
	jQuery( document ).ready( function( ) {
		$.fn.pressEnter = function(fn) {
			return this.each(function() {
				$(this).bind('enterPress', fn);
				$(this).keyup(function(e){
					if(e.keyCode == 13)
					{
						$(this).trigger("enterPress");
					}
				})
			});
		};

		function fw_option_map_initialize( data ) {

			if( typeof google === 'undefined' ){
				data.find( '.fw-option-map-inputs').attr( 'readonly', 'readonly' );
				return;
			}

			var used_autocomplete = false;

			// Define map option
			var option = {
				fields  : {
					location    : {
						element  : data.find( '.map-location' ),
						value   : data.find( '.map-location').attr('value')
					},
					venue       : {
						element  : data.find( '.map-venue' ),
						value   : data.find( '.map-venue').attr('value')
					},
					address     : {
						element  : data.find( '.map-address' ),
						value   : data.find( '.map-address').attr('value')
					},
					city        : {
						element  : data.find( '.map-city' ),
						value   : data.find( '.map-city').attr('value')
					},
					state       : {
						element  : data.find( '.map-state' ),
						value   : data.find( '.map-state').attr('value')
					},
					country     : {
						element  : data.find( '.map-country' ),
						value   : data.find( '.map-country').attr('value')
					},
					zipCode     : {
						element  : data.find( '.map-zip' ),
						value   : data.find( '.map-zip').attr('value')
					},
					coordinates    : {
						element  : data.find( '.map-coordinates' ),
						value   : jQuery.parseJSON( data.find( '.map-coordinates').attr('value') )
					}
				},
				map     : {
					container   : data.find( '.map-googlemap' ),
					object      : {
						map     : {},
						marker  : {}
					}
				},
				toggles : {
					expand : data.find('.fw-option-maps-expand'),
					reset : data.find('.fw-option-maps-close')
				},
				tabs    : {
					first   : data.find('.fw-option-maps-tab.first'),
					second  : data.find('.fw-option-maps-tab.second')
				},

				getComputedLongAddress: function() {
					var longAddress = '';
					if ( option.getFreshValue('zipCode') || option.getFreshValue('venue') || option.getFreshValue('address') || option.getFreshValue('city') || option.getFreshValue('state') || option.getFreshValue('country') ){
						//join array without empty fields
						longAddress = _.reduce([option.getFreshValue('venue'), option.getFreshValue('address'), option.getFreshValue('city'), option.getFreshValue('state'), option.getFreshValue('country'), option.getFreshValue('zipCode')],
							function(a, b) {
								return b = b.trim(), b && (a = a ? a + ", " + b : b), a
							}, "");
					}

					return longAddress;
				},

				refreshMap: function()
				{
					var googleMapsPos = ( typeof this.fields.coordinates.value === "object" && this.fields.coordinates.value != null ) ?
						new google.maps.LatLng( this.fields.coordinates.value.lat, this.fields.coordinates.value.lng ) :
						new google.maps.LatLng( -34, 150 );

					var mapOptions = {
						center  : googleMapsPos,
						zoom    : 15,
						mapTypeControl: false,
						streetViewControl: false
					};

					if (_.isEmpty(this.map.object.map)){
						this.map.object.map = new google.maps.Map( option.map.container[0], mapOptions );
					}

					if (_.isEmpty(this.map.object.marker)){
						this.map.object.marker = new google.maps.Marker({
							position: googleMapsPos,
							map: option.map.object.map,
							draggable: true
						});
					}

					this.map.object.map.setCenter(googleMapsPos);

					google.maps.event.addListener( this.map.object.marker, 'dragend', function(){
						geocoder.geocode({
							latLng: this.getPosition()
						}, function(responses) {
							if (responses && responses.length > 0) {
								option.updateFields( responses[0] );
							}
						});
					});
				},
				setValue : function( property, value, encode ) {
					this.fields[ property ].value = value;
					if( encode )
						this.fields[ property ].element.val(JSON.stringify(value));
					else
						this.fields[ property ].element.val( value );
				},
				updateMapCoords: function(geoObject) {
					if( geoObject == null )
						return;

					option.map.object.map.panTo(geoObject.geometry.location);
					option.map.object.marker.setPosition( geoObject.geometry.location );
					option.setValue( 'coordinates', {
						lat: geoObject.geometry.location.lat(),
						lng: geoObject.geometry.location.lng()
					}, true );
				},
				updateFields : function( geoObject ){

					option.setValue( 'location', '' );
					option.setValue( 'state', '' );
					option.setValue( 'country', '');
					option.setValue( 'city', '' );
					option.setValue( "address", '' );
					option.setValue( 'zipCode', '' );
					option.setValue( 'venue', '');
					option.setValue( 'coordinates', '');

					if( geoObject == null )
						return;

					option.updateMapCoords(geoObject);

					for( var i = 0; i < geoObject.address_components.length; i++ ){
						var current = geoObject.address_components[i];


						/*if ( current.types[0] == "establishment" )
						 option.setValue( 'venue', current.long_name );*/

						if ( current.types[0] == "administrative_area_level_1" )
							option.setValue( 'state', current.long_name.trim() );

						if ( current.types[0] == "country" )
							option.setValue( 'country', current.long_name.trim() );

						if ( current.types[0] == "locality" )
							option.setValue( 'city', current.long_name.trim() );

						if ( current.types[0] == "route" )
							option.setValue( "address", current.long_name.trim() );

						if ( current.types[0] == "postal_code" )
							option.setValue( 'zipCode', current.long_name.trim() );
					}

					//option.setValue('location', geoObject.formatted_address.trim() );
					option.setValue('location', option.getComputedLongAddress());


					if ( typeof geoObject.name != 'undefined' ){
						option.setValue('venue', geoObject.name);
					}
				},
				getFreshValue: function( property ) {
					return this.fields[ property ].element.val( );
				}
			};

			//Create google map
			var geocoder = new google.maps.Geocoder();
			option.refreshMap();

			data.on('blur', '.map-city, .map-address, .map-state, .map-country, .map-zip, .map-venue', function(){
				var address = option.getComputedLongAddress();
				handleGeoCoder(address);
			});

			// Define autocomplete
			var autocomplete = new google.maps.places.Autocomplete( option.fields.location.element[0] );
			autocomplete.bindTo('bounds', option.map.object.map);

			// Add events
			google.maps.event.addListener( autocomplete, 'place_changed', function(){
				var place = autocomplete.getPlace();
				if (!place.geometry) {
					return;
				}
				used_autocomplete = true;
				option.updateFields( place );
				setTimeout( function() {
					option.toggles.expand.trigger( 'click' );
				}, 200 );
			});

			$( option.fields.location.element).keydown(function (e) {
				if(e.keyCode === 13){
					return false;
				}
			});

			var handleGeoCoder = function(address) {
				"undefined" == typeof geocoder && (geocoder = new google.maps.Geocoder());
				geocoder.geocode({
					address: address
				}, function(responses, status) {

					if ( responses.length > 0 && status === 'OK') {

						option.updateMapCoords( responses[0] );
						option.setValue('location', option.getComputedLongAddress());

					} else {
						option.setValue( 'coordinates', {
							lat: 0,
							lng: 0
						}, true );
					}

					setTimeout( function() {
						option.refreshMap();
					}, 200 );

				});
			}

			$( option.fields.location.element).pressEnter( function(e) {
				var address = option.getFreshValue('location');
				geocoder.geocode({
					address: address
				}, function(responses) {
					if ( responses.length > 0) {
						if( used_autocomplete ){
							used_autocomplete = false;
							return;
						}
						setTimeout( function() {
							option.toggles.expand.trigger( 'click' );
						}, 200 );
						option.updateFields( responses[0] );
						setTimeout( function() {
							option.setValue('location', option.getComputedLongAddress());
						}, 200 );

					}
				});
				return false;
			});

			option.toggles.expand.on( 'click', function( e ){
				e.preventDefault();
				option.tabs.first.hide().addClass('closed');
				option.tabs.second.show().removeClass('closed');
				google.maps.event.trigger(option.map.object.map, 'resize');
				option.refreshMap();
			});
			option.toggles.reset.on( 'click', function( e ){
				e.preventDefault();
				option.updateFields( null );
				option.tabs.second.hide().addClass('closed');
				option.tabs.first.show().removeClass('closed');
			});

			if (option.fields.location.value){
				//open map
				option.toggles.expand.trigger('click');
			}
		}

		var pendingInit = [];

		fwe.on('fw:options:init', function (data) {

			var obj = data.$elements.find('.fw-option-type-map:not(.initialized)');

			if (!obj.length) {
				return;
			}

			if (typeof google == 'undefined' || typeof google.maps == 'undefined') {
				if (pendingInit.length) { // already in process of loading the script
					pendingInit.push(obj);
				} else {
					pendingInit.push(obj);

					/**
					 * Lazy load script only on option init to prevent API request limit and error
					 * Fixes https://github.com/ThemeFuse/Unyson/issues/1675
					 */
					$.ajax({
						type: "GET",
						url: localized.google_maps_js_uri,
						dataType: "script",
						cache: true
					}).done(function(){
						$.each(pendingInit, function(i, obj){
							obj.each(function(){
								fw_option_map_initialize($(this));
							});
						});
						pendingInit = [];
					}).fail(function(){
						console.error('Failed to load Google Maps script');
						pendingInit = [];
					});
				}
			} else {
				obj.each(function(){
					fw_option_map_initialize($(this));
				});
			}

			obj.addClass('initialized');
		});
	});

})(jQuery, _, fwEvents, _fw_option_type_map);
