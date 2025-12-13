<?php
namespace src\Models;

use src\Exceptions\InsufficientBalanceException;

class SavingsAccount extends BankAccount
{
    private float $interestRate; // e.g., 0.02 = 2%

    public function __construct(string $accountNumber, string $ownerName, float $initialBalance = 0.0, float $interestRate = 0.02)
    {
        parent::__construct($accountNumber, $ownerName, $initialBalance);
        $this->interestRate = $interestRate;
    }

    public function withdraw(float $amount): void
    {
        // Savings has minimum balance requirement of 100.00
        $minBalance = 100.00;
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Withdrawal amount must be positive");
        }

        if ($this->balance - $amount < $minBalance) {
            throw new InsufficientBalanceException("Cannot withdraw: minimum balance of {$minBalance} would be violated.");
        }

        $this->balance -= $amount;
    }

    public function applyInterest(): void
    {
        $this->balance += $this->balance * $this->interestRate;
    }

    public function getAccountType(): string
    {
        return 'Savings';
    }
}
