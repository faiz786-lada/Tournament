    </main>
    
    <?php if (isLoggedIn() && !isAdminLoggedIn()): ?>
    <nav class="fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700">
        <div class="flex justify-around items-center p-3">
            <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-white">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="my_tournaments.php" class="flex flex-col items-center text-gray-400 hover:text-white">
                <i class="fas fa-trophy text-xl mb-1"></i>
                <span class="text-xs">My Tournaments</span>
            </a>
            <a href="wallet.php" class="flex flex-col items-center text-gray-400 hover:text-white">
                <i class="fas fa-wallet text-xl mb-1"></i>
                <span class="text-xs">Wallet</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-white">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </nav>
    <?php endif; ?>
    
    <script>
        // Prevent zoom
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('gesturechange', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('gestureend', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>
