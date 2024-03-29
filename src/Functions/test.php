<?php
/**
 * Test function for the custom components installer.
 *
 * @package Composer Custom Components Installer
 */
function dave_test() {
	echo 'Hello World!';
}
add_action( 'init', 'dave_test' );
