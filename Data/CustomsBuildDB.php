<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Customs\Data
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

// Sources:

// Sanctions US Structure: https://ofac.treasury.gov/sdn-list-data-formats-data-schemas/tutorial-on-the-use-of-list-related-legacy-flat-files
// Sanctions US SDN: https://ofac.treasury.gov/specially-designated-nationals-list-data-formats-data-schemas
// Sanctions US Consolidated: https://ofac.treasury.gov/consolidated-sanctions-list-non-sdn-lists
// Sanctions UK: https://www.gov.uk/government/publications/the-uk-sanctions-list
// Sanctions EU: https://data.europa.eu/data/datasets/consolidated-list-of-persons-groups-and-entities-subject-to-eu-financial-sanctions?locale=en
// Sanctions EU: 881/2002 https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=celex%3A32002R0881
// Sanctions EU: List https://www.zoll.de/DE/Fachthemen/Aussenwirtschaft-Bargeldverkehr/Warenausfuhr/Personen/Liste/liste_node.html
// TARIC: https://circabc.europa.eu/ui/group/0e5f18c2-4b2f-42e9-aed4-dfe50ae1263b

require_once __DIR__ . '/../../../phpOMS/Autoloader.php';
use phpOMS\Autoloader;

Autoloader::addPath(__DIR__ . '/../../../Resources');

use phpOMS\DataStorage\Database\Connection\SQLiteConnection;
use phpOMS\DataStorage\Database\Schema\Builder;
use phpOMS\Utils\IO\Csv\CsvDatabaseMapper;
use phpOMS\Utils\IO\Spreadsheet\SpreadsheetDatabaseMapper;

$file = __DIR__ . '/customs.sqlite';

if (\is_file($file)) {
    \unlink($file);
}

$con = new SQLiteConnection(
    [
        'db'       => 'sqlite',
        'database' => $file,
    ]
);
$con->connect();

$schemaContent = \file_get_contents(__DIR__ . '/schema.json');
if ($schemaContent === false) {
    return;
}

$schema = \json_decode($schemaContent, true);
if (!\is_array($schema)) {
    return;
}

foreach ($schema as $table) {
    Builder::createFromSchema($table, $con)->execute();
}

$dataTransform = function (string $column, mixed $value) : mixed {
    $column = \strtolower($column);
    if (!\str_ends_with($column, 'date') && !\str_ends_with($column, 'dat')) {
        return $value;
    }

    if (!\is_string($value)) {
        return $value;
    }

    if (\preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) === 1) {
        return DateTime::createFromFormat('d/m/Y', $value);
    } elseif (\preg_match('/^\d{2}-\d{2}-\d{4}$/', $value) === 1) {
        return DateTime::createFromFormat('d-m-Y', $value);
    } elseif (\preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
        return DateTime::createFromFormat('Y-m-d', $value);
    } else {
        return $value;
    }
};

// TARIC
$spreadsheet = new SpreadsheetDatabaseMapper($con);
$spreadsheet->import(__DIR__ . '/TARIC/Nomenclature EN.xlsx', 'taric_good', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Nomenclature DE.xlsx', 'taric_good', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Nomenclature FR.xlsx', 'taric_good', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Footnotes descriptions.xlsx', 'taric_footnote_description', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Additional codes descriptions.xlsx', 'taric_add_code', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Measure footnotes.xlsx', 'taric_measure_footnote', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Duties Export 01-99.xlsx', 'taric_duties_export', $dataTransform);
$spreadsheet->import(__DIR__ . '/TARIC/Duties Import 01-99.xlsx', 'taric_duties_import', $dataTransform);

// SANCTIONS
// US (remember to modify the csv files when downloading new files -> add header to csv files)
$csvsheet = new CsvDatabaseMapper($con);
$csvsheet->import(__DIR__ . '/Sanctions/US/SDN/SDN.CSV', 'sanction_us_sdn');
$csvsheet->import(__DIR__ . '/Sanctions/US/SDN/ALT.CSV', 'sanction_us_sdn_alt');
$csvsheet->import(__DIR__ . '/Sanctions/US/SDN/ADD.CSV', 'sanction_us_sdn_add');
$csvsheet->import(__DIR__ . '/Sanctions/US/SDN/SDN_COMMENTS.CSV', 'sanction_us_sdn_comments');

$csvsheet->import(__DIR__ . '/Sanctions/US/Consolidated/CONS_PRIM.CSV', 'sanction_us_cons');
$csvsheet->import(__DIR__ . '/Sanctions/US/Consolidated/CONS_ALT.CSV', 'sanction_us_cons_alt');
$csvsheet->import(__DIR__ . '/Sanctions/US/Consolidated/CONS_ADD.CSV', 'sanction_us_cons_add');
$csvsheet->import(__DIR__ . '/Sanctions/US/Consolidated/CONS_COMMENTS.CSV', 'sanction_us_cons_comments');

// EU
$csvsheet = new CsvDatabaseMapper($con);
$csvsheet->import(
    __DIR__ . '/Sanctions/EU/Consolidated Financial Sanctions File 1-9.csv',
    'sanction_eu_cons',
    $dataTransform
);

// UK

$con->close();
