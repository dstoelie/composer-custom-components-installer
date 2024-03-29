<?php
/**
 * Global functions for the custom components installer.
 *
 * @package Composer Custom Components Installer
 */

// phpcs:disable Generic.Arrays.DisallowShortArraySyntax.Found

/**
 * Function to render options with checkboxes and labels.
 *
 * @param array  $options         The options to render.
 * @param string $selected_option The selected option.
 */
function render_options( $options, $selected_option ) {
	$output = '';
	foreach ( $options as $option => [ $label, $selected ] ) {
		$checkbox = $selected ? '[x]' : '[ ]';
		if ( $option === $selected_option ) {
			$output .= "\033[7m";
		}
		$output .= "$checkbox $label\n\033[0m";
	}
	return $output;
}

/**
 * Function to read single keypress from terminal.
 */
function read_keypress() {
	system( 'stty cbreak -echo' );
	$key = ord( fgetc( STDIN ) );
	system( 'stty -cbreak echo' );
	return $key;
}
