<?php
include 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user wallet balance
$stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Get admin UPI settings
$stmt = $pdo->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();

// Handle deposit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_request'])) {
    $amount = $_POST['amount'];
    $transaction_id = $_POST['transaction_id'];
    
    $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount, transaction_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $amount, $transaction_id])) {
        $success = "Deposit request submitted successfully! It will be processed after verification.";
    } else {
        $error = "Failed to submit deposit request. Please try again.";
    }
}

// Handle withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_request'])) {
    $amount = $_POST['withdrawal_amount'];
    
    // Check if user has sufficient balance
    if ($user['wallet_balance'] >= $amount) {
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount) VALUES (?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $amount])) {
            $success = "Withdrawal request submitted successfully!";
        } else {
            $error = "Failed to submit withdrawal request. Please try again.";
        }
    } else {
        $error = "Insufficient balance for withdrawal.";
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold mb-4">My Wallet</h2>
    
    <!-- Wallet Balance Card -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-300">Current Balance</p>
                <p class="text-3xl font-bold"><?php echo formatCurrency($user['wallet_balance']); ?></p>
            </div>
            <i class="fas fa-wallet text-4xl text-white opacity-80"></i>
        </div>
    </div>
    
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
    
    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Add Money Button -->
        <button onclick="openDepositModal()" 
            class="bg-green-600 p-4 rounded-lg text-center hover:bg-green-700">
            <i class="fas fa-plus-circle text-2xl mb-2"></i>
            <p class="font-semibold">Add Money</p>
        </button>
        
        <!-- Withdraw Button -->
        <button onclick="openWithdrawalModal()" 
            class="bg-blue-600 p-4 rounded-lg text-center hover:bg-blue-700">
            <i class="fas fa-money-bill-wave text-2xl mb-2"></i>
            <p class="font-semibold">Withdraw</p>
        </button>
    </div>
    
    <!-- Recent Transactions -->
    <div class="bg-gray-800 p-4 rounded-lg">
        <h3 class="text-xl font-semibold mb-4">Recent Transactions</h3>
        
        <?php if (empty($transactions)): ?>
            <p class="text-gray-400 text-center py-4">No transactions yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-700 rounded">
                        <div>
                            <p class="font-semibold"><?php echo htmlspecialchars($transaction['description']); ?></p>
                            <p class="text-sm text-gray-400"><?php echo date('M j, g:i A', strtotime($transaction['created_at'])); ?></p>
                        </div>
                        <span class="font-semibold <?php echo $transaction['type'] === 'credit' ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php echo $transaction['type'] === 'credit' ? '+' : '-'; ?>
                            <?php echo formatCurrency($transaction['amount']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Deposit Modal -->
<div id="depositModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-gray-800 rounded-lg max-w-md w-full">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-xl font-semibold">Add Money to Wallet</h3>
        </div>
        
        <div class="p-4">
            <?php if ($settings && $settings['upi_id'] && $settings['qr_code']): ?>
                <div class="text-center mb-4">
                    <p class="text-gray-400 mb-2">Scan QR Code or Use UPI ID</p>
                    <div class="bg-white p-4 rounded inline-block mb-2">
                        <img src="<?php echo htmlspecialchars($settings['qr_code']); ?>" alt="QR Code" class="w-48 h-48 mx-auto">
                    </div>
                    <p class="font-semibold text-lg">UPI ID: <?php echo htmlspecialchars($settings['upi_id']); ?></p>
                </div>
                
                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Amount Paid (₹)</label>
                            <input type="number" name="amount" required min="10" step="0.01"
                                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">UPI Transaction ID</label>
                            <input type="text" name="transaction_id" required 
                                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400"
                                placeholder="Enter transaction ID from your payment app">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeDepositModal()" 
                                class="flex-1 bg-gray-600 py-2 rounded hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" name="deposit_request"
                                class="flex-1 bg-green-600 py-2 rounded hover:bg-green-700">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-3"></i>
                    <p class="text-gray-400">Deposit service is currently unavailable.</p>
                    <p class="text-sm text-gray-500 mt-2">Please contact admin to set up UPI details.</p>
                </div>
                <button onclick="closeDepositModal()" 
                    class="w-full bg-gray-600 py-2 rounded hover:bg-gray-700">
                    Close
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-gray-800 rounded-lg max-w-md w-full">
        <div class="p-4 border-b border-gray-700">
            <h3 class="text-xl font-semibold">Withdraw Money</h3>
        </div>
        
        <div class="p-4">
            <?php
            // Get user's UPI ID
            $stmt = $pdo->prepare("SELECT upi_id FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_info = $stmt->fetch();
            ?>
            
            <?php if ($user_info && $user_info['upi_id']): ?>
                <div class="mb-4">
                    <p class="text-gray-400">Amount will be sent to:</p>
                    <p class="font-semibold"><?php echo htmlspecialchars($user_info['upi_id']); ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <a href="profile.php" class="text-blue-400 hover:text-blue-300">Update UPI ID in Profile</a>
                    </p>
                </div>
                
                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Amount to Withdraw (₹)</label>
                            <input type="number" name="withdrawal_amount" required min="10" step="0.01" max="<?php echo $user['wallet_balance']; ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
                            <p class="text-sm text-gray-400 mt-1">Available: <?php echo formatCurrency($user['wallet_balance']); ?></p>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeWithdrawalModal()" 
                                class="flex-1 bg-gray-600 py-2 rounded hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" name="withdrawal_request"
                                class="flex-1 bg-blue-600 py-2 rounded hover:bg-blue-700">
                                Request Withdrawal
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl mb-3"></i>
                    <p class="text-gray-400">Please set your UPI ID to withdraw money.</p>
                    <a href="profile.php" class="inline-block mt-3 bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
                        Set UPI ID in Profile
                    </a>
                </div>
                <button onclick="closeWithdrawalModal()" 
                    class="w-full bg-gray-600 py-2 rounded hover:bg-gray-700">
                    Close
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openDepositModal() {
    document.getElementById('depositModal').classList.remove('hidden');
    document.getElementById('depositModal').classList.add('flex');
}

function closeDepositModal() {
    document.getElementById('depositModal').classList.add('hidden');
    document.getElementById('depositModal').classList.remove('flex');
}

function openWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.remove('hidden');
    document.getElementById('withdrawalModal').classList.add('flex');
}

function closeWithdrawalModal() {
    document.getElementById('withdrawalModal').classList.add('hidden');
    document.getElementById('withdrawalModal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('depositModal').addEventListener('click', function(e) {
    if (e.target === this) closeDepositModal();
});

document.getElementById('withdrawalModal').addEventListener('click', function(e) {
    if (e.target === this) closeWithdrawalModal();
});
</script>

<?php include 'common/bottom.php'; ?>
