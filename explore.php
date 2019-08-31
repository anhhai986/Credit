<?php

include __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Embedders\TSNE;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Other\Loggers\Screen;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\NumericStringConverter;
use League\Csv\Reader;
use League\Csv\Writer;

ini_set('memory_limit', '-1');

echo '╔═══════════════════════════════════════════════════════════════╗' . PHP_EOL;
echo '║                                                               ║' . PHP_EOL;
echo '║ Credit Card Dataset Embedder using t-SNE                      ║' . PHP_EOL;
echo '║                                                               ║' . PHP_EOL;
echo '╚═══════════════════════════════════════════════════════════════╝' . PHP_EOL;
echo PHP_EOL;

echo 'Loading data into memory ...' . PHP_EOL;

$reader = Reader::createFromPath(__DIR__ . '/dataset.csv')
    ->setDelimiter(',')->setEnclosure('"')->setHeaderOffset(0);

$samples = $reader->getRecords([
    'credit_limit', 'gender', 'education', 'marital_status', 'age',
    'timeliness_1', 'timeliness_2', 'timeliness_3', 'timeliness_4',
    'timeliness_5', 'timeliness_6', 'balance_1', 'balance_2', 'balance_3',
    'balance_4', 'balance_5', 'balance_6', 'payment_1', 'payment_2',
    'payment_3', 'payment_4', 'payment_5', 'payment_6', 'avg_balance',
    'avg_payment',
]);

$labels = $reader->fetchColumn('default');

$dataset = Labeled::fromIterator($samples, $labels)->randomize()->head(1000);

$dataset->apply(new NumericStringConverter());
$dataset->apply(new OneHotEncoder());
$dataset->apply(new ZScaleStandardizer());

$embedder = new TSNE(2, 30);

$embedder->setLogger(new Screen('credit'));

$embedding = $embedder->embed($dataset);

$writer = Writer::createFromPath('embedding.csv', 'w+');
$writer->insertOne(['x', 'y']);
$writer->insertAll($embedding);

echo 'Embedding saved to embedding.csv' . PHP_EOL;