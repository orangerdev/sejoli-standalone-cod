(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

	$(document).ready(function($){
	    
	    $('body').on( 'change', 'select#shipping_method', function(e){
	        e.preventDefault();

	        $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').closest('.eight').removeClass('hide');
	        $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').prop('checked', true);

	        var s = $(this).val();
	        if (s.match(/COD.*/)) {
	            $('input[name="payment_gateway"]').closest('.eight').addClass('hide');
	            $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').closest('.eight').removeClass('hide');
	            $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').prop('checked', true);
	        } else {
	        	$('input[name="payment_gateway"]').closest('.eight').removeClass('hide');
	            $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').closest('.eight').addClass('hide');
	            $('input[name="payment_gateway"][value="cod:::CashOnDelivery"]').prop('checked', false);
	            $('.eight:nth-child(1) .ui input[name="payment_gateway"]').prop('checked', true);
	        }
	    });

	    $("#shipment-tracking-form").submit(function(event){
            event.preventDefault();
            
           	let baseURL = sejoli_public.shipment_tracking.ajaxurl;
	    	let nonce   = sejoli_public.shipment_tracking.nonce;

	    	// Get detail request
	    	$.ajax({
	    		dataType: "json",
                url : baseURL,
                type : 'POST',
                data : {
                    shipmentNumber: $('#shipment-number').val(),
                    nonce:  nonce
                },
                success : function(response) {
                    console.log(response);
                    $('#shipment-history').html(response);
                    // window.location.reload();
                },
                error: function (request, status, error) {
                    console.log(request);
                    console.log(error);
                }
            });
        });

    });

})( jQuery );
