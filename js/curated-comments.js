jQuery('document').ready(function($){

	var commentform = $('#commentform');
	var commentsubmit = $('#comment-submit');	
	var confirmsubmit = $('#confirm-submit');
	var feedbackform = $('#article-feedback-form');
	var feedbacksubmit = $('#feedback-submit');
	
	feedbacksubmit.click(function() {
		
		var feedbacksubmitdiv = $('#wrap-feedback-submit');
		var feedbackdata = "action=article_feedback_handler&" + feedbackform.serialize();	
		var feedbackbuttons = feedbacksubmitdiv.html();
		
		feedbacksubmitdiv.html('Processing...');

	    jQuery.ajax({
	        type: 'POST',
	        url: votecommentajax.ajaxurl,
	        data: feedbackdata,
	        success: function(data, textStatus, XMLHttpRequest) {

				if(data.substring(0,7) == "success") {
					alert("Thank you!  Your feedback has been submitted.");
					
					$('#article-feedback-window').modal('hide');
					feedbacksubmitdiv.html(feedbackbuttons);

					// if there is a comment here, render it
					if (data.length > 7) {

						var newcomment = $('<div></div>').append(data.substring(7));
						var listitem = (newcomment.find('li'));
											
						// create list if necessary, insert comment, highlight comment						
						var listselector = 'ol.commentlist';
						if ( $(listselector).length == 0) {
							$('#comments').append('<ol class="commentlist"></ol>');							
						} 
						
						$(listselector).prepend(listitem);
						listitem.effect('highlight', {}, 1500);
					}
					
				} else if (data.substring(0,5) == "error") {
					alert(data.substring(5));
					feedbacksubmitdiv.html(feedbackbuttons);
				}	        	
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown) {
	            alert(errorThrown);
	        }
	    });		
	});

	commentsubmit.click(function() {
		
		var commenttext = $('textarea#comment').val();
		$('#confirmation-text').html(commenttext);
	});
	
	confirmsubmit.click(function() {
		
		// store form data and parent id
		var parent = $('#comment_parent').val();		
		var formdata = commentform.serialize();
		var formurl = commentform.attr('action');
		
		//Add a status message
		$('#confirm-cancel').hide();
		$('#confirm-submit').hide();	
		$('#confirm-message').html('<p>Processing...</p>');
		
		$.ajax({
			type: 'post',
			url: formurl,
			data: formdata,
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				confirmdiv.html('<p class="error-msg" >There was an error with your submission.  Please close this dialog and try again.</p>');
			},
			success: function(data, textStatus){				
									
				if(data.substring(0,7) == "success"){

					// reset textarea and confirmation form buttons, hide modal
					$('textarea#comment').val('');	
					$('#confirmation-modal').modal('hide');
					$('#confirm-cancel').show();
					$('#confirm-submit').show();	
					$('#confirm-message').html('');
					
					// insert new comment where it belongs
					var newcomment = $('<div></div>').append(data.substring(7));
					var listitem = (newcomment.find('li'));
										
					// create list if necessary, insert comment, highlight comment
					
					if (parent == '0') {
						var listselector = 'ol.commentlist';
						if ( $(listselector).length == 0) {
							$('#comments').append('<ol class="commentlist"></ol>');							
						}
					} else {
						var listselector = '#li-comment-' + parent + ' ul.children';						
						if ( $(listselector).length == 0) {
							var itemselector = '#li-comment-' + parent;
							$(itemselector).append('<ul class="children"></ul>');							
						}
					}
									
					$(listselector).prepend(listitem);
					listitem.effect('highlight', {}, 1500);
						
				} else {
					$('#confirm-cancel').show();
					$('#confirm-submit').show();	
					$('#confirm-message').html('<p class="error-msg" >There was an error with your submission.  Please try again.</p>');
				}
			}
		});
	});	
});

function comment_upvote(comment_id,nonce) {
 
    jQuery.ajax({
        type: 'POST',
        url: votecommentajax.ajaxurl,
        data: {
            action: 'comment_upvote_handler',
            commentid: comment_id,
            nonce: nonce
        },
        success: function(data, textStatus, XMLHttpRequest) {
            var votecount = '#comment-upvote-count-' + comment_id;
            jQuery(votecount).html('');
            jQuery(votecount).append(data);
            
            var commentlink = '#comment-upvote-' + comment_id + ' a';
            jQuery(commentlink).addClass('disabled');           
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
 
}

function comment_alert(comment_id,nonce) {
 
    jQuery.ajax({
        type: 'POST',
        url: votecommentajax.ajaxurl,
        data: {
            action: 'comment_alert_handler',
            commentid: comment_id,
            nonce: nonce
        },
        success: function(data, textStatus, XMLHttpRequest) {
            var alertcount = '#comment-alert-count-' + comment_id;
            jQuery(alertcount).html('');
            jQuery(alertcount).append(data);
            
            var commentlink = '#comment-alert-' + comment_id + ' a';
            jQuery(commentlink).addClass('disabled');           
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
 
}