(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	//  $(document).on('click', '.update-order', function(){
		
	// 	if($('.sejolisa-confirm-order-pickup').length == 0){
	// 		var $appendElem = $('<a class="sejolisa-confirm-order-pickup ui primary button">Proses Pickup</a>');
	// 		$appendElem.appendTo('form#confirmation-confirmed-modal .actions');
	// 	}

	// });

	$(document).on('click', '.update-order', function(){
        let proceed  = true;
        let order_id = [];
        let status   = $(this).parent().find('select[name=update-order-select]').val();

        if($('.sejolisa-confirm-order-pickup').length == 0){
			var $appendElem = $('<a class="sejolisa-confirm-order-pickup ui primary button">Proses Pickup</a>');
			$appendElem.appendTo('form#confirmation-confirmed-modal .actions');
		}

		$("tbody input[type=checkbox]:checked").each(function(i, el){
            order_id.push($(el).data('id'));
        });
            
        // if('' === status) {
        //     alert('<?php _e('Anda belum memilih aksi', 'sejoli'); ?>');
        //     return;
        // }

        // if('shipping' != status) {
        //     proceed = confirm('Anda yakin akan melakukan update pada order yang dipilih?');
        // }

        // if(proceed) {

        //     $("tbody input[type=checkbox]:checked").each(function(i, el){
        //         order_id.push($(el).data('id'));
        //     });

        //     if(0 < order_id.length) {
        //         if('shipping' === status) {
        //             sejoli_check_shipping(order_id);
        //         } else {
        //             sejoli_direct_update(order_id, status);
        //         }
        //     } else {
        //         alert('<?php _e('Anda belum memilih order', 'sejoli'); ?>');
        //         return;
        //     }
        // }
    });

	$(document).on('click', '.sejolisa-confirm-order-pickup', function(){

		//Set params
    	let invoice_number = $('input[name="order_id"]').val();
    	let baseURL 	   = sejoli_cod_jne.pickup_generate_resi.ajaxurl;
    	let nonce 		   = sejoli_cod_jne.pickup_generate_resi.nonce;

    	if (confirm('Apakah Anda yakin ingin melakukan proses request pickup order id #'+invoice_number+'?')) {
			// Save it!
			console.log('Requesting Pickup Succesfull.');

			//Get detail request
	    	$.ajax({
	    		dataType: "json",
	            url : baseURL,
	            type: 'POST',
	            data: {
	                invoice_number: invoice_number,
	                nonce:  nonce
	            },
	            success : function(response) {
	                if(response == null) {
	                	alert('Gagal Mendapatkan No Resi!');
	                	window.location.reload();
	                } else {
	                	alert('No. Resi: ' + response);
						$('.sejolisa-confirm-order-pickup').hide();
						$('.noresi').val(response);
						$('.label-resi').show();
						$('.no-resi').html(response);
						$('.sejolisa-confirm-order-shipping').attr('style', 'display: none !important');
						$('.sejolisa-confirm-order-shipping').trigger("click");
	                }
	            },
	            error: function (request, status, error) {
	                console.log(error);
	            }
	        });

		} else {
			// Do nothing!
			console.log('Requesting Pickup Failed.');
		}
    	
	});

})( jQuery );
