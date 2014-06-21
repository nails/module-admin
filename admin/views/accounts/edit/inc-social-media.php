<?php if ( app_setting( 'social_signin_enabled' ) ) : ?>
<fieldset  id="edit-user-social" class="social">
	<legend><?=lang( 'accounts_edit_social_legend' )?></legend>
	<p>
	<?php

		if ( $user_edit->fb_id  || $user_edit->tw_id || $user_edit->li_id ) :

			if ( $user_edit->fb_id ) :

				echo '<div class="icon" style="background-image: url(' . NAILS_ASSETS_URL . 'img/admin/accounts/icons/facebook-icon.png)">';
				echo 'ID: ' . $user_edit->fb_id;
				echo '<br />Token: ' . wordwrap( $user_edit->fb_token, 100, '<br />', TRUE );
				echo '</div>';

			endif;

			// --------------------------------------------------------------------------

			if ( $user_edit->tw_id ) :

				echo '<div class="icon" style="background-image: url(' . NAILS_ASSETS_URL . 'img/admin/accounts/icons/twitter-icon.png)">';
				echo 'ID: ' . $user_edit->tw_id;
				echo '<br />Token: ' . wordwrap( $user_edit->tw_token, 100, '<br />', TRUE );
				echo '</div>';

			endif;

			// --------------------------------------------------------------------------

			if ( $user_edit->li_id ) :

				echo '<div class="icon" style="background-image: url(' . NAILS_ASSETS_URL . 'img/admin/accounts/icons/linkedin-icon.png)">';
				echo 'ID: ' . $user_edit->li_id;
				echo '<br />Token: ' . wordwrap( $user_edit->li_token, 100, '<br />', TRUE );
				echo '</div>';

			endif;

			// --------------------------------------------------------------------------

		else :

			echo lang( 'accounts_edit_social_none' );

		endif;

	?>
	</p>
</fieldset>
<?php endif;