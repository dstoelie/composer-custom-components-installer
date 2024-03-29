#!/usr/bin/env php

<?php
/**
 * Installer for Composer Custom Components Installer.
 *
 * @package Composer Custom Components Installer
 */

// phpcs:disable Generic.Arrays.DisallowShortArraySyntax.Found
// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers/helpers.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

$application = new Application();

$command = $application->register( 'install' )
	->setDescription( 'Install custom files' )
	->setCode(
		function ( InputInterface $input, OutputInterface $output ) use ( $application ) {
			$io = new SymfonyStyle( $input, $output );

			// Clear terminal.
			echo "\033[2J\033[;H";

			// Render instructions.
			echo "Select the type of install:\n";

			// Offer the user a choice between 'Components' and 'Snippets'.
			$question    = new ChoiceQuestion(
				'What kind of install would you like to execute?',
				[ 'Components', 'Snippets' ]
			);
			$installType = $io->askQuestion( $question );

			if ( 'Components' === $installType ) {
				// Options for installing components.
				$options = [
					'text-image.php' => [ 'Text Image Component', false, 'component' ],
					'faq.php'        => [ 'FAQ Component', false, 'component' ],
					'slider.php'     => [ 'Slider Component', false, 'component' ],
				];
			} elseif ( 'Snippets' === $installType ) {
				// Options for installing snippets.
				$options = [
					'test.php'  => [ 'Hello world!', false, 'snippet' ],
					'test2.php' => [ 'Hello Again!', false, 'snippet' ],
				];
			} else {
				// Invalid option selected.
				$io->error( 'Invalid install type selected.' );
				return;
			}

			$selectedOptionIndex = 0; // Index of the currently selected option.

			while ( true ) {
				// Clear terminal.
				echo "\033[2J\033[;H";

				// Render instructions.
				echo "Use arrow keys to navigate, Space to select/deselect, and Enter to confirm:\n";

				// Render options with checkboxes and labels.
				$output->write( render_options( $options, array_keys( $options )[ $selectedOptionIndex ] ) );

				// Get the key pressed by the user.
				$key = read_keypress();

				// Handle arrow keys.
				if ( 65 === $key ) { // Up arrow.
					$selectedOptionIndex = max( 0, $selectedOptionIndex - 1 );
				} elseif ( 66 === $key ) { // Down arrow.
					$selectedOptionIndex = min( count( $options ) - 1, $selectedOptionIndex + 1 );
				} elseif ( 32 === $key ) { // Spacebar to toggle selection.
					$option                = array_keys( $options )[ $selectedOptionIndex ];
					$options[ $option ][1] = ! $options[ $option ][1];
				} elseif ( 10 === $key ) { // Enter to confirm selection.
					break;
				}

				// Move cursor to beginning of the list.
				echo "\033[" . ( count( $options ) + 2 ) . "A\r"; // Add 2 for the instruction line.
			}

			// Install selected files to the destination folder.
			foreach ( $options as $option => [ $label, $selected, $type ] ) {
				if ( $selected && 'component' === $type ) {
					$source      = realpath( __DIR__ . '/../src/TemplateParts/Components/' . $option );
					$destination = __DIR__ . '/../template-parts/components/' . $option;

					if ( $source && file_exists( $source ) ) {
						$destinationDir = dirname( $destination );
						if ( ! file_exists( $destinationDir ) ) {
							mkdir( $destinationDir, 0755, true );
						}

						copy( $source, $destination );
					} else {
						$output->writeln( "<error>Source file '{$option}' does not exist.</error>" );
					}
				}

				if ( $selected && 'snippet' === $type ) {
					$destination = __DIR__ . '/../template-parts/functions.php';
					$source      = __DIR__ . '/../src/Functions/' . $option;
					$source_code = file_get_contents( __DIR__ . '/../src/Functions/' . $option );
					$source_code = preg_replace( '/^<\?php/', '', $source_code ); // Remove "<?php" tag if present.

					if ( $source && file_exists( $source ) ) {
						$destinationDir = dirname( $destination );
						if ( ! file_exists( $destinationDir ) ) {
							mkdir( $destinationDir, 0755, true );
						}

						if ( ! file_exists( $destination ) ) {
							file_put_contents( $destination, "<?php\n" );
						}

						// Insert the code at the bottom of the file.
						file_put_contents( $destination, $source_code, FILE_APPEND );
					}
				}
			}

			$output->writeln( '<info>Files installed successfully.</info>' );
		}
	);

$application->add( $command );
$application->setDefaultCommand( 'install', true );
$application->run();
