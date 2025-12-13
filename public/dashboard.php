<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
// Handle logout (from dashboard logout button)
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

use src\Services\AccountManager;
use src\Services\PersistenceManager;
use src\Models\SavingsAccount;
use src\Models\CheckingAccount;

// Check if logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$persistence = new PersistenceManager(
    __DIR__ . '/../data/accounts.txt',
    __DIR__ . '/../data/users.txt'
);

// Load accounts from file
$manager = new AccountManager();
$accountsData = $persistence->loadAccounts($username);
foreach ($accountsData as $account) {
    $manager->addAccount($account);
}

$message = '';
$messageType = '';

// Handle create account
if (isset($_POST['create_account'])) {
    $type = $_POST['account_type'] ?? '';
    $accNo = $_POST['account_number'] ?? '';
    $balance = (float)($_POST['initial_balance'] ?? 0);
    
    if ($accNo && $balance > 0) {
        if ($type === 'savings') {
            $account = new SavingsAccount($accNo, $username, $balance);
        } else {
            $account = new CheckingAccount($accNo, $username, $balance);
        }
        
        $manager->addAccount($account);
        $persistence->saveAccounts($username, $manager->listAccounts());
        $message = 'Account created successfully!';
        $messageType = 'success';
    } else {
        $message = 'Invalid account details';
        $messageType = 'error';
    }
}

// Handle deposit
if (isset($_POST['deposit'])) {
    $accNo = $_POST['deposit_account'] ?? '';
    $amount = (float)($_POST['deposit_amount'] ?? 0);
    
    $account = $manager->getAccount($accNo);
    if ($account && $amount > 0) {
        try {
            $account->deposit($amount);
            $persistence->saveAccounts($username, $manager->listAccounts());
            $message = 'Deposit successful!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid account or amount';
        $messageType = 'error';
    }
}

// Handle withdraw
if (isset($_POST['withdraw'])) {
    $accNo = $_POST['withdraw_account'] ?? '';
    $amount = (float)($_POST['withdraw_amount'] ?? 0);
    
    $account = $manager->getAccount($accNo);
    if ($account && $amount > 0) {
        try {
            $account->withdraw($amount);
            $persistence->saveAccounts($username, $manager->listAccounts());
            $message = 'Withdrawal successful!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid account or amount';
        $messageType = 'error';
    }
}

// Handle transfer
if (isset($_POST['transfer'])) {
    $fromAcc = $_POST['transfer_from'] ?? '';
    $toAcc = $_POST['transfer_to'] ?? '';
    $amount = (float)($_POST['transfer_amount'] ?? 0);
    
    $accFrom = $manager->getAccount($fromAcc);
    $accTo = $manager->getAccount($toAcc);
    
    if ($accFrom && $accTo && $amount > 0 && $fromAcc !== $toAcc) {
        try {
            $accFrom->withdraw($amount);
            $accTo->deposit($amount);
            $persistence->saveAccounts($username, $manager->listAccounts());
            $message = 'Transfer successful!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Transfer failed: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid accounts or amount';
        $messageType = 'error';
    }
}

$accounts = $manager->listAccounts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>🏦 Banking System</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="btn btn-secondary">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <div class="tabs">
                <button class="tab-button active" onclick="openTab('accounts')">My Accounts</button>
                <button class="tab-button" onclick="openTab('create')">Create Account</button>
                <button class="tab-button" onclick="openTab('deposit')">Deposit</button>
                <button class="tab-button" onclick="openTab('withdraw')">Withdraw</button>
                <button class="tab-button" onclick="openTab('transfer')">Transfer</button>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content">

                <!-- My Accounts Tab -->
                <div id="accounts" class="tab-pane active">
                    <h2>My Accounts</h2>
                    <?php if (count($accounts) > 0): ?>
                        <div class="accounts-grid">
                            <?php foreach ($accounts as $account): ?>
                                <div class="account-card">
                                    <div class="account-header">
                                        <h3><?php echo ucfirst($account->getAccountType()); ?></h3>
                                        <span class="account-number"><?php echo htmlspecialchars($account->getAccountNumber()); ?></span>
                                    </div>
                                    <div class="account-body">
                                        <p><strong>Owner:</strong> <?php echo htmlspecialchars($account->getOwnerName()); ?></p>
                                        <p class="balance"><strong>Balance:</strong> $<?php echo number_format($account->getBalance(), 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-accounts">No accounts yet. Create one to get started!</p>
                    <?php endif; ?>
                </div>

                <!-- Create Account Tab -->
                <div id="create" class="tab-pane">
                    <h2>Create New Account</h2>
                    <form method="POST" class="form-group-full">
                        <div class="form-group">
                            <label for="account_type">Account Type</label>
                            <select id="account_type" name="account_type" required>
                                <option value="savings">Savings Account</option>
                                <option value="checking">Checking Account</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" id="account_number" name="account_number" placeholder="e.g., ACC001" required>
                        </div>

                        <div class="form-group">
                            <label for="initial_balance">Initial Balance</label>
                            <input type="number" id="initial_balance" name="initial_balance" step="0.01" min="0" placeholder="0.00" required>
                        </div>

                        <button type="submit" name="create_account" class="btn btn-primary">Create Account</button>
                    </form>
                </div>

                <!-- Deposit Tab -->
                <div id="deposit" class="tab-pane">
                    <h2>Make a Deposit</h2>
                    <?php if (count($accounts) > 0): ?>
                        <form method="POST" class="form-group-full">
                            <div class="form-group">
                                <label for="deposit_account">Select Account</label>
                                <select id="deposit_account" name="deposit_account" required>
                                    <option value="">-- Select Account --</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?php echo htmlspecialchars($account->getAccountNumber()); ?>">
                                            <?php echo htmlspecialchars($account->getAccountNumber()); ?> - Balance: $<?php echo number_format($account->getBalance(), 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="deposit_amount">Amount</label>
                                <input type="number" id="deposit_amount" name="deposit_amount" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>

                            <button type="submit" name="deposit" class="btn btn-primary">Deposit</button>
                        </form>
                    <?php else: ?>
                        <p>Create an account first to make deposits.</p>
                    <?php endif; ?>
                </div>

                <!-- Withdraw Tab -->
                <div id="withdraw" class="tab-pane">
                    <h2>Make a Withdrawal</h2>
                    <?php if (count($accounts) > 0): ?>
                        <form method="POST" class="form-group-full">
                            <div class="form-group">
                                <label for="withdraw_account">Select Account</label>
                                <select id="withdraw_account" name="withdraw_account" required>
                                    <option value="">-- Select Account --</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?php echo htmlspecialchars($account->getAccountNumber()); ?>">
                                            <?php echo htmlspecialchars($account->getAccountNumber()); ?> - Balance: $<?php echo number_format($account->getBalance(), 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="withdraw_amount">Amount</label>
                                <input type="number" id="withdraw_amount" name="withdraw_amount" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>

                            <button type="submit" name="withdraw" class="btn btn-primary">Withdraw</button>
                        </form>
                    <?php else: ?>
                        <p>Create an account first to make withdrawals.</p>
                    <?php endif; ?>
                </div>

                <!-- Transfer Tab -->
                <div id="transfer" class="tab-pane">
                    <h2>Transfer Funds</h2>
                    <?php if (count($accounts) > 1): ?>
                        <form method="POST" class="form-group-full">
                            <div class="form-group">
                                <label for="transfer_from">From Account</label>
                                <select id="transfer_from" name="transfer_from" required>
                                    <option value="">-- Select Account --</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?php echo htmlspecialchars($account->getAccountNumber()); ?>">
                                            <?php echo htmlspecialchars($account->getAccountNumber()); ?> - Balance: $<?php echo number_format($account->getBalance(), 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="transfer_to">To Account</label>
                                <select id="transfer_to" name="transfer_to" required>
                                    <option value="">-- Select Account --</option>
                                    <?php foreach ($accounts as $account): ?>
                                        <option value="<?php echo htmlspecialchars($account->getAccountNumber()); ?>">
                                            <?php echo htmlspecialchars($account->getAccountNumber()); ?> - Balance: $<?php echo number_format($account->getBalance(), 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="transfer_amount">Amount</label>
                                <input type="number" id="transfer_amount" name="transfer_amount" step="0.01" min="0.01" placeholder="0.00" required>
                            </div>

                            <button type="submit" name="transfer" class="btn btn-primary">Transfer</button>
                        </form>
                    <?php elseif (count($accounts) === 1): ?>
                        <p>You need at least 2 accounts to perform a transfer. Create another account first.</p>
                    <?php else: ?>
                        <p>Create accounts first to perform transfers.</p>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab panes
            const panes = document.querySelectorAll('.tab-pane');
            panes.forEach(pane => pane.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));

            // Show selected tab and mark button as active
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
