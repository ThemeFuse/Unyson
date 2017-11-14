jQuery( document ).ready( function ( $ ) {
	setTimeout( function () {
		fwEvents.trigger( 'fw:options:init', {
			$elements: $( document.body )
		} );
	}, 30 );

	$( '#post-preview' ).on( 'mousedown touchend', function () {

		var $content      = $( '#content' ),
			$contentValue = $content.val(),
			$session      = '<!-- <fw_preview_session>' + new Date().getTime() + '</fw_preview_session> -->';

		if ( $contentValue.indexOf( '<!-- <fw_preview_session>' ) !== -1 ) {
			$contentValue = $contentValue.replace( /<!-- <fw_preview_session>(.*?)<\/fw_preview_session> -->/g, $session );
		} else {
			$contentValue = $contentValue + $session;
		}

		$content.val( $contentValue );
	} );
} );