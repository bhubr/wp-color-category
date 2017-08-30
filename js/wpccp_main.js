jQuery(document).ready(function($) {

	var selectedTax = $( "#current_taxonomy" ).val();
        
	$( '.colorpickerField' ).each( 
            function() {
                // taxonomy is in input field's classes at position 1
                var classesString = $(this).attr( 'class' ),
                    classes = classesString.split( ' ' ),
                    taxonomy = classes[1],
                    parentRow = $(this).parent().parent();
                    parentRow.attr( 'class', taxonomy );
                    if( taxonomy != selectedTax ) {
                            parentRow.hide();
                    }
            }
        );
	
	$( '#current_taxonomy' ).change( function() {
		
		var selectedBeforeHolder = $( "#old_taxonomy" ),
                    selectedBefore = selectedBeforeHolder.val(),
                    selectedNow = $(this).val();
		//alert( selectedBefore );
		selectedBeforeHolder.val( selectedNow );
		console.log( selectedNow + ', before: ' + selectedBefore );
                
		
		//$( '.tax-' + $(this).val() ).removeClass( 'hidden' );
		//$( '.tax-' + selectedBefore ).addClass( 'hidden' );
		
		$( '.' + selectedNow ).show(); 
                
                //.show();
		$( '.' + selectedBefore ).hide();
		
		/*params = {
			action: 'wpccp_tax_get_terms',
			taxonomy: $(this).val()
		};

		$.post(
			'admin-ajax.php', 
			params,
			function(r) {
				if( $( '#terms_table' ) != undefined )
					$( '#terms_table' ).replaceWith( r );
			}, 
			'html'
		);
		return false;*/
	});
	
});
