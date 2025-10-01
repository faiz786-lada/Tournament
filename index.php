<?php
include 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch upcoming tournaments
$stmt = $pdo->prepare("
    SELECT * FROM tournaments 
    WHERE status = 'Upcoming' 
    ORDER BY match_time ASC
");
$stmt->execute();
$tournaments = $stmt->fetchAll();

// Handle tournament joining
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_tournament'])) {
    $tournament_id = $_POST['tournament_id'];
    
    // Get tournament details
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
    $stmt->execute([$tournament_id]);
    $tournament = $stmt->fetch();
    
    // Check if user has sufficient balance
    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['wallet_balance'] >= $tournament['entry_fee']) {
        // Deduct entry fee
        $new_balance = $user['wallet_balance'] - $tournament['entry_fee'];
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $stmt->execute([$new_balance, $_SESSION['user_id']]);
        
        // Add transaction record
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)");
        $stmt->execute([$_SESSION['user_id'], $tournament['entry_fee'], "Entry fee for tournament: " . $tournament['title']]);
        
        // Add participant
        $stmt = $pdo->prepare("INSERT INTO participants (user_id, tournament_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $tournament_id]);
        
        $success = "Successfully joined tournament!";
    } else {
        $error = "Insufficient balance to join this tournament";
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Upcoming Tournaments</h2>
        <a href="my_tournaments.php" class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
            My Tournaments
        </a>
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
    
    <?php if (empty($tournaments)): ?>
        <div class="bg-gray-800 p-6 rounded-lg text-center">
            <i class="fas fa-trophy text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-400">No upcoming tournaments available.</p>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($tournaments as $tournament): ?>
                <div class="bg-gray-800 p-4 rounded-lg">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($tournament['title']); ?></h3>
                        <span class="bg-green-600 px-2 py-1 rounded text-sm"><?php echo $tournament['game_name']; ?></span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-gray-400 text-sm">Entry Fee</p>
                            <p class="font-semibold"><?php echo formatCurrency($tournament['entry_fee']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Prize Pool</p>
                            <p class="font-semibold text-green-400"><?php echo formatCurrency($tournament['prize_pool']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Match Time</p>
                            <p class="font-semibold"><?php echo date('M j, g:i A', strtotime($tournament['match_time'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Players</p>
                            <p class="font-semibold">
                                <?php
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE tournament_id = ?");
                                $stmt->execute([$tournament['id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="tournament_id" value="<?php echo $tournament['id']; ?>">
                        <button type="submit" name="join_tournament" 
                            class="w-full bg-yellow-500 text-gray-900 py-2 rounded font-semibold hover:bg-yellow-400">
                            Join Tournament - <?php echo formatCurrency($tournament['entry_fee']); ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
