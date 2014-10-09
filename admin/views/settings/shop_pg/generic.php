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
				<p>
					These settings allow you to customise how the customer percieves the payment gateway.
				</p>
				<hr />
				<?php

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
					$_field['bucket']	= 'shop-payment-gateway-image-' . $gateway_slug;
					$_field['default']	= app_setting( $_field['key'], 'shop' );
					$_field['tip']		= 'No image is shown by default, but you can choose to show one, perhaps a logo, or an image showing which cards are accepted.';

					echo form_field_mm_image( $_field );

				?>
			</div>

			<div class="tab page basics fieldset" id="tab-params">
				<p>
					Generally speaking, these fields are provided by <?=$gateway_name?>.
				</p>
				<hr />
				<?php

					foreach ( $params AS $key => $value ) :

						//	Prep the visible label
						$_label		= $key;
						$_label		= preg_replace( '/([a-z])([A-Z])/', '$1 $2', $_label );
						$_label		= ucwords( $_label );
						$_replace	= array( 'API', 'ID' );
						$_label		= str_ireplace( $_replace, $_replace, $_label );

						$_field				= array();
						$_field['key']		= 'omnipay_' . $gateway_slug . '_' . $key;
						$_field['label']	= $_label;
						$_field['default']	= app_setting( $_field['key'], 'shop' );
						$_field['required']	= TRUE;

						echo form_field( $_field );

					endforeach;

				?>
			</div>
		</section>
		<p>
			<button type="submit" class="awesome">
				Save Changes
			</button>
		</p>
	<?=form_close()?>
</div>