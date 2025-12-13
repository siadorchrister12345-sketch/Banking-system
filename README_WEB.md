# Banking System - Web Application

A modern PHP web-based banking system with user authentication, account management, and persistent file storage.

## Features

✅ **User Authentication**
- Registration and login system
- Password hashing with PHP's `password_hash()`
- Session management

✅ **Account Management**
- Create Savings and Checking accounts
- Deposit and withdraw funds
- Transfer money between accounts
- View account details and balances

✅ **Data Persistence**
- Stores user credentials in `data/users.txt`
- Saves account information in `data/accounts.txt`
- JSON-based account serialization

✅ **Responsive Design**
- Modern HTML/CSS interface
- Mobile-friendly dashboard
- Tab-based navigation

## Project Structure

```
bank_system/
├── public/
│   ├── index.php           # Login & Registration page
│   ├── dashboard.php       # Main banking dashboard
│   ├── styles.css          # All styling
│   └── .htaccess          # Security rules
├── src/
│   ├── Models/            # BankAccount, SavingsAccount, CheckingAccount, Customer
│   ├── Services/
│   │   ├── AccountManager.php
│   │   └── PersistenceManager.php  # File I/O for persistence
│   ├── Interfaces/        # Transactionable
│   └── Exceptions/        # Custom exceptions
├── data/
│   ├── users.txt          # User credentials (username|password_hash)
│   └── accounts.txt       # Account data (JSON format)
├── bin/
│   └── console.php        # Original CLI application
├── vendor/                # Composer autoloader
└── composer.json
```

## Installation & Setup

### Requirements
- PHP 7.4 or higher
- Web server (Apache, Nginx, or built-in PHP server)
- Composer

### Steps

1. **Navigate to the project directory:**
   ```cmd
   cd c:\Users\siado\OneDrive\Desktop\php\finalrequirement\bank_system
   ```

2. **Ensure data directory has correct permissions:**
   ```cmd
   mkdir data
   ```
   The `data` folder should be writable by the web server.

3. **Option A: Using PHP Built-in Server**
   ```cmd
   cd public
   php -S localhost:8000
   ```
   Then open: `http://localhost:8000`

4. **Option B: Using Apache/IIS**
   - Point your web root to the `public/` folder
   - Access: `http://localhost/` (or your configured domain)

## Usage

### First Time Users
1. Go to the login page (`index.php`)
2. Click "Register here" to create a new account
3. Enter username and password
4. Confirm registration and login

### Banking Operations
1. **Create Account** - Set up Savings or Checking accounts
2. **Deposit** - Add funds to any account
3. **Withdraw** - Remove funds (with account restrictions):
   - Savings: Minimum balance of $100
   - Checking: Overdraft limit of $200
4. **Transfer** - Move money between your own accounts
5. **View Accounts** - See all your accounts and balances

## File Formats

### users.txt
```
username|hashed_password
john_doe|$2y$10$N9qo8uLO...
```

### accounts.txt
```json
{"username":"john_doe","type":"Savings","accountNumber":"SAV001","ownerName":"john_doe","balance":5000.50,"interestRate":0.02}
{"username":"john_doe","type":"Checking","accountNumber":"CHK001","ownerName":"john_doe","balance":2500.00,"overdraftLimit":200}
```

## Security Features

- Passwords hashed with `PASSWORD_DEFAULT` algorithm
- Session-based authentication
- Input validation and sanitization
- Direct access to `.txt` files blocked via `.htaccess`
- SQL injection prevention (file-based, not database)

## Account Types

### Savings Account
- Minimum balance requirement: $100
- Cannot withdraw below minimum
- Interest rate: 2% (can be modified)

### Checking Account
- Overdraft protection: $200
- Can go negative up to -$200
- No minimum balance requirement

## Troubleshooting

**Issue: "Cannot write to data folder"**
- Ensure the `data/` directory exists and is writable
- On Windows, check folder permissions

**Issue: Sessions not persisting**
- Verify PHP sessions directory is writable
- Check browser cookie settings

**Issue: "Account not found"**
- Accounts are stored per user - each user only sees their own accounts
- Make sure you're logged in with the correct account

## Future Enhancements

- Database integration (MySQL/PostgreSQL)
- Transaction history/logging
- Email notifications
- Two-factor authentication
- Interest calculation for savings accounts
- Admin dashboard
