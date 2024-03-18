<?php

declare(strict_types = 1);

function getTransactionFiles(string $dirPath): array {
    $files = [];

    foreach (scandir($dirPath) as $file){
        if (is_dir($file)) continue;
        $files[] = $dirPath . $file;
    }

    return $files; 
}

function getTransactions(string $fileName): array {
    if (!file_exists($fileName)){
        trigger_error("File" . $fileName . "does not exist." , E_USER_ERROR);
    }
    $file = fopen ($fileName, "r");

    fgetcsv($file);

    $transactions = [];

    while (($transaction = fgetcsv($file)) !== false) {
        $transactions[] = parseTransactions($transaction);
    }

    return $transactions;
}


function parseTransactions(array $transactionRow): array {
    [$date, $checkNumber, $description, $amount] = $transactionRow;

    $amount = (float) str_replace(['$', ','], '', $amount);

    return [
        'date' => $date,
        'checkNumber' => $checkNumber,
        'description' => $description,
        'amount' => $amount
    ];
}

function calculateTotal(array $transactions): array {

    $incomes = array_filter($transactions, fn($transaction) => $transaction['amount'] > 0);
    $outcomes = array_filter($transactions, fn($transaction) => $transaction['amount'] < 0);

    $totalIncome = array_reduce($incomes, fn($sum, $income) => $sum + $income['amount']);
    $totalExpense = array_reduce($outcomes, fn($sum, $outcome) => $sum + $outcome['amount']);

    $netTotal = $totalIncome + $totalExpense;

    return [
        'totalIncome' => $totalIncome,
        'totalExpense' => $totalExpense,
        'netTotal' => $netTotal
    ];
}