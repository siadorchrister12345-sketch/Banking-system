<?php
namespace src\Services;

use src\Models\BankAccount;
use src\Models\SavingsAccount;
use src\Models\CheckingAccount;

class PersistenceManager
{
    private string $accountsFile;
    private string $usersFile;

    public function __construct(string $accountsFile, string $usersFile)
    {
        $this->accountsFile = $accountsFile;
        $this->usersFile = $usersFile;
        $this->ensureFilesExist();
    }

    private function ensureFilesExist(): void
    {
        if (!file_exists($this->accountsFile)) {
            file_put_contents($this->accountsFile, '');
        }
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, '');
        }
    }

    // Save user (username:password_hash)
    public function saveUser(string $username, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $line = $username . '|' . $hash . "\n";
        file_put_contents($this->usersFile, $line, FILE_APPEND);
    }

    // Verify user credentials
    public function verifyUser(string $username, string $password): bool
    {
        $lines = file_get_contents($this->usersFile);
        foreach (explode("\n", trim($lines)) as $line) {
            if (empty($line)) continue;
            [$user, $hash] = explode('|', $line);
            if ($user === $username && password_verify($password, $hash)) {
                return true;
            }
        }
        return false;
    }

    // Check if user exists
    public function userExists(string $username): bool
    {
        $lines = file_get_contents($this->usersFile);
        foreach (explode("\n", trim($lines)) as $line) {
            if (empty($line)) continue;
            [$user] = explode('|', $line);
            if ($user === $username) {
                return true;
            }
        }
        return false;
    }

    // Save accounts for a user
    public function saveAccounts(string $username, array $accounts): void
    {
        $lines = file_get_contents($this->accountsFile);
        $allLines = explode("\n", trim($lines));
        
        // Remove old accounts for this user
        $allLines = array_filter($allLines, function ($line) use ($username) {
            if (empty($line)) return false;
            $parts = explode('|', $line);
            return $parts[0] !== $username;
        });
        
        // Add new accounts
        foreach ($accounts as $account) {
            $type = $account->getAccountType();
            $accountNumber = $account->getAccountNumber();
            $ownerName = $account->getOwnerName();
            $balance = $account->getBalance();
            
            $data = [
                'username' => $username,
                'type' => $type,
                'accountNumber' => $accountNumber,
                'ownerName' => $ownerName,
                'balance' => $balance,
            ];
            
            if ($account instanceof SavingsAccount) {
                $data['interestRate'] = 0.02; // default
            } elseif ($account instanceof CheckingAccount) {
                $data['overdraftLimit'] = 200.0; // default
            }
            
            $allLines[] = json_encode($data);
        }
        
        $content = implode("\n", array_filter($allLines)) . "\n";
        file_put_contents($this->accountsFile, $content);
    }

    // Load accounts for a user
    public function loadAccounts(string $username): array
    {
        $accounts = [];
        $lines = file_get_contents($this->accountsFile);
        
        foreach (explode("\n", trim($lines)) as $line) {
            if (empty($line)) continue;
            
            $data = json_decode($line, true);
            if ($data['username'] === $username) {
                if ($data['type'] === 'Savings') {
                    $account = new SavingsAccount(
                        $data['accountNumber'],
                        $data['ownerName'],
                        $data['balance'],
                        $data['interestRate'] ?? 0.02
                    );
                } else {
                    $account = new CheckingAccount(
                        $data['accountNumber'],
                        $data['ownerName'],
                        $data['balance'],
                        $data['overdraftLimit'] ?? 200.0
                    );
                }
                $accounts[$account->getAccountNumber()] = $account;
            }
        }
        
        return $accounts;
    }
}
