<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convergence - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;900&display=swap');
        
        body { font-family: 'Space Grotesk', sans-serif; background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3); }
        .btn-start { background: linear-gradient(45deg, #6ee7b7, #3b82f6); transition: all 0.2s; color: #0f0c29; }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4); }

        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }

        .text-glow { text-shadow: 0 0 20px rgba(110, 231, 183, 0.6); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="text-6xl mb-4 text-emerald-300"><i class="fa-solid fa-arrows-to-circle"></i></div>
        <h1 class="text-4xl font-black mb-2">Convergence</h1>
        <p class="text-gray-300 text-sm mb-8">Trouvez le lien parfait jusqu'à penser au même mot.</p>

        <div class="mb-6 p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-gray-300 mb-3"><i class="fa-solid fa-desktop"></i> Sur le même écran</h3>
            <button onclick="startLocal()" class="btn-start w-full py-3 rounded-xl font-bold">Lancer en Local</button>
        </div>

        <div class="p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-cyan-300 mb-3"><i class="fa-solid fa-globe"></i> En ligne avec un ami</h3>
            <input type="text" id="room-code" placeholder="Code du salon" class="w-full bg-black/40 border-2 border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 mb-3 text-center tracking-widest font-bold text-xl uppercase">
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 bg-cyan-600 hover:bg-cyan-500 py-3 rounded-xl font-bold text-white transition text-sm">Créer (J1)</button>
                <button onclick="joinRoom()" class="flex-1 bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold text-white transition text-sm">Rejoindre (J2)</button>
            </div>
        </div>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="w-full max-w-4xl hidden flex-col items-center mt-8">
        
        <div class="bg-black/50 border border-white/20 px-6 py-2 rounded-full flex items-center gap-4 shadow-xl mb-8">
            <span id="mode-badge" class="text-xs font-bold px-3 py-1 bg-white/20 rounded-full"></span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-purple-400"></i>
                <span id="attempt-display" class="font-bold text-white text-lg">Essai N°1</span>
            </div>
        </div>

        <div class="text-center mb-6 w-full">
            <p class="text-gray-400 uppercase tracking-widest text-sm font-bold mb-4">Trouvez un mot qui relie :</p>
            <div class="flex flex-col md:flex-row justify-center items-center gap-4 md:gap-8 w-full">
                <div class="glass-panel py-6 px-10 rounded-2xl border-orange-500/30 border-2 min-w-[250px]">
                    <span id="word-left" class="text-3xl md:text-4xl font-black text-white">...</span>
                </div>
                <div class="text-2xl font-black text-white/30"><i class="fa-solid fa-link"></i></div>
                <div class="glass-panel py-6 px-10 rounded-2xl border-blue-500/30 border-2 min-w-[250px]">
                    <span id="word-right" class="text-3xl md:text-4xl font-black text-white">...</span>
                </div>
            </div>
        </div>

        <!-- Zone de saisie -->
        <div class="w-full max-w-xl bg-white/5 p-8 rounded-3xl border border-white/10 mt-4">
            <div id="input-zone">
                <h3 class="text-emerald-300 font-bold mb-4 text-center text-xl">Quel est le point commun ?</h3>
                <input type="text" id="my-answer" class="w-full bg-black/50 border-2 border-white/20 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-emerald-400 mb-4 text-center text-2xl font-bold" placeholder="Écris 1 seul mot...">
                <button onclick="submitWord()" class="btn-start w-full py-4 rounded-xl font-black text-xl shadow-lg">
                    Envoyer
                </button>
            </div>
            
            <div id="wait-zone" class="hidden text-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-5xl text-emerald-400 mb-6 block mx-auto"></i>
                <h3 class="text-2xl font-bold text-white">Mot verrouillé !</h3>
                <p class="text-gray-400 mt-2">L'autre joueur réfléchit encore...</p>
            </div>
        </div>
    </div>

    <!-- ECRAN DE RESULTAT (INTERMEDIAIRE OU FINAL) -->
    <div id="result-screen" class="glass-panel p-8 rounded-3xl w-full max-w-3xl hidden text-center mt-8 relative">
        <div id="result-icon" class="text-6xl mb-4"></div>
        <h2 id="result-title" class="text-4xl font-black mb-8 text-white"></h2>
        
        <div class="flex flex-col md:flex-row gap-6 mb-12">
            <div class="flex-1 p-6 rounded-2xl bg-black/40 border-2 border-orange-500/50">
                <span class="text-sm font-bold text-orange-400 uppercase tracking-widest mb-2 block">Joueur 1</span>
                <span id="res-ans-1" class="text-3xl font-black text-white"></span>
            </div>
            <div class="flex-1 p-6 rounded-2xl bg-black/40 border-2 border-blue-500/50">
                <span class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-2 block">Joueur 2</span>
                <span id="res-ans-2" class="text-3xl font-black text-white"></span>
            </div>
        </div>

        <div id="action-zone">
            <!-- Rempli dynamiquement : bouton manche suivante ou rejouer -->
        </div>
    </div>

    <script>
        const socket = io('https://partyscreen-server.onrender.com');
        
        let isOnline = false;
        let myRole = 0; 
        let currentRoom = "";
        
        let attempts = 1;
        let wordP1 = null;
        let wordP2 = null;
        
        // Mots de départ
        const startingPairs = [
            ["Feu", "Eau"], ["Chien", "Chat"], ["Espace", "Océan"], 
            ["Jour", "Nuit"], ["Cinéma", "Livre"], ["Voiture", "Avion"],
            ["Montagne", "Plage"], ["Ordinateur", "Nature"], ["Musique", "Silence"]
        ];
        
        let currentPair = ["", ""];

        function startLocal() {
            isOnline = false; myRole = 1;
            document.getElementById('mode-badge').innerText = "MODE LOCAL";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-white/20 text-white rounded-full";
            initGame();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 1; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J1)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-cyan-500/30 text-cyan-300 rounded-full";
            initGame();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 2; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            socket.emit('actionJeu', { room: currentRoom, type: 'j2_joined' });
            
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J2)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-blue-500/30 text-blue-300 rounded-full";
            
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            
            document.getElementById('input-zone').classList.add('hidden');
            document.getElementById('wait-zone').classList.remove('hidden');
            document.getElementById('wait-zone').querySelector('h3').innerText = "Connexion...";
            document.getElementById('wait-zone').querySelector('p').innerText = "En attente des mots du Joueur 1.";
        }

        function initGame() {
            attempts = 1;
            let randomPair = startingPairs[Math.floor(Math.random() * startingPairs.length)];
            currentPair = [randomPair[0], randomPair[1]];
            
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('result-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            
            setupRound();
            if (isOnline && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'syncRound', pair: currentPair, att: attempts });
            }
        }

        function setupRound() {
            wordP1 = null; wordP2 = null;
            document.getElementById('attempt-display').innerText = `Essai N°${attempts}`;
            document.getElementById('word-left').innerText = currentPair[0];
            document.getElementById('word-right').innerText = currentPair[1];
            
            document.getElementById('my-answer').value = "";
            document.getElementById('input-zone').classList.remove('hidden');
            document.getElementById('wait-zone').classList.add('hidden');
            document.getElementById('input-zone').querySelector('h3').innerText = (myRole === 1 && !isOnline) ? "Joueur 1, quel est le point commun ?" : "Quel est le point commun ?";
        }

        document.getElementById("my-answer").addEventListener("keypress", function(event) {
            if (event.key === "Enter") submitWord();
        });

        function submitWord() {
            const w = document.getElementById('my-answer').value.trim();
            if(w === "") return;

            document.getElementById('input-zone').classList.add('hidden');
            document.getElementById('wait-zone').classList.remove('hidden');
            document.getElementById('wait-zone').querySelector('h3').innerText = "Mot verrouillé !";
            document.getElementById('wait-zone').querySelector('p').innerText = "L'autre joueur réfléchit encore...";

            if (isOnline) {
                socket.emit('actionJeu', { room: currentRoom, type: 'submit', player: myRole, word: w });
            } else {
                if (wordP1 === null) {
                    wordP1 = w;
                    // Reset pour le Joueur 2
                    document.getElementById('my-answer').value = "";
                    document.getElementById('input-zone').classList.remove('hidden');
                    document.getElementById('wait-zone').classList.add('hidden');
                    document.getElementById('input-zone').querySelector('h3').innerText = "Joueur 2, à toi !";
                } else {
                    wordP2 = w;
                    checkBothSubmitted();
                }
            }
        }

        function checkBothSubmitted() {
            if (wordP1 !== null && wordP2 !== null) {
                reveal();
            }
        }

        function reveal() {
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('result-screen').classList.remove('hidden');

            document.getElementById('res-ans-1').innerText = wordP1;
            document.getElementById('res-ans-2').innerText = wordP2;

            // Nettoyage des chaînes pour la comparaison (minuscules, sans accents)
            const clean1 = wordP1.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            const clean2 = wordP2.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();

            if (clean1 === clean2) {
                // VICTOIRE
                document.getElementById('result-icon').innerHTML = '<i class="fa-solid fa-star text-yellow-400"></i>';
                document.getElementById('result-title').innerText = "CONVERGENCE !!!";
                document.getElementById('result-title').className = "text-5xl font-black mb-8 text-emerald-400 text-glow";
                
                document.getElementById('action-zone').innerHTML = `
                    <p class="text-gray-300 font-bold mb-6">Vous avez trouvé la même chose en ${attempts} essai(s) !</p>
                    <button onclick="initGame()" class="bg-purple-600 hover:bg-purple-500 w-full py-4 rounded-xl font-black text-xl text-white shadow-lg transition">
                        Rejouer une partie
                    </button>
                `;
                
                if(isOnline && myRole === 1) socket.emit('actionJeu', { room: currentRoom, type: 'restart' });
            } else {
                // ECHEC - MANCHE SUIVANTE
                document.getElementById('result-icon').innerHTML = '<i class="fa-solid fa-bolt text-red-500"></i>';
                document.getElementById('result-title').innerText = "Presque...";
                document.getElementById('result-title').className = "text-4xl font-black mb-8 text-white";
                
                if (!isOnline || myRole === 1) {
                    document.getElementById('action-zone').innerHTML = `
                        <button onclick="nextAttempt()" class="btn-start w-full py-4 rounded-xl font-black text-xl shadow-lg">
                            Continuer avec ces mots <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    `;
                } else {
                    document.getElementById('action-zone').innerHTML = `
                        <p class="text-gray-400 font-bold animate-pulse">En attente que le Joueur 1 lance la suite...</p>
                    `;
                }
            }
        }

        function nextAttempt() {
            attempts++;
            currentPair = [wordP1, wordP2]; // Les mots dits deviennent la nouvelle base
            
            if (isOnline && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'syncRound', pair: currentPair, att: attempts });
            }
            
            document.getElementById('result-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            
            setupRound();
        }

        // --- ECOUTE RESEAU ---
        socket.on('actionRecue', (data) => {
            if (data.type === 'j2_joined' && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'syncRound', pair: currentPair, att: attempts });
            }
            
            if (data.type === 'syncRound') {
                currentPair = data.pair;
                attempts = data.att;
                document.getElementById('result-screen').classList.add('hidden');
                document.getElementById('game-screen').classList.remove('hidden');
                document.getElementById('game-screen').classList.add('flex');
                setupRound();
            }
            
            if (data.type === 'submit') {
                if (data.player === 1) wordP1 = data.word;
                if (data.player === 2) wordP2 = data.word;
                if (myRole === 1) checkBothSubmitted(); // J1 check et déclenche le reveal via la variable locale
                if (wordP1 !== null && wordP2 !== null) reveal(); // Déclenche chez tout le monde
            }

            if (data.type === 'restart' && myRole === 2) {
                // J2 affiche qu'il attend le nouveau mot
                document.getElementById('result-screen').classList.add('hidden');
                document.getElementById('game-screen').classList.remove('hidden');
                document.getElementById('game-screen').classList.add('flex');
                document.getElementById('input-zone').classList.add('hidden');
                document.getElementById('wait-zone').classList.remove('hidden');
                document.getElementById('wait-zone').querySelector('h3').innerText = "Nouvelle partie...";
            }
        });
    </script>
</body>
</html>