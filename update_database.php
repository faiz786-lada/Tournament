<?php
// Database update script for existing installations
// This script safely adds new tables and columns without affecting existing data

include 'common/config.php';

try {
    // Create deposits table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS deposits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            transaction_id VARCHAR(255) NOT NULL,
            status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create withdrawals table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            status ENUM('Pending', 'Completed', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create settings table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            upi_id VARCHAR(255) NULL,
            qr_code VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Add upi_id column to users table if not exists
    $check_column = $pdo->query("SHOW COLUMNS FROM users LIKE 'upi_id'")->fetch();
    if (!$check_column) {
        $pdo->exec("ALTER TABLE users ADD COLUMN upi_id VARCHAR(255) NULL");
    }
    
    // Insert default settings record if not exists
    $check_settings = $pdo->query("SELECT id FROM settings WHERE id = 1")->fetch();
    if (!$check_settings) {
        $pdo->exec("INSERT INTO settings (id) VALUES (1)");
    }
    
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Update Complete</title>
        <script src='https://cdn.tailwindcss.com'></script>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    </head>
    <body class='bg-gray-900 text-white min-h-screen flex items-center justify-center'>
        <div class='bg-gray-800 p-8 rounded-lg shadow-lg max-w-md w-full'>
            <div class='text-center mb-6'>
                <i class='fas fa-check-circle text-green-400 text-5xl mb-4'></i>
                <h1 class='text-2xl font-bold'>Database Updated Successfully!</h1>
            </div>
            <div class='space-y-4'>
                <div class='bg-gray-700 p-4 rounded'>
                    <h3 class='font-semibold mb-2'>Updates Applied:</h3>
                    <ul class='list-disc list-inside space-y-1 text-sm'>
                        <li>Created deposits table</li>
                        <li>Created withdrawals table</li>
                        <li>Created settings table</li>
                        <li>Added upi_id column to users table</li>
                    </ul>
                </div>
                <p class='text-green-400 text-sm text-center'>✅ Database schema updated successfully! You can now safely delete this file.</p>
                <a href='index.php' class='block w-full bg-green-600 text-white py-3 rounded text-center font-semibold hover:bg-green-700'>
                    Go to Home
                </a>
            </div>
        </div>
    </body>
    </html>
    ";
    
} catch(PDOException $e) {
    die("
    <div class='bg-red-600 text-white p-8 rounded-lg shadow-lg max-w-md mx-auto mt-8'>
        <h1 class='text-2xl font-bold mb-4'>Database Update Failed</h1>
        <p>Error: " . $e->getMessage() . "</p>
        <p class='mt-4'>Please check your database connection and try again.</p>
    </div>
    ");
}
?>
