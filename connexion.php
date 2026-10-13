<?php
session_start();
require 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo']);
    $password = $_POST['password'];

    if (!empty($pseudo) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = ?");
        $stmt->execute([$pseudo]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Tout est bon, on crée la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['pseudo'] = $user['pseudo'];
            
            header('Location: index.php');
            exit;
        } else {
            $erreur = "Pseudo ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - La Boîte à Jeux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: white; font-family: sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body>
    <div class="glass-panel p-8 rounded-3xl w-full max-w-sm text-center shadow-2xl">
        <h1 class="text-3xl font-bold mb-6">Connexion</h1>
        
        <?php if ($erreur): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded mb-4"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="pseudo" placeholder="Ton pseudo" required class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400">
            <input type="password" name="password" placeholder="Mot de passe" required class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400">
            <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 py-3 rounded-xl font-bold transition-colors">Se connecter</button>
        </form>
        <p class="mt-4 text-sm text-gray-400">Pas encore de compte ? <a href="inscription.php" class="text-cyan-400 hover:underline">S'inscrire</a></p>
    </div>
</body>
</html>