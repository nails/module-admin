<?php

	$_known_user = isset( $id ) && $id ? '' : 'no-data';
	echo '<td class="' . $_known_user . ' user-cell">';

	// --------------------------------------------------------------------------

	//	Profile image
	if ( isset( $profile_img ) && $profile_img ) :

		echo anchor( cdn_serve( $profile_img ) ,img( cdn_thumb( $profile_img, 36, 36 ) ), 'class="fancybox"' );

	else :

		$_gender = isset( $gender ) ? $gender : 'undisclosed';
		echo img( cdn_blank_avatar( 36, 36, $_gender ) );

	endif;

	// --------------------------------------------------------------------------

	//	User details
	echo '<span class="user-data">';

		$_name  = '';
		$_name .= isset( $first_name ) && $first_name ? $first_name . ' ' : '';
		$_name .= isset( $last_name ) && $last_name ? $last_name . ' ' : '';
		$_name  = $_name ? $_name : 'Unknown User';

		if ( isset( $id ) && $id ) :

			echo anchor( 'admin/accounts/edit/' . $id, $_name, 'class="fancybox" data-fancybox-type="iframe"' );

		else :

			echo $_name;

		endif;

		if ( isset( $email ) && $email ) :

			echo '<small>' . mailto( $email ) . '</small>';

		else :

			echo '<small>No email address</small>';

		endif;

	echo '</span>';

	// --------------------------------------------------------------------------

	echo '</td>';