<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-login" id="theme-my-login<?php $template->the_instance(); ?>">

	<?php $template->the_errors(); ?>

	<form name="loginform" id="loginform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'login' ); ?>" method="post">
		<div class="form-group row clearfix">
			<div class="col-sm-8">
				<label for="user_login<?php $template->the_instance(); ?>"><?php
					if ( 'email' == $theme_my_login->get_option( 'login_type' ) )
						_e( 'E-mail', 'theme-my-login' );
					elseif ( 'both' == $theme_my_login->get_option( 'login_type' ) )
						_e( 'Username or E-mail', 'theme-my-login' );
					else
						_e( 'Username', 'theme-my-login' );
				?></label>
				<input type="text" name="log" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'log' ); ?>" size="20" />
			</div>
		</div>
		<div class="form-group row clearfix">
			<div class="col-sm-8">
				<label for="user_pass<?php $template->the_instance(); ?>"><?php _e( 'Password', 'theme-my-login' ); ?></label>
				<input type="password" name="pwd" id="user_pass<?php $template->the_instance(); ?>" class="input" value="" size="20" autocomplete="off" />
			</div>
		</div>

		<?php do_action( 'login_form' ); ?>

		<div class="tml-rememberme-submit-wrap">
			<div class="checkbox row clearfix">
				<div class="col-sm-8">
					<label for="rememberme<?php $template->the_instance(); ?>">
						<input name="rememberme" type="checkbox" id="rememberme<?php $template->the_instance(); ?>" value="forever" />
						<?php esc_attr_e( 'Remember Me', 'theme-my-login' ); ?>
					</label>
				</div>
			</div>

			<p class="tml-submit-wrap">
				<input type="submit" name="wp-submit" class="btn btn-primary" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Log In', 'theme-my-login' ); ?>" />
				<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'login' ); ?>" />
				<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
				<input type="hidden" name="action" value="login" />
			</p>
		</div>
	</form>
	<?php $template->the_action_links( array( 'login' => false ) ); ?>
</div>
