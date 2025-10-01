<?php
include 'common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = "Username or email already exists";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                header('Location: index.php');
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="max-w-md mx-auto mt-10">
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <i class="fas fa-user-plus text-yellow-400 text-4xl mb-4"></i>
            <h2 class="text-2xl font-bold">Join Adept Play</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-600 text-white p-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required 
                    class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-yellow-400">
            </div>
            
            <button type="submit" 
                class="w-full bg-yellow-500 text-gray-900 py-2 rounded font-semibold hover:bg-yellow-400">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-400">Already have an account?</p>
            <a href="login.php" class="text-yellow-400 hover:text-yellow-300 font-semibold">
                Login here
            </a>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
