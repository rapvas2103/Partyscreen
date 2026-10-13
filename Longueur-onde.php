<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Longueur d'Onde - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
        
        body { font-family: 'Outfit', sans-serif; background: linear-gradient(135deg, #1f1c2c 0%, #4b3869 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(30, 30, 40, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3); }
        .btn-neutral { background: linear-gradient(45deg, #a855f7, #ec4899); transition: transform 0.2s; }
        .btn-neutral:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(236, 72, 153, 0.4); }
        
        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }

        .spectrum-container { position: relative; width: 100%; height: 80px; background: linear-gradient(90deg, #3b82f6, #a855f7, #ef4444); border-radius: 40px; border: 4px solid rgba(255,255,255,0.2); overflow: hidden; }
        .target-zone { position: absolute; height: 100%; width: 15%; background: rgba(255, 255, 255, 0.9); box-shadow: 0 0 20px rgba(255,255,255,0.8); top: 0; transition: opacity 0.5s; display: flex; justify-content: center; align-items: center;}
        .target-center { width: 4px; height: 100%; background: #000; }
        
        input[type=range] { -webkit-appearance: none; width: 100%; position: absolute; top: 0; left: 0; height: 80px; background: transparent; margin: 0; outline: none; z-index: 10; cursor: pointer; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 90px; width: 12px; border-radius: 10px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.8); border: 2px solid #000; transition: transform 0.1s; }
        input[type=range]:active::-webkit-slider-thumb { transform: scale(1.2); background: #facc15; }
        input[type=range]:disabled::-webkit-slider-thumb { background: #9ca3af; cursor: not-allowed; }

        /* Jauge masquée instantanément sans animation */
		.target-hidden .target-zone { opacity: 0 !important; transition: none !important; }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="text-6xl mb-4 text-purple-400"><i class="fa-solid fa-wifi"></i></div>
        <h1 class="text-4xl font-bold mb-2">Longueur d'Onde</h1>
        <p class="text-gray-300 text-sm mb-8">Un mot pour deviner la cible secrète.</p>

        <div class="mb-6 p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-gray-300 mb-3"><i class="fa-solid fa-desktop"></i> Sur le même écran</h3>
            <button onclick="startLocal()" class="btn-neutral w-full py-3 rounded-xl font-bold text-white">Lancer en Local</button>
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
        
        <div class="w-full flex justify-between items-center mb-8 px-4">
            <div class="bg-black/50 border border-white/10 px-4 py-2 rounded-xl">
                <span class="text-sm text-gray-400 block leading-none mb-1">Score Total</span>
                <span id="total-score" class="font-black text-2xl text-yellow-400 leading-none">0</span>
            </div>
            <div id="role-badge" class="font-bold text-lg px-6 py-2 rounded-full animate-pulse"></div>
        </div>

        <div class="flex justify-between w-full mb-4 px-2 font-bold text-xl sm:text-2xl text-gray-200">
            <div class="text-left flex-1" id="concept-left">Chaud</div>
            <div class="text-right flex-1" id="concept-right">Froid</div>
        </div>

        <div class="spectrum-container target-hidden mb-12 shadow-2xl" id="spectrum">
            <div class="target-zone" id="target-ui"><div class="target-center"></div></div>
            <input type="range" min="0" max="100" value="50" id="slider" oninput="handleSliderMove()">
        </div>

        <div class="w-full max-w-sm">
            <button id="btn-validate" onclick="validateGuess()" class="hidden btn-neutral w-full py-4 rounded-2xl font-black text-2xl text-white shadow-lg">
                <i class="fa-solid fa-check-double"></i> VALIDER
            </button>
            
            <div id="msg-wait" class="text-center font-bold text-gray-400 p-4 border border-white/10 rounded-2xl bg-black/30 hidden"></div>
            
            <button id="btn-next" onclick="triggerNextRound()" class="hidden w-full mt-4 bg-gray-600 hover:bg-gray-500 py-4 rounded-2xl font-black text-xl text-white shadow-lg transition-colors">
                Manche suivante <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
        
        <div id="round-result" class="mt-8 text-3xl font-black text-center hidden"></div>
    </div>

    <script>
        const socket = io('http://localhost:3000');
        
        let isOnline = false;
        let myRole = 0; 
        let currentRoom = "";
        
        let activePsychic = 1; 
        let targetValue = 50; 
        let currentScore = 0;
        let gameState = 'setup'; 

        const allConcepts = [
            ["Inutile", "Indispensable"], ["Très chaud", "Très froid"], ["Film nul", "Chef-d'œuvre"],
            ["Mauvais pour la santé", "Bon pour la santé"], ["Facile à tuer", "Difficile à tuer"],
            ["Doux", "Piquant"], ["Mauvais acteur", "Bon acteur"], ["Pire cadeau", "Meilleur cadeau"],
            ["Chanson énervante", "Chanson relaxante"], ["Petit", "Immense"], ["Odeur désagréable", "Odeur agréable"],
            ["Démodé", "Tendance"], ["Plat insipide", "Plat très épicé"], ["Travail ennuyeux", "Travail passionnant"],
            ["Savoir inutile", "Savoir indispensable"], ["Voiture de papy", "Voiture de kéké"],
            ["Je n'aimerais pas être coincé avec", "J'adorerais être coincé avec"], ["Éphémère", "Éternel"]
        ];
        let conceptsDeck = [];

        function startLocal() {
            isOnline = false;
            myRole = 0;
            activePsychic = 1;
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            setupRound();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true;
            myRole = 1;
            currentRoom = code;
            activePsychic = 1;
            socket.emit('rejoindreSalon', code);
            
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            setupRound();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true;
            myRole = 2;
            currentRoom = code;
            
            socket.emit('rejoindreSalon', code);
            // LE FIX EST ICI : J2 dit à J1 qu'il vient d'arriver pour recevoir la partie
            socket.emit('actionJeu', { room: currentRoom, type: 'j2_joined' });
            
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            
            document.getElementById('msg-wait').classList.remove('hidden');
            document.getElementById('msg-wait').innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Connexion au Joueur 1...";
        }

        function setupRound() {
            gameState = 'playing';
            document.getElementById('spectrum').classList.add('target-hidden');
            document.getElementById('slider').disabled = false;
            document.getElementById('slider').value = 50;
            document.getElementById('btn-next').classList.add('hidden');
            document.getElementById('round-result').classList.add('hidden');

            const isMyTurnPsychic = (!isOnline || myRole === activePsychic);

            if (isMyTurnPsychic) {
                if (conceptsDeck.length === 0) conceptsDeck = [...allConcepts].sort(() => Math.random() - 0.5);
                const card = conceptsDeck.pop();
                targetValue = Math.floor(Math.random() * 80) + 10; 
                
                applyRoundData(card[0], card[1], targetValue);
                
                document.getElementById('spectrum').classList.remove('target-hidden');
                document.getElementById('slider').disabled = true; 
                
                document.getElementById('role-badge').innerText = "Tu es le MÉDIUM 🧠";
                document.getElementById('role-badge').className = "font-bold text-lg px-6 py-2 bg-purple-500/30 border-2 border-purple-400 text-purple-200 rounded-full animate-pulse";
                document.getElementById('btn-validate').classList.add('hidden');
                
                if (isOnline) {
                    document.getElementById('msg-wait').classList.remove('hidden');
                    document.getElementById('msg-wait').innerHTML = "Donne ton indice à l'oral et regarde <span class='text-white'>le Télépathe</span> chercher...";
                    socket.emit('actionJeu', { room: currentRoom, type: 'newRound', c1: card[0], c2: card[1], target: targetValue, psychic: activePsychic });
                } else {
                    document.getElementById('msg-wait').classList.remove('hidden');
                    document.getElementById('msg-wait').innerHTML = 'Regarde la cible, donne ton indice, puis clique ici pour la <a href="#" onclick="hideTargetLocal()" class="text-blue-400 underline">Masquer et laisser deviner</a>';
                }
            } else {
                document.getElementById('role-badge').innerText = "Tu es le TÉLÉPATHE 📡";
                document.getElementById('role-badge').className = "font-bold text-lg px-6 py-2 bg-blue-500/30 border-2 border-blue-400 text-blue-200 rounded-full animate-pulse";
                document.getElementById('btn-validate').classList.remove('hidden');
                document.getElementById('msg-wait').classList.add('hidden');
            }
        }

        function hideTargetLocal() {
            document.getElementById('spectrum').classList.add('target-hidden');
            document.getElementById('slider').disabled = false;
            document.getElementById('btn-validate').classList.remove('hidden');
            document.getElementById('msg-wait').classList.add('hidden');
            document.getElementById('role-badge').innerText = "C'est au TÉLÉPATHE 📡";
        }

        function applyRoundData(c1, c2, target) {
            document.getElementById('concept-left').innerText = c1;
            document.getElementById('concept-right').innerText = c2;
            targetValue = target;
            let pos = target - 7.5;
            document.getElementById('target-ui').style.left = pos + "%";
        }

        function handleSliderMove() {
            const val = document.getElementById('slider').value;
            if (isOnline && myRole !== activePsychic) {
                socket.emit('actionJeu', { room: currentRoom, type: 'sliderMove', value: val });
            }
        }

        function validateGuess() {
            if (gameState !== 'playing') return;
            const guess = document.getElementById('slider').value;
            
            if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'validate', guess: guess });
            reveal(guess);
        }

        function reveal(guessValue) {
            gameState = 'revealed';
            document.getElementById('spectrum').classList.remove('target-hidden');
            document.getElementById('slider').disabled = true;
            document.getElementById('slider').value = guessValue;
            document.getElementById('btn-validate').classList.add('hidden');
            document.getElementById('msg-wait').classList.add('hidden');
            document.getElementById('btn-next').classList.remove('hidden');

            const diff = Math.abs(targetValue - guessValue);
            let points = 0; let msg = ""; let color = "";

            if (diff <= 3) { points = 4; msg = "DANS LE MILLE !"; color = "text-green-400"; }
            else if (diff <= 7.5) { points = 3; msg = "EXCELLENT !"; color = "text-lime-400"; }
            else if (diff <= 15) { points = 2; msg = "PAS MAL !"; color = "text-yellow-400"; }
            else { points = 0; msg = "Aïe... complètement à côté !"; color = "text-red-400"; }

            currentScore += points;
            document.getElementById('total-score').innerText = currentScore;
            
            const resDiv = document.getElementById('round-result');
            resDiv.innerHTML = `<span class="${color}">${msg}</span> <br><span class="text-xl text-gray-300">+${points} points</span>`;
            resDiv.classList.remove('hidden');
        }

        // Appelée quand on clique sur le bouton "Manche suivante"
        function triggerNextRound() {
            document.getElementById('btn-next').classList.add('hidden'); // Empêche le double clic
            handleNextRoundLogic(false);
        }

        // Gère la logique de passage de manche
        function handleNextRoundLogic(fromNetwork) {
            activePsychic = activePsychic === 1 ? 2 : 1;
            if (isOnline && !fromNetwork) {
                socket.emit('actionJeu', { room: currentRoom, type: 'next' });
            }
            setupRound();
        }

        // --- ECOUTE RESEAU ---
        socket.on('actionRecue', (data) => {
            // FIX : Le Médium actuel reçoit le signal "J2 vient d'arriver" et lui renvoie la partie
            if (data.type === 'j2_joined' && myRole === activePsychic) {
                socket.emit('actionJeu', { 
                    room: currentRoom, 
                    type: 'newRound', 
                    c1: document.getElementById('concept-left').innerText, 
                    c2: document.getElementById('concept-right').innerText, 
                    target: targetValue,
                    psychic: activePsychic
                });
            }
            
            if (data.type === 'newRound') {
                if(data.psychic) activePsychic = data.psychic;
                applyRoundData(data.c1, data.c2, data.target);
                setupRound();
            }
            if (data.type === 'sliderMove' && myRole === activePsychic) {
                document.getElementById('slider').value = data.value;
            }
            if (data.type === 'validate') {
                reveal(data.guess);
            }
            if (data.type === 'next') {
                handleNextRoundLogic(true); // true = vient du réseau
            }
        });
    </script>
</body>
</html>