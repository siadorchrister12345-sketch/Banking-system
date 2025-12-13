<?php
namespace src\Services;

use src\Models\BankAccount;

class AccountManager
{
    private array $accounts = [];

    public function addAccount(BankAccount $account): void {
        $this->accounts[$account->getAccountNumber()] = $account;
    }

    public function getAccount(string $accountNumber): ?BankAccount {
        return $this->accounts[$accountNumber] ?? null;
    }

    public function listAccounts(): array {
        return $this->accounts;
    }
}
