<?php

declare(strict_types=1);

namespace App;

use JsonException;

class FileTransactionsParser
{
    public function __construct(private readonly string $fileName)
    {
        if (!file_exists($this->fileName)) {
            throw new \Exception("Input file [$this->fileName] does not exist.");
        }
    }

    /**
     * @return Transaction[]
     * @throws JsonException
     *
     * @see \App\Tests\Unit\FileTransactionsParserTest
     */
    public function parseTransactions(): array
    {
        $content = file_get_contents($this->fileName);
        if (empty($content)) {
            throw new \Exception("Input file [$this->fileName] is empty.");
        }
        $input = explode("\n", trim($content));
        $transactions = [];
        foreach ($input as $row) {
            if (empty($row)) {
                continue;
            }
            $rowData = json_decode($row, true, flags: JSON_THROW_ON_ERROR);
            $transactions[] = new Transaction(
                $rowData['bin'] ?? '',
                $rowData['amount'] ?? '',
                $rowData['currency'] ?? ''
            );
        }
        return $transactions;
    }
}
