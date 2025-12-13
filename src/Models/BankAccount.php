<?php
namespace src\Models;

use src\Interfaces\Transactionable;
use src\Exceptions\InsufficientBalanceException;

abstract class BankAccount implements Transactionable
{
    // Encapsulation
    private string $accountNumber;
    protected float $balance;
    private string $ownerName;

    public function __construct(string $accountNumber, string $ownerName, float $initialBalance = 0.0)
    {
        $this->accountNumber = $accountNumber;
        $this->ownerName = $ownerName;
        $this->balance = $initialBalance;
    }

    // Magic method 1
    public function __get($name)
    {
        if (in_array($name, ['accountNumber', 'ownerName', 'balance'])) {
            return $this->$name;
        }
        trigger_error("Undefined property: " . static::class . "::$name", E_USER_NOTICE);
        return null;
    }

    // Magic method 2
    public function __set($name, $value)
    {
        //barrier
        if ($name === 'ownerName') {
            $this->ownerName = (string) $value;
            return;
        }
        throw new \Exception("Cannot set property '$name' directly.");
    }

    // Magic method 3
    public function __toString(): string
    {
        return sprintf(
            "%s Account [%s] - Owner: %s - Balance: %.2f",
            $this->getAccountType(),
            $this->accountNumber,
            $this->ownerName,
            $this->balance
        );
    }

    // Magic method 4
    public function __call($method, $args)
    {
        if ($method === 'printSummary') {
            echo $this->__toString() . PHP_EOL;
            return;
        }
        throw new \BadMethodCallException("Method $method not found on " . static::class);
    }

    // Encapsulated getters
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    // Transaction methods from Transactionable
    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Deposit amount must be positive");
        }
        $this->balance += $amount;
    }

    //polymorphism
    abstract public function withdraw(float $amount): void;

    
    abstract public function getAccountType(): string;

    
    public function __clone()
    {
        
        $this->accountNumber = $this->accountNumber . '-clone';
    }
}
