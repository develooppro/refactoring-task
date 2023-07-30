<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\FileTransactionsParser;
use App\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * @covers FileTransactionsParser::parseTransactions
 */
class FileTransactionsParserTest extends TestCase
{
    public function testParseTransactions(): void
    {
        $parser = new FileTransactionsParser(__DIR__ . '/data/test_input.txt');
        $transactions = $parser->parseTransactions();

        $this->assertCount(2, $transactions);
        $this->assertInstanceOf(Transaction::class, $transactions[0]);

        $this->assertEquals('123456', $transactions[0]->getBin());
        $this->assertEquals('100.00', $transactions[0]->getAmount());
        $this->assertEquals('USD', $transactions[0]->getCurrencyCode());

        $this->assertEquals('789012', $transactions[1]->getBin());
        $this->assertEquals('50.00', $transactions[1]->getAmount());
        $this->assertEquals('EUR', $transactions[1]->getCurrencyCode());
    }

    public function testEmptyLine(): void
    {
        $parser = new FileTransactionsParser(__DIR__ . '/data/test_input_with_empty_line.txt');
        $transactions = $parser->parseTransactions();
        $this->assertCount(2, $transactions);
    }

    public function testNotValidJson(): void
    {
        $this->expectException(\JsonException::class);
        $parser = new FileTransactionsParser(__DIR__ . '/data/test_input_with_invalid_json.txt');
        $parser->parseTransactions();
    }

    public function testMissingField(): void
    {
        $parser = new FileTransactionsParser(__DIR__ . '/data/test_input_with_missing_field.txt');
        $transactions = $parser->parseTransactions();
        $this->assertCount(2, $transactions);
    }

    public function testNoFile(): void
    {
        $fileName = 'NotValidFileName.txt';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Input file [$fileName] does not exist.");

        $parser = new FileTransactionsParser($fileName);
        $parser->parseTransactions();
    }

    public function testEmptyFile(): void
    {
        $fileName = __DIR__ . '/data/test_input_empty.txt';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Input file [$fileName] is empty.");

        $parser = new FileTransactionsParser($fileName);
        $parser->parseTransactions();
    }
}
