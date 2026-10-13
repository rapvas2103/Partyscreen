<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Mot Interdit - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap');
        
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #111827 0%, #374151 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); }
        .btn-start { background: linear-gradient(45deg, #10b981, #3b82f6); transition: all 0.2s ease; }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }
        .btn-success { background: linear-gradient(45deg, #059669, #10b981); border-bottom: 4px solid #047857; }
        .btn-success:active { transform: translateY(4px); border-bottom: 0px; }
        .btn-pass { background: linear-gradient(45deg, #dc2626, #ef4444); border-bottom: 4px solid #b91c1c; }
        .btn-pass:active { transform: translateY(4px); border-bottom: 0px; }
        .timer-urgent { color: #ef4444; animation: pulse 1s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .forbidden-word { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); }

        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="text-6xl mb-4 text-red-500"><i class="fa-solid fa-comment-slash"></i></div>
        <h1 class="text-4xl font-black mb-2">Le Mot Interdit</h1>
        
        <div class="mb-6 p-5 bg-white/5 border border-white/10 rounded-xl mt-6">
            <h3 class="font-bold text-gray-300 mb-3"><i class="fa-solid fa-desktop"></i> Sur le même écran</h3>
            <button onclick="startLocal()" class="btn-start w-full py-3 rounded-xl font-bold text-white">Lancer en Local</button>
        </div>

        <div class="p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-cyan-300 mb-3"><i class="fa-solid fa-globe"></i> En ligne avec un ami</h3>
            <input type="text" id="room-code" placeholder="Code du salon (ex: 1234)" class="w-full bg-black/40 border-2 border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 mb-3 text-center tracking-widest font-bold text-xl uppercase">
            
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 bg-cyan-600 hover:bg-cyan-500 py-3 rounded-xl font-bold text-white transition text-sm">Créer (J1)</button>
                <button onclick="joinRoom()" class="flex-1 bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold text-white transition text-sm">Rejoindre (J2)</button>
            </div>
            <p class="text-xs text-gray-400 mt-4 text-left"><i class="fa-solid fa-circle-info"></i> L'écran de la personne qui devine sera masqué automatiquement.</p>
        </div>
    </div>

    <!-- ECRAN DE TRANSITION -->
    <div id="transition-screen" class="glass-panel p-8 rounded-3xl w-full max-w-lg hidden text-center mt-8 border-2 border-green-500/30">
        <div class="text-6xl mb-6 text-yellow-400" id="transition-icon"><i class="fa-solid fa-microphone"></i></div>
        <h2 class="text-2xl font-bold text-gray-300 mb-2" id="transition-title">C'est au tour de Joueur 1</h2>
        
        <div class="bg-red-500/20 border border-red-500/50 p-4 rounded-xl my-8" id="transition-box">
            <p id="transition-msg" class="text-white font-bold text-lg"></p>
        </div>

        <button id="btn-start-timer" onclick="startRoundTimer()" class="btn-start w-full py-5 rounded-xl font-black text-2xl text-white shadow-lg">
            Lancer le chrono (Espace)
        </button>
        <div id="wait-msg" class="hidden text-gray-400 font-bold mt-4 animate-pulse">En attente que l'autre joueur lance le chrono...</div>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="w-full max-w-2xl hidden flex-col items-center mt-4">
        
        <div class="w-full flex justify-between items-center mb-6 px-4">
            <div class="bg-black/50 border border-white/10 px-4 py-2 rounded-xl text-center">
                <span class="text-sm text-gray-400 block leading-none mb-1">Score</span>
                <span id="round-score" class="font-black text-2xl text-green-400 leading-none">0</span>
            </div>
            <div id="timer-display" class="text-5xl font-black text-white tracking-widest bg-black/50 px-6 py-2 rounded-2xl border-2 border-white/20">60</div>
        </div>

        <!-- VUE DU SPEAKER (Celui qui fait deviner) -->
        <div id="speaker-view" class="w-full hidden flex-col items-center">
            <div class="glass-panel w-full p-8 rounded-3xl flex flex-col items-center text-center mb-8">
                <span class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Fais deviner ce mot :</span>
                <h2 id="target-word" class="text-5xl sm:text-6xl font-black text-white mb-8 tracking-tight">MOT</h2>
                <div class="w-full border-t border-white/10 pt-6">
                    <span class="text-sm font-bold text-red-400 uppercase tracking-widest mb-4 block"><i class="fa-solid fa-triangle-exclamation"></i> Mots Interdits :</span>
                    <div class="flex flex-col gap-3 w-full max-w-xs mx-auto">
                        <div id="forbidden-1" class="forbidden-word text-2xl font-bold text-red-300 py-2 rounded-lg"></div>
                        <div id="forbidden-2" class="forbidden-word text-2xl font-bold text-red-300 py-2 rounded-lg"></div>
                        <div id="forbidden-3" class="forbidden-word text-2xl font-bold text-red-300 py-2 rounded-lg"></div>
                    </div>
                </div>
            </div>
            <div class="flex w-full gap-4">
                <button onclick="handlePass()" class="btn-pass flex-1 py-5 rounded-2xl font-bold text-xl text-white"><i class="fa-solid fa-forward-step"></i> PASSER (P)</button>
                <button onclick="handleSuccess()" class="btn-success flex-[2] py-5 rounded-2xl font-black text-2xl text-white"><i class="fa-solid fa-check"></i> TROUVÉ ! (Espace)</button>
            </div>
        </div>

        <!-- VUE DU GUESSER (Celui qui devine - Seulement en ligne) -->
        <div id="guesser-view" class="w-full hidden flex-col items-center">
            <div class="glass-panel w-full p-12 rounded-3xl flex flex-col items-center text-center border-blue-500/50 border-2">
                <i class="fa-solid fa-ear-listen text-6xl text-blue-400 mb-6 animate-pulse"></i>
                <h2 class="text-4xl font-black text-white mb-4">Écoute bien !</h2>
                <p class="text-xl text-gray-300">Devine le mot décrit par ton ami.</p>
            </div>
        </div>
    </div>

    <script>
        const socket = io('https://partyscreen-server.onrender.com');
        
        let isOnline = false;
        let myRole = 0; // 0=Local, 1=J1, 2=J2
        let currentRoom = "";
        
        let activePlayer = 1; // 1 = J1 fait deviner, 2 = J2 fait deviner
        let roundScore = 0;
        let timeLeft = 60;
        let timerInterval = null;
        let gameState = 'setup';

        // Base de mots (N'oublie pas de coller tes 100 mots ici !)
        const allWords = [
            // Thèmes personnalisés et clins d'œil
            { t: "Shaker", f: ["Cocktail", "Mélanger", "Bar"] },
            { t: "Psychologue", f: ["Parler", "Thérapie", "Tête"] },
            { t: "Routeur", f: ["Internet", "Wifi", "Box"] },
            { t: "Mécanicien", f: ["Voiture", "Réparer", "Garage"] },
            { t: "Aspirateur", f: ["Ménage", "Poussière", "Bruit"] },
            { t: "Barcelone", f: ["Espagne", "Ville", "Vacances"] },
            { t: "Permis Bateau", f: ["Mer", "Naviguer", "Examen"] },
            { t: "Fusée", f: ["Espace", "Décoller", "Lune"] },
            { t: "Ail", f: ["Cuisine", "Odeur", "Vampire"] },
            { t: "Serveur", f: ["Informatique", "Restaurant", "Plateau"] },
            { t: "Clio", f: ["Voiture", "Renault", "Marque"] },
            { t: "Essuie-glace", f: ["Pluie", "Pare-brise", "Eau"] },
            { t: "Onde", f: ["Longueur", "Radio", "Fréquence"] },
            { t: "Vidange", f: ["Huile", "Moteur", "Garage"] },
            { t: "Action", f: ["Magasin", "Acheter", "Rayon"] },
            { t: "Menthe", f: ["Vert", "Feuille", "Mojito"] },

            // Objets du quotidien
            { t: "Parapluie", f: ["Pluie", "Ouvrir", "Protéger"] },
            { t: "Brosse à dents", f: ["Dentifrice", "Laver", "Bouche"] },
            { t: "Oreiller", f: ["Dormir", "Lit", "Tête"] },
            { t: "Télécommande", f: ["Télévision", "Bouton", "Changer"] },
            { t: "Clé", f: ["Porte", "Ouvrir", "Serrure"] },
            { t: "Lunettes", f: ["Yeux", "Voir", "Soleil"] },
            { t: "Montre", f: ["Heure", "Poignet", "Temps"] },
            { t: "Couteau", f: ["Couper", "Fourchette", "Lame"] },
            { t: "Savon", f: ["Laver", "Mains", "Mousse"] },
            { t: "Chargeur", f: ["Batterie", "Téléphone", "Prise"] },
            { t: "Réveil", f: ["Matin", "Sonner", "Heure"] },
            { t: "Micro-ondes", f: ["Chauffer", "Manger", "Plat"] },
            { t: "Ciseaux", f: ["Couper", "Papier", "Lame"] },
            { t: "Valise", f: ["Voyage", "Bagage", "Vêtements"] },
            { t: "Miroir", f: ["Reflet", "Regarder", "Glace"] },
            { t: "Bouteille", f: ["Eau", "Boire", "Plastique"] },
            { t: "Chaussure", f: ["Pied", "Marcher", "Lacet"] },
            { t: "Sac à dos", f: ["École", "Porter", "Bretelles"] },
            { t: "Éponge", f: ["Vaisselle", "Nettoyer", "Eau"] },
            { t: "Ascenseur", f: ["Monter", "Descendre", "Étage"] },

            // Aliments / Boissons
            { t: "Chocolat", f: ["Cacao", "Tablette", "Noir"] },
            { t: "Café", f: ["Matin", "Boire", "Noir"] },
            { t: "Pizza", f: ["Italie", "Fromage", "Pâte"] },
            { t: "Fraise", f: ["Rouge", "Fruit", "Dessert"] },
            { t: "Fromage", f: ["Lait", "Vache", "Camembert"] },
            { t: "Bière", f: ["Alcool", "Mousse", "Blonde"] },
            { t: "Pâtes", f: ["Italie", "Spaghetti", "Eau"] },
            { t: "Hamburger", f: ["Pain", "Viande", "Fast-food"] },
            { t: "Glace", f: ["Froid", "Cornet", "Vanille"] },
            { t: "Pomme", f: ["Fruit", "Arbre", "Vert"] },
            { t: "Crevette", f: ["Rose", "Fruit de mer", "Décortiquer"] },
            { t: "Saucisson", f: ["Porc", "Apéro", "Tranche"] },
            { t: "Banane", f: ["Jaune", "Singe", "Fruit"] },
            { t: "Pain", f: ["Boulangerie", "Baguette", "Mie"] },

            // Animaux
            { t: "Chien", f: ["Chat", "Aboyer", "Animal"] },
            { t: "Oiseau", f: ["Voler", "Plumes", "Ciel"] },
            { t: "Poisson", f: ["Eau", "Nager", "Aquarium"] },
            { t: "Cheval", f: ["Monter", "Galop", "Animal"] },
            { t: "Araignée", f: ["Toile", "Pattes", "Peur"] },
            { t: "Vache", f: ["Lait", "Pré", "Taches"] },
            { t: "Serpent", f: ["Ramper", "Venin", "Reptile"] },
            { t: "Lion", f: ["Roi", "Savane", "Rugir"] },
            { t: "Moustique", f: ["Piquer", "Sang", "Nuit"] },
            { t: "Éléphant", f: ["Trompe", "Gros", "Gris"] },

            // Lieux / Nature / Concepts
            { t: "Plage", f: ["Sable", "Mer", "Soleil"] },
            { t: "Montagne", f: ["Neige", "Skier", "Altitude"] },
            { t: "Hôpital", f: ["Médecin", "Malade", "Urgence"] },
            { t: "Cinéma", f: ["Film", "Écran", "Pop-corn"] },
            { t: "École", f: ["Élève", "Professeur", "Classe"] },
            { t: "Aéroport", f: ["Avion", "Voyage", "Vol"] },
            { t: "Soleil", f: ["Ciel", "Chaud", "Jaune"] },
            { t: "Lune", f: ["Nuit", "Ciel", "Étoile"] },
            { t: "Vent", f: ["Souffler", "Air", "Tempête"] },
            { t: "Neige", f: ["Froid", "Hiver", "Blanc"] },
            { t: "Feu", f: ["Chaud", "Brûler", "Flamme"] },
            { t: "Forêt", f: ["Arbre", "Bois", "Nature"] },
            { t: "Mariage", f: ["Bague", "Robes", "Fête"] },
            { t: "Cadeau", f: ["Anniversaire", "Noël", "Ouvrir"] },

            // Actions / Sports / Loisirs
            { t: "Piscine", f: ["Nager", "Eau", "Maillot"] },
            { t: "Football", f: ["Ballon", "But", "Stade"] },
            { t: "Musique", f: ["Écouter", "Chanson", "Instrument"] },
            { t: "Livre", f: ["Lire", "Pages", "Histoire"] },
            { t: "Photographie", f: ["Appareil", "Image", "Sourire"] },
            { t: "Danse", f: ["Musique", "Bouger", "Fête"] },
            { t: "Vélo", f: ["Pédaler", "Deux-roues", "Selle"] },
            { t: "Dormir", f: ["Lit", "Fatigue", "Nuit"] },
            { t: "Pleurer", f: ["Larmes", "Triste", "Yeux"] },
            { t: "Cuisiner", f: ["Repas", "Manger", "Plat"] },
            { t: "Courir", f: ["Vite", "Sport", "Jambes"] },
            { t: "Chanter", f: ["Voix", "Musique", "Micro"] },

            // Divers
            { t: "Fantôme", f: ["Peur", "Esprit", "Blanc"] },
            { t: "Robot", f: ["Machine", "Métal", "Artificiel"] },
            { t: "Bouton", f: ["Appuyer", "Vêtement", "Coudre"] },
            { t: "Billet", f: ["Argent", "Papier", "Payer"] },
            { t: "Batterie", f: ["Téléphone", "Énergie", "Pourcentage"] },
            { t: "Sourire", f: ["Bouche", "Heureux", "Dents"] },
            { t: "Nuage", f: ["Ciel", "Blanc", "Pluie"] },
            { t: "Étoile", f: ["Nuit", "Ciel", "Briller"] }
        ];
        let wordsDeck = [];

        function startLocal() {
            isOnline = false;
            myRole = 0;
            activePlayer = 1;
            wordsDeck = [...allWords].sort(() => Math.random() - 0.5);
            document.getElementById('setup-screen').classList.add('hidden');
            setupTransition();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true;
            myRole = 1;
            currentRoom = code;
            activePlayer = 1;
            wordsDeck = [...allWords].sort(() => Math.random() - 0.5);
            socket.emit('rejoindreSalon', code);
            document.getElementById('setup-screen').classList.add('hidden');
            setupTransition();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true;
            myRole = 2;
            currentRoom = code;
            activePlayer = 1;
            socket.emit('rejoindreSalon', code);
            document.getElementById('setup-screen').classList.add('hidden');
            setupTransition();
        }

        function setupTransition() {
            gameState = 'transition';
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('transition-screen').classList.remove('hidden');

            const isMyTurn = (!isOnline || myRole === activePlayer);

            if (isMyTurn) {
                document.getElementById('transition-icon').innerHTML = '<i class="fa-solid fa-microphone"></i>';
                document.getElementById('transition-title').innerText = "C'est à TOI de faire deviner !";
                document.getElementById('transition-msg').innerText = "Prépare-toi à parler.";
                document.getElementById('btn-start-timer').classList.remove('hidden');
                document.getElementById('wait-msg').classList.add('hidden');
            } else {
                document.getElementById('transition-icon').innerHTML = '<i class="fa-solid fa-ear-listen text-blue-400"></i>';
                document.getElementById('transition-title').innerText = "C'est à toi de DEVINER !";
                if(myRole === 0) { // Si local
                    document.getElementById('transition-msg').innerText = "Ferme les yeux et ne regarde pas l'écran !";
                    document.getElementById('btn-start-timer').classList.remove('hidden');
                    document.getElementById('wait-msg').classList.add('hidden');
                } else { // Si online
                    document.getElementById('transition-msg').innerText = "Regarde ton écran et écoute bien.";
                    document.getElementById('btn-start-timer').classList.add('hidden');
                    document.getElementById('wait-msg').classList.remove('hidden');
                }
            }
        }

        function startRoundTimer() {
            if (isOnline) {
                // Notifie l'autre joueur que le chrono démarre et lui envoie le deck de mots mélangés (si c'est J1 qui gère)
                socket.emit('actionJeu', { room: currentRoom, type: 'startTimer', deck: wordsDeck });
            }
            triggerTimerLocal();
        }

        function triggerTimerLocal() {
            gameState = 'playing';
            roundScore = 0;
            timeLeft = 60;
            
            document.getElementById('round-score').innerText = "0";
            document.getElementById('timer-display').innerText = "60";
            document.getElementById('timer-display').classList.remove('timer-urgent');

            document.getElementById('transition-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');

            const isMyTurn = (!isOnline || myRole === activePlayer);
            
            if(isMyTurn) {
                document.getElementById('speaker-view').classList.remove('hidden');
                document.getElementById('speaker-view').classList.add('flex');
                document.getElementById('guesser-view').classList.add('hidden');
                document.getElementById('guesser-view').classList.remove('flex');
                nextWord();
            } else {
                document.getElementById('speaker-view').classList.add('hidden');
                document.getElementById('speaker-view').classList.remove('flex');
                document.getElementById('guesser-view').classList.remove('hidden');
                document.getElementById('guesser-view').classList.add('flex');
            }

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer-display').innerText = timeLeft;
                if (timeLeft <= 10) document.getElementById('timer-display').classList.add('timer-urgent');
                if (timeLeft <= 0) endRound();
            }, 1000);
        }

        function nextWord() {
            if (wordsDeck.length === 0) wordsDeck = [...allWords].sort(() => Math.random() - 0.5);
            const currentCard = wordsDeck.pop();
            
            document.getElementById('target-word').innerText = currentCard.t;
            document.getElementById('forbidden-1').innerText = currentCard.f[0];
            document.getElementById('forbidden-2').innerText = currentCard.f[1];
            document.getElementById('forbidden-3').innerText = currentCard.f[2];
        }

        function handleSuccess() {
            if (gameState !== 'playing' || (isOnline && myRole !== activePlayer)) return;
            roundScore++;
            document.getElementById('round-score').innerText = roundScore;
            if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'updateScore', score: roundScore });
            nextWord();
        }

        function handlePass() {
            if (gameState !== 'playing' || (isOnline && myRole !== activePlayer)) return;
            nextWord();
        }

        function endRound() {
            clearInterval(timerInterval);
            // On inverse les rôles pour la prochaine manche
            activePlayer = activePlayer === 1 ? 2 : 1;
            setupTransition();
            document.getElementById('transition-title').innerText = "Temps écoulé ! Score : " + roundScore;
        }

        // --- ECOUTE RESEAU ---
        socket.on('actionRecue', (data) => {
            if (data.type === 'startTimer') {
                if(data.deck) wordsDeck = data.deck; // Synchro du deck
                triggerTimerLocal();
            }
            if (data.type === 'updateScore') {
                roundScore = data.score;
                document.getElementById('round-score').innerText = roundScore;
            }
        });

        // --- CLAVIER ---
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            
            if (gameState === 'transition' && key === ' ') {
                if (!isOnline || myRole === activePlayer) {
                    e.preventDefault();
                    startRoundTimer();
                }
            }

            if (gameState === 'playing' && (!isOnline || myRole === activePlayer)) {
                if (key === ' ') { e.preventDefault(); handleSuccess(); }
                if (key === 'p') { handlePass(); }
            }
        });
    </script>
</body>
</html>