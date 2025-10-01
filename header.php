<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adept Play - Tournament App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Disable text selection, right-click, and zoom
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
            
            document.addEventListener('selectstart', function(e) {
                e.preventDefault();
            });
            
            document.addEventListener('touchmove', function(e) {
                if (e.scale !== 1) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            document.addEventListener('wheel', function(e) {
                if (e.ctrlKey) {
                    e.preventDefault();
                }
            }, { passive: false });
        });
    </script>
</head>
<body class="bg-gray-900 text-white overflow-hidden select-none">
    <header class="bg-gray-800 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-trophy text-yellow-400 text-xl"></i>
                <h1 class="text-xl font-bold">Adept Play</h1>
            </div>
            
            <?php if (isLoggedIn()): ?>
            <div class="flex items-center space-x-4">
                <div class="bg-gray-700 px-3 py-1 rounded-full">
                    <i class="fas fa-wallet text-green-400 mr-1"></i>
                    <span class="font-semibold">
                        <?php
                        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        echo formatCurrency($user['wallet_balance']);
                        ?>
                    </span>
                </div>
                <a href="logout.php" class="bg-red-600 px-3 py-1 rounded hover:bg-red-700">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="container mx-auto p-4 pb-20">
