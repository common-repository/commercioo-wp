var commercioo_required_plugin_installer = commercioo_required_plugin_installer || {};

jQuery(document).ready(function($) {
	
	"use strict"; 	
	
	var is_loading = false;	
	
		
	
	/*
   *  install_plugin
   *  Install the plugin
   *
   *
   *  @param el       object Button element
   *  @param plugin   string Plugin slug
   *  @since 1.0
   */ 
   
	commercioo_required_plugin_installer.install_plugin = function(el, plugin){
   	
   	// Confirm activation      	
   	var r = confirm(commercioo_required_plugin_ajax_obj.install_now);
   	    	
      if (r) {
         is_loading = true;
         el.addClass('installing');
		  el.addClass('disabled');
		  el.html(commercioo_required_plugin_ajax_obj.please_wait_btn);
      	$.ajax({
	   		type: 'POST',
	   		url: commercioo_required_plugin_ajax_obj.ajax_url,
	   		data: {
	   			action: 'commercioo_required_plugin_installer',
				is_update: el.attr("data-update"),
	   			plugin: plugin,
	   			nonce: commercioo_required_plugin_ajax_obj.admin_nonce,
	   			dataType: 'json'
	   		},
	   		success: function(data) { 
		   		if(data){
			   		if(data.status === 'success'){
						el.removeClass('installing');
						el.removeClass('disabled');
				   		el.attr('class', 'activate button button-primary');
				   		el.html(commercioo_required_plugin_ajax_obj.activate_btn);
			   		} else {
						el.html(commercioo_required_plugin_ajax_obj.installed_btn);
			   			el.removeClass('installing');
						el.removeClass('disabled');
		   			}
		   		} else {
					el.removeClass('installing');
					el.removeClass('disabled');
		   		}
		   		is_loading = false;
	   		},
	   		error: function(xhr, status, error) {
	      		console.log(status);
				el.removeClass('installing');
				el.removeClass('disabled');
				el.html(commercioo_required_plugin_ajax_obj.install_btn);
				alert(status);
	      		is_loading = false;
	   		}
	   	});
	   	
   	}
	}

	/*
       *  update_plugin
       *  Update the plugin
       *
       *
       *  @param el       object Button element
       *  @param plugin   string Plugin slug
       *  @since 1.0
       */

	commercioo_required_plugin_installer.update_plugin = function(el, plugin){

		// Confirm activation
		var r = confirm(commercioo_required_plugin_ajax_obj.update_now);

		if (r) {
			is_loading = true;
			el.addClass('installing');
			el.addClass('disabled');
			el.html(commercioo_required_plugin_ajax_obj.please_wait_btn);
			$.ajax({
				type: 'POST',
				url: commercioo_required_plugin_ajax_obj.ajax_url,
				data: {
					action: 'commercioo_required_plugin_installer',
					is_update: el.attr("data-update"),
					plugin: plugin,
					nonce: commercioo_required_plugin_ajax_obj.admin_nonce,
					dataType: 'json'
				},
				success: function(data) {
					if(data){
						if(data.status === 'success'){
							el.removeClass('installing');
							el.removeClass('disabled');
							el.attr('class', data.button_class);
							el.html(data.button_text);
						} else {
							el.html(commercioo_required_plugin_ajax_obj.update_now);
							el.removeClass('installing');
							el.removeClass('disabled');
						}
					} else {
						el.html(commercioo_required_plugin_ajax_obj.update_now);
						el.removeClass('installing');
						el.removeClass('disabled');
					}
					is_loading = false;
				},
				error: function(xhr, status, error) {
					console.log(status);
					el.removeClass('installing');
					el.removeClass('disabled');
					el.html(commercioo_required_plugin_ajax_obj.update_now);
					alert(status);
					is_loading = false;
				}
			});

		}
	}
	
	/*
   *  activate_plugin
   *  Activate the plugin
   *
   *
   *  @param el       object Button element
   *  @param plugin   string Plugin slug
   *  @since 1.0
   */ 
   
	commercioo_required_plugin_installer.activate_plugin = function(el, plugin){
		is_loading = true;
		el.addClass('installing');
		el.addClass('disabled');
		el.html(commercioo_required_plugin_ajax_obj.please_wait_btn);
      $.ajax({
   		type: 'POST',
   		url: commercioo_required_plugin_ajax_obj.ajax_url,
   		data: {
   			action: 'commercioo_required_plugin_activation',
   			plugin: plugin,
   			nonce: commercioo_required_plugin_ajax_obj.admin_nonce,
   			dataType: 'json'
   		},
   		success: function(data) { 
	   		if(data){
		   		if(data.status === 'success'){
			   		el.attr('class', 'installed button disabled');
			   		el.html(commercioo_required_plugin_ajax_obj.installed_btn);
		   		}else{
					el.removeClass('installing');
					el.removeClass('disabled');
					el.html(commercioo_required_plugin_ajax_obj.activate_btn);
		   			alert(data.msg);
				}
	   		}	
	   		is_loading = false;		   		
   		},
   		error: function(xhr, status, error) {
			el.removeClass('installing');
			el.removeClass('disabled');
			el.html(commercioo_required_plugin_ajax_obj.activate_btn);
			alert(status);
      		console.log(status);
      		is_loading = false;
   		}
   	});
	
	};
	
	
	
	/*
   *  Install/Activate Button Click
   *
   *  @since 1.0
   */ 
   
	// $(document).on('click', '.commercioo-required-plugin-installer a.button', function(e){
   	// var el = $(this),
   	// 	 plugin = el.data('slug');
   	//
   	// e.preventDefault();
   	//
   	// if(!el.hasClass('disabled')){
    //
    //   	if(is_loading) return false;
	//
	//    	// Installation
    //   	if(el.hasClass('install')){
	//       	commercioo_required_plugin_installer.install_plugin(el, plugin);
	//    	}
	// 	// Installation
	// 	if(el.hasClass('update-now')){
	// 		commercioo_required_plugin_installer.update_plugin(el, plugin);
	// 	}
	//    	// Activation
	//    	if(el.hasClass('activate')){
	// 	   	commercioo_required_plugin_installer.activate_plugin(el, plugin);
	// 	   }
   	// }
	// });
	
	
});