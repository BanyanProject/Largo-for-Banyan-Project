<?php
/**
 * Description: Membership Form
 */
  
require_once('inc/class.FormSubmission.php');
require_once('inc/class.CreditCardSubmission.php');

wp_enqueue_script(
	'membership',
	'/wp-content/themes/Largo-for-Banyan-Project/js/membership.js',
	array('jquery'),
	'0.1',
	true
);
  
class MembershipForm extends CreditCardSubmission {

	protected $dbtable = 'frm_membership';	

	public function transform() {
		parent::transform();
		
		// total
		$total = (int) $this->outputValue('type');
				
		if ($this->outputValue('additional_donation') == 1) {
			$total = $total + $this->outputValue('additional_amount');
		}
		
		if ($this->outputValue('recurring') == 'monthly') {
			$total = round($total / 12,2,PHP_ROUND_HALF_UP);
		}	
		
		$this->output[$this->dbtable]['total'] = $total;			
	}
}


// has user submitted form
if (is_array($_POST) && $_POST['submitted'] === '1') {
		
	$form = new MembershipForm;
	
	// anti-spam token
	if ($form->requiresToken()) {
		$form->checkToken();
	}	
	
	if ($form->isValid()) {
		
		// validation
		$form->validate('type','required');
		$form->validate('type','allowedValues',array('36','60','125','250','500'));
		$form->validate('recurring','required');
		$form->validate('recurring','allowedValues',array('annual','monthly','non-recurring'));
		$form->validate('additional_donation','boolean');
	
		if ($_POST['addtional_donation'] == '1') {
			$form->validate('additional_amount','notEmpty',NULL,'If you wish to make an additional donation, you must enter a donation amount.');
			$form->validate('additional_amount','float');
			$form->validate('additional_amount','lessThan',array('max' => 5000),NULL,'Please contact '.AFFLILATE_NAME. ' directly to make an addtional donation of greater than $5,000.');			
		}	
		
		$form->validate('additional_amount','float');
		
		$form->validate('full_name','required');
		$form->validate('full_name','stringLength',array('maxlength' => 60));
		$form->validate('address','required');
		$form->validate('address','stringLength',array('maxlength' => 125));
		$form->validate('city','required');
		$form->validate('city','stringLength',array('maxlength' => 60));
		$form->validate('state_province','stringLength',array('minlength' => 2, 'maxlength' => 2));
		$form->validate('state_province','alpha');
		$form->validate('zip_postal','required');
		$form->validate('zip_postal','stringLength',array('minlength' => 5, 'maxlength' => 10));
		$form->validate('country','required');
		$form->validate('country','stringLength',array('minlength' => 2, 'maxlength' => 2));
		$form->validate('country','alpha');
		$form->validate('email','required');
		$form->validate('email','stringLength',array('maxlength' => 60));
		$form->validate('email','email');
		$form->validate('phone','required');
		$form->validate('phone','stringLength',array('minlength' => 6, 'maxlength' => 20));
		
		$form->validate('cc','required');
		$form->validate('cc','visaMasterCardAmExDiscover');
		$form->validate('exp_month','required');
		$form->validate('exp_month','allowedValues',array('01','02','03','04','05','06','07','08','09','10','11','12'));
		$form->validate('exp_year','required');
		$form->validate('exp_year','stringLength',array('minlength' => 2, 'maxlength' => 2));
		$form->validate('exp_year','greaterThan',date("y"));
		
		$form->validate('email_signup','boolean');
						
	}
	
	$form->transform();
	
	if ($form->isValid() && $form->isApproved()) {
		
		$form->adminMsg('affiliate-admin-membership');
		$form->adminMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->adminMsg()->setTo(DEFAULT_TO_NAME,DEFAULT_TO_EMAIL);
				
		$form->adminMsg()->setSubject('Membership Purchase Notification');
		
		$form->adminMsg()->setVariable('affiliate_name',AFFILIATE_NAME);
		
		$form->adminMsg()->setVariable('full_name',$form->outputValue('full_name'));
		$form->adminMsg()->setVariable('address',$form->outputValue('address'));
		$form->adminMsg()->setVariable('city',$form->outputValue('city'));		
		$form->adminMsg()->setVariable('state_province',$form->outputValue('state_province'));		
		$form->adminMsg()->setVariable('zip_postal',$form->outputValue('zip_postal'));
		$form->adminMsg()->setVariable('country',$form->outputValue('country'));
		$form->adminMsg()->setVariable('sender_email',$form->outputValue('email'));
		$form->adminMsg()->setVariable('phone',$form->outputValue('phone'));
				
		switch ($form->outputValue('recurring')) {
			
			case 'annual' :
				$form->adminMsg()->setVariable('recurring','Annual Recurring Dues Payment');
				break;
				
			case 'monthly' :
				$form->adminMsg()->setVariable('recurring','Monthly Recurring  Dues Payment');
				break;
				
			case 'non-recurring' :
				$form->adminMsg()->setVariable('recurring','No Recurring Dues Payment');
				break; 
								
		}		

		if ($form->outputValue('additional_donation' == '1'))
			$form->adminMsg()->setVariable('additional_donation',$form->outputValue('additional_amount'));
		else		
			$form->adminMsg()->setVariable('additional_donation','None');
			
		$form->adminMsg()->setVariable('total_donation',$form->outputValue('total'));	
		$form->adminMsg()->setVariable('permalink',get_permalink());
		
		$form->adminMsg()->send();
				
		$form->userMsg('affiliate-user-membership');
		$form->userMsg()->setFrom(DEFAULT_FROM_NAME, DEFAULT_FROM_EMAIL);
		$form->userMsg()->setTo($form->outputValue('full_name'), $form->outputValue('email'));
		
		$form->userMsg()->setVariable('affiliate_name',AFFILIATE_NAME);
		$form->userMsg()->setVariable('affiliate_city',AFFILIATE_CITY);

		$form->userMsg()->setVariable('full_name',$form->outputValue('full_name'));
		$form->userMsg()->setVariable('address',$form->outputValue('address'));
		$form->userMsg()->setVariable('city',$form->outputValue('city'));		
		$form->userMsg()->setVariable('state_province',$form->outputValue('state_province'));		
		$form->userMsg()->setVariable('zip_postal',$form->outputValue('zip_postal'));
		$form->userMsg()->setVariable('country',$form->outputValue('country'));
		$form->userMsg()->setVariable('sender_email',$form->outputValue('email'));
		$form->userMsg()->setVariable('phone',$form->outputValue('phone'));
						
		switch ($form->outputValue('recurring')) {
			
			case 'annual' :
				$form->userMsg()->setVariable('recurring','Annual Recurring Dues Payment');
				break;
				
			case 'monthly' :
				$form->userMsg()->setVariable('recurring','Monthly Recurring Dues Payment');
				break;
				
			case 'non-recurring' :
				$form->userMsg()->setVariable('recurring','No Recurring Dues Payment');
				break; 
								
		}		

		if ($form->outputValue('additional_donation' == '1'))
			$form->userMsg()->setVariable('additional_donation',$form->outputValue('additional_amount'));
		else		
			$form->userMsg()->setVariable('additional_donation','None');
			
		$form->userMsg()->setVariable('datetime',date("F j, Y, g:i a"));
		$form->userMsg()->setVariable('total_donation',$form->outputValue('total'));	
		$form->userMsg()->setVariable('cc',$form->outputValue('cc'));
			
		$form->userMsg()->send();	

		$form->setMandrillFlags();
	}

	$form->persist();
	
	if (!$form->isValid())
		$response = $form->formatErrorMsgHtml();
		
	if ($form->isValid()) {
		header("Location: ". home_url("/membership/thank-you"));
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

<div id="content" class="col-md-10 col-md-offset-1" role="main">
	
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
										
					<form action="<?php the_permalink(); ?>" method="post" id="membership-form">
						
						<fieldset>
							
							<!-- Membership Type Radio -->
	
							<div class="form-group row clearfix">
								<div class="col-sm-8">
		 							<label for="type">Type of membership. <span class="required" title="This field is required.">*</span></label>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="type" value="500" id="type-500" <?php if (esc_attr($_POST['type']) == '500') echo 'checked'; ?> >
		 									<span id="500-amount">$500/year</span>
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="type" value="250" id="type-250" <?php if (esc_attr($_POST['type']) == '250') echo 'checked'; ?> >
		 									<span id="250-amount">$250/year</span> <i>Founding Membership</i>
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="type" value="125" id="type-125" <?php if (esc_attr($_POST['type']) == '125') echo 'checked'; ?> >
		 									<span id="125-amount">$125/year</span>
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="type" value="60" id="type-60" <?php if (!isset($_POST['type']) || esc_attr($_POST['type']) == '60') echo 'checked'; ?> >
		 									<span id="60-amount">$60/year</span> <i>Standard Membership</i>
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="type" value="36" id="type-36" <?php if (esc_attr($_POST['type']) == '36') echo 'checked'; ?> >
		 									<span id="36-amount">$36/year</span> <i>Budget Membership</i>
		 								</label>
		 							</div>
								</div>						
							</div>
	
							<!-- Recurring Donation Radio -->
	
							<div class="form-group row clearfix">
								<div class="col-sm-8">
		 							<label for="recurring">Pay membership dues with a recurring contribution. <span class="required" title="This field is required.">*</span></label>
		 							<p class="form-caption">
		 								<?php echo(AFFILIATE_NAME); ?> members are encouraged to pay dues with an automatically recurring contribution, which will keep your membership from expiring. 
		 								Non-recurring memberships must be renewed every year.
		 								Monthly recurring memberships will be billed 1/12 the annual membership rate each month.
		 							</p>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="recurring" value="annual" id="recurring-annual" <?php if (!isset($_POST['recurring']) || esc_attr($_POST['recurring']) == 'annual') echo 'checked'; ?> >
		 									Annually Recurring Membership
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="recurring" value="monthly" id="recurring-monthly" <?php if (esc_attr($_POST['recurring']) == 'monthly') echo 'checked'; ?> >
		 									Monthly Recurring Membership
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="recurring" value="non-recurring" id="non-recurring" <?php if (esc_attr($_POST['recurring']) == 'non-recurring') echo 'checked'; ?> >
		 									Non-Recurring Membership
		 								</label>
		 							</div>
		 							
								</div>						
							</div>
		
							<!-- Additional Donation Radio -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
		 							<label for="additional_donation">Would you like to make an additional donation to <?php echo(AFFILIATE_NAME); ?>? <span class="required" title="This field is required.">*</span></label>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="additional_donation" value="1" id="additional-donation-yes" <?php if (esc_attr($_POST['additional_donation']) == '1') echo 'checked'; ?> >
		 									Yes
		 								</label>
		 							</div>
		 							<div class="radio">
		 								<label> 									
		 									<input type="radio" name="additional_donation" value="0" id="additional-donation-no" <?php if (esc_attr($_POST['additional_donation']) == '0') echo 'checked'; ?> >
		 									No
		 								</label>
		 							</div>
		 						</div>
		 					</div>
			 				
			 				<!-- Additional Donation Text -->
			 					
							<div class="form-group row clearfix" id="wrap-additional-amount" style="display: none;">
								<div class="col-xs-4">
									<label for="additional_amount">Amount of Additional Donation (US$):</label>
									<input type="text" name="additional_amount" id="additional-amount" size="20" maxlength="20" value="<?php echo esc_attr($_POST['additional_amount']); ?>">
								</div>
							</div>
							
			 			</fieldset>

						<fieldset>
							
							<!-- Full Name Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="full_name">Full Name: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="full_name" value="<?php echo esc_attr($_POST['full_name']); ?>">
								</div>
							</div>
							
							<!-- Address Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="address">Address: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="address" value="<?php echo esc_attr($_POST['address']); ?>">
								</div>
							</div>
							
							<!-- City Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="city">City: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="city" value="<?php echo esc_attr($_POST['city']); ?>">
								</div>
							</div>
							
							<!-- State Select -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="state_province">State/Province: </label>
								    
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
								    
								    <div class="form-caption">Required for US and Canada residents</div>
								</div>
							</div>
							
							<!-- Zip/Postal Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-3">
								    <label for="zip_postal">Zip/Postal Code: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="zip_postal" value="<?php echo esc_attr($_POST['zip_postal']); ?>">
								</div>
							</div>
										
							<!-- Country Select -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="country">Country: <span class="required" title="This field is required.">*</span></label>
								    
									<select class="form-control" name="country" id="country">
										
										<option value="US" <?php if (!isset($_POST['country'])) echo('selected'); ?> >United States</option>
										<option value="CA">Canada</option>
										<option value="AF">Afghanistan</option>
										<option value="AL">Albania</option>
										<option value="DZ">Algeria</option>
										<option value="AS">American Samoa</option>
										<option value="AD">Andorra</option>
										<option value="AO">Angola</option>
										<option value="AI">Anguilla</option>
										<option value="AQ">Antarctica</option>
										<option value="AG">Antigua And Barbuda</option>
										<option value="AR">Argentina</option>
										<option value="AM">Armenia</option>
										<option value="AW">Aruba</option>
										<option value="AU">Australia</option>
										<option value="AT">Austria</option>
										<option value="AZ">Azerbaijan</option>
										<option value="BS">Bahamas</option>
										<option value="BH">Bahrain</option>
										<option value="BD">Bangladesh</option>
										<option value="BB">Barbados</option>
										<option value="BY">Belarus</option>
										<option value="BE">Belgium</option>
										<option value="BZ">Belize</option>
										<option value="BJ">Benin</option>
										<option value="BM">Bermuda</option>
										<option value="BT">Bhutan</option>
										<option value="BO">Bolivia</option>
										<option value="BA">Bosnia And Herzegowina</option>
										<option value="BW">Botswana</option>
										<option value="BV">Bouvet Island</option>
										<option value="BR">Brazil</option>
										<option value="IO">British Indian Ocean Territory</option>
										<option value="BN">Brunei Darussalam</option>
										<option value="BG">Bulgaria</option>
										<option value="BF">Burkina Faso</option>
										<option value="BI">Burundi</option>
										<option value="KH">Cambodia</option>
										<option value="CM">Cameroon</option>
										<option value="CV">Cape Verde</option>
										<option value="KY">Cayman Islands</option>
										<option value="CF">Central African Republic</option>
										<option value="TD">Chad</option>
										<option value="CL">Chile</option>
										<option value="CN">China</option>
										<option value="CX">Christmas Island</option>
										<option value="CC">Cocos (Keeling) Islands</option>
										<option value="CO">Colombia</option>
										<option value="KM">Comoros</option>
										<option value="CG">Congo</option>
										<option value="CD">Congo, The Democratic Republic Of The</option>
										<option value="CK">Cook Islands</option>
										<option value="CR">Costa Rica</option>
										<option value="CI">Cote D'Ivoire</option>
										<option value="HR">Croatia (Hrvatska)</option>
										<option value="CU">Cuba</option>
										<option value="CY">Cyprus</option>
										<option value="CZ">Czech Republic</option>
										<option value="DK">Denmark</option>
										<option value="DJ">Djibouti</option>
										<option value="DM">Dominica</option>
										<option value="DO">Dominican Republic</option>
										<option value="TP">East Timor</option>
										<option value="EC">Ecuador</option>
										<option value="EG">Egypt</option>
										<option value="SV">El Salvador</option>
										<option value="GQ">Equatorial Guinea</option>
										<option value="ER">Eritrea</option>
										<option value="EE">Estonia</option>
										<option value="ET">Ethiopia</option>
										<option value="FK">Falkland Islands (Malvinas)</option>
										<option value="FO">Faroe Islands</option>
										<option value="FJ">Fiji</option>
										<option value="FI">Finland</option>
										<option value="FR">France</option>
										<option value="FX">France, Metropolitan</option>
										<option value="GF">French Guiana</option>
										<option value="PF">French Polynesia</option>
										<option value="TF">French Southern Territories</option>
										<option value="GA">Gabon</option>
										<option value="GM">Gambia</option>
										<option value="GE">Georgia</option>
										<option value="DE">Germany</option>
										<option value="GH">Ghana</option>
										<option value="GI">Gibraltar</option>
										<option value="GR">Greece</option>
										<option value="GL">Greenland</option>
										<option value="GD">Grenada</option>
										<option value="GP">Guadeloupe</option>
										<option value="GU">Guam</option>
										<option value="GT">Guatemala</option>
										<option value="GN">Guinea</option>
										<option value="GW">Guinea-Bissau</option>
										<option value="GY">Guyana</option>
										<option value="HT">Haiti</option>
										<option value="HM">Heard And McDonald Islands</option>
										<option value="VA">Holy See (Vatican City State)</option>
										<option value="HN">Honduras</option>
										<option value="HK">Hong Kong</option>
										<option value="HU">Hungary</option>
										<option value="IS">Iceland</option>
										<option value="IN">India</option>
										<option value="ID">Indonesia</option>
										<option value="IR">Iran (Islamic Republic Of)</option>
										<option value="IQ">Iraq</option>
										<option value="IE">Ireland</option>
										<option value="IL">Israel</option>
										<option value="IT">Italy</option>
										<option value="JM">Jamaica</option>
										<option value="JP">Japan</option>
										<option value="JO">Jordan</option>
										<option value="KZ">Kazakhstan</option>
										<option value="KE">Kenya</option>
										<option value="KI">Kiribati</option>
										<option value="KP">Korea, Democratic People's Republic of</option>
										<option value="KR">Korea, Republic Of</option>
										<option value="KW">Kuwait</option>
										<option value="KG">Kyrgyzstan</option>
										<option value="LA">Lao People's Democratic Republic</option>
										<option value="LV">Latvia</option>
										<option value="LB">Lebanon</option>
										<option value="LS">Lesotho</option>
										<option value="LR">Liberia</option>
										<option value="LY">Libya</option>
										<option value="LI">Liechtenstein</option>
										<option value="LT">Lithuania</option>
										<option value="LU">Luxembourg</option>
										<option value="MO">Macau</option>
										<option value="MK">Macedonia, Former Yugoslav Republic Of</option>
										<option value="MG">Madagascar</option>
										<option value="MW">Malawi</option>
										<option value="MY">Malaysia</option>
										<option value="MV">Maldives</option>
										<option value="ML">Mali</option>
										<option value="MT">Malta</option>
										<option value="MH">Marshall Islands</option>
										<option value="MQ">Martinique</option>
										<option value="MR">Mauritania</option>
										<option value="MU">Mauritius</option>
										<option value="YT">Mayotte</option>
										<option value="MX">Mexico</option>
										<option value="FM">Micronesia, Federated States Of</option>
										<option value="MD">Moldova, Republic Of</option>
										<option value="MC">Monaco</option>
										<option value="MN">Mongolia</option>
										<option value="MS">Montserrat</option>
										<option value="MA">Morocco</option>
										<option value="MZ">Mozambique</option>
										<option value="MM">Myanmar</option>
										<option value="NA">Namibia</option>
										<option value="NR">Nauru</option>
										<option value="NP">Nepal</option>
										<option value="NL">Netherlands</option>
										<option value="AN">Netherlands Antilles</option>
										<option value="NC">New Caledonia</option>
										<option value="NZ">New Zealand</option>
										<option value="NI">Nicaragua</option>
										<option value="NE">Niger</option>
										<option value="NG">Nigeria</option>
										<option value="NU">Niue</option>
										<option value="NF">Norfolk Island</option>
										<option value="MP">Northern Mariana Islands</option>
										<option value="NO">Norway</option>
										<option value="OM">Oman</option>
										<option value="PK">Pakistan</option>
										<option value="PW">Palau</option>
										<option value="PA">Panama</option>
										<option value="PG">Papua New Guinea</option>
										<option value="PY">Paraguay</option>
										<option value="PE">Peru</option>
										<option value="PH">Philippines</option>
										<option value="PN">Pitcairn</option>
										<option value="PL">Poland</option>
										<option value="PT">Portugal</option>
										<option value="PR">Puerto Rico</option>
										<option value="QA">Qatar</option>
										<option value="RE">Reunion</option>
										<option value="RO">Romania</option>
										<option value="RU">Russian Federation</option>
										<option value="RW">Rwanda</option>
										<option value="KN">Saint Kitts And Nevis</option>
										<option value="LC">Saint Lucia</option>
										<option value="VC">Saint Vincent And The Grenadines</option>
										<option value="WS">Samoa</option>
										<option value="SM">San Marino</option>
										<option value="ST">Sao Tome And Principe</option>
										<option value="SA">Saudi Arabia</option>
										<option value="SN">Senegal</option>
										<option value="SC">Seychelles</option>
										<option value="SL">Sierra Leone</option>
										<option value="SG">Singapore</option>
										<option value="SK">Slovakia (Slovak Republic)</option>
										<option value="SI">Slovenia</option>
										<option value="SB">Solomon Islands</option>
										<option value="SO">Somalia</option>
										<option value="ZA">South Africa</option>
										<option value="GS">South Georgia, South Sandwich Islands</option>
										<option value="ES">Spain</option>
										<option value="LK">Sri Lanka</option>
										<option value="SH">St. Helena</option>
										<option value="PM">St. Pierre And Miquelon</option>
										<option value="SD">Sudan</option>
										<option value="SR">Suriname</option>
										<option value="SJ">Svalbard And Jan Mayen Islands</option>
										<option value="SZ">Swaziland</option>
										<option value="SE">Sweden</option>
										<option value="CH">Switzerland</option>
										<option value="SY">Syrian Arab Republic</option>
										<option value="TW">Taiwan</option>
										<option value="TJ">Tajikistan</option>
										<option value="TZ">Tanzania, United Republic Of</option>
										<option value="TH">Thailand</option>
										<option value="TG">Togo</option>
										<option value="TK">Tokelau</option>
										<option value="TO">Tonga</option>
										<option value="TT">Trinidad And Tobago</option>
										<option value="TN">Tunisia</option>
										<option value="TR">Turkey</option>
										<option value="TM">Turkmenistan</option>
										<option value="TC">Turks And Caicos Islands</option>
										<option value="TV">Tuvalu</option>
										<option value="UG">Uganda</option>
										<option value="UA">Ukraine</option>
										<option value="AE">United Arab Emirates</option>
										<option value="GB">United Kingdom</option>
										<option value="UM">United States Minor Outlying Islands</option>
										<option value="UY">Uruguay</option>
										<option value="UZ">Uzbekistan</option>
										<option value="VU">Vanuatu</option>
										<option value="VE">Venezuela</option>
										<option value="VN">Viet Nam</option>
										<option value="VG">Virgin Islands (British)</option>
										<option value="VI">Virgin Islands (U.S.)</option>
										<option value="WF">Wallis And Futuna Islands</option>
										<option value="EH">Western Sahara</option>
										<option value="YE">Yemen</option>
										<option value="YU">Yugoslavia</option>
										<option value="ZM">Zambia</option>
										<option value="ZW">Zimbabwe</option>

									</select>										
									
								</div>
							</div>
							
							<!-- Email Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-8">
								    <label for="email">Email: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="email" value="<?php echo esc_attr($_POST['email']); ?>">
								</div>
							</div>
				
							<!-- Phone Text -->
							
							<div class="form-group row clearfix">
								<div class="col-sm-5">
								    <label for="phone">Phone: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="phone" value="<?php echo esc_attr($_POST['phone']); ?>">
								</div>
							</div>
							
						</fieldset>
						
						<fieldset>

							<!-- Credit Card Number Text -->

							<div class="form-group row clearfix">
								<div class="col-sm-4">
								    <label for="cc">Credit Card Number: <span class="required" title="This field is required.">*</span></label>
								    <input type="text" name="cc" value="<?php echo esc_attr($_POST['cc']); ?>">
								</div>
							</div>

							<!-- Credit Card Expiration Select (2) -->
							
							<div class="form-group row clearfix">

								<div class="col-xs-4 col col-left">
									<label for="exp_month">Expiration Month: <span class="required" title="This field is required." title="This field is required.">*</span></label>
									<select name="exp_month">
										<option value="">Month</option>
										<option value="01" <?php if (esc_attr($_POST['exp_month'] == '01')) echo('selected'); ?> >January</option>
										<option value="02" <?php if (esc_attr($_POST['exp_month'] == '02')) echo('selected'); ?> >February</option>
										<option value="03" <?php if (esc_attr($_POST['exp_month'] == '03')) echo('selected'); ?> >March</option>
										<option value="04" <?php if (esc_attr($_POST['exp_month'] == '04')) echo('selected'); ?> >April</option>
										<option value="05" <?php if (esc_attr($_POST['exp_month'] == '05')) echo('selected'); ?> >May</option>
										<option value="06" <?php if (esc_attr($_POST['exp_month'] == '06')) echo('selected'); ?> >June</option>
										<option value="07" <?php if (esc_attr($_POST['exp_month'] == '07')) echo('selected'); ?> >July</option>
										<option value="08" <?php if (esc_attr($_POST['exp_month'] == '08')) echo('selected'); ?> >August</option>
										<option value="09" <?php if (esc_attr($_POST['exp_month'] == '09')) echo('selected'); ?> >September</option>
										<option value="10" <?php if (esc_attr($_POST['exp_month'] == '10')) echo('selected'); ?> >October</option>
										<option value="11" <?php if (esc_attr($_POST['exp_month'] == '11')) echo('selected'); ?> >November</option>
										<option value="12" <?php if (esc_attr($_POST['exp_month'] == '12')) echo('selected'); ?> >December</option>
									</select>						
								</div>
										
								<div class="col-xs-2 col col-right">
									
									<label for="exp_year">Year: <span class="required" title="This field is required." title="This field is required.">*</span></label>
									
									<select name="exp_year" >
										<option value="">Year </option>
												
											<?php 
												
												$year = date("Y");
												for ($y = $year; $y < ($year + 10); $y++) {
													$short = substr($y,-2);
													$option = "<option value=\"{$short}\"";
													if (esc_attr($_POST['exp_year'] == $y))
														$option .= ' selected ';
													$option .= ">{$y}</option>";
													echo($option);
												}												
												
											?>

									</select>
								</div>

							</div>							
							
						</fieldset>
						
						<fieldset>
							<div class="checkbox">
								<label for="email_signup">
									<input type="checkbox" name="email_signup" value="1" checked> Sign-up to receive weekly email updates from <?php echo(AFFILIATE_NAME); ?>.
								</label>	
							</div>
						</fieldset>

						<p id="form-receipt" style="display: none;"></p>

						<input type="hidden" name="load_timestamp" value="<?php echo(time()); ?>">
						<input type="hidden" name="submitted" value="1">
						<input type="submit" value="Submit" id="btn-submit" class="btn btn-success">
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
