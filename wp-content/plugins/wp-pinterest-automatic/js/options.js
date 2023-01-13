jQuery(document)
		.ready(
				function() {

					// ctb add
					jQuery(document).on('click','.ctb .ctb_add',function() {
								jQuery('.ctb-contain').append('<div class="ctb">'+ jQuery(this).parent().parent().html()+ '</div>');
								return false;

					});

					jQuery(document).on('click','.ctb .ctb_remove',function() {
								
							if (jQuery('.ctb_add').length > 1) {
									jQuery(this).parent().parent().fadeOut('slow', function() {

												jQuery(this).remove();

									});

								} else {
									jQuery('#field-ctb_enable-1').trigger('click');
								}

								return false;

					});
					
					//CTT ADD/REMOVE
					jQuery(document).on('click','.ctt .ctt_add',function() {
						jQuery('.ctt-contain').append('<div class="ctt">'+ jQuery(this).parent().parent().html()+ '</div>');
						return false;

					});
		
					jQuery(document).on('click','.ctt .ctt_remove',function() {
								
							if (jQuery('.ctt_add').length > 1) {
									jQuery(this).parent().parent().fadeOut('slow', function() {
		
												jQuery(this).remove();
		
									});
		
								} else {
									jQuery('#field-ctt_enable-1').trigger('click');
								}
		
								return false;
		
					});
					

					jQuery('#get_boards')
							.click(
									function() {

										if( jQuery('#field-wp_pinterest_automatic_session').val() == '' ){
											alert('Please fill the Pinterest Session Cookie field');
											return false;
										}
										
										jQuery
												.ajax({
													url : ajaxurl,
													type : 'POST',
													data : {
														action : "wp_pinterest_automatic_boards",
														sess : jQuery(
																'#field-wp_pinterest_automatic_session')
																.val() 
													},

													success : function(data) {
														jQuery(
																'#ajax-loadingimg')
																.addClass(
																		'ajax-loading');

														var res = jQuery
																.parseJSON(data);
														if (res['status'] == 'success') {
															// console.log(data);

															jQuery(
																	'#field1zz option')
																	.remove();

															var ids = res['ids'];
															var titles = res['titles'];

															for (var i = 0; i < ids.length; i++) {

																jQuery('#field1zz').append('<option value="'
																						+ ids[i]
																						+ '">'
																						+ titles[i]
																						+ '</option>');

															
																//CTB BOARDS UPDATE
																if(jQuery('select[name="wp_pinterest_automatic_pinterest_category[]"] option[value="'+ids[i]+'"]').length == 0 ){
																	//append it 
																	jQuery('select[name="wp_pinterest_automatic_pinterest_category[]"]').append('<option value="'
																			+ ids[i]
																			+ '">'
																			+ titles[i]
																			+ '</option>');
																	
																	
																}
															
															}

														} else if (res['status'] == 'fail') {

															alert(res['message']);
															return false;

														}

													},

													beforeSend : function() {

														jQuery(
																'#ajax-loadingimg')
																.removeClass(
																		'ajax-loading');

													}

												});
										return false;
									});

					// data controler
					jQuery('input[data-controls]').each(
							function() {

								slider(this, '#'
										+ jQuery(this).attr('data-controls'));

								jQuery(this).change(
										function() {
											slider(this, '#'
													+ jQuery(this).attr(
															'data-controls'));
										});

							});

					// slider function
					function slider(id, slide) {

						if (jQuery(id).prop("checked") == true) {
							// call the function to be fired
							// when your box changes from
							// unchecked to checked
							jQuery(slide).slideDown();
						} else {
							// call the function to be fired
							// when your box changes from
							// checked to unchecked
							jQuery(slide).slideUp();
						}

					}

					jQuery(".hndle").on('click', function() {
						if (jQuery(this).parent().hasClass("closed")) {
							jQuery(this).parent().removeClass("closed");
						} else {
							jQuery(this).parent().addClass("closed");
						}
					});
					
					//Select box controls a div
					function showHide(divClass){
						
						if(typeof divClass == "undefined"){
							divClass = 'select_control_div_div';
						}
						
					    	jQuery('.' + divClass ).slideUp();

					    	jQuery('.select_control_div').each(function(){
					    		jQuery('.'+  jQuery(this).attr('name') + '_' + jQuery(this).val() ).slideDown();
					    	});
					    	
					}

					showHide();
					
					//Select box controls a div
					jQuery('.select_control_div').change(function(){
						showHide(jQuery(this).attr('name'));
					});
					
					
				});


