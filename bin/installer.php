#!/usr/bin/env php
<?php

// installer.php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Define the options with custom labels
$options = [
    'text-image.php' => ['Text Image Component', false],
    'faq.php' => ['FAQ Component', false],
    'slider.php' => ['Slider Component', false],
    'function_dave_test' => ['Install the function dave_test', false],
];

// Function to render the options with checkboxes and labels
function renderOptions($options, $selectedOption) {
    $output = '';
    foreach ($options as $option => [$label, $selected]) {
        $checkbox = $selected ? '[x]' : '[ ]';
        if ($option === $selectedOption) {
            $output .= "\033[7m"; // Reverse video for the selected option
        }
        $output .= "$checkbox $label\n\033[0m"; // Reset video attributes
    }
    return $output;
}

// Function to read single keypress from terminal
function readKeyPress() {
    system("stty cbreak -echo");
    $key = ord(fgetc(STDIN));
    system("stty -cbreak echo");
    return $key;
}

// Function to insert code into functions.php
function insertFunctionCode() {
    $functionsFilePath = __DIR__ . '/../template-parts/functions.php';
    $functionCode = file_get_contents(__DIR__ . '/../src/Functions/test.php');
    $functionCode = preg_replace('/^<\?php/', '', $functionCode); // Remove "<?php" tag if present

    // Ensure the file exists and create it if not
    if (!file_exists($functionsFilePath)) {
        file_put_contents($functionsFilePath, "<?php\n");
    }

    // Insert the code at the bottom of the file
    file_put_contents($functionsFilePath, $functionCode, FILE_APPEND);
}

$application = new Application();

$command = $application->register('install')
    ->setDescription('Install custom files')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($application, $options) {
        $selectedOptionIndex = 0; // Index of the currently selected option

        while (true) {
            // Clear terminal
            echo "\033[2J\033[;H";

            // Render instructions
            echo "Use arrow keys to navigate, Space to select/deselect, and Enter to confirm:\n";

            // Render options with checkboxes and labels
            $output->write(renderOptions($options, array_keys($options)[$selectedOptionIndex]));

            // Get the key pressed by the user
            $key = readKeyPress();

            // Handle arrow keys
            if ($key === 65) { // Up arrow
                $selectedOptionIndex = max(0, $selectedOptionIndex - 1);
            } elseif ($key === 66) { // Down arrow
                $selectedOptionIndex = min(count($options) - 1, $selectedOptionIndex + 1);
            } elseif ($key === 32) { // Spacebar to toggle selection
                $option = array_keys($options)[$selectedOptionIndex];
                $options[$option][1] = !$options[$option][1];
            } elseif ($key === 10) { // Enter to confirm selection
                break;
            }

            // Move cursor to beginning of the list
            echo "\033[" . (count($options) + 2) . "A\r"; // Add 2 for the instruction line
        }

        // Install selected files to the destination folder
        foreach ($options as $option => [$label, $selected]) {
            if ($selected && $option !== 'function_dave_test') {
                $source = realpath(__DIR__ . '/../src/TemplateParts/Components/' . $option);
                $destination = __DIR__ . '/../template-parts/components/' . $option;

                if ($source && file_exists($source)) {
                    $destinationDir = dirname($destination);
                    if (!file_exists($destinationDir)) {
                        mkdir($destinationDir, 0755, true);
                    }

                    copy($source, $destination);
                } else {
                    $output->writeln("<error>Source file '{$option}' does not exist.</error>");
                }
            }
        }

        // Install function_dave_test
        if ($options['function_dave_test'][1]) {
            insertFunctionCode();
            $output->writeln('<info>Function dave_test installed successfully.</info>');
        }

        $output->writeln('<info>Files installed successfully.</info>');
    });

$application->add($command);
$application->setDefaultCommand('install', true);
$application->run();
