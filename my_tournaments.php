<?php
include 'common/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch user's tournaments
$stmt = $pdo->prepare("
    SELECT t.*, p.joined_at 
    FROM tournaments t 
    JOIN participants p ON t.id = p.tournament_id 
    WHERE p.user_id = ? 
    ORDER BY t.match_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_tournaments = $stmt->fetchAll();
?>

<?php include 'common/header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold mb-4">My Tournaments</h2>
    
    <?php if (empty($my_tournaments)): ?>
        <div class="bg-gray-800 p-6 rounded-lg text-center">
            <i class="fas fa-trophy text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-400">You haven't joined any tournaments yet.</p>
            <a href="index.php" class="inline-block mt-3 bg-yellow-500 text-gray-900 px-4 py-2 rounded font-semibold hover:bg-yellow-400">
                Browse Tournaments
            </a>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($my_tournaments as $tournament): ?>
                <div class="bg-gray-800 p-4 rounded-lg">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($tournament['title']); ?></h3>
                        <span class="bg-<?php echo $tournament['status'] === 'Completed' ? 'green' : ($tournament['status'] === 'Live' ? 'red' : 'blue'); ?>-600 px-2 py-1 rounded text-sm">
                            <?php echo $tournament['status']; ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <p class="text-gray-400 text-sm">Game</p>
                            <p class="font-semibold"><?php echo $tournament['game_name']; ?></p>
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
                            <p class="text-gray-400 text-sm">Joined On</p>
                            <p class="font-semibold"><?php echo date('M j, g:i A', strtotime($tournament['joined_at'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($tournament['status'] === 'Live' && $tournament['room_id']): ?>
                        <div class="bg-gray-700 p-3 rounded mb-3">
                            <h4 class="font-semibold mb-2">Match Details</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-gray-400 text-sm">Room ID</p>
                                    <p class="font-semibold"><?php echo $tournament['room_id']; ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm">Password</p>
                                    <p class="font-semibold"><?php echo $tournament['room_password']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
