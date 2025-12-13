<?php

require __DIR__ . '/../vendor/autoload.php';

use src\Models\SavingsAccount;
use src\Models\CheckingAccount;
use src\Services\AccountManager;

$manager = new AccountManager();

function input($msg)
{
    echo $msg;
    return trim(fgets(STDIN));
}

while (true) {

    echo "\n=== BANKING SYSTEM MENU ===\n";
    echo "1. Create Account\n";
    echo "2. Deposit\n";
    echo "3. Withdraw\n";
    echo "4. Transfer\n";
    echo "5. View Accounts\n";
    echo "6. Exit\n";
    echo "Select option: ";

    $choice = trim(fgets(STDIN));

    switch ($choice) {

        case "1":
            echo "\n--- Create Account ---\n";
            $type = input("Account Type (1 = Savings, 2 = Checking): ");
            $accNo = input("Enter Account Number: ");
            $owner = input("Enter Owner Name: ");
            $balance = (float) input("Initial Balance: ");

            if ($type == "1") {
                $account = new SavingsAccount($accNo, $owner, $balance);
            } else {
                $account = new CheckingAccount($accNo, $owner, $balance);
            }

            $manager->addAccount($account);

            echo "Account successfully created!\n";
            break;

        case "2":
            echo "\n--- Deposit ---\n";
            $accNo = input("Account Number: ");
            $account = $manager->getAccount($accNo);

            if (!$account) {
                echo "Account not found.\n";
                break;
            }

            $amount = (float) input("Amount to deposit: ");
            $account->deposit($amount);

            echo "New Balance: " . $account->getBalance() . "\n";
            break;

        case "3":
            echo "\n--- Withdraw ---\n";
            $accNo = input("Account Number: ");
            $account = $manager->getAccount($accNo);

            if (!$account) {
                echo "Account not found.\n";
                break;
            }

            $amount = (float) input("Amount to withdraw: ");

            try {
                $account->withdraw($amount);
                echo "New Balance: " . $account->getBalance() . "\n";

            } catch (Exception $e) {
                echo "Withdrawal failed: " . $e->getMessage() . "\n";
            }
            break;

        case "4":
            echo "\n--- Transfer ---\n";
            $from = input("From Account: ");
            $to   = input("To Account: ");
            $amount = (float) input("Amount to transfer: ");

            $accFrom = $manager->getAccount($from);
            $accTo   = $manager->getAccount($to);

            if (!$accFrom || !$accTo) {
                echo "One or both accounts not found.\n";
                break;
            }

            try {
                $accFrom->withdraw($amount);
                $accTo->deposit($amount);
                echo "Transfer successful!\n";

            } catch (Exception $e) {
                echo "Transfer failed: " . $e->getMessage() . "\n";
            }

            break;

        case "5":
            echo "\n--- Account List ---\n";

            foreach ($manager->listAccounts() as $acc) {
                echo $acc . "\n"; // uses __toString()
            }
            break;

        case "6":
            echo "Exiting system...\n";
            exit;

        default:
            echo "Invalid option.\n";
    }
}

