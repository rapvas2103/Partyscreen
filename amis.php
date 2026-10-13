<?php
session_start();
require 'db.php';

// Rediriger vers la connexion si on n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$mon_id = $_SESSION['user_id'];
$message = '';

// --- TRAITEMENT DES ACTIONS (Ajouter, Accepter, Refuser) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id_cible = (int)$_POST['id_cible'];

        if ($action === 'ajouter') {
            // Vérifier si une demande n'existe pas déjà
            $check = $pdo->prepare("SELECT id FROM amis WHERE (id_demandeur = ? AND id_receveur = ?) OR (id_demandeur = ? AND id_receveur = ?)");
            $check->execute([$mon_id, $id_cible, $id_cible, $mon_id]);
            
            if ($check->rowCount() == 0) {
                $stmt = $pdo->prepare("INSERT INTO amis (id_demandeur, id_receveur, statut) VALUES (?, ?, 'attente')");
                $stmt->execute([$mon_id, $id_cible]);
                $message = "<div class='text-green-400 mb-4'>Demande d'ami envoyée !</div>";
            }
        } elseif ($action === 'accepter') {
            $stmt = $pdo->prepare("UPDATE amis SET statut = 'accepte' WHERE id_demandeur = ? AND id_receveur = ?");
            $stmt->execute([$id_cible, $mon_id]);
            $message = "<div class='text-green-400 mb-4'>Demande acceptée ! Vous êtes maintenant amis.</div>";
        } elseif ($action === 'refuser') {
            $stmt = $pdo->prepare("DELETE FROM amis WHERE id_demandeur = ? AND id_receveur = ?");
            $stmt->execute([$id_cible, $mon_id]);
        }
    }
}

// --- RECHERCHE D'UN UTILISATEUR ---
$resultats_recherche = [];
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $recherche = "%" . trim($_GET['q']) . "%";
    // Chercher un pseudo similaire, qui n'est pas nous-même
    $stmt = $pdo->prepare("SELECT id, pseudo FROM utilisateurs WHERE pseudo LIKE ? AND id != ? LIMIT 10");
    $stmt->execute([$recherche, $mon_id]);
    $resultats_recherche = $stmt->fetchAll();
}

// --- LISTE DES DEMANDES REÇUES ---
$stmt_demandes = $pdo->prepare("
    SELECT u.id, u.pseudo 
    FROM amis a 
    JOIN utilisateurs u ON a.id_demandeur = u.id 
    WHERE a.id_receveur = ? AND a.statut = 'attente'
");
$stmt_demandes->execute([$mon_id]);
$demandes = $stmt_demandes->fetchAll();

// --- LISTE DES AMIS ACCEPTÉS ---
$stmt_amis = $pdo->prepare("
    SELECT u.id, u.pseudo 
    FROM amis a 
    JOIN utilisateurs u ON (a.id_demandeur = u.id OR a.id_receveur = u.id)
    WHERE (a.id_demandeur = ? OR a.id_receveur = ?) AND a.statut = 'accepte' AND u.id != ?
");
$stmt_amis->execute([$mon_id, $mon_id, $mon_id]);
$mes_amis = $stmt_amis->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Amis - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: white; font-family: sans-serif; min-height: 100vh; padding: 2rem;}
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="flex flex-col items-center">

    <!-- Bouton Retour -->
    <a href="index.php" class="absolute top-4 left-4 bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl font-bold transition-colors border border-white/20">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>

    <h1 class="text-4xl font-black mb-8 text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mt-8">
        Réseau PartyScreen
    </h1>

    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- COLONNE GAUCHE : RECHERCHE -->
        <div class="glass-panel p-6 rounded-3xl h-fit">
            <h2 class="text-2xl font-bold mb-4"><i class="fa-solid fa-magnifying-glass"></i> Ajouter un ami</h2>
            
            <?= $message ?>

            <form method="GET" class="flex gap-2 mb-6">
                <input type="text" name="q" placeholder="Chercher un pseudo..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400">
                <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 px-6 rounded-xl font-bold"><i class="fa-solid fa-search"></i></button>
            </form>

            <?php if (!empty($resultats_recherche)): ?>
                <div class="space-y-3">
                    <?php foreach ($resultats_recherche as $user): ?>
                        <div class="bg-white/5 border border-white/10 p-3 rounded-xl flex justify-between items-center">
                            <span class="font-bold text-lg"><?= htmlspecialchars($user['pseudo']) ?></span>
                            <form method="POST">
                                <input type="hidden" name="action" value="ajouter">
                                <input type="hidden" name="id_cible" value="<?= $user['id'] ?>">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-sm px-4 py-2 rounded-lg font-bold"><i class="fa-solid fa-user-plus"></i> Ajouter</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif(isset($_GET['q'])): ?>
                <p class="text-gray-400 text-sm">Aucun joueur trouvé avec ce pseudo.</p>
            <?php endif; ?>
        </div>

        <!-- COLONNE DROITE : DEMANDES ET LISTE D'AMIS -->
        <div class="space-y-8">
            
            <!-- Demandes en attente -->
            <?php if (!empty($demandes)): ?>
                <div class="glass-panel p-6 rounded-3xl border-orange-500/30">
                    <h2 class="text-xl font-bold mb-4 text-orange-400"><i class="fa-solid fa-bell"></i> Demandes reçues</h2>
                    <div class="space-y-3">
                        <?php foreach ($demandes as $demande): ?>
                            <div class="bg-white/5 border border-white/10 p-3 rounded-xl flex justify-between items-center">
                                <span class="font-bold text-lg"><?= htmlspecialchars($demande['pseudo']) ?></span>
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="id_cible" value="<?= $demande['id'] ?>">
                                    <button type="submit" name="action" value="accepter" class="bg-green-500 hover:bg-green-600 w-10 h-10 rounded-lg font-bold"><i class="fa-solid fa-check"></i></button>
                                    <button type="submit" name="action" value="refuser" class="bg-red-500 hover:bg-red-600 w-10 h-10 rounded-lg font-bold"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Liste des amis -->
            <div class="glass-panel p-6 rounded-3xl">
                <h2 class="text-xl font-bold mb-4"><i class="fa-solid fa-users"></i> Mes Amis (<?= count($mes_amis) ?>)</h2>
                <?php if (empty($mes_amis)): ?>
                    <p class="text-gray-400 text-sm">Tu n'as pas encore d'amis sur PartyScreen. Utilise la recherche pour en ajouter !</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($mes_amis as $ami): ?>
                            <div class="bg-black/30 border border-white/5 p-3 rounded-xl flex items-center gap-3">
                                <div class="bg-cyan-500/20 text-cyan-400 w-10 h-10 flex items-center justify-center rounded-full font-bold">
                                    <?= strtoupper(substr($ami['pseudo'], 0, 1)) ?>
                                </div>
                                <span class="font-bold text-lg"><?= htmlspecialchars($ami['pseudo']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>