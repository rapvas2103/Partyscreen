<?php
session_start();
require 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo']);
    $password = $_POST['password'];

    if (!empty($pseudo) && !empty($password)) {
        // Hachage du mot de passe pour la sécurité
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, password) VALUES (?, ?)");
            $stmt->execute([$pseudo, $passwordHash]);
            
            // Connecter l'utilisateur automatiquement après l'inscription
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['pseudo'] = $pseudo;
            
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur pour une contrainte d'unicité (pseudo déjà pris)
                $erreur = "Ce pseudo est déjà utilisé.";
            } else {
                $erreur = "Erreur lors de l'inscription.";
            }
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
    <title>Inscription - La Boîte à Jeux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: white; font-family: sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body>
    <div class="glass-panel p-8 rounded-3xl w-full max-w-sm text-center shadow-2xl">
        <h1 class="text-3xl font-bold mb-6">Inscription</h1>
        
        <?php if ($erreur): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded mb-4"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="pseudo" placeholder="Ton pseudo" required class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-400">
            <input type="password" name="password" placeholder="Mot de passe" required class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-400">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 py-3 rounded-xl font-bold transition-colors">Créer mon compte</button>
        </form>
        <p class="mt-4 text-sm text-gray-400">Déjà un compte ? <a href="connexion.php" class="text-blue-400 hover:underline">Se connecter</a></p>
    </div>
</body>
</html>