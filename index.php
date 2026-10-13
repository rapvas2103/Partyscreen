<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;900&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .glass-card:hover {
            transform: translateY(-5px) scale(1.02);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .card-icon-wrapper {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            background: rgba(0, 0, 0, 0.2);
        }

        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body class="flex flex-col items-center">

    <!-- Header -->
    <!-- Header avec gestion de la session -->
    <header class="text-center mb-16 mt-4 w-full relative">
        
        <!-- Barre utilisateur en haut à droite -->
        <div class="absolute top-0 right-0 flex gap-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="bg-white/10 border border-white/20 px-4 py-2 rounded-xl flex items-center gap-3">
                    <span class="font-bold text-cyan-300"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['pseudo']) ?></span>
                    <a href="amis.php" class="text-sm hover:text-white text-gray-400 border-l border-white/20 pl-3"><i class="fa-solid fa-users"></i> Amis</a>
                    <a href="deconnexion.php" class="text-sm hover:text-red-400 text-red-500 border-l border-white/20 pl-3"><i class="fa-solid fa-right-from-bracket"></i> Quitter</a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="bg-cyan-500/80 hover:bg-cyan-500 px-6 py-2 rounded-xl font-bold transition-colors">Connexion</a>
                <a href="inscription.php" class="bg-white/10 hover:bg-white/20 px-6 py-2 rounded-xl font-bold transition-colors">Inscription</a>
            <?php endif; ?>
        </div>

        <h1 class="text-5xl md:text-7xl font-black mb-4 text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mt-12">
            PartyScreen
        </h1>
        <p class="text-xl text-gray-300">Bienvenue dans l'espace de jeux ! Choisis ton ambiance.</p>
    </header>

    <!-- Grille des jeux -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 w-full max-w-7xl">

        <!-- Jeu 1 -->
        <a href="Longueur-onde.php" class="glass-card relative">
            <div class="badge bg-purple-500/80 text-white">Équipe / Débat</div>
            <div class="card-icon-wrapper text-purple-400">
                <i class="fa-solid fa-wifi"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Longueur d'Onde</h2>
                <p class="text-gray-400 text-sm">Trouvez la cible cachée sur le spectre grâce à un seul indice subtil. Idéal en groupe !</p>
            </div>
        </a>

        <!-- Jeu 2 -->
        <a href="neon-soccer.php" class="glass-card relative">
            <div class="badge bg-green-500/80 text-white">Action / 1v1</div>
            <div class="card-icon-wrapper text-green-400">
                <i class="fa-solid fa-car-burst"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Neon Bumper Soccer</h2>
                <p class="text-gray-400 text-sm">Pousse le ballon dans le but adverse avec ton auto-tamponneuse. Physique et chaos !</p>
            </div>
        </a>

        <!-- Jeu 3 -->
        <a href="convergence.php" class="glass-card relative">
            <div class="badge bg-pink-500/80 text-white">Coop / Couple</div>
            <div class="card-icon-wrapper text-pink-400">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Convergence</h2>
                <p class="text-gray-400 text-sm">Trouvez le lien entre deux mots pour réussir à penser exactement à la même chose.</p>
            </div>
        </a>

        <!-- Jeu 4 -->
        <a href="test-connexion.php" class="glass-card relative">
            <div class="badge bg-red-500/80 text-white">Quiz / Couple</div>
            <div class="card-icon-wrapper text-red-400">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Test de Connexion</h2>
                <p class="text-gray-400 text-sm">Devine ce que l'autre a répondu à la question. Vous connaissez-vous par cœur ?</p>
            </div>
        </a>

        <!-- Jeu 5 -->
        <a href="quidenousdeux.php" class="glass-card relative">
            <div class="badge bg-blue-500/80 text-white">Débat / 1v1</div>
            <div class="card-icon-wrapper text-blue-400">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Qui de nous deux ?</h2>
                <p class="text-gray-400 text-sm">Votez en secret pour désigner le ou la coupable sur une série de situations absurdes.</p>
            </div>
        </a>

        <!-- Jeu 6 -->
        <a href="tu-preferes.php" class="glass-card relative">
            <div class="badge bg-orange-500/80 text-white">Dilemmes</div>
            <div class="card-icon-wrapper text-orange-400">
                <i class="fa-solid fa-fire"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Tu préfères ?</h2>
                <p class="text-gray-400 text-sm">Le jeu des choix impossibles. Votez simultanément et comparez vos décisions.</p>
            </div>
        </a>

        <!-- Jeu 7 -->
        <a href="mot-interdit.php" class="glass-card relative">
            <div class="badge bg-yellow-500/80 text-white">Rapidité</div>
            <div class="card-icon-wrapper text-yellow-400">
                <i class="fa-solid fa-comment-slash"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Le Mot Interdit</h2>
                <p class="text-gray-400 text-sm">60 secondes pour faire deviner un mot à l'autre sans prononcer les mots interdits.</p>
            </div>
        </a>
		
		<!-- Polytrack -->
		<a href="polytrack.html" class="glass-card relative">
			<div class="badge bg-yellow-500/80 text-white">Course / 3D</div>
			<div class="card-icon-wrapper text-yellow-400">
				<i class="fa-solid fa-flag-checkered"></i>
			</div>
			<div class="p-6">
				<h2 class="text-2xl font-bold mb-2">Polytrack</h2>
				<p class="text-gray-400 text-sm">Course contre la montre façon Trackmania sur des circuits low-poly.</p>
			</div>
		</a>

		<!-- OpenFront (Ouverture dans un nouvel onglet) -->
		<a href="https://openfront.io/" target="_blank" class="glass-card relative">
			<div class="badge bg-indigo-500/80 text-white">Stratégie / MMO</div>
			<div class="card-icon-wrapper text-indigo-400">
				<i class="fa-solid fa-arrow-up-right-from-square"></i>
			</div>
			<div class="p-6">
				<h2 class="text-2xl font-bold mb-2">OpenFront</h2>
				<p class="text-gray-400 text-sm">Conquête de territoires en temps réel multijoueur. <br><span class="text-indigo-300 text-xs italic">S'ouvre dans un nouvel onglet</span></p>
			</div>
		</a>
		
		<!-- Partypoly -->
		<a href="partypoly.php" class="glass-card relative">
            <div class="badge bg-green-500/80 text-white">stratégie</div>
            <div class="card-icon-wrapper text-green-400">
                <i class="fa-solid fa-car-burst"></i>
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2">Partypoly</h2>
                <p class="text-gray-400 text-sm">Prend le contrôle de toutes les case et détruit tes adversaires !</p>
            </div>
        </a>

    </div>
</body>
</html>