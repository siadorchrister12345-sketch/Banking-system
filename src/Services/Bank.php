<?php
namespace src\Services;

use src\Models\BankAccount;
use src\Models\SavingsAccount;
use src\Models\CheckingAccount;
use src\Exceptions\AccountNotFoundException;
use src\Exceptions\InsufficientBalanceException;

class Bank
{
    /** @var BankAccount[] */
    private array $accounts = [];

    public function addAccount(BankAccount $acc): void
    {
        $this->accounts[$acc->getAccountNumber()] = $acc;
    }

    public function getAccount(string $accountNumber): BankAccount
    {
        if (!isset($this->accounts[$accountNumber])) {
            throw new AccountNotFoundException("Account $accountNumber not found.");
        }
        return $this->accounts[$accountNumber];
    }

    public function transfer(string $fromAccNo, string $toAccNo, float $amount): void
    {
        try {
            $from = $this->getAccount($fromAccNo);
            $to = $this->getAccount($toAccNo);

            
            $from->withdraw($amount);
            $to->deposit($amount);

            echo "Transferred {$amount} from {$fromAccNo} to {$toAccNo}\n";
        } catch (AccountNotFoundException $e) {
            echo "Transfer error: " . $e->getMessage() . PHP_EOL;
            throw $e;
        } catch (InsufficientBalanceException $e) {
            echo "Transfer failed: " . $e->getMessage() . PHP_EOL;
            throw $e;
        } finally {
            
            echo "[Transfer attempt logged]" . PHP_EOL;
        }
    }

    public function listAccounts(): void
    {
        foreach ($this->accounts as $acc) {
            echo $acc . PHP_EOL;
        }
    }
}
