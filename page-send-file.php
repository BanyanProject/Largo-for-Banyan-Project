<?php
/**
 * Description: Send File Form
 */

if (!defined(PUBLIC_UPLOAD_DIRECTORY))
	define(PUBLIC_UPLOAD_DIRECTORY,'/tmp/uploads');
   
require_once('inc/class.FormSubmission.php');
  
wp_enqueue_script(
	'send-file',
	'/wp-content/themes/Largo-BP/js/send-file.js',
	array('jquery'),
	'0.1',
	true
);

class SendFileForm extends FormSubmission {

	protected $dbtable = 'frm_file';
	
	protected $cdnUrl = 'https://cdn.banyanproject.coop/affiliate/files';
	
	protected $validMime = array(
		'txt' => 'text/plain'
		, 'png' => 'image/png'
		, 'jpg' => 'image/jpeg'
		, 'gif' => 'image/gif'
		, 'pdf' => 'application/pdf'
	);
	
	protected $fileBlacklist = array(
		'.php'
		,'.phtml'
		,'.php3'
		,'.php4'
		,'.php5'
		,'.html'
		,'.htm'
		,'.xml'
		,'.swf'
		,'.flv'
		,'.zip'
		,'.rar'
		,'.exe'
		,'.msi'
		,'.cab'
		,'.psd'
		,'.ai'
		,'.eps'
		,'.ps'
		,'.doc'
		,'.rtf'
		,'.xls'
		,'.ppt'
		,'.odt'
		,'.ods'
		,'.pages'
		,'.numbers'
		,'.keynote'
		,'.java'	
	);
	
	public function validateFileType() {
		
		// check mime
		if (!in_array($_FILES['submission']['type'],$this->validMime)) {
			
			$this->validationErrors[] = "File Submission: for security reasons, your file must be one of the listed file types. ";
			$this->errorFields[] = 'submission';
			return;
		}	
		
		// analyze image file and check mime again
		if (in_array($_FILES['submission']['type'],array('image/png'.'image/jpeg','image/gif'))) {
			
			$imageinfo = getimagesize($_FILES['submission']['tmp_name']);
			
			if (!in_array($imageinfo['mime'],$this->validMime)) {
				$this->validationErrors[] = "File Submission: for security reasons, your file must be one of the listed file types.";
				$this->errorFields[] = 'submission';
				return;
			}
		}
		
		// check against blacklist
		foreach ($this->fileBlacklist as $bad) {
			
			if (preg_match("/{$bad}$/i", $_FILES['submission']['name'])) {
				$this->validationErrors[] = "File Submission: we do not accept {$bad} $files.";
				$this->errorFields[] = 'submission';
				return;
			}
		}

		// no empty files
		if ($_FILES['submission']['size'] == 0) {
			$this->validationErrors[] = "File Submission: your file is blank.";
			$this->errorFields[] = 'submission';
		}


		// validate file name -- no blank characters
		if (!preg_match("`^[-0-9A-Z_\.]+$`i",$_FILES['submission']['name'])) {
			$this->validationErrors[] = "File Submission: your file has an invalid name.  The file name must consist of English letters, numbers, and the following characters [-_.].";
			$this->errorFields[] = 'submission';
		}	
	
		// file name length
		if (mb_strlen($_FILES['submission']['name'],"UTF-8") < 8) {
			$this->validationErrors[] = "File Submission: your file name must be longer than 8 characters.";
			$this->errorFields[] = 'submission';
		}

		if (mb_strlen($_FILES['submission']['name'],"UTF-8") > 100) {
			$this->validationErrors[] = "File Submission: your file name must be shorter than 100 characters.";
			$this->errorFields[] = 'submission';
		}		
	}
	
	public function saveFile() {
		
		/*
		 * TODO: Replace uploads directory with Rackspace.
		 */
		
		$dir = PUBLIC_UPLOAD_DIRECTORY."/".date("Y")."/".date("m")."/".date("d")."/" . time();
				
		mkdir($dir,0777,true);							
		$fileLocation = $dir . "/" . basename($_FILES['submission']['name']);
						
		$res = move_uploaded_file($_FILES['submission']['tmp_name'],$fileLocation);
		
		
		if (!$res) {
			$this->validationErrors[] = "Due to a server error, your file could not be sent.";
		}
		
		$this->output[$this->dbtable]['location'] = $this->getFileURL();
		
	}
	
	protected function getFileFolder()
	{
		return $this->cdnUrl . date("Y")."/".date("m")."/".date("d"). "/" . time();
	}

	public function getFileURL() {	
		return $this->getFileFolder(). "/" . basename($_FILES['submission']['name']);
	}
		
}


// has user submitted form
if (is_array($_POST) && $_POST['submitted'] === '1') {
		
	$form = new SendFileForm;
	
	// anti-spam token
	if ($form->requiresToken()) {
		$form->checkToken();
	}	
	
	if ($form->isValid()) {
		
		$form->validateFileType();
		
		// validation
		$form->validate('full_name','required');
		$form->validate('full_name','stringLength',array('maxlength' => 60));
		$form->validate('email','required');
		$form->validate('email','stringLength',array('maxlength' => 60));
		$form->validate('email','email');
		$form->validate('message','required');
		$form->validate('message','stringLength',array('maxlength' => 5000));
		$form->validate('email_signup','boolean');
		
	}
	
	if ($form->isValid())
		$form->saveFile();
	
	$form->transform();
	
	if ($form->isValid()) {
		
		$form->adminMsg('affiliate-admin-file-submission');
		$form->adminMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->adminMsg()->setTo(DEFAULT_TO_NAME,DEFAULT_TO_EMAIL);		

		$form->adminMsg()->setSubject('File Submission Notification');		
								
		$form->adminMsg()->setVariable('affiliate_name',AFFILIATE_NAME);
		$form->adminMsg()->setVariable('full_name',$form->outputValue('full_name'));
		$form->adminMsg()->setVariable('sender_email',$form->outputValue('email'));	
		$form->adminMsg()->setVariable('file_location',$form->getFileURL());	
		$form->adminMsg()->setVariable('permalink',get_permalink());
		$form->adminMsg()->setContentMain($form->outputValue('message'));
		$form->adminMsg()->send();
		
		$form->userMsg('affiliate-user-file-submission');
		$form->userMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->userMsg()->setTo($form->outputValue('full_name'), $form->outputValue('email'));
		$form->userMsg()->setSubject("Thanks! We've received your file submission.");
		$form->userMsg()->setVariable('affiliate_name',AFFILIATE_NAME);
		$form->userMsg()->setVariable('file_name',basename($_FILES['submission']['name']));
		$form->userMsg()->setContentMain($form->outputValue('message'));
		$form->userMsg()->send();		

		$form->setMandrillFlags();
	}

	$form->persist();
	
	if (!$form->isValid())
		$response = $form->formatErrorMsgHtml();
		
	if ($form->isValid()) {
		header("Location: ". home_url("/send-file/thank-you"));
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

					<?php if (is_user_logged_in()) : ?>
					
					<?php if ($response) : ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $response; ?>
					</div>
					<?php endif; ?>
										
					<form action="<?php the_permalink(); ?>" method="post" enctype="multipart/form-data" id="send-file-form">
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="full_name">Name: <span class="required" title="This field is required.">*</span></label>
							    <input type="text" name="full_name" value="<?php echo esc_attr($_POST['full_name']); ?>">
							</div>
						</div>
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="email">Email: <span class="required" title="This field is required.">*</span></label>
							    <input type="text" name="email" value="<?php echo esc_attr($_POST['email']); ?>">
							</div>
						</div>
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
								<label for="submission">File: <span class="required" title="This field is required.">*</span></label>
								<input type="file" name="submission">
								<p class="form-caption">
									You may submit image files or .pdf documents.  The file name must consist of letters, numbers, and the following characters: "_" "-" "." . 
								</p>
							</div>
							
						</div>
						
						<div class="form-group row clearfix">
							<div class="col-sm-8">
							    <label for="message">Please describe your file. If you are submitting an image for publication, please include a detailed description of the image contents and an appropriate citation. <span class="required" title="This field is required.">*</span></label>
							    <textarea type="text" name="message" rows="6"><?php echo esc_textarea($_POST['message']); ?></textarea>
							</div>
						</div>
	
						<div class="checkbox">
							<label for="email_signup">
								<input type="checkbox" name="email_signup" value="1" checked> Sign-up to receive weekly email updates from <?php echo(AFFILIATE_NAME); ?>.
							</label>	
						</div>

						<input type="hidden" name="load_timestamp" value="<?php echo(time()); ?>">
						<input type="hidden" name="submitted" value="1">
						<input type="submit" value="Submit" class="btn btn-success">
					</form>
					
					<?php else : ?>
					<!-- Not logged in -->	

					<p>You must have a user account and be logged in to send a file to <?php echo(AFFILIATE_NAME); ?></p>
					
					<p>Click here to create a user account.</p>
					
					<p>Click here to login.</p>
					
					<?php endif; ?>
					
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
