/**
 * NOTICE OF LICENSE
 *
 * This source file is used for javascript validations on send.
 *
 * @category    Zalw
 * @package     Advancemsg
 * @author	Zalw
 **/
jQuery.noConflict();
jQuery(document).ready(function(){		
		
	
	jQuery(".submit").click(function() {
	var messagetitle = jQuery("#messagetitle").val();		
	var msg = jQuery("#message").val();		
	//var dataString ='messagetitle='+ msgtitle + '&message=' + msg;
		
	if(messagetitle == '' || msg == '' )
	{			
            jQuery('.success').fadeIn(200).hide();
            jQuery('.error').fadeOut(200).show();
        }
	else{
	    jQuery('.success').fadeOut(200).show();
	    var vurl= window.location.href;		
            jQuery.ajax({
            type: "POST",
            url: vurl,
            data: {messagetitle:messagetitle,message:msg},
            success: function(){                
                jQuery('.error').fadeOut(200).hide();}
            });
        }
        return false;
	
	});
});


