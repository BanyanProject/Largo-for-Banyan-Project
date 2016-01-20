<?php
/**
 * Description: Email Newsletter Form
 */
  
require_once('inc/class.FormSubmission.php');
 
wp_enqueue_script(
	'email-newsletter',
	'/wp-content/themes/Largo-BP/js/create-user-account.js',
	array('jquery'),
	'0.1',
	true
);

  
class CreateUserAccountForm extends FormSubmission {

	protected $dbtable = '';	
}


// has user submitted form
if (is_array($_POST) && $_POST['submitted'] === '1') {
		
	$form = new CreateUserAccountForm;
	
	// anti-spam token
	if ($form->requiresToken()) {
		$form->checkToken();
	}	
	
	if ($form->isValid()) {
		
		// validation
		$form->validate('full_name','required');
		$form->validate('full_name','stringLength',array('maxlength' => 60));
		$form->validate('email','required');
		$form->validate('email','stringLength',array('maxlength' => 60));
		$form->validate('email','email');
		$form->validate('password','required');
		$form->validate('password','stringLength',array('minlength' => 8, 'maxlength' => 60));
	}
	
	$form->transform();
	
	if ($form->isValid()) {
		
		$form->adminMsg('affiliate-admin-register');
		
		$name = AFFILIATE_NAME;
		
		$form->userMsg('affiliate-user-register');

		//$form->setMandrillFlags();		
	}

	$form->persist();
	
	if (!$form->isValid())
		$response = $form->formatErrorMsgHtml();
		
	if ($form->isValid()) {
		header("Location: ". home_url("/create-a-user-account/thank-you"));
		exit;
	} else
		$response = $form->formatErrorMsgHtml();
}

 
global $shown_ids;

add_filter( 'body_class', function( $classes ) {
	$classes[] = 'normal';
	return $classes;
} );
     
get_header();

?>

<div id="content" role="main">
	
	<?php
		while ( have_posts() ) : the_post();
			
			$shown_ids[] = get_the_ID();
			
			?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php edit_post_link(__('Edit This Page', 'largo'), '<h5 class="byline"><span class="edit-link">', '</span></h5>'); ?>
				</header><!-- .entry-header -->
			
				<section class="entry-content">
					<?php the_content(); ?>
					
					<?php if ($response) : ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $response; ?>
					</div>
					<?php endif; ?>
										
					<form action="<?php the_permalink(); ?>" method="post" id="email-newsletter-form">
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="full_name">Name:</label>
							    <input type="text" name="full_name" value="<?php echo esc_attr($_POST['full_name']); ?>">
							</div>
						</div>
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="email">Email: <span class="required" title="This field is required.">*</span></label>
							    <input type="text" name="email" value="<?php echo esc_attr($_POST['email']); ?>">
							</div>
						</div>
						
						<input type="hidden" name="load_timestamp" value="<?php echo(time()); ?>">
						<input type="hidden" name="submitted" value="1">
						<input type="submit" value="Submit" class="btn btn-success">
					</form>
					
				</section><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->
		
			<?php
		
			if ( is_active_sidebar( 'article-bottom' ) ) {

				do_action( 'largo_before_post_bottom_widget_area' );

				echo '<div class="article-bottom nocontent">';
				dynamic_sidebar( 'article-bottom' );
				echo '</div>';

				do_action( 'largo_after_post_bottom_widget_area' );

			}

		endwhile;
	?>
</div>

<?php do_action( 'largo_after_content' ); ?>

<?php get_footer();
