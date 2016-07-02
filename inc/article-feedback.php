<?php

require_once('class.FormSubmission.php');

class ArticleFeedbackForm extends FormSubmission {

	protected $dbtable = 'frm_feedback';	
}

function ajax_article_feedback_handler() {
		
	$form = new ArticleFeedbackForm;
		
	if ($form->isValid()) {
		
		// validation
		$form->validate('status','required');
		$form->validate('status','allowedValues',array('public','private'));
		$form->validate('feedback','required');
		$form->validate('feedback','stringLength',array('maxlength' => 5000));
	}
	
	$form->transform();
	
	if ($form->isValid()) {
		
		$user = wp_get_current_user();
		$post_id = $form->outputValue('post_id');

		$form->adminMsg('affiliate-admin-feedback');
		$form->adminMsg()->setFrom(get_bloginfo('name'), of_get_option('from_email'));
		$form->adminMsg()->setReplyTo($user->get('display_name'), $user->get('user_email'));
		$form->adminMsg()->setTo(of_get_option('editor_name'), of_get_option('editor_email'));
		$form->adminMsg()->setSubject('Article Feedback');
		
		$form->adminMsg()->setVariable('username',$user->get('display_name'));
		
		if ($form->outputValue('relevant_respectful') === 1)
			$form->adminMsg()->setVariable('respectful', 'Yes');
		elseif ($form->outputValue('relevant_respectful') === -1)
			$form->adminMsg()->setVariable('respectful', 'No');
		else
			$form->adminMsg()->setVariable('respectful', 'Did not vote');
		
		$form->adminMsg()->setVariable('feedback',$form->outputValue('feedback'));		
		$form->adminMsg()->setVariable('article_url',get_permalink($post_id));
		
		$form->adminMsg()->send();
		
		$form->userMsg('affiliate-user-feedback');
		$form->userMsg()->setFrom(get_bloginfo('name'), of_get_option('from_email'));
		$form->userMsg()->setTo($user->get('display_name'), $user->get('user_email'));
		$form->userMsg()->setSubject('Thank you for your feedback!');

		$form->userMsg()->setVariable('feedback',$form->outputValue('feedback'));		
		$form->userMsg()->setVariable('article_url',get_permalink($post_id));
		$form->userMsg()->send();
		
		$form->setMandrillFlags();
	}
	else {
		die('error'.join(' ',$form->getValidationErrors()));
	}

	$form->persist();
	
	// record upvote / downvote article meta
	
	if ($form->outputValue('relevant_respectful') == 1) {
		
		$up = get_post_meta($post_id,'upvotes',true);
		
		if (!empty($up))
			$up++;
		else 
			$up = 1;
		
		update_post_meta($post_id,'upvotes',$up);
			
	} elseif ($form->outputValue('relevant_respectful') == -1) {
		
		$down = get_post_meta($post_id,'downvotes',true);
		
		if (!empty($down))
			$down++;
		else 
			$down = 1;
		
		update_post_meta($post_id,'downvotes',$down);
	}

	
	// if this is a public comment, post it and return comment HTML
	if ($form->outputValue('status') == 'public') {
	
		$commentdata = array(
			'comment_post_ID' => $post_id
			, 'comment_author' => $user->user_login
			, 'comment_author_email' => $user->user_email
			, 'comment_author_url' => $user->user_url
			, 'comment_content' => $form->outputValue('feedback')
			, 'comment_type' => ''
			, 'comment_parent' => 0
			, 'user_ID' => $user->ID
		);
	
		$comment_id = wp_new_comment( $commentdata );
		if ( ! $comment_id ) {
			die( "errorThe comment could not be saved. Please try again later." );
		}
		
		$comment = get_comment( $comment_id );
		
		echo("success");
		$commenthtml = bp_comment($comment);
		die($commenthtml);
	}

	// else return simple success message
	else {
		die("success");
	}
	
	
}

add_action( 'wp_ajax_article_feedback_handler', 'ajax_article_feedback_handler' );

?>
