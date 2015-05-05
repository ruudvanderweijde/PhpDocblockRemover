#!/usr/bin/env php
<?php

use PhpDocblockRemover\Printer\DocBlockRemover;

require __DIR__ . '/../vendor/autoload.php';

ini_set('xdebug.max_nesting_level', 3000);

// Disable XDebug var_dump() output truncation
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

if (count($argv) !== 3) {
    showHelp("Invalid parameters provided");
}
$sourceFolder = $argv[1];
$targetFolder = $argv[2];
if (!is_dir($sourceFolder)) {
    showHelp(sprintf('Source folder \'%s\' does not exist.', $sourceFolder));
}
if (!is_readable($sourceFolder)) {
    showHelp(sprintf('Source folder \'%s\' is not readable.', $sourceFolder));
}
if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0777, true);
}

$lexer = new PhpParser\Lexer\Emulative(array('usedAttributes' => array(
    'startLine', 'endLine', 'startFilePos', 'endFilePos'
)));
$parser = new PhpParser\Parser($lexer);
$printer = new DocBlockRemover();

$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceFolder));
/** @var SplFileInfo $file */
foreach ($allFiles as $file) {
    if (0 === strpos($file->getFilename(), '.')) continue;

    $targetFile = str_replace($sourceFolder, $targetFolder, $file);

    // copy the file source to the target
    if (!file_exists(dirname($targetFile))) {
        mkdir(dirname($targetFile), 0777, true);
    }
    copy($file, $targetFile);

    if (!preg_match('/\.php$/', $file->getFilename())) continue;

    $code = file_get_contents($file);

    try {
        $stmts = $parser->parse($code);
    } catch (PhpParser\Error $e) {
        die($e->getMessage() . "\n");
    }

    echo "Writing file to $targetFile.\n";
    file_put_contents($targetFile, $printer->prettyPrintFile($stmts) . "\n");
}

function showHelp($error) {
    die($error . "\n\n" .
        <<<OUTPUT
Usage:

    php remove-docblock.php path/to/source pat/to/target

path/to/source should be an existing folder

OUTPUT
    );
}

