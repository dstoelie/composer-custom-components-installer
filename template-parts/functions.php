<?php

/**
 * Test function for the custom components installer.
 *
 * @package Composer Custom Components Installer
 */
function another_test() {
	echo 'Hello Again!';
}
add_action( 'init', 'another_test' );
