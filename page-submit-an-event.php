<?php
/**
 * Description: Submit an Event Form
 */
  
require_once('inc/class.FormSubmission.php');

if (!defined('EVENT_ID'))
	define('EVENT_ID',13);
  
 wp_enqueue_script(
	'contact',
	'/wp-content/themes/Largo-for-Banyan-Project/js/submit-an-event.js',
	array('jquery'),
	'0.1',
	true
);
  
class EventForm extends FormSubmission {

	protected $dbtable = 'wp_posts';
	
	public function __construct() {
		parent::__construct();
		
		// set wp_posts fields
		$d = date("Y-m-d H:i:s");
		$gmd = gmdate("Y-m-d H:i:s");
		
		$this->output[$this->dbtable]['post_author'] = get_current_user_id();
		$this->output[$this->dbtable]['post_date'] = $d;
		$this->output[$this->dbtable]['post_date_gmt'] = $gmd;
		$this->output[$this->dbtable]['post_status'] = 'draft';
		$this->output[$this->dbtable]['post_modified'] = $d;
		$this->output[$this->dbtable]['post_modified_gmt'] = $gmd;
	}
	
	public function transformInput() {
		
		$this->input['start_date'] .= ' ' . $this->input['start_time'];
		$this->input['end_date'] .= ' ' . $this->input['end_time'];
		
	
		foreach ($this->input as $f => $v) {
			
			switch ($f) {
				
				// fields that are not saved
				case 'submitted' :
				case 'token' :
				case 'submit' :
				case 'start_time' :
				case 'end_time' :
					
					break; 
				
				// form fields saved to submission table
				case 'load_timestamp' : 
					
					if (!in_array($f,$this->errorFields))
						$this->output[SUBMISSION_TABLE][$f] = $v;
					
					break;				
				
				// form fields saved to post table
				
				case 'post_content' :
				case 'post_title' :
					
					if (!in_array($f,$this->errorFields)) {
						$this->output[$this->dbtable][$f] = $v;

						if ($f == 'post_title') {
							$this->output[$this->dbtable]['post_name'] = sanitize_title($v);
						}
					}
					break;
				
					
				// all other form fields
				default :
					
					if (!in_array($f,$this->errorFields)) {
						
						if ($f == 'google_maps_embed')
							$this->output['wp_postmeta'][$f] = $v;
						else
							$this->output['wp_postmeta'][$f] = sanitize_text_field($v);
					}
					
					break;
			}
		}
		
		// set end_date = start_date for single-day events (where end_date has been left blank)
			
		if ($this->outputValue('end_date') == NULL) 
			$this->output['wp_postmeta']['end_date'] = $this->outputValue('start_date');
				
	}
	
	public function persist() {
			
		global $wpdb;
		$submission_id = NULL;
		$post_id = NULL;
		$term_id = NULL;		
		
		// save data to submission table regardless of whether input is valid
		
		$res = $wpdb->insert(SUBMISSION_TABLE,$this->output[SUBMISSION_TABLE]);
		$submission_id = $wpdb->insert_id;
		$this->output['wp_postmeta']['submission_id'] = $submission_id;
		
		// save data to wp tables IF input is valid
		
		if ($this->isValid()) {
		
			$res = wp_insert_post($this->output[$this->dbtable]);
					
			if (is_numeric($res) && $res > 0)
				$post_id = $res;
			
			$guid = get_site_url() . "/?p=" . $post_id;
	
			$res = $wpdb->update($this->dbtable,array('guid' => $guid),array('ID' => $post_id));
	
			foreach ($this->output['wp_postmeta'] as $field => $value) {
				
				$metadata = array(
					'post_id' => $post_id
					, 'meta_key' => $field
					, 'meta_value' => $value
				);
	
				$res = $wpdb->insert('wp_postmeta',$metadata);
			}
			
			$term_id = EVENT_ID;
			
			$termdata = array(
				'object_id' => $post_id
				, 'term_taxonomy_id' => $term_id
			);
			
			$res = $wpdb->insert('wp_term_relationships',$termdata);
		}
	}

	public function outputValue($field) 
	{
		if (isset($this->output[SUBMISSION_TABLE][$field]))
			return $this->output[SUBMISSION_TABLE][$field];
		
		if (isset($this->output[$this->dbtable][$field]))
			return $this->output[$this->dbtable][$field];
		
		if (isset($this->output['wp_postmeta'][$field]))
			return $this->output['wp_postmeta'][$field];
		
		return NULL;
	}	
}


// has user submitted form
if (is_array($_POST) && $_POST['submitted'] === '1') {
		
	$form = new EventForm;
	
	// anti-spam token
	if ($form->requiresToken()) {
		$form->checkToken();
	}	
	
	if ($form->isValid()) {
		
		// validation

		$form->validate('post_title','required');
		$form->validate('post_title','stringLength',array('maxlength' => 120));
		$form->validate('post_content', 'required');
		$form->validate('post_content','stringLength',array('maxlength' => 5000));
		$form->validate('event_url','url',NULL,'Event URL must be a valid URL.');
		$form->validate('event_email','email');
		
		$form->validate('event_all_day','boolean');
		$form->validate('start_date','required');
		$form->validate('start_date','date',array('format'=>'YYYY-MM-DD'));
		$form->validate('end_date','date',array('format'=>'YYYY-MM-DD'));
		$form->validate('recurs_until','date',array('format'=>'YYYY-MM-DD'));
		$form->validate('start_time','date',array('format'=>'HH:mm'));
		$form->validate('end_time','date',array('format'=>'HH:mm'));
		
		$form->validate('location_title','required');
		$form->validate('location_title','stringLength',array('maxlength' => 60));
		$form->validate('address','stringLength',array('maxlength' => 125));
		$form->validate('city','required');
		$form->validate('city','stringLength',array('maxlength' => 60));
		$form->validate('state_province','required');
		$form->validate('state_province','stringLength',array('minlength' => 2, 'maxlength' => 2));
		$form->validate('state_province','alpha');

		$form->validate('email_signup','boolean');			
	}
	
	$form->transform();
	
	if ($form->isValid()) {
		
		global $display_name, $user_email, $current_user;
 		get_currentuserinfo();		
		
		$form->adminMsg('affiliate-admin-submit-an-event');
		
		$form->adminMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->adminMsg()->setReplyTo($display_name,$user_email);
		$form->adminMsg()->setTo(DEFAULT_TO_NAME,DEFAULT_TO_EMAIL);
		$form->adminMsg()->setSubject('Event Submission');
	
		$form->adminMsg()->setVariable('affiliate_name', get_bloginfo('name'));
		$form->adminMsg()->setVariable('full_name',$display_name);
		$form->adminMsg()->setVariable('sender_email',$user_email);		
		$form->adminMsg()->setVariable('permalink',get_permalink());
			
		$form->adminMsg()->setVariable('event_name',$form->outputValue('post_title'));
		$form->adminMsg()->setContentMain($form->outputValue('post_content'));
		$form->adminMsg()->setVariable('event_url',$form->outputValue('event_url'));
		$form->adminMsg()->setVariable('event_email',$form->outputValue('event_email'));
		$form->adminMsg()->setVariable('event_cost',$form->outputValue('event_cost'));

		if ($form->outputValue('event_all_day') == 1) 
			$form->adminMsg()->setVariable('event_all_day','Yes');
		else
			$form->adminMsg()->setVariable('event_all_day','No');
		
		$form->adminMsg()->setVariable('start_date',$form->outputValue('start_date'));
		$form->adminMsg()->setVariable('start_time',$form->outputValue('start_time'));
		//$form->adminMsg()->setVariable('end_date',$form->outputValue('end_date'));
		//$form->adminMsg()->setVariable('end_time',$form->outputValue('end_time'));
		
		if ($form->outputValue('event_recurrence') == 'none') {
			$form->adminMsg()->setVariable('event_recurrence','No');
			$form->adminMsg()->setVariable('recurs_until','Not applicable');
		} elseif ($form->outputValue('event_recurrence') == 'weekly') {
			$form->adminMsg()->setVariable('event_recurrence','Yes');
			$form->adminMsg()->setVariable('recurs_until',$form->outputValue('recurs_until'));
		}
				
		$form->adminMsg()->setVariable('location_name',$form->outputValue('location_name'));
		$form->adminMsg()->setVariable('address',$form->outputValue('address'));
		$form->adminMsg()->setVariable('city',$form->outputValue('city'));
		$form->adminMsg()->setVariable('state_province',$form->outputValue('state_province'));

		// google map embed code
		if ($form->outputValue('google_map_embed') != NULL) {
			$form->adminMsg()->setVariable('google_map_embed','Included');
			$form->adminMsg()->setVariable('google_map_description',$form->outputValue('google_map_description'));
		} else {
			$form->adminMsg()->setVariable('google_map_embed','Not included');
			$form->adminMsg()->setVariable('google_map_description','Not applicable');
		}
						
		$form->adminMsg()->send();
		
		$form->userMsg('affiliate-user-submit-an-event');

		$form->userMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->userMsg()->setTo($display_name, $user_email);
		$form->userMsg()->setSubject("Thanks! We've received your event submission.");
		
		$form->userMsg()->setVariable('affiliate_name', get_bloginfo('name'));

		$form->userMsg()->setVariable('event_name',$form->outputValue('post_title'));
		$form->userMsg()->setContentMain($form->outputValue('post_content'));
		$form->userMsg()->setVariable('event_url',$form->outputValue('event_url'));
		$form->userMsg()->setVariable('event_email',$form->outputValue('event_email'));
		$form->userMsg()->setVariable('event_cost',$form->outputValue('event_cost'));

		if ($form->outputValue('event_all_day') == 1) 
			$form->userMsg()->setVariable('event_all_day','Yes');
		else
			$form->userMsg()->setVariable('event_all_day','No');
		
		$form->userMsg()->setVariable('start_date',$form->outputValue('start_date'));
		$form->userMsg()->setVariable('start_time',$form->outputValue('start_time'));
		//$form->userMsg()->setVariable('end_date',$form->outputValue('end_date'));
		//$form->userMsg()->setVariable('end_time',$form->outputValue('end_time'));
		
		if ($form->outputValue('event_recurrence') == 'none') {
			$form->userMsg()->setVariable('event_recurrence','No');
			$form->userMsg()->setVariable('recurs_until','Not applicable');
		} elseif ($form->outputValue('event_recurrence') == 'weekly') {
			$form->userMsg()->setVariable('event_recurrence','Yes');
			$form->userMsg()->setVariable('recurs_until',$form->outputValue('recurs_until'));
		}
				
		$form->userMsg()->setVariable('location_name',$form->outputValue('location_name'));
		$form->userMsg()->setVariable('address',$form->outputValue('address'));
		$form->userMsg()->setVariable('city',$form->outputValue('city'));
		$form->userMsg()->setVariable('state_province',$form->outputValue('state_province'));

		// google map embed code
		if ($form->outputValue('google_map_embed') != NULL) {
			$form->userMsg()->setVariable('google_map_embed','Included');
			$form->userMsg()->setVariable('google_map_description',$form->outputValue('google_map_description'));
		} else {
			$form->userMsg()->setVariable('google_map_embed','Not included');
			$form->userMsg()->setVariable('google_map_description','Not applicable');
		}

		$form->userMsg()->send();

		$form->setMandrillFlags();
	}

	$form->persist();
			
	if ($form->isValid()) {
		header("Location: ". home_url("/submit-an-event/thank-you"));
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

<div id="content" class="col-md-8 col-md-offset-2" role="main">
	
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
					
					<?php if (is_user_logged_in()) : ?>
					
					<?php the_content(); ?>
					
					<?php if ($response) : ?>
					<div class="alert alert-danger" role="alert">
						<?php echo $response; ?>
					</div>
					<?php endif; ?>
										
					<form action="<?php the_permalink(); ?>" method="post" id="event-submission-form">

						<fieldset>
							
							<legend>Event Description</legend>
						
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="post_title">Event Name: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="post_title" value="<?php echo esc_attr($_POST['post_title']); ?>">
								</div>
							</div>
														
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="post_content">Description: <span class="required" title="This field is required.">*</span></label>
								    <p class="form-caption">A promotional description of the event.</p>
								    <textarea type="text" name="post_content" rows="6"><?php echo esc_textarea($_POST['post_content']); ?></textarea>
								</div>
							</div>					

							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="event_url">Event Website: </label>
								    <input type="text" name="event_url" value="<?php echo esc_attr($_POST['event_url']); ?>">
								</div>
							</div>
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="event_email">Event Email Address: </label>
								    <input type="text" name="event_email" value="<?php echo esc_attr($_POST['event_email']); ?>">
								    <p class="form-caption">An email address people can write to find out more information about the event.</p>
								</div>
							</div>

							<div class="form-group row clearfix">
								<div class="col-sm-4">
								    <label for="event_cost">Cost to attend: </label>
								    <input type="text" name="event_cost" value="<?php echo esc_attr($_POST['event_cost']); ?>">
								</div>
							</div>
						
						</fieldset>
						
						<fieldset>
							
							<legend>Event Time</legend>						
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
		 							<label for="event_all_day">All day/multi-day event? <span class="required" title="This field is required.">*</span></label>
		 							<div class="radio">
		 								<label> 									
		 									<input type="checkbox" name="event_all_day" value="1" id="event-all-day" <?php if (esc_attr($_POST['event_all_day']) == '1') echo 'checked'; ?> >
		 									<span id="500-amount">Yes, the event is an all-day or multiple day event.</span>
		 								</label>
		 							</div>
		 						</div>
		 					</div>
							
							<div class="form-group row clearfix" >
								
								<div class="col-sm-4" id="wrap-start-date">
									<label for="start_date"><span id="start-date-label">Date</span>: <span class="required" title="This field is required.">*</span></label>
									<div id="start-date" class="input-group date">
									   	<input type="text" name="start_date" class="form-control"></input>
									    <span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
									    </span>
									</div>				
								</div>

								<div class="col-sm-4" id="wrap-start-time">
									<label for="start_time">Start Time:</label>
									<div id="start-time" class="input-group date">
									    <input type="text" name="start_time" class="form-control"></input>
									    <span class="input-group-addon">
											<span class="glyphicon glyphicon-time"></span>
									    </span>
									</div>				
								</div>
	
							</div>
							
							<div class="form-group row clearfix" >
								
								<div class="col-sm-4" id="wrap-end-date">
									<label for="end_date">End Date:</label>
									<div id="end-date" class="input-group date">
									    <input type="text" name="end_date" class="form-control"></input>
									    <span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
									    </span>
									</div>				
								</div>
								
								<div class="col-sm-4" id="wrap-end-time">
									<label for="end_time">End Time:</label>
									<div id="end-time" class="input-group date">
									    <input type="text" name="end_time" class="form-control"></input>
									    <span class="input-group-addon">
											<span class="glyphicon glyphicon-time"></span>
									    </span>
									</div>				
								</div>
																
							</div>
							
							<div class="form-group row clearfix">

								<div class="col-sm-9">
									<label for="event_recurrence">Does this event recur? <span class="required" title="This field is required." title="This field is required.">*</span></label>
									<select id="event-recurrence" name="event_recurrence">
										<option value="none" <?php if (!isset($_POST['event_recurrence']) || esc_attr($_POST['event_recurrence'] == 'none')) echo('selected'); ?>>Non-Recurring Event</option>
										<option value="weekly"<?php if (esc_attr($_POST['event_recurrence'] == 'weekly')) echo('selected'); ?>>Weekly Recurring Event</option>
									</select>						
								</div>
								
							</div>
							
							<div class="form-group row clearfix" id="wrap-recurs-until" style="display: none;">
								<div class="col-sm-4">
									<label for="recurs_until">Recurs Until:</label>
									<div id="recurs-until" class="input-group date">
									    <input type="text" name="recurs_until" class="form-control"></input>
									    <span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
									    </span>
									</div>				
								</div>								
								
							</div> 	
													
						</fieldset>
						
						<fieldset>
							
							<legend>Event Location</legend>
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="location_title">Location Name: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="location_title" value="<?php echo esc_attr($_POST['location_title']); ?>">
								    <p class="form-caption">A popular name for the event location. You may use 'TBD' for events where the location is not yet determined.</p>
								</div>
							</div>

							<!-- Address Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="address">Address: </label>
								    <input type="text" name="address" value="<?php echo esc_attr($_POST['address']); ?>">
								</div>
							</div>
							
							<!-- City Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="city">City: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="city" value="<?php echo(isset($_POST['city']) ? esc_attr($_POST['city']) : of_get_option('location_col')); ?>">
								</div>
							</div>
							
							<!-- State Select -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="state_province">State/Province: <span class="required" title="This field is required.">*</span></label>
								    
									<select class="form-control" name="state_province" id="state_province">
										<option value="AL" <?php if (esc_attr($_POST['state_province']) == 'AL') echo('selected'); ?> >Alabama</option>
										<option value="AK" <?php if (esc_attr($_POST['state_province']) == 'AK') echo('selected'); ?> >Alaska</option>
										<option value="AZ" <?php if (esc_attr($_POST['state_province']) == 'AZ') echo('selected'); ?> >Arizona</option>
										<option value="AR" <?php if (esc_attr($_POST['state_province']) == 'AR') echo('selected'); ?> >Arkansas</option>
										<option value="CA" <?php if (esc_attr($_POST['state_province']) == 'CA') echo('selected'); ?> >California</option>
										<option value="CO" <?php if (esc_attr($_POST['state_province']) == 'CO') echo('selected'); ?> >Colorado</option>
										<option value="CT" <?php if (esc_attr($_POST['state_province']) == 'CT') echo('selected'); ?> >Connecticut</option>
										<option value="DE" <?php if (esc_attr($_POST['state_province']) == 'DE') echo('selected'); ?> >Delaware</option>
										<option value="DC" <?php if (esc_attr($_POST['state_province']) == 'DC') echo('selected'); ?> >District of Columbia</option>
										<option value="FL" <?php if (esc_attr($_POST['state_province']) == 'FL') echo('selected'); ?> >Florida</option>
										<option value="GA" <?php if (esc_attr($_POST['state_province']) == 'GA') echo('selected'); ?> >Georgia</option>
										<option value="HI" <?php if (esc_attr($_POST['state_province']) == 'HI') echo('selected'); ?> >Hawaii</option>
										<option value="ID" <?php if (esc_attr($_POST['state_province']) == 'ID') echo('selected'); ?> >Idaho</option>
										<option value="IL" <?php if (esc_attr($_POST['state_province']) == 'IL') echo('selected'); ?> >Illinois</option>
										<option value="IN" <?php if (esc_attr($_POST['state_province']) == 'IN') echo('selected'); ?> >Indiana</option>
										<option value="IA" <?php if (esc_attr($_POST['state_province']) == 'IA') echo('selected'); ?> >Iowa</option>
										<option value="KS" <?php if (esc_attr($_POST['state_province']) == 'KS') echo('selected'); ?> >Kansas</option>
										<option value="KY" <?php if (esc_attr($_POST['state_province']) == 'KY') echo('selected'); ?> >Kentucky</option>
										<option value="LA" <?php if (esc_attr($_POST['state_province']) == 'LA') echo('selected'); ?> >Louisiana</option>
										<option value="ME" <?php if (esc_attr($_POST['state_province']) == 'ME') echo('selected'); ?> >Maine</option>
										<option value="MD" <?php if (esc_attr($_POST['state_province']) == 'MD') echo('selected'); ?> >Maryland</option>
										<option value="MA" <?php if (!isset($_POST['state_province']) || esc_attr($_POST['state_province']) == 'MA') echo('selected'); ?> >Massachusetts</option>
										<option value="MI" <?php if (esc_attr($_POST['state_province']) == 'MI') echo('selected'); ?> >Michigan</option>
										<option value="MN" <?php if (esc_attr($_POST['state_province']) == 'MN') echo('selected'); ?> >Minnesota</option>
										<option value="MS" <?php if (esc_attr($_POST['state_province']) == 'MS') echo('selected'); ?> >Mississippi</option>
										<option value="MO" <?php if (esc_attr($_POST['state_province']) == 'MO') echo('selected'); ?> >Missouri</option>
										<option value="MT" <?php if (esc_attr($_POST['state_province']) == 'MT') echo('selected'); ?> >Montana</option>
										<option value="NE" <?php if (esc_attr($_POST['state_province']) == 'NE') echo('selected'); ?> >Nebraska</option>
										<option value="NV" <?php if (esc_attr($_POST['state_province']) == 'NV') echo('selected'); ?> >Nevada</option>
										<option value="NH" <?php if (esc_attr($_POST['state_province']) == 'NH') echo('selected'); ?> >New Hampshire</option>
										<option value="NJ" <?php if (esc_attr($_POST['state_province']) == 'NJ') echo('selected'); ?> >New Jersey</option>
										<option value="NM" <?php if (esc_attr($_POST['state_province']) == 'NM') echo('selected'); ?> >New Mexico</option>
										<option value="NY" <?php if (esc_attr($_POST['state_province']) == 'NY') echo('selected'); ?> >New York</option>
										<option value="NC" <?php if (esc_attr($_POST['state_province']) == 'NC') echo('selected'); ?> >North Carolina</option>
										<option value="ND" <?php if (esc_attr($_POST['state_province']) == 'ND') echo('selected'); ?> >North Dakota</option>
										<option value="OH" <?php if (esc_attr($_POST['state_province']) == 'OH') echo('selected'); ?> >Ohio</option>
										<option value="OK" <?php if (esc_attr($_POST['state_province']) == 'OK') echo('selected'); ?> >Oklahoma</option>
										<option value="OR" <?php if (esc_attr($_POST['state_province']) == 'OR') echo('selected'); ?> >Oregon</option>
										<option value="PA" <?php if (esc_attr($_POST['state_province']) == 'PA') echo('selected'); ?> >Pennsylvania</option>
										<option value="RI" <?php if (esc_attr($_POST['state_province']) == 'RI') echo('selected'); ?> >Rhode Island</option>
										<option value="SC" <?php if (esc_attr($_POST['state_province']) == 'SC') echo('selected'); ?> >South Carolina</option>
										<option value="SD" <?php if (esc_attr($_POST['state_province']) == 'SD') echo('selected'); ?> >South Dakota</option>
										<option value="TN" <?php if (esc_attr($_POST['state_province']) == 'TN') echo('selected'); ?> >Tennessee</option>
										<option value="TX" <?php if (esc_attr($_POST['state_province']) == 'TX') echo('selected'); ?> >Texas</option>
										<option value="UT" <?php if (esc_attr($_POST['state_province']) == 'UT') echo('selected'); ?> >Utah</option>
										<option value="VT" <?php if (esc_attr($_POST['state_province']) == 'VT') echo('selected'); ?> >Vermont</option>
										<option value="VA" <?php if (esc_attr($_POST['state_province']) == 'VA') echo('selected'); ?> >Virginia</option>
										<option value="WA" <?php if (esc_attr($_POST['state_province']) == 'WA') echo('selected'); ?> >Washington</option>
										<option value="WV" <?php if (esc_attr($_POST['state_province']) == 'WV') echo('selected'); ?> >West Virginia</option>
										<option value="WI" <?php if (esc_attr($_POST['state_province']) == 'WI') echo('selected'); ?> >Wisconsin</option>
										<option value="WY" <?php if (esc_attr($_POST['state_province']) == 'WY') echo('selected'); ?> >Wyoming</option>
										<option value="AB" <?php if (esc_attr($_POST['state_province']) == 'AB') echo('selected'); ?> >Alberta</option>
										<option value="BC" <?php if (esc_attr($_POST['state_province']) == 'BC') echo('selected'); ?> >British Columbia</option>
										<option value="MB" <?php if (esc_attr($_POST['state_province']) == 'MB') echo('selected'); ?> >Manitoba</option>
										<option value="NB" <?php if (esc_attr($_POST['state_province']) == 'NB') echo('selected'); ?> >New Brunswick</option>
										<option value="NL" <?php if (esc_attr($_POST['state_province']) == 'NL') echo('selected'); ?> >Newfoundland &amp; Labrador</option>
										<option value="NT" <?php if (esc_attr($_POST['state_province']) == 'NT') echo('selected'); ?> >Northwest Territories</option>
										<option value="NS" <?php if (esc_attr($_POST['state_province']) == 'NS') echo('selected'); ?> >Nova Scotia</option>
										<option value="NU" <?php if (esc_attr($_POST['state_province']) == 'NU') echo('selected'); ?> >Nunavut</option>
										<option value="ON" <?php if (esc_attr($_POST['state_province']) == 'ON') echo('selected'); ?> >Ontario</option>
										<option value="PE" <?php if (esc_attr($_POST['state_province']) == 'PE') echo('selected'); ?> >Prince Edward Island</option>
										<option value="QC" <?php if (esc_attr($_POST['state_province']) == 'QC') echo('selected'); ?> >Quebec</option>
										<option value="SK" <?php if (esc_attr($_POST['state_province']) == 'SK') echo('selected'); ?> >Saskatchewan</option>
										<option value="YT" <?php if (esc_attr($_POST['state_province']) == 'YT') echo('selected'); ?> >Yukon</option>
	
									</select>			    
								    
								</div>
							</div>
							
														
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="google_map_embed">Google Map Embed Code:</label>
								    <p class="form-caption">Create a Google Map for your event, then use the "Share or Embed Map" link to create an embed code, and paste the embed code below.</p>
								    <textarea type="text" name="google_map_embed" rows="3"><?php echo esc_textarea($_POST['google_map_embed']); ?></textarea>
								</div>
							</div>					
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="google_map_description">Google Map Description:</label>
								    <p class="form-caption">A single-paragraph description of the Google Map.</p>
								    <textarea type="text" name="google_map_description" rows="3"><?php echo esc_textarea($_POST['google_map_description']); ?></textarea>
								</div>
							</div>					
							
						</fieldset>

						<input type="hidden" name="load_timestamp" value="<?php echo(time()); ?>">
						<input type="hidden" name="submitted" value="1">
						<input type="submit" value="Submit" class="btn btn-primary">
					</form>
					
					<?php else : // user not logged in ?>
					
					<p>You must have a user account and be logged in to submit an event to <?php bloginfo('name'); ?></p>
					
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
