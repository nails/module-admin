<div class="group-settings shop-pg">
	<p <?=$this->input->get( 'is_fancybox' ) ? 'class="system-alert"' : ''?>>
		Configure the <?=$gateway_name?> payment gateway.
	</p>
	<hr />
	<?=form_open( 'admin/settings/shop_pg/' . $this->uri->segment( 4 ) . '?is_fancybox=' . $this->input->get( 'is_fancybox' ) )?>
		<ul class="tabs">
			<li class="tab active">
				<a href="#" data-tab="tab-customise">Customise</a>
			</li>
			<li class="tab">
				<a href="#" data-tab="tab-params">Gateway Parameters</a>
			</li>
		</ul>
		<section class="tabs pages">

			<div class="tab page basics active fieldset" id="tab-customise">
				<?php

					echo '<fieldset>';
						echo '<legend>Checkout Customisation</legend>';
						echo '<p>These customisations will be applied on <em>this</em> site.</p>';

						$_field					= array();
						$_field['key']			= 'omnipay_' . $gateway_slug . '_customise_label';
						$_field['label']		= 'Label';
						$_field['placeholder']	= 'Give this Payment Gateway a custom customer facing label';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['tip']			= 'Set this to override the default payment gateway name.';

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_customise_img';
						$_field['label']	= 'Image';
						$_field['bucket']	= 'shop-pg-img-' . $gateway_slug;
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['tip']		= 'No image is shown by default, but you can choose to show one, perhaps a logo, or an image showing which cards are accepted.';

						echo form_field_mm_image( $_field );

					echo '</fieldset>';

					// --------------------------------------------------------------------------

					echo '<fieldset>';
						echo '<legend>PayPal Customisation</legend>';
						echo '<p>These customisations apply to the PayPal interface.</p>';

						$_field					= array();
						$_field['key']			= 'omnipay_' . $gateway_slug . '_brandName';
						$_field['label']		= 'Brand Name';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['placeholder']	= 'Choose a name for your store, as seen on PayPal. Defaults to PayPal settings.';
						$_field['required']		= FALSE;

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_headerImageUrl';
						$_field['label']	= 'Header Image';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['bucket']	= 'shop-pg-img-' . strtolower( $gateway_slug ) . '-header';
						$_field['tip']		= 'Ideal dimensions are 750x90px';

						echo form_field_mm_image( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_logoImageUrl';
						$_field['label']	= 'Logo';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= FALSE;
						$_field['bucket']	= 'shop-pg-img-' . strtolower( $gateway_slug ) . '-logo';
						$_field['tip']		= 'Ideal dimensions are 190x60px';

						echo form_field_mm_image( $_field );

						// --------------------------------------------------------------------------

						$_field					= array();
						$_field['key']			= 'omnipay_' . $gateway_slug . '_borderColor';
						$_field['label']		= 'Border Color';
						$_field['default']		= app_setting( $_field['key'], 'shop' );
						$_field['required']		= FALSE;
						$_field['placeholder']	= 'e.g., CCCCCC';
						$_field['info']			= 'If supplied, this should be a 6 character hex code, without the # symbol, e.g. FFFFFF for white';

						echo form_field( $_field );

					echo '</fieldset>';

				?>
			</div>

			<div class="tab page basics fieldset" id="tab-params">
				<fieldset>
					<legend>API Credentials</legend>
					<p>
					You can get these details from within your PayPal account's settings.
					</p>
					<?php

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_username';
						$_field['label']	= 'API Username';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_password';
						$_field['label']	= 'API Password';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field_password( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_signature';
						$_field['label']	= 'API Signature';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field( $_field );

					?>
				</fieldset>
				<fieldset>
					<legend>Other Configuration</legend>
					<?php

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_solutionType';
						$_field['label']	= 'Solution Type';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field( $_field );

						// --------------------------------------------------------------------------

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_landingPage';
						$_field['label']	= 'Landing Page';
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field( $_field );

					?>
				</fieldset>
			</div>
		</section>
		<p>
			<button type="submit" class="awesome">
				Save Changes
			</button>
		</p>
	<?=form_close()?>
</div>