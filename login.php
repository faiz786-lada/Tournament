<?php
include 'common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="max-w-md mx-auto mt-10">
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <i class="fas fa-trophy text-yellow-400 text-4xl mb-4"></i>
            <h2 class="text-2xl font-bold">Login to Adept Play</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-600 text-white p-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username or Email</label>
                <input type="text" name="username" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <button type="submit" 
                class="w-full bg-yellow-500 text-gray-900 py-2 rounded font-semibold hover:bg-yellow-400">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-400">Don't have an account?</p>
            <a href="register.php" class="text-yellow-400 hover:text-yellow-300 font-semibold">
                Register here
            </a>
        </div>
        
        <div class="mt-4 text-center">
            <a href="admin/login.php" class="text-blue-400 hover:text-blue-300">
                Admin Login
            </a>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
