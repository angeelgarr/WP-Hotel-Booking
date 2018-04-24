<?php

/**
 * Admin View: Admin meta box text field.
 *
 * @version     2.0
 * @package     WP_Hotel_Booking/Views
 * @category    View
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;
?>

<?php
$field      = wp_parse_args( $field, array(
	'id'          => '',
	'name'        => '',
	'std'         => '',
	'placeholder' => '',
	'attr'        => '',
	'filter'      => null
) );

$field_attr = '';
if ( $field['attr'] ) {
	if ( is_array( $field['attr'] ) ) {
		$field_attr = join( " ", $field['attr'] );
	} else {
		$field_attr = $field['attr'];
	}
}

$value = $field['std'];
if ( is_callable( $field['filter'] ) ) {
	$value = call_user_func_array( $field['filter'], array( $value ) );
}

printf( '<input class="regular-text" type="text" name="%s" id="%s" value="%s" placeholder="%s" %s />', $field['name'], $field['id'], $value, $field['placeholder'], $field_attr ); ?>