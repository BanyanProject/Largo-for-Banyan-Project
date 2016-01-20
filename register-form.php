<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-register" id="theme-my-login<?php $template->the_instance(); ?>">

	<?php $template->the_errors(); ?>
	
	<form name="registerform" id="registerform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'register' ); ?>" method="post">
		<?php if ( 'email' != $theme_my_login->get_option( 'login_type' ) ) : ?>
		<div class="form-group row clearfix">
			<div class="col-sm-8">
				<label for="user_login<?php $template->the_instance(); ?>"><?php _e( 'Full Name', 'theme-my-login' ); ?></label>
				<p class="form-caption"><?php echo(AFFILIATE_NAME); ?> encourages users to use their real name.</p>
				<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
			</div>
		</div>
		<?php endif; ?>

		<div class="form-group row clearfix">
			<div class="col-sm-8">
				<label for="user_email<?php $template->the_instance(); ?>"><?php _e( 'Email', 'theme-my-login' ); ?></label>
				<p class="form-caption">If you are already a <?php echo(AFFILIATE_NAME); ?> member, please use the email address associated with your membership account.</p>
				<input type="text" name="user_email" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20" />
			</div>
		</div>

		<?php do_action( 'register_form' ); ?>

		<p class="tml-registration-confirmation form-caption" id="reg_passmail<?php $template->the_instance(); ?>"><?php echo apply_filters( 'tml_register_passmail_template_message', __( 'A registration confirmation will be emailed to you.', 'theme-my-login' ) ); ?></p>

		<p class="tml-submit-wrap">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Register', 'theme-my-login' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'register' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="register" />
		</p>
	</form>
		
</div>
