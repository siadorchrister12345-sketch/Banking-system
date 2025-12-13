<?php
namespace src\Models;

use src\Exceptions\InsufficientBalanceException;

class CheckingAccount extends BankAccount
{
    private float $overdraftLimit;

    public function __construct(string $accountNumber, string $ownerName, float $initialBalance = 0.0, float $overdraftLimit = 200.0)
    {
        parent::__construct($accountNumber, $ownerName, $initialBalance);
        $this->overdraftLimit = $overdraftLimit;
    }

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Withdrawal amount must be positive");
        }
       
        if ($this->balance - $amount < -$this->overdraftLimit) {
            throw new InsufficientBalanceException("Cannot withdraw: overdraft limit ({$this->overdraftLimit}) exceeded.");
        }
        $this->balance -= $amount;
    }

    public function getAccountType(): string
    {
        return 'Checking';
    }
}
