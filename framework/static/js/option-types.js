jQuery( document ).ready( function ( $ ) {
	setTimeout( function () {
		fwEvents.trigger( 'fw:options:init', {
			$elements: $( document.body )
		} );
	}, 30 );

	function updateContent( $content ) {
		if ( tinymce.get( 'content' ) ) {
			tinymce.get( 'content' ).setContent( $content );
		} else {
			$content.val( $content );
		}
	}

	$( '#post-preview' ).on( 'mousedown touchend', function () {

		var $content      = $( '#content' ),
			$contentValue = tinymce.get( 'content' ) ? tinymce.get( 'content' ).getContent() : $content.val(),
			$session      = '<!-- <fw_preview_session>' + new Date().getTime() + '</fw_preview_session> -->';

		if ( $contentValue.indexOf( '<!-- <fw_preview_session>' ) !== -1 ) {
			$contentValue = $contentValue.replace( /<!-- <fw_preview_session>(.*?)<\/fw_preview_session> -->/gi, $session );
		} else {
			$contentValue = $contentValue + $session;
		}

		updateContent( $contentValue );
		updateContent( $contentValue.replace( /<!-- <fw_preview_session>(.*?)<\/fw_preview_session> -->/gi, '' ) );
	} );
} );