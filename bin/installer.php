<?php
/**
 * Executable to start installer.
 *
 * @package Composer Custom Components Installer
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

// Your application logic goes here.
$application = new Application();

$application->register( 'install' )
	->setDescription( 'Install custom files' )
	->setCode(
		function ( InputInterface $input, OutputInterface $output ) {
			$io = new SymfonyStyle( $input, $output );

			// Here you can prompt the user to select which files to install.
			$helper   = $application->getHelper( 'question' );
			$question = new ChoiceQuestion(
				'Which files do you want to install?',
				array(
					'text-image.php',
				),
				'0,1'
			);

			$question->setMultiselect( true );
			$selected_files = $helper->ask( $input, $output, $question );

			// Install selected files to the destination folder.
			foreach ( $selected_files as $file ) {
				$source      = __DIR__ . '/../src/TemplateParts/Components/' . $file;
				$destination = __DIR__ . '/../template-parts/components/' . $file;
				copy( $source, $destination );
			}

			$io->success( 'Files installed successfully.' );
		}
	);

$application->run();
