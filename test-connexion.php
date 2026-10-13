<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Connexion - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');
        
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(30, 30, 40, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3); }
        .btn-neutral { background: linear-gradient(45deg, #10b981, #3b82f6); transition: transform 0.2s, box-shadow 0.2s; }
        .btn-neutral:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.4); }
        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="text-6xl mb-4 text-emerald-400"><i class="fa-solid fa-comments"></i></div>
        <h1 class="text-4xl font-bold mb-2">Test de Connexion</h1>
        <p class="text-gray-300 text-sm mb-8">Devinez ce que l'autre va répondre.</p>

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
        
        <div class="w-full flex justify-between items-center mb-6 px-4">
            <div class="bg-black/50 border border-white/20 px-6 py-2 rounded-full flex items-center gap-4 shadow-xl">
                <span id="mode-badge" class="text-xs font-bold px-3 py-1 bg-white/20 rounded-full"></span>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-fire text-orange-500"></i>
                    <span id="score-display" class="font-bold text-white text-lg">0 Match</span>
                </div>
            </div>
        </div>

        <div class="glass-panel w-full p-8 rounded-3xl flex items-center justify-center text-center min-h-[150px] mb-8 border-emerald-500/30 border-2">
            <h2 id="question-text" class="text-2xl md:text-3xl font-black text-white leading-tight">En attente du Joueur 1...</h2>
        </div>

        <!-- Zone de saisie -->
        <div class="w-full max-w-2xl bg-white/5 p-6 rounded-2xl border border-white/10">
            <div id="input-zone">
                <h3 class="text-gray-300 font-bold mb-4 text-center">Ta réponse (sois précis !)</h3>
                <input type="text" id="my-answer" class="w-full bg-black/40 border-2 border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-emerald-400 mb-4 text-center text-xl font-bold" placeholder="Écris ta réponse ici...">
                <button id="btn-submit" onclick="submitAnswer()" class="btn-neutral w-full py-4 rounded-xl font-bold text-xl text-white shadow-lg">
                    Valider ma réponse
                </button>
            </div>
            
            <div id="wait-zone" class="hidden text-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-4xl text-emerald-400 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-300">Réponse verrouillée !</h3>
                <p class="text-gray-400 mt-2">En attente de l'autre joueur...</p>
            </div>
        </div>
    </div>

    <!-- ECRAN DE RESULTAT -->
    <div id="result-screen" class="glass-panel p-8 rounded-3xl w-full max-w-3xl hidden text-center mt-8 relative overflow-hidden">
        <h2 class="text-3xl font-black mb-8 text-white">Résultats !</h2>
        
        <div class="flex flex-col md:flex-row gap-6 mb-8 relative z-10">
            <div class="flex-1 p-6 rounded-2xl bg-black/40 border-2 border-orange-500/50">
                <span class="text-sm font-bold text-orange-400 uppercase tracking-widest mb-2 block">Joueur 1</span>
                <span id="res-ans-1" class="text-2xl font-bold text-white break-words"></span>
            </div>
            <div class="flex items-center justify-center font-bold text-2xl text-white/30">VS</div>
            <div class="flex-1 p-6 rounded-2xl bg-black/40 border-2 border-blue-500/50">
                <span class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-2 block">Joueur 2</span>
                <span id="res-ans-2" class="text-2xl font-bold text-white break-words"></span>
            </div>
        </div>

        <div id="judge-zone" class="bg-white/5 p-6 rounded-2xl border border-white/10 relative z-10">
            <h3 class="text-xl font-bold mb-4 text-gray-200">Alors, est-ce que ça valide ?</h3>
            <div class="flex gap-4">
                <button onclick="judge(true)" class="flex-1 bg-green-600 hover:bg-green-500 py-4 rounded-xl font-black text-white text-xl transition"><i class="fa-solid fa-check"></i> C'est un Match !</button>
                <button onclick="judge(false)" class="flex-1 bg-red-600 hover:bg-red-500 py-4 rounded-xl font-black text-white text-xl transition"><i class="fa-solid fa-xmark"></i> Faux</button>
            </div>
            <p id="judge-info" class="text-xs text-gray-400 mt-3 hidden">Seul le Joueur 1 peut valider le match.</p>
        </div>
    </div>

    <script>
        const socket = io('https://partyscreen-server.onrender.com');
        
        let isOnline = false;
        let myRole = 0; 
        let currentRoom = "";
        
        let matchCount = 0;
        let ansP1 = null;
        let ansP2 = null;
        let currentQuestion = "";

        // Banque de questions (Tu peux en rajouter autant que tu veux)
        const allQuestions = [
            "Quel est mon plat préféré ?",
            "Quel est le cocktail que je maîtrise le mieux au shaker ?",
            "Dans quel magasin je pourrais dépenser mon salaire sans m'en rendre compte ?",
            "Quel est mon jeu vidéo préféré de tous les temps ?",
            "Quel est mon grade maximum atteint sur Rocket League ?",
            "Si je dois réparer ma voiture (Clio ou autre), quelle est la première chose que je vérifie ?",
            "Quelle est la série que j'ai regardée le plus de fois ?",
            "Si on m'offre un serveur sous Linux, qu'est-ce que j'installe dessus en premier ?",
            "Quel est le surnom ridicule qu'on m'a déjà donné ?",
            "Si j'ai le permis bateau, vers quelle destination je navigue en premier ?",
            "Quel est le truc qui m'énerve le plus au volant ?",
            "Quel est le sujet de discussion où je suis le plus têtu ?",
            "Quelle est mon application mobile la plus utilisée ?",
            "Si je devais faire un autre métier, ce serait quoi ?",
            "Quel est mon plus gros défaut au quotidien ?"
        ];
        let questionsDeck = [];

        function startLocal() {
            isOnline = false; myRole = 1;
            document.getElementById('mode-badge').innerText = "MODE LOCAL";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-white/20 text-white rounded-full";
            transitionToGame();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 1; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J1)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-cyan-500/30 text-cyan-300 rounded-full";
            transitionToGame();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 2; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            socket.emit('actionJeu', { room: currentRoom, type: 'j2_joined' });
            
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J2)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-blue-500/30 text-blue-300 rounded-full";
            document.getElementById('judge-info').classList.remove('hidden');
            transitionToGame();
        }

        function transitionToGame() {
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            if(myRole === 1 || !isOnline) setupRound();
        }

        function setupRound() {
            ansP1 = null; ansP2 = null;
            document.getElementById('my-answer').value = "";
            document.getElementById('input-zone').classList.remove('hidden');
            document.getElementById('wait-zone').classList.add('hidden');

            if (myRole === 1 || !isOnline) {
                if (questionsDeck.length === 0) questionsDeck = [...allQuestions].sort(() => Math.random() - 0.5);
                currentQuestion = questionsDeck.pop();
                document.getElementById('question-text').innerText = currentQuestion;
                
                if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'newRound', q: currentQuestion });
            }
        }

        // Permet d'appuyer sur Entrée pour valider
        document.getElementById("my-answer").addEventListener("keypress", function(event) {
            if (event.key === "Enter") submitAnswer();
        });

        function submitAnswer() {
            const ans = document.getElementById('my-answer').value.trim();
            if(ans === "") return;

            document.getElementById('input-zone').classList.add('hidden');
            document.getElementById('wait-zone').classList.remove('hidden');

            if (isOnline) {
                socket.emit('actionJeu', { room: currentRoom, type: 'submit', player: myRole, answer: ans });
            } else {
                // En mode local, on simule que J1 tape puis J2 tape.
                if (ansP1 === null) {
                    ansP1 = ans;
                    document.getElementById('my-answer').value = "";
                    document.getElementById('input-zone').classList.remove('hidden');
                    document.getElementById('wait-zone').classList.add('hidden');
                    document.getElementById('input-zone').querySelector('h3').innerText = "Joueur 2, à toi (sois précis !)";
                } else {
                    ansP2 = ans;
                    checkBothSubmitted();
                }
            }
        }

        function checkBothSubmitted() {
            if (ansP1 !== null && ansP2 !== null) {
                reveal();
            }
        }

        function reveal() {
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('result-screen').classList.remove('hidden');

            document.getElementById('res-ans-1').innerText = ansP1;
            document.getElementById('res-ans-2').innerText = ansP2;

            if (isOnline && myRole === 2) {
                // J2 ne peut pas cliquer sur les boutons de jugement
                const buttons = document.getElementById('judge-zone').querySelectorAll('button');
                buttons.forEach(b => b.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none'));
            } else {
                const buttons = document.getElementById('judge-zone').querySelectorAll('button');
                buttons.forEach(b => b.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none'));
            }
        }

        function judge(isMatch) {
            if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'judge', match: isMatch });
            applyJudgment(isMatch);
        }

        function applyJudgment(isMatch) {
            if (isMatch) {
                matchCount++;
                document.getElementById('score-display').innerText = `${matchCount} Match${matchCount > 1 ? 's' : ''}`;
            }
            
            document.getElementById('result-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            
            if (myRole === 1 || !isOnline) setupRound();
        }

        // --- ECOUTE RESEAU ---
        socket.on('actionRecue', (data) => {
            if (data.type === 'j2_joined' && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'newRound', q: currentQuestion });
            }
            if (data.type === 'newRound') {
                currentQuestion = data.q;
                document.getElementById('question-text').innerText = currentQuestion;
                document.getElementById('my-answer').value = "";
                document.getElementById('input-zone').classList.remove('hidden');
                document.getElementById('wait-zone').classList.add('hidden');
                ansP1 = null; ansP2 = null;
            }
            if (data.type === 'submit') {
                if (data.player === 1) ansP1 = data.answer;
                if (data.player === 2) ansP2 = data.answer;
                if (myRole === 1) checkBothSubmitted();
                if (ansP1 !== null && ansP2 !== null) reveal();
            }
            if (data.type === 'judge') {
                applyJudgment(data.match);
            }
        });
    </script>
</body>
</html>