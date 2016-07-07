<?php

require_once('class.MandrillMessage.php');
require_once('class.NationbuilderAPI.php');

/*
 * Contents
 * 
 * Display
 * 
 *   BP_Comments_Filter Object - returns comments in array format
 * 	 bp_comments_template - template for comment list
 *   bp_comment - template for single commment
 *   bp_show_upvotes - upvote icon and voting
 *   bp_show_upvotes_anon
 *   bp_ajax_comment_upvote_handler - processes upvotes
 *   bp_show_alerts - upvote icon and voting
 *   bp_show_alerts_anon
 *   bp_ajax_comment_alert_handler - process alerts
 *  
 * Comment Form 
 * 
 *   Display
 *     bp_comment_form
 *     bp_hidden_term_inject
 * 
 *   Submission
 *     bp_save_comment
 *     bp_ajax_comment_handler
 *     bp_category_comment_post
 * 
 * Admin
 * 
 *   bp_add_comment_columns
 *   bp_render_comment_columns
 *   bp_comment_quick_edit_form
 *   bp_comment_hidden_fields
 *   bp_comment_quick_edit_javascript
 *   bp_comment_quick_edit_action
 *   bp_admin_save_comment
 *   bp_edit_comment_meta_boxes
 *   bp_edit_comment_category_box
 * 
 * Utility
 * 
 *   bp_get_comment
 *   bp_get_comment_from_id
 * 
 * 
 *****/ 
 
 
/**
 * Returns comments for display. Filters Comments by Top Term and apply ordering algorithm.
 */
class BP_Comments_Filter
{
    protected static $term_id;

    /**
     * Returns comments. Called like get_comments.
     *
	 * @param  int $term_id
     * @param  array $args
     * @return array
     */
    public static function get_comments($term_id=null, $args = array ())
    {
        if (isset($term_id))
        {
            self::$term_id = $term_id;
        }
        return get_comments( $args );
    }

    /**
     * Filters the comment query and applies ordering algorithm.
     *
     * @param array $q Query parts, see WP_Comment_Query::query()
     *
     * @return array
     */
    public static function filter( $q )
    {
  		$q['join'] .= "JOIN bp_comments on wp_comments.comment_ID = bp_comments.comment_id";
  
  		if (isset(self::$term_id)) {
  			$q['where'] .= " AND term_id = ".self::$term_id;
			
  		}
	
		$q['orderby'] = "1000000 * (1 + member + upvotes / 2) / ( UNIX_TIMESTAMP() - UNIX_TIMESTAMP( wp_comments.comment_date )) DESC";
	
        return $q;
    }
}

add_filter( 'comments_clauses', array('BP_Comments_Filter' , 'filter'));


/**
 * Loads the comment template specified in $file. Overrides Largo's comments_template().
 */
function bp_comments_template($term_id = null, $file = '/comments.php', $separate_comments = false ) {
		
	global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $overridden_cpage;

	if ( !($term_id))
		return;

	if ( empty($file) )
		$file = '/comments.php';

	/*
	 * Include javascript for voting on comments 
	 */

	wp_enqueue_script(
		'curated-comments'
		,'/wp-content/themes/Largo-for-Banyan-Project/js/curated-comments.js'
		, array('jquery')
		, '0.1'
		, true
	);
		
    wp_localize_script( 'curated-comments', 'votecommentajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	$req = get_option('require_name_email');

	/*
	 * Comment author information fetched from the comment cookies.
	 */
	$commenter = wp_get_current_commenter();

	/*
	 * The name of the current comment author escaped for use in attributes.
	 * Escaped by sanitize_comment_cookies().
	 */
	$comment_author = $commenter['comment_author'];

	/*
	 * The email address of the current comment author escaped for use in attributes.
	 * Escaped by sanitize_comment_cookies().
	 */
	$comment_author_email = $commenter['comment_author_email'];

	/*
	 * The url of the current comment author escaped for use in attributes.
	 */
	$comment_author_url = esc_url($commenter['comment_author_url']);

	$comment_args = array(
		'order'   => 'ASC',
		'orderby' => 'comment_date_gmt',
		'status'  => 'approve',
	);

	if ( $user_ID ) {
		$comment_args['include_unapproved'] = array( $user_ID );
	} elseif ( ! empty( $comment_author_email ) ) {
		$comment_args['include_unapproved'] = array( $comment_author_email );
	}
		
	$comments = BP_Comments_Filter::get_comments($term_id, $comment_args );

	/**
	 * Filter the comments array.
	 *
	 * @since 2.1.0
	 *
	 * @param array $comments Array of comments supplied to the comments template.
	 * @param int   $post_ID  Post ID.
	 */
	$wp_query->comments = apply_filters( 'comments_array', $comments, $post->ID );
	$comments = &$wp_query->comments;
	$wp_query->comment_count = count($wp_query->comments);
	update_comment_cache($wp_query->comments);

	if ( $separate_comments ) {
		$wp_query->comments_by_type = separate_comments($comments);
		$comments_by_type = &$wp_query->comments_by_type;
	}

	$overridden_cpage = false;
	if ( '' == get_query_var('cpage') && get_option('page_comments') ) {
		set_query_var( 'cpage', 'newest' == get_option('default_comments_page') ? get_comment_pages_count() : 1 );
		$overridden_cpage = true;
	}

	if ( !defined('COMMENTS_TEMPLATE') )
		define('COMMENTS_TEMPLATE', true);

	$theme_template = STYLESHEETPATH . $file;
	/**
	 * Filter the path to the theme template file used for the comments template.
	 *
	 * @since 1.5.1
	 *
	 * @param string $theme_template The path to the theme template file.
	 */
	$include = apply_filters( 'comments_template', $theme_template );
	if ( file_exists( $include ) )
		require( $include );
	elseif ( file_exists( TEMPLATEPATH . $file ) )
		require( TEMPLATEPATH . $file );
	else // Backward compat code will be removed in a future release
		require( ABSPATH . WPINC . '/theme-compat/comments.php');
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.  
 * Used in place of Largo's largo_comment() and Wordpress' comment().
 * @since 0.3
 */

function bp_comment( $comment, $args = array('max_depth' => 3), $depth = 1 ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback', 'largo' ); ?>: <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'largo' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
				break;
			default :
	?>
	<li <?php comment_class("comment-" . $comment->comment_source); ?> id="li-comment-<?php comment_ID(); ?>">
			
		<article id="comment-<?php comment_ID(); ?>" class="comment">
				
			<header class="comment-meta">
								
				<?php
					if ($comment->comment_source == 'website') :
						$avatar_size = 68;
						if ( '0' != $comment->comment_parent )
							$avatar_size = 39;
					
						echo get_avatar( $comment, $avatar_size );
					
					elseif ($comment->comment_source == 'facebook') : ?>		
				
				<div class="wrap-icon">			
					<img class="facebook-icon" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/FB-f-Logo__blue_29.png"/>
				</div>
					
				<?php elseif ($comment->comment_source == 'twitter') : ?>
						
				<div class="wrap-icon">			
					<img class="twitter-icon" src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/twitter-logo-29.png"/>
				</div>
						
				<?php endif; ?>
					
				<?php if ($comment->member) : ?>
					<div class="member-medal">
						<img src="/wp-content/themes/Largo-for-Banyan-Project/img/member-medal-140.png" alt="<?php bloginfo('name'); ?> Member" />
					</div>							
				<?php endif; ?>	
					
				<div class="comment-author">
						
					<?php printf('<span class="fn">%s</span>', get_comment_author_link()); ?>
					
					<?php 
						if ($comment->comment_source == 'website'):
				
							if ($comment->comment_parent == 0) {
									
								if ($comment->comment_post_ID != 0) {
									echo('<span>&nbsp;&nbsp;>&nbsp;&nbsp;</span>');
									$parent = get_post($comment->post_ID);
									printf('<a class="comment-parent" href="%1$s">%2$s</a>', get_permalink($parent) , $parent->post_title);
								}
									
							} else {
									
								echo('<span>&nbsp;&nbsp;>&nbsp;&nbsp;</span>');
								$parent = get_comment($comment->comment_parent);
								printf('<span class="comment-parent">%s</span>' , $parent->comment_author);
							}
								
						endif;
					?>

					<?php edit_comment_link( __( 'Edit', 'largo' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .comment-author -->
					
				<?php if ($comment->comment_source == 'twitter') : ?>

				<a class="comment-twitter-username" href="<?php echo('https://twitter.com/' . $comment->twitter_username); ?>">
					<?php echo('@' . $comment->twitter_username); ?>
				</a>
					
				<?php endif; ?>
										
				<div class="comment-datetime">
					<?php
						
						if ($comment->comment_source == 'website') {
							$linktext = '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>';
							$url = esc_url( get_comment_link( $comment->comment_ID ) );
						} else {
							$linktext = '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time> on '. ucfirst($comment->comment_source) .'</a>';
							$url = esc_url($comment->comment_url);
						}

						printf( $linktext,
							$url,
							get_comment_time( 'c' ),
							/* translators: 1: date, 2: time */
							sprintf( '%1$s at %2$s', get_comment_date(), get_comment_time() )
						);
					?>
				</div>
					
			</header>

			<div class="comment-content">
					
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'largo' ); ?></em>
					<br />
				<?php endif; ?>

				<?php comment_text(); ?>
										
			</div>

			<?php if ($comment->comment_source == 'website') : ?>
				
			<div class="wrap-comment-vote">
					
				<?php if (is_user_logged_in()) : ?>
						
				<?php bp_show_upvotes($comment->upvotes); ?>
				<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
				<?php bp_show_alerts($comment->alerts); ?>
					
				<?php else : ?>
					
				<?php bp_show_upvotes_anon($comment->upvotes); ?>
				<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
				<?php bp_show_alerts_anon($comment->alerts); ?>
											
				<?php endif; ?>
					
			</div>
				
			<?php elseif ($comment->comment_source == 'facebook') : ?>
					
			<div class="wrap-facebook-like">
				<div class="fb-like" 
					data-href="<?php echo($comment->comment_url); ?>" 
					data-layout="button_count" 
					data-action="like" 
					data-show-faces="false">
	    		</div>
			</div>
				
			<?php elseif ($comment->comment_source == 'twitter') : ?>
				
			<div class="wrap-twitter-intents">
				<a href="https://twitter.com/intent/tweet?in_reply_to=<?php echo($comment->tweet_id); ?>">
					<img src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/reply-action.png" width="20">
				</a>
				<a href="https://twitter.com/intent/retweet?tweet_id=<?php echo($comment->tweet_id); ?>">
					<img src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/retweet-action.png" width="20">
				</a>
				<a href="https://twitter.com/intent/like?tweet_id=<?php echo($comment->tweet_id); ?>">
					<img src="/wp-content/themes/Largo-for-Banyan-Project/img/icons/like-action.png" width="20">
				</a>		
			</div>
				
			<?php endif; ?>		

			<?php if (is_user_logged_in()) : ?>
			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => sprintf( '%s <span>&darr;</span>', __( 'Reply', 'largo' ) ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
			<?php endif; ?>
				
				<!-- Dialog message -->
								
		</article><!-- #comment-## -->

	<?php
		break;
	endswitch;
}


/**
 * Display upvotes icon on comment template with upvoting enabled
 */
function bp_show_upvotes($votes=0) {
    $link = "";
    $nonce = wp_create_nonce("comment_upvote_nonce");
    $current_CommentID =  get_comment_ID();
    $arguments = $current_CommentID.",'".$nonce."'";
    $link = ' <a onclick="comment_upvote('.$arguments.');" title="Click to upvote this comment."><span class="glyphicon glyphicon-thumbs-up"></span></a>';
    $completelink = '<span class="comment-upvote" id="comment-upvote-'.$current_CommentID.'">';
    $completelink .= $link.'&nbsp;&nbsp;<span id="comment-upvote-count-'. $current_CommentID .'">'.$votes.'</span>';
    $completelink .= '</span>';
    echo $completelink;
}

/**
 * Display upvotes icon on comment template for anonymous users
 */
function bp_show_upvotes_anon($votes=0) {
    $link = "";
    $link = ' <a onclick="alert(\'You must be logged in to upvote a comment.\')" title="Please log in to upvote this comment."><span class="glyphicon glyphicon-thumbs-up"></span></a>';
    $completelink = '<span class="comment-upvote" id="comment-upvote-'.$current_CommentID.'">';
    $completelink .= $link.'&nbsp;&nbsp;<span id="comment-upvote-count-'. $current_CommentID .'">'.$votes.'</span>';
    $completelink .= '</span>';
    echo $completelink;
}

/**
 * handles upvote ajax submissions
 */
function bp_ajax_comment_upvote_handler() {
    if ( !wp_verify_nonce( $_POST['nonce'], "comment_upvote_nonce")) {
        die("<span>Error</span>");
    }
 
    $results = '';
	$data = array();
      
  	$comment = bp_get_comment_from_id($_POST['commentid']);
	
	$upvotes = $comment->upvotes;
		
	// validate user
	if ($comment->upvotes_users != NULL) {
					
		$upvoters = unserialize($comment->upvotes_users);
		
		if (in_array(get_current_user_id(),$upvoters))
			die("<span>" . $upvotes . " - You have already voted.</span>");
		else {
			$upvoters[] = get_current_user_id();
			$data['upvotes_users'] = serialize($upvoters);
		}
		
	} else {
		
		$data['upvotes_users'] = serialize(array(get_current_user_id()));		
	}
	
	$upvotes++;
	
	$data['upvotes'] = $upvotes;
	
    global $wpdb;
    $wpdb->update( 'bp_comments', $data, array('comment_id' => $_POST['commentid']) ); 
	
    $results .= '<span>' . $upvotes . '</span>';
 
    //Return the String
    die($results);
}

add_action( 'wp_ajax_comment_upvote_handler', 'bp_ajax_comment_upvote_handler' );

/**
 * Displays alert icon and enables alert voting
 */
function bp_show_alerts($alerts=0) {
    $link = "";
    $nonce = wp_create_nonce("comment_alert_nonce");
    $current_CommentID =  get_comment_ID();
    $arguments = $current_CommentID.",'".$nonce."'";
    $link = ' <a onclick="comment_alert('.$arguments.');" title="Click to alert the editor that this comment is not relevant and respectful, or makes a personal attack."><span class="glyphicon glyphicon-alert"></span></a>';
    $completelink = '<span class="comment-alert" id="comment-alert-'.$current_CommentID.'">';
    $completelink .= $link.'&nbsp;&nbsp;<span id="comment-alert-count-'. $current_CommentID .'">'.$alerts.'</span>';
    $completelink .= '</span>';
    echo $completelink;
}

 
/**
 * Displays alert icon for anonymous users, with alerts disabled
 */ 
function bp_show_alerts_anon($alerts=0) {
    $link = "";
    $link = ' <a onclick="alert(\'You must be logged in to send an alert.\')" title="Please log in to send an alert."><span class="glyphicon glyphicon-alert"></span></a>';
    $completelink = '<span class="comment-alert" id="comment-alert-'.$current_CommentID.'">';
    $completelink .= $link.'&nbsp;&nbsp;<span id="comment-alert-count-'. $current_CommentID .'">'.$alerts.'</span>';
    $completelink .= '</span>';
    echo $completelink;
}


/**
 * Handles alert ajax submissions
 */ 
function bp_ajax_comment_alert_handler() {
    if ( !wp_verify_nonce( $_POST['nonce'], "comment_alert_nonce")) {
        die("<span>Error</span>");
    }
 
    $results = '';
	$data = array();
      
  	$comment = bp_get_comment_from_id($_POST['commentid']);
	
	$alerts = $comment->alerts;
		
	// validate user
	if ($comment->alerts_users != NULL) {
					
		$alerts_users = unserialize($comment->alerts_users);
		
		if (in_array(get_current_user_id(),$alerts_users))
			die("<span>" . $alerts . " - You may only make one alert per comment.</span>");
		else {
			$alerts_users[] = get_current_user_id();
			$data['alerts_users'] = serialize($alerts_users);
		}
		
	} else {
		
		$data['alerts_users'] = serialize(array(get_current_user_id()));		
	}
	
	$alerts++;
	
	$data['alerts'] = $alerts;
	
    global $wpdb;
    $wpdb->update( 'bp_comments', $data, array('comment_id' => $_POST['commentid']) ); 
	
    $results .= '<span>' . $alerts . ' - Thank you! Your alert has been sent to our editors.</span>';

	// Alert message

	$user = wp_get_current_user();
	
	$comment_url = get_category_link($comment->term_id) . '#comment-' . $comment->comment_ID;
	
	$adminMsg = new MandrillMessage('affiliate-admin-comment-alert');
	$adminMsg->setFrom(get_bloginfo('name'), of_get_from_email('from_email'));
	$adminMsg->setReplyTo($user->user_login, $user->user_email);	
	$adminMsg->setTo(of_get_option('editor_name'), of_get_option('editor_email'));
	$adminMsg->setSubject('Comment Alert!');
		
	$adminMsg->setVariable('username', $user->user_login);
	$adminMsg->setVariable('comment_url', $comment_url);
	$adminMsg->setVariable('comment_text', $comment->comment_content);		
 	$adminMsg->send();
 
    //Return the String
    die($results);
}

add_action( 'wp_ajax_comment_alert_handler', 'bp_ajax_comment_alert_handler' );


/**
 * Output a complete comment form for use within a template.
 *
 * Most strings and form fields may be controlled through the $args array passed
 * into the function, while you may also choose to use the comment_form_default_fields
 * filter to modify the array of default fields if you'd just like to add a new
 * one or remove a single field. All fields are also individually passed through
 * a filter of the form comment_form_field_$name where $name is the key used
 * in the array of fields.
 * 
 * @param array       $args {
 *     Optional. Default arguments and form fields to override.
 *
 *     @type array $fields {
 *         Default comment fields, filterable by default via the 'comment_form_default_fields' hook.
 *
 *         @type string $author Comment author field HTML.
 *         @type string $email  Comment author email field HTML.
 *         @type string $url    Comment author URL field HTML.
 *     }
 *     @type string $comment_field        The comment textarea field HTML.
 *     @type string $must_log_in          HTML element for a 'must be logged in to comment' message.
 *     @type string $logged_in_as         HTML element for a 'logged in as [user]' message.
 *     @type string $comment_notes_before HTML element for a message displayed before the comment form.
 *                                        Default 'Your email address will not be published.'.
 *     @type string $comment_notes_after  HTML element for a message displayed after the comment form.
 *                                        Default 'You may use these HTML tags and attributes ...'.
 *     @type string $id_form              The comment form element id attribute. Default 'commentform'.
 *     @type string $id_submit            The comment submit element id attribute. Default 'submit'.
 *     @type string $class_submit         The comment submit element class attribute. Default 'submit'.
 *     @type string $name_submit          The comment submit element name attribute. Default 'submit'.
 *     @type string $title_reply          The translatable 'reply' button label. Default 'Leave a Reply'.
 *     @type string $title_reply_to       The translatable 'reply-to' button label. Default 'Leave a Reply to %s',
 *                                        where %s is the author of the comment being replied to.
 *     @type string $cancel_reply_link    The translatable 'cancel reply' button label. Default 'Cancel reply'.
 *     @type string $label_submit         The translatable 'submit' button label. Default 'Post a comment'.
 *     @type string $submit_button        HTML format for the Submit button.
 *                                        Default: '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />'.
 *     @type string $submit_field         HTML format for the markup surrounding the Submit button and comment hidden
 *                                        fields. Default: '<p class="form-submit">%1$s %2$s</a>', where %1$s is the
 *                                        submit button markup and %2$s is the comment hidden fields.
 *     @type string $format               The comment form format. Default 'xhtml'. Accepts 'xhtml', 'html5'.
 * }
 * @param int|WP_Post $post_id Post ID or WP_Post object to generate the form for. Default current post.
 */
function bp_comment_form( $args = array(), $post_id = null ) {
			
	if ( null === $post_id && !is_category())
		$post_id = get_the_ID();
	
	if ($post_id)
		$post = get_post($post_id);
	
	$commenter = wp_get_current_commenter();
	$user = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	
	$args = wp_parse_args( $args );
	if ( ! isset( $args['format'] ) )
		$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
	
	$req      = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$html_req = ( $req ? " required='required'" : '' );
	$html5    = 'html5' === $args['format'];
	$fields   =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . $html_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-describedby="email-notes"' . $aria_req . $html_req  . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website' ) . '</label> ' .
		            '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);
	
	$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );
	
	/**
	 * Filter the default comment form fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields The default comment fields.
	 */
	$fields = apply_filters( 'comment_form_default_fields', $fields );
	$defaults = array(
		'fields'               => $fields,
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-describedby="form-allowed-tags" aria-required="true" required="required"></textarea></p>',
		/** This filter is documented in wp-includes/link-template.php */
		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		/** This filter is documented in wp-includes/link-template.php */
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . __( 'Your email address will not be published.' ) . '</span>'. ( $req ? $required_text : '' ) . '</p>',
		'comment_notes_after'  => '<p class="form-allowed-tags" id="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'class_submit'         => 'submit',
		'name_submit'          => 'submit',
		'title_reply'          => '',
		'title_reply_to'       => __( 'Leave a Reply to %s' ),
		'cancel_reply_link'    => __( 'Cancel reply' ),
		'label_submit'         => __( 'Post Comment' ),
		'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
		'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
		'format'               => 'xhtml',
	);
	
	/**
	 * Filter the comment form default arguments.
	 *
	 * Use 'comment_form_default_fields' to filter the comment fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $defaults The default comment form arguments.
	 */
	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );
	
	if ( comments_open( $post_id ) ) : ?>
		<?php
		/**
		 * Fires before the comment form.
		 *
		 *  @since 3.0.0
		 */
			do_action( 'comment_form_before' );
		?>
			<div id="respond" class="comment-respond">
				
				<p id="reply-title" class="comment-reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> </p> <p><small><?php cancel_comment_reply_link( $args['cancel_reply_link'] ); ?></small></p>

				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
					<?php echo $args['must_log_in']; ?>
					<?php
					/**
					 * Fires after the HTML-formatted 'must log in after' message in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_must_log_in_after' );
					?>
				<?php else : ?>
					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form"<?php echo $html5 ? ' novalidate' : ''; ?>>
						<?php
						/**
						 * Fires at the top of the comment form, inside the form tag.
						 *
						 * @since 3.0.0
						 */
						do_action( 'comment_form_top' );
						?>
						<?php if ( is_user_logged_in() ) : ?>

							<?php									
							/**
							 * Add the comment header
							 */	
							?>
								
							<header class="comment-meta clearfix">
												
								<?php echo get_avatar($user->ID, 68 ); ?>
																		
								<div class="comment-author">
										
									<?php
										printf('<span class="fn">%s</span>', $user->display_name);										
										//echo('<span>&nbsp;&nbsp;>&nbsp;&nbsp;</span>');
										//echo('<span>'. $post->post_title .'</span>');
									?>
								
								</div><!-- .comment-author -->
									
								<div class="comment-datetime"><?php echo(date('F j, Y \a\t g:ia')); ?></div>

									
								<div class="clearfix"></div>
							</header>
								
							<?php
							/**
							 * Fires after the is_user_logged_in() check in the comment form.
							 *
							 * @since 3.0.0
							 *
							 * @param array  $commenter     An array containing the comment author's
							 *                              username, email, and URL.
							 * @param string $user_identity If the commenter is a registered user,
							 *                              the display name, blank otherwise.
							 */
							do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
							?>
						<?php else : ?>
							<?php echo $args['comment_notes_before']; ?>
							<?php
							/**
							 * Fires before the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_before_fields' );
							foreach ( (array) $args['fields'] as $name => $field ) {
								/**
								 * Filter a comment form field for display.
								 *
								 * The dynamic portion of the filter hook, `$name`, refers to the name
								 * of the comment form field. Such as 'author', 'email', or 'url'.
								 *
								 * @since 3.0.0
								 *
								 * @param string $field The HTML-formatted output of the comment form field.
								 */
								echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
							}
							/**
							 * Fires after the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_after_fields' );
							?>
						<?php endif; ?>
						<?php
						/**
						 * Filter the content of the comment textarea field for display.
						 *
						 * @since 3.0.0
						 *
						 * @param string $args_comment_field The content of the comment textarea field.
						 */
						echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
						?>
						<?php echo $args['comment_notes_after']; ?>
	
						<?php
	
							if (is_category())
								echo(bp_get_comment_id_fields_category( $post_id ));
							else
								echo(get_comment_id_fields( $post_id ));
	
							?>
							
							<p class="form-submit">
								<button type="button" class="btn btn-primary btn-lg" id="comment-submit" data-toggle="modal" data-target="#confirmation-modal">
	  								Post comment
								</button>
							</p>
							
							<?php
	
							/**
							 * Fires at the bottom of the comment form, inside the closing </form> tag.
							 *
							 * @since 1.5.0
							 *
							 * @param int $post_id The post ID.
							 */
							do_action( 'comment_form', $post_id );
							?>
							
					</form>

				<?php endif; ?>

				<!-- Modal -->
				<div class="modal fade" id="confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="Confirmation Form">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					        	<h4 class="modal-title" id="confirmation-modal-label">Please Check Your Comment</h4>
							</div>
							<div class="modal-body" id="confirmation-modal-body">
								<p><?php bloginfo('name'); ?> asks that all comments be relevant, respectful and refrain from personal attacks.  Please confirm that your comment complies with these rules before posting.</p>
								<blockquote id="confirmation-text"></blockquote>
							</div>
							<div class="modal-footer" id="confirmation-buttons">
								<button type="button" class="btn btn-default" data-dismiss="modal" id="confirm-cancel">I'll make changes</button>
								<button type="button" class="btn btn-primary" id="confirm-submit">Submit my comment</button>
								<span id="confirm-message"></span>
					      	</div>
						</div>
					</div>
				</div>

			</div><!-- #respond -->
			<?php
			/**
			 * Fires after the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_after' );

		else :
			/**
			 * Fires after the comment form if comments are closed.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_comments_closed' );
		endif;
}


/**
 * Include hidden term_id field in Comment Form
 */
function bp_inject_term_id( $post_id ) {
			
		if (is_category())
			$term_id = get_queried_object_id();
		else
			$term_id = get_post_meta( get_the_ID(), 'top_term', TRUE );
	
		echo '<p style="display: none;">';
		echo '<input type="hidden" id="term_id" name="term_id" value="'.$term_id.'" />';
		echo '</p>';
}

add_action('comment_form','bp_inject_term_id');


/**
 * Save Comment
 */
function bp_save_comment( $cid, $approved ) {

	if (isset($_POST['term_id']))
		$term_id = $_POST['term_id'];

	// persist 

    if ($approved && is_numeric($term_id)) {
				
		global $wpdb;
		
		$data = array(
			'comment_id' => $cid
			, 'term_id' =>  $term_id
			, 'comment_source' => 'website'
		);
		
		$wpdb->insert('bp_comments', $data);
    }
	
	// info to NationBuilder
	
	$nbapi = new NationbuilderAPI;
	$user = wp_get_current_user();
	$user_nb_raw = get_usermeta($user->ID, 'nationbuilder', true);

	$person = array();
	
	if (!empty($user_nb_raw)) {		
		$user_nb = unserialize($user_nb_raw);
		$person['id'] = $user_nb['person']['id'];		
	} else {		
		$person['email'] = $user->user_email;		
	}
	
	$person['last_wordpress_comment'] = date("Ymd");		
	$res = $nbapi->put('/api/v1/people/push',array('person' => $person));
	
	if (isset($res['person']['id']))
		$res = $nbapi->put("/api/v1/people/{$res['person']['id']}/taggings",array('tagging' => array('tag' => 'web-comment')));		
}

add_action( 'comment_post', 'bp_save_comment', 10, 2 );


/*
 * Responds to AJAX submission of comment
 */
function bp_ajax_comment_handler($comment_ID, $comment_status) {
	
	// screen for Ajax comments
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			
		switch ($comment_status) {
			
			case '0':
			
				//notify moderator of unapproved comment
				wp_notify_moderator($comment_ID);
				break;
				
			case '1': 
			
				//Approved comment				
				echo "success";
				$commentdata = &get_comment($comment_ID);

				$permaurl = get_permalink( $post->ID );
				$url = str_replace('http://', '/', $permaurl);
				
				bp_comment($commentdata);
				
				$post = &get_post($commentdata->comment_post_ID);
				wp_notify_postauthor($comment_ID, $commentdata->comment_type);
				break;
				
			default:
				echo "error";
		}

		exit;
	}
}

add_action('comment_post', 'bp_ajax_comment_handler', 100, 2);

/*
 * Handles comment posts from category pages. Called by comment_id_not_found action.
 */
function bp_category_comment_post($comment_post_ID) {
	
	if ($comment_post_ID != 0) {
		wp_die( __( 'Sorry, there was an error processing your comment.' ), 403 );	
	}


	$comment_author       = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
	$comment_author_email = ( isset($_POST['email']) )   ? trim($_POST['email']) : null;
	$comment_author_url   = ( isset($_POST['url']) )     ? trim($_POST['url']) : null;
	$comment_content      = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
	
	// If the user is logged in
	$user = wp_get_current_user();
	if ( $user->exists() ) {
		if ( empty( $user->display_name ) )
			$user->display_name=$user->user_login;
		
		$user_ID = $user->ID;
		$comment_author       = wp_slash( $user->display_name );
		$comment_author_email = wp_slash( $user->user_email );
		$comment_author_url   = wp_slash( $user->user_url );
		if ( current_user_can( 'unfiltered_html' ) ) {
			if ( ! isset( $_POST['_wp_unfiltered_html_comment'] )
				|| ! wp_verify_nonce( $_POST['_wp_unfiltered_html_comment'], 'unfiltered-html-comment_' . $comment_post_ID )
			) {
				kses_remove_filters(); // start with a clean slate
				kses_init_filters(); // set up the filters
			}
		}
	} else {
		if ( get_option( 'comment_registration' ) || 'private' == $status ) {
			wp_die( __( 'Sorry, you must be logged in to post a comment.' ), 403 );
		}
	}
	
	$comment_type = '';
	
	if ( get_option('require_name_email') && !$user->exists() ) {
		if ( 6 > strlen( $comment_author_email ) || '' == $comment_author ) {
			wp_die( __( '<strong>ERROR</strong>: please fill the required fields (name, email).' ), 200 );
		} elseif ( ! is_email( $comment_author_email ) ) {
			wp_die( __( '<strong>ERROR</strong>: please enter a valid email address.' ), 200 );
		}
	}
	
	if ( '' == $comment_content ) {
		wp_die( __( '<strong>ERROR</strong>: please type a comment.' ), 200 );
	}
	
	$comment_parent = isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0;
	
	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');
	
	$comment_id = wp_new_comment( $commentdata );
	if ( ! $comment_id ) {
		wp_die( __( "<strong>ERROR</strong>: The comment could not be saved. Please try again later." ) );
	}
	
	$comment = get_comment( $comment_id );

	/**
	 * Perform other actions when comment cookies are set.
	 *
	 * @since 3.4.0
	 *
	 * @param object $comment Comment object.
	 * @param WP_User $user   User object. The user may not exist.
	 */
	do_action( 'set_comment_cookies', $comment, $user );
	
	$location = empty($_POST['redirect_to']) ? get_comment_link($comment_id) : $_POST['redirect_to'] . '#comment-' . $comment_id;
	
	/**
	 * Filter the location URI to send the commenter after posting.
	 *
	 * @since 2.0.5
	 *
	 * @param string $location The 'redirect_to' URI sent via $_POST.
	 * @param object $comment  Comment object.
	 */
	$location = apply_filters( 'comment_post_redirect', $location, $comment );
	
	wp_safe_redirect( $location );
	exit;

}

add_action('comment_id_not_found', 'bp_category_comment_post', 100, 2);


/*
 * Add columns for custom fields to edit comment table
 */
 function bp_add_comment_columns($columns) {
	
	if (isset($columns['response']))	
		unset($columns['response']);
	
	$columns['comment_source'] = _x( ' Source', 'column name' );
	$columns['term_id'] = _x( 'Forum', 'column name' );
    return $columns;
}

add_filter('manage_edit-comments_columns', 'bp_add_comment_columns');

/**
 * Render columns for custom fields on edit column table
 */
function bp_render_comment_columns($column_name, $id) {
 
 	$comment = get_comment($id);
 
    switch ($column_name) {
    	
	    case 'term_id':
			$category = get_category($comment->term_id);
			
			if ($category)
				echo($category->name);
			else 
				echo "None";
			
	        break;
			
		case 'comment_source' :
			echo($comment->comment_source);
	}
}

add_action('manage_comments_custom_column', 'bp_render_comment_columns', 10, 2);
 
/**
 * Add custom fields to quick edit menu
 */ 
function bp_comment_quick_edit_form($str, $input) {
    extract($input);
    $table_row = TRUE;
    if ( $mode == 'single' ) {
        $wp_list_table = _get_list_table('WP_Post_Comments_List_Table');
    } else {
        $wp_list_table = _get_list_table('WP_Comments_List_Table');
    }
 
 
    // Get editor string
    ob_start();
        $quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' );
    wp_editor( '', 'replycontent', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings, 'tabindex' => 104 ) );
    $editorStr = ob_get_contents();
    ob_end_clean();
 
 
    // Get nonce string
    ob_start();     
    wp_nonce_field( "replyto-comment", "_ajax_nonce-replyto-comment", false );
        if ( current_user_can( "unfiltered_html" ) )
        wp_nonce_field( "unfiltered-html-comment", "_wp_unfiltered_html_comment", false );
    $nonceStr = ob_get_contents();
    ob_end_clean();
 
 
    $content = '<form method="get" action="">';
    if ( $table_row ) : 
        $content .= '<table style="display:none;"><tbody id="com-reply"><tr id="replyrow" style="display:none;"><td colspan="'.$wp_list_table->get_column_count().'" class="colspanchange">';
    else : 
        $content .= '<div id="com-reply" style="display:none;"><div id="replyrow" style="display:none;">';
    endif;
 
    $content .= '
            <div id="replyhead" style="display:none;"><h5>Reply to Comment</h5></div>
            <div id="addhead" style="display:none;"><h5>Add new Comment</h5></div>
            <div id="edithead" style="display:none;">';
             
    $content .= '   
                <div class="inside">
                <label for="author">Name</label>
                <input type="text" name="newcomment_author" size="50" value="" tabindex="101" id="author" />
                </div>
         
                <div class="inside">
                <label for="author-email">E-mail</label>
                <input type="text" name="newcomment_author_email" size="50" value="" tabindex="102" id="author-email" />
                </div>
         
                <div class="inside">
                <label for="author-url">URL</label>
                <input type="text" id="author-url" name="newcomment_author_url" size="100 value="" tabindex="103" />
                </div>';
				
	$content .= '<div class="inside">
				<label for="comment-term-id">Forum</label>';
	
	$content .=	wp_dropdown_categories(array(
			'show_option_none'   => 'No Forum Assigned',
			'option_none_value'  => '-1',
			'orderby'            => 'NAME', 
			'order'              => 'ASC',
			'show_count'         => false,
			'hide_empty'         => false, 
			'echo'               => false,
			'hierarchical'       => true, 
			'name'               => 'comment-term-id',
			'id'                 => 'comment-term-id',
			'depth'              => 3,
			'tab_index'          => 104,
			'taxonomy'           => 'category',
			'hide_if_empty'      => false,
		));  

	$content .= '</div>
				<div style="clear:both;"></div>';
         
    // Add editor
    $content .= "<div id='replycontainer'>\n";    
    $content .= $editorStr;
    $content .= "</div>\n";   
             
    $content .= '           
            <p id="replysubmit" class="submit">
            <a href="#comments-form" class="cancel button-secondary alignleft" tabindex="106">Cancel</a>
            <a href="#comments-form" class="save button-primary alignright" tabindex="104">
            <span id="addbtn" style="display:none;">Add Comment</span>
            <span id="savebtn" style="display:none;">Update Comment</span>
            <span id="replybtn" style="display:none;">Submit Reply</span></a>
            <img class="waiting" style="display:none;" src="'.esc_url( admin_url( "images/wpspin_light.gif" ) ).'" alt="" />
            <span class="error" style="display:none;"></span>
            <br class="clear" />
            </p>';
             
        $content .= '
            <input type="hidden" name="term_id" id="term_id" value="" />
            <input type="hidden" name="user_ID" id="user_ID" value="'.get_current_user_id().'" />
            <input type="hidden" name="action" id="action" value="" />
            <input type="hidden" name="comment_ID" id="comment_ID" value="" />
            <input type="hidden" name="comment_post_ID" id="comment_post_ID" value="" />
            <input type="hidden" name="status" id="status" value="" />
            <input type="hidden" name="position" id="position" value="'.$position.'" />
            <input type="hidden" name="checkbox" id="checkbox" value="';
         
    if ($checkbox) $content .= '1'; else $content .=  '0';
    $content .= "\" />\n"; 
        $content .= '<input type="hidden" name="mode" id="mode" value="'.esc_attr($mode).'" />';
         
    $content .= $nonceStr;
    $content .="\n";
         
    if ( $table_row ) : 
        $content .= '</td></tr></tbody></table>';
    else : 
        $content .= '</div></div>';
    endif; 
    $content .= "\n</form>\n";
    return $content;
}

add_filter( 'wp_comment_reply', 'bp_comment_quick_edit_form', 10, 2);

/**
 * Adds hidden data to edit comment form 
 */ 
function bp_comment_hidden_data($comment_text, $comment=NULL ) {
	if ($comment) :
    ?>
        <div id="inline-xtra-<?php echo $comment->comment_ID; ?>" class="hidden">
        <div class="comment-term-id"><?php echo esc_attr( $comment->term_id ); ?></div>
        </div>
        <?php
	endif;
    return $comment_text;
}
add_filter( 'comment_text', 'bp_comment_hidden_data', 10, 2); 

/**
 * Creates JavaScript for custom quick edit comment form
 */ 
function bp_comment_quick_edit_javascript() {
?>
    <script type="text/javascript">
   
    function expandedOpen(id) {
        editRow = jQuery('#replyrow');
        rowData = jQuery('#inline-xtra-'+id);
            jQuery('#term_id', editRow).val( jQuery('div.comment-term-id', rowData).text() );
            jQuery('#comment-term-id', editRow).val( jQuery('div.comment-term-id', rowData).text() );
    }   
    
    jQuery('select#comment-term-id').change(function(){
    	jQuery('input#term_id').val( jQuery(this).val() );
    });
    
    </script>
   <?php
}

add_action('admin_footer', 'bp_comment_quick_edit_javascript');

/*
 * Mofifies Quick Edit link in Edit Comment table
 */  
function bp_comment_quick_edit_action($actions, $comment ) {
    global $post;
    $actions['quickedit'] = '<a onclick="commentReply.close();if (typeof(expandedOpen) == \'function\') expandedOpen('.$comment->comment_ID.');commentReply.open( \''.$comment->comment_ID.'\',\''.$post->ID.'\',\'edit\' );return false;" class="vim-q" title="'.esc_attr__( 'Quick Edit' ).'" href="#">' . __( 'Quick&nbsp;Edit' ) . '</a>';
    return $actions;
}

add_filter( 'comment_row_actions', 'bp_comment_quick_edit_action', 10, 2); 


function bp_admin_save_comment($comment_content) {
    global $wpdb;
    $data = array();
	if (is_numeric($_POST['term_id']))
	{
		$data['term_id'] = intval($_POST['term_id']);     
	    $comment_id = absint($_POST['comment_ID']);
	    $res = $wpdb->update( 'bp_comments', $data, compact( 'comment_id' ) );
	}
	
    return $comment_content;
}

add_filter('comment_save_pre', 'bp_admin_save_comment' );


/** 
 * Creates metaboxes for editing comment category in Edit Comment form
 */
function bp_edit_comment_meta_boxes() {
	add_meta_box('bp_edit_comment_category_box', 'Select Forum', 'bp_edit_comment_category_box', 'comment', 'normal');	
}


/**
 * Formats drop-down menu for editing comment in Edit Comment form
 */
function bp_edit_comment_category_box($comment) {
    ?>
        <table class="form-table editcomment comment_xtra">
        <tbody>
        <tr valign="top">
            <td class="first"><?php _e( 'Forum:' ); ?></td>
            <td><?php wp_dropdown_categories(array(
			'show_option_none'   => 'No Forum Assigned',
			'option_none_value'  => '-1',
			'orderby'            => 'NAME', 
			'order'              => 'ASC',
			'show_count'         => false,
			'hide_empty'         => false, 
			'echo'               => true,
			'hierarchical'       => true, 
			'name'               => 'term_id',
			'id'                 => 'comment-term-id',
			'depth'              => 3,
			'tab_index'          => 104,
			'taxonomy'           => 'category',
			'hide_if_empty'      => false,
			'selected'			 => $comment->term_id
		)); ?></td>
        </tr>
       </tbody>
       </table>
    <?php
}

add_action( 'add_meta_boxes', 'bp_edit_comment_meta_boxes');


/**
 * Returns complete Comment object 
 */
function bp_get_comment($comment) {

	global $wpdb;
	$res = $wpdb->get_row($wpdb->prepare("SELECT * FROM bp_comments WHERE comment_id = %d LIMIT 1", $comment->comment_ID));
	
	if ( ! $res )
		return $comment;
	
	foreach (get_object_vars($res) as $k => $v) {
		if ($k != 'comment_id')
			$comment->$k = $v;
	}
 
	return $comment;
}

add_filter('get_comment', 'bp_get_comment', 10, 2);

/**
 * Returns Comment object based on comment ID
 */  
function bp_get_comment_from_id($comment_id) {

	global $wpdb;
	$res = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_comments JOIN bp_comments ON wp_comments.comment_ID = bp_comments.comment_id WHERE wp_comments.comment_ID = %d LIMIT 1", $comment_id));
		
	if ( ! $res )
		return $NULL;
	
	$comment = new stdClass;
	
	foreach (get_object_vars($res) as $k => $v) {
		if ($k != 'comment_id')
			$comment->$k = $v;
	}
 
	return $comment;
}



function bp_get_comment_id_fields_category() {

	$replytoid = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0;
	$result  = "<input type='hidden' name='comment_post_ID' value='0' id='comment_post_ID' />\n";
	$result .= "<input type='hidden' name='comment_parent' id='comment_parent' value='$replytoid' />\n";

	/**
	 * Filter the returned comment id fields.
	 *
	 * @since 3.0.0
	 *
	 * @param string $result    The HTML-formatted hidden id field comment elements.
	 * @param int    $id        The post ID.
	 * @param int    $replytoid The id of the comment being replied to.
	 */
	return apply_filters( 'comment_id_fields', $result, 0, $replytoid );
}


?>