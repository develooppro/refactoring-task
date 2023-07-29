<?php

declare(strict_types=1);

use App\CountriesCachedClient;
use App\CurrencyRateCachedClient;
use App\DiscountCalculator;
use App\FileTransactionsParser;
use GuzzleHttp\Client;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require dirname(__DIR__).'/config/bootstrap.php';

$params = require dirname(__DIR__).'/config/params.php';

$formatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
$formatter->includeStacktraces();

$stream = new StreamHandler(dirname(__DIR__).'/var/error.log', Level::Warning);
$stream->setFormatter($formatter);

$logger = new Logger('main');
$logger->pushHandler($stream);

try {
    $parser = new FileTransactionsParser($argv[1]);

    $countriesClient = new CountriesCachedClient(new Client(), $params['countriesUrl']);
    $currencyRateClient = new CurrencyRateCachedClient(
        new Client(),
        $params['currenciesUrl'],
        $params['currenciesApiAccessKey']
    );
    $discountCalculator = new DiscountCalculator(
        $countriesClient,
        $currencyRateClient,
        $params['euCodes'],
        $params['calculationPrecision'],
        $params['outputPrecision']
    );

    $transactions = $parser->parseTransactions();
    foreach ($transactions as $transaction) {
        $discount = $discountCalculator->getDiscount($transaction);
        echo $discount . PHP_EOL;
    }

    return 0;
} catch (Throwable $e) {
    $logger->error($e);
    echo 'Unfortunately operation had no success. Please try again later or check logs for the details.' . PHP_EOL;

    return 1;
}