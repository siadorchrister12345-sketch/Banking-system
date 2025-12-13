<?php
namespace src\Interfaces;

interface Transactionable
{
    public function deposit(float $amount): void;
    public function withdraw(float $amount): void;
}
