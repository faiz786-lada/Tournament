<?php
include 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $upi_id = $_POST['upi_id'];
    
    $stmt = $pdo->prepare("UPDATE users SET upi_id = ? WHERE id = ?");
    if ($stmt->execute([$upi_id, $_SESSION['user_id']])) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold mb-4">My Profile</h2>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-600 text-white p-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-600 text-white p-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-gray-800 p-6 rounded-lg">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-user text-2xl text-gray-900"></i>
            </div>
            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($user['username']); ?></h3>
            <p class="text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" disabled>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" disabled>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Wallet Balance</label>
                <input type="text" value="<?php echo formatCurrency($user['wallet_balance']); ?>" 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" disabled>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">UPI ID</label>
                <input type="text" name="upi_id" value="<?php echo htmlspecialchars($user['upi_id']); ?>" 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400"
                    placeholder="Enter your UPI ID for withdrawals">
                <p class="text-sm text-gray-400 mt-1">This UPI ID will be used for all withdrawal transactions.</p>
            </div>
            
            <button type="submit" name="update_profile"
                class="w-full bg-yellow-500 text-gray-900 py-2 rounded font-semibold hover:bg-yellow-400">
                Update Profile
            </button>
        </form>
        
        <div class="mt-6 pt-6 border-t border-gray-700">
            <h4 class="font-semibold mb-3">Account Statistics</h4>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-700 p-3 rounded text-center">
                    <p class="text-2xl font-bold text-yellow-400">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        echo $stmt->fetchColumn();
                        ?>
                    </p>
                    <p class="text-sm text-gray-400">Tournaments Joined</p>
                </div>
                <div class="bg-gray-700 p-3 rounded text-center">
                    <p class="text-2xl font-bold text-green-400">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        echo $stmt->fetchColumn();
                        ?>
                    </p>
                    <p class="text-sm text-gray-400">Total Transactions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
