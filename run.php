<?php

/*
 * Parse an input file to produce a better looking output.
 */

require_once __DIR__.'/vendor/autoload.php';

$inputFile = $argv[1];
$outputFile = $argv[2] ?? 'data/output.csv';

if (false === ($handle = fopen($inputFile, 'r'))) {
    echo 'Could not read input file: '.$inputFile;
    exit(1);
}

$parser = new \App\Parser();
$output = new \App\Output($outputFile);

function printException(array $row, Exception $e)
{
    echo sprintf(
        '\n##########\nWe failed parsing line with the following data:\n"%s"\n%s\n\n######\n',
        json_encode($row),
        $e->getMessage()
    );
}

while (false !== ($row = fgetcsv($handle, 0, ';'))) {
    try {
        $data = $parser->parse($row);
        $output->addData($data);
    } catch (\App\Exception\SkipRowException $e) {
        continue;
    } catch (\Exception $e) {
        printException($row, $e);
        continue;
    } catch (\Throwable $e) {
        printException($row, $e);
        echo '^^^ Fatal error, we must abort';
        fclose($handle);
        exit(2);
    }
}

fclose($handle);

// Print stuff
$output->flush();

echo 'Done';
