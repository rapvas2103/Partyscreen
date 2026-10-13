<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qui de nous deux ? - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');
        
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(30, 30, 40, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3); }
        .btn-neutral { background: linear-gradient(45deg, #8b5cf6, #3b82f6); transition: transform 0.2s, box-shadow 0.2s; }
        .btn-neutral:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.4); }

        /* Touches silencieuses et sans animation */
        .key-btn { background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 12px; text-align: center; flex: 1; }
        .key-cap { display: inline-block; background: #2d3748; color: #fff; border-radius: 6px; padding: 4px 12px; font-size: 1.2rem; font-weight: 800; border-bottom: 4px solid #1a202c; margin-bottom: 8px; font-family: monospace; }
        
        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="text-6xl mb-4 text-purple-400"><i class="fa-solid fa-scale-balanced"></i></div>
        <h1 class="text-4xl font-bold mb-2">Qui de nous deux ?</h1>
        <p class="text-gray-300 text-sm mb-8">Désignez le coupable en secret.</p>

        <div class="mb-6 p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-gray-300 mb-3"><i class="fa-solid fa-desktop"></i> Sur le même écran</h3>
            <button onclick="startLocal()" class="btn-neutral w-full py-3 rounded-xl font-bold text-white">Lancer en Local</button>
        </div>

        <div class="p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-cyan-300 mb-3"><i class="fa-solid fa-globe"></i> En ligne avec un ami</h3>
            <input type="text" id="room-code" placeholder="Code du salon (ex: 1234)" class="w-full bg-black/40 border-2 border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 mb-3 text-center tracking-widest font-bold text-xl uppercase">
            
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 bg-cyan-600 hover:bg-cyan-500 py-3 rounded-xl font-bold text-white transition text-sm">Créer (J1)</button>
                <button onclick="joinRoom()" class="flex-1 bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold text-white transition text-sm">Rejoindre (J2)</button>
            </div>
            <p class="text-xs text-gray-400 mt-4 text-left"><i class="fa-solid fa-circle-info"></i> Le Joueur 1 (A/Z) contrôle l'avancée de la partie avec Espace.</p>
        </div>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="w-full max-w-4xl hidden flex-col items-center mt-8">
        
        <div class="bg-black/50 border border-white/20 px-6 py-2 rounded-full flex items-center gap-4 shadow-xl mb-6">
            <span id="mode-badge" class="text-xs font-bold px-3 py-1 bg-white/20 rounded-full"></span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-star text-yellow-400"></i>
                <span id="score-display" class="font-bold text-white text-lg">0 Accord</span>
            </div>
        </div>

        <!-- Question -->
        <div class="glass-panel w-full p-8 rounded-3xl flex items-center justify-center text-center min-h-[200px] mb-8 border-purple-500/30 border-2">
            <h2 id="question-text" class="text-3xl md:text-4xl font-black text-white leading-tight">En attente du Joueur 1...</h2>
        </div>

        <!-- Claviers -->
        <div class="flex flex-col md:flex-row gap-6 w-full max-w-2xl mx-auto">
            <div id="p1-card" class="flex-1 bg-white/5 p-4 rounded-2xl border border-white/10">
                <h3 class="text-gray-300 font-bold mb-4 text-center">Joueur 1 (Toi)</h3>
                <div class="flex gap-3">
                    <div class="key-btn border-b-4 border-b-purple-500/50"><div class="key-cap text-purple-400 border-b-purple-700">A</div><div class="text-xs font-bold">Voter J1</div></div>
                    <div class="key-btn border-b-4 border-b-blue-500/50"><div class="key-cap text-blue-400 border-b-blue-700">Z</div><div class="text-xs font-bold">Voter J2</div></div>
                </div>
                <div id="status-p1" class="mt-4 font-semibold text-gray-500 h-6 text-center">Réfléchit...</div>
            </div>

            <div id="p2-card" class="flex-1 bg-white/5 p-4 rounded-2xl border border-white/10">
                <h3 class="text-gray-300 font-bold mb-4 text-center">Joueur 2 (L'autre)</h3>
                <div class="flex gap-3">
                    <div class="key-btn border-b-4 border-b-purple-500/50"><div class="key-cap text-purple-400 border-b-purple-700">L</div><div class="text-xs font-bold">Voter J1</div></div>
                    <div class="key-btn border-b-4 border-b-blue-500/50"><div class="key-cap text-blue-400 border-b-blue-700">M</div><div class="text-xs font-bold">Voter J2</div></div>
                </div>
                <div id="status-p2" class="mt-4 font-semibold text-gray-500 h-6 text-center">Réfléchit...</div>
            </div>
        </div>

        <div id="reveal-text" class="mt-8 text-xl font-bold text-white/50 hidden animate-pulse">
            Le Joueur 1 appuie sur ESPACE pour révéler
        </div>
    </div>

    <!-- ECRAN DE RESULTAT -->
    <div id="result-screen" class="glass-panel p-8 rounded-3xl w-full max-w-2xl hidden text-center mt-8">
        <h2 id="result-title" class="text-4xl font-black mb-4"></h2>
        
        <div class="flex gap-4 justify-center items-end mb-8 h-40">
            <div id="bar-p1" class="w-32 bg-gradient-to-t from-purple-900 to-purple-500 rounded-t-xl transition-all duration-700 flex flex-col justify-end pb-4 border-2 border-purple-400/50" style="height: 0%">
                <span class="font-black text-2xl" id="count-p1">0</span>
                <span class="text-sm font-bold opacity-80">Votes J1</span>
            </div>
            <div id="bar-p2" class="w-32 bg-gradient-to-t from-blue-900 to-blue-500 rounded-t-xl transition-all duration-700 flex flex-col justify-end pb-4 border-2 border-blue-400/50" style="height: 0%">
                <span class="font-black text-2xl" id="count-p2">0</span>
                <span class="text-sm font-bold opacity-80">Votes J2</span>
            </div>
        </div>

        <div class="bg-black/30 border border-white/10 p-4 rounded-xl flex justify-around mb-8 text-sm text-gray-400">
            <div>Joueur 1 a voté : <strong id="detail-v1" class="text-white">...</strong></div>
            <div>Joueur 2 a voté : <strong id="detail-v2" class="text-white">...</strong></div>
        </div>

        <div id="next-text" class="text-gray-400 font-bold animate-pulse">Le Joueur 1 appuie sur ESPACE pour continuer</div>
    </div>

    <script>
        const socket = io('http://localhost:3000');
        
        let isOnline = false;
        let myRole = 0; // 0 = Local, 1 = J1, 2 = J2
        let currentRoom = "";

        const allQuestions = [
            // -- Les petits clins d'œil --
            "Qui est le plus susceptible de ressortir d'un magasin Action avec plein de choses qui n'étaient pas prévues ?",
            "Qui a le plus tendance à analyser psychologiquement le comportement des autres ?",
            "Qui gère le mieux le shaker pour préparer des cocktails le week-end ?",
            "Qui rage le plus fort après une défaite sur Rocket League ou un autre jeu vidéo ?",
            "Qui est le plus à l'aise pour mettre les mains dans le moteur et bricoler une voiture ?",
            "Qui est le meilleur capitaine si on vous confiait la barre d'un bateau ?",
            "Qui pourrait passer des heures à configurer un serveur informatique ou bidouiller des lignes de code ?",
            "Qui est le meilleur copilote pour gérer le GPS et la musique pendant un long road trip ?",
            "Qui est le plus maniaque quand il s'agit de passer l'aspirateur dans l'appartement ?",
            
            // -- Vie de couple & Quotidien --
            "Qui est le plus susceptible de s'endormir en premier devant une série ?",
            "Qui met le plus de temps à se préparer le matin ?",
            "Qui est le plus râleur/râleuse le matin au réveil ?",
            "Qui est le plus bordélique dans la chambre ?",
            "Qui finit toujours les restes de l'autre au restaurant ?",
            "Qui a toujours faim, même une heure après avoir mangé ?",
            "Qui est le plus susceptible d'oublier de répondre à un SMS pendant 24h ?",
            "Qui est le plus dépensier ?",
            "Qui est le plus radin ?",
            "Qui est le premier à s'excuser après une dispute ?",
            "Qui est le plus rancunier ?",
            "Qui a les goûts musicaux les plus inavouables ?",
            "Qui est le plus susceptible de perdre ses clés ou son téléphone ?",
            "Qui a la pire mémoire pour les dates importantes ?",
            "Qui est le plus frileux et pique toute la couette la nuit ?",
            "Qui est le plus susceptible de lancer une nouvelle série et de la regarder sans l'autre ?",
            "Qui est le/la plus têtu(e) lors d'un débat politique ou de société ?",
            "Qui propose le plus souvent des idées de sorties le week-end ?",
            "Qui a le plus de mal à choisir ce qu'on mange le soir ?",
            "Qui fait les pires blagues (et en rigole tout seul) ?",
            "Qui est le plus susceptible de parler tout seul ?",
            "Qui ronfle ou parle le plus en dormant ?",
            "Qui est le/la plus jaloux/jalouse ?",
            "Qui a le plus de patience dans la vie de tous les jours ?",
            "Qui est le plus susceptible de s'énerver contre un objet inanimé (qui ne marche pas) ?",
            "Qui passe le plus de temps aux toilettes avec son téléphone ?",
            "Qui est le plus doué pour faire des surprises ?",
            "Qui est le plus romantique ?",
            "Qui est le plus à l'aise pour parler en public ?",
            "Qui est le plus stressé de nature ?",
            "Qui est le plus maniaque sur la propreté de la vaisselle ?",
            
            // -- Situations improbables & Survie --
            "Qui mourrait en premier dans un film d'horreur ?",
            "Qui survivrait le plus longtemps lors d'une apocalypse zombie ?",
            "Qui est le plus susceptible de pleurer devant un film pas du tout triste ?",
            "Qui serait capable de manger la même chose tous les jours pendant un an ?",
            "Qui pourrait partir vivre au milieu de la forêt loin de tout ?",
            "Qui serait le pire complice pour cacher un corps ou faire un cambriolage ?",
            "Qui a le plus de chances de finir en prison par accident ?",
            "Qui est le plus susceptible de devenir ami avec un inconnu dans la rue ?",
            "Qui serait le meilleur acteur/actrice pour mentir à la police ?",
            "Qui est le plus susceptible d'adopter un animal trouvé dans la rue sur un coup de tête ?",
            "Qui a le plus de chances de devenir célèbre un jour ?",
            "Qui est le plus susceptible de tout plaquer pour ouvrir un bar sur la plage ?",
            "Qui craquerait en premier dans l'émission Koh-Lanta ?",
            "Qui gagnerait un combat à mains nues entre vous deux ?",
            
            // -- Honnêteté & Mauvaise foi --
            "Qui est le plus doué pour mentir ?",
            "Qui est le plus mauvais perdant aux jeux de société ?",
            "Qui est le plus susceptible de tricher pour gagner ?",
            "Qui est le plus hypocrite face à quelqu'un qu'il n'aime pas ?",
            "Qui a toujours raison (même quand il/elle a tort) ?",
            "Qui est le plus susceptible de simuler d'avoir compris quelque chose pour ne pas avoir l'air bête ?",
            "Qui juge le plus les gens dans la rue ou à la télé ?",
            "Qui est le plus susceptible de faire semblant de ne pas avoir vu un appel entrant ?",
            "Qui est le plus commère (et adore les potins) ?",
            "Qui est le plus susceptible de fouiller dans le téléphone de l'autre ?",
            
            // -- Travail, Loisirs & Social --
            "Qui a le plus de chance de s'endormir au travail ou en cours ?",
            "Qui est le plus accro aux réseaux sociaux (TikTok, Insta...) ?",
            "Qui est le plus susceptible de scroller sur son téléphone alors que l'autre parle ?",
            "Qui est le plus doué pour cuisiner un repas gastronomique improvisé avec trois fois rien ?",
            "Qui fait cramer le plus souvent la nourriture au four ?",
            "Qui conduit le plus mal (ou freine le plus au dernier moment) ?",
            "Qui râle le plus dans les bouchons ?",
            "Qui est le plus poli avec les serveurs/vendeurs ?",
            "Qui est le plus à l'aise pour négocier un prix dans un magasin ?",
            "Qui serait capable de dépenser la moitié de son salaire dans une seule passion ?",
            "Qui a eu les pires coupes de cheveux dans son enfance ?",
            "Qui s'habille le mieux au quotidien ?",
            "Qui met le plus de temps à choisir sa tenue avant de sortir ?",
            "Qui est le plus susceptible d'annuler une soirée à la dernière minute pour rester dans le canapé ?",
            "Qui est le plus sportif/sportive de base ?",
            "Qui abandonne un régime ou de bonnes résolutions le plus rapidement ?",
            "Qui boit le moins d'eau dans la journée ?",
            "Qui a la pire tolérance à l'alcool ou à la fatigue ?",
            "Qui est le plus tactile et câlin ?",
            "Qui a le plus d'esprit de contradiction juste pour le plaisir de débattre ?",
            "Qui pose le plus de questions pendant un film parce qu'il ne suit rien ?",
            "Qui a le rire le plus communicatif (ou le plus bruyant) ?",
            "Qui est le plus maladroit avec un verre plein dans les mains ?",
            "Qui est le plus peureux face aux insectes (araignées, guêpes) ?",
            "Qui a le plus tendance à dramatiser quand il/elle est malade (le fameux 'rhume mortel') ?",
            "Qui passe le plus de temps sous la douche ?",
            "Qui chante le plus faux (et le plus fort) dans la voiture ?",
            "Qui est le plus susceptible de se perdre dans un magasin alors qu'il cherchait juste la sortie ?",
            "Qui a le plus d'imagination ?",
            "Qui est le plus pragmatique et organisé ?",
            "Qui est le plus superstitieux ?",
            "Qui est le plus perfectionniste dans tout ce qu'il/elle entreprend ?",
            "Qui est le plus flemmard quand il s'agit de sortir les poubelles ?",
            "Qui est le plus susceptible de rire au pire moment possible (ex: un enterrement) ?",
            "Qui a été (ou est toujours) le plus rebelle avec l'autorité ?",
            "Qui sait le mieux réconforter l'autre quand ça ne va pas ?"
        ];

        let questionsDeck = [...allQuestions].sort(() => Math.random() - 0.5);
        let matchCount = 0;
        let currentQuestion = "";
        let voteP1 = null, voteP2 = null; 
        let gameState = 'setup';

        function startLocal() {
            isOnline = false;
            myRole = 0;
            document.getElementById('mode-badge').innerText = "MODE LOCAL";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-white/20 text-white rounded-full";
            document.getElementById('reveal-text').innerText = "Appuyez sur ESPACE pour révéler";
            document.getElementById('next-text').innerText = "Appuyez sur ESPACE pour continuer";
            transitionToGame();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return alert("Saisis un code de salon !");
            isOnline = true;
            myRole = 1;
            currentRoom = code;
            socket.emit('rejoindreSalon', code);
            
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J1)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-cyan-500/30 text-cyan-300 rounded-full border border-cyan-500/50";
            transitionToGame();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return alert("Saisis un code de salon !");
            isOnline = true;
            myRole = 2;
            currentRoom = code;
            socket.emit('rejoindreSalon', code);
            
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J2)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-blue-500/30 text-blue-300 rounded-full border border-blue-500/50";
            
            document.getElementById('reveal-text').innerText = "En attente du Joueur 1 pour révéler...";
            document.getElementById('next-text').innerText = "En attente du Joueur 1 pour continuer...";
            transitionToGame();
        }

        function transitionToGame() {
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            setupRound();
        }

        function setupRound() {
            if (isOnline && myRole === 2) {
                gameState = 'voting';
                return; 
            }

            if(questionsDeck.length === 0) questionsDeck = [...allQuestions].sort(() => Math.random() - 0.5);
            currentQuestion = questionsDeck.pop();
            
            updateUIWithQuestion(currentQuestion);
            gameState = 'voting';

            if (isOnline && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'newRound', question: currentQuestion });
            }
        }

        function updateUIWithQuestion(q) {
            document.getElementById('question-text').innerText = q;
        }

        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            
            if (gameState === 'voting' || gameState === 'ready') {
                if (!isOnline || myRole === 1) {
                    if (key === 'a' || key === 'q') handleVote(1, 1);
                    if (key === 'z' || key === 'w') handleVote(1, 2);
                }
                if (!isOnline || myRole === 2) {
                    if (key === 'l') handleVote(2, 1);
                    if (key === 'm' || key === ';') handleVote(2, 2);
                }
            }

            if (key === ' ') {
                if (gameState === 'ready') {
                    e.preventDefault();
                    if (!isOnline || myRole === 1) {
                        if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'reveal' });
                        reveal();
                    }
                } else if (gameState === 'result') {
                    e.preventDefault();
                    if (!isOnline || myRole === 1) {
                        if (isOnline) socket.emit('actionJeu', { room: currentRoom, type: 'next' });
                        nextRound();
                    }
                }
            }
        });

        function handleVote(playerNum, votedFor) {
            castVoteLocal(playerNum, votedFor);
            if (isOnline) {
                socket.emit('actionJeu', { room: currentRoom, type: 'vote', player: playerNum, vote: votedFor });
            }
        }

        function castVoteLocal(playerNum, votedFor) {
            if(playerNum === 1) {
                voteP1 = votedFor;
                document.getElementById('status-p1').innerText = "A voté ! 🔒";
                document.getElementById('status-p1').className = "mt-4 font-bold text-green-400 h-6 text-center";
                document.getElementById('p1-card').classList.replace('bg-white/5', 'bg-white/10');
            } else {
                voteP2 = votedFor;
                document.getElementById('status-p2').innerText = "A voté ! 🔒";
                document.getElementById('status-p2').className = "mt-4 font-bold text-green-400 h-6 text-center";
                document.getElementById('p2-card').classList.replace('bg-white/5', 'bg-white/10');
            }

            if(voteP1 !== null && voteP2 !== null) {
                gameState = 'ready';
                document.getElementById('reveal-text').classList.remove('hidden');
            }
        }

        socket.on('actionRecue', (data) => {
            if (data.type === 'newRound') {
                currentQuestion = data.question;
                updateUIWithQuestion(currentQuestion);
                gameState = 'voting';
            }
            if (data.type === 'vote') castVoteLocal(data.player, data.vote);
            if (data.type === 'reveal') reveal();
            if (data.type === 'next') nextRound();
        });

        function reveal() {
            gameState = 'result';
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('result-screen').classList.remove('hidden');

            let p1Votes = (voteP1 === 1 ? 1 : 0) + (voteP2 === 1 ? 1 : 0);
            let p2Votes = (voteP1 === 2 ? 1 : 0) + (voteP2 === 2 ? 1 : 0);

            document.getElementById('count-p1').innerText = p1Votes;
            document.getElementById('count-p2').innerText = p2Votes;

            // Animation des barres de vote
            setTimeout(() => {
                document.getElementById('bar-p1').style.height = p1Votes > 0 ? (p1Votes === 2 ? '100%' : '60%') : '20%';
                document.getElementById('bar-p2').style.height = p2Votes > 0 ? (p2Votes === 2 ? '100%' : '60%') : '20%';
            }, 50);

            document.getElementById('detail-v1').innerText = voteP1 === 1 ? 'Lui-même' : 'Joueur 2';
            document.getElementById('detail-v2').innerText = voteP2 === 2 ? 'Lui-même' : 'Joueur 1';

            if (voteP1 === voteP2) {
                matchCount++;
                document.getElementById('score-display').innerText = `${matchCount} Accord${matchCount > 1 ? 's' : ''}`;
                document.getElementById('result-title').innerText = "Verdict Unanime !";
                document.getElementById('result-title').className = "text-4xl font-black mb-4 text-green-400";
            } else {
                document.getElementById('result-title').innerText = "Désaccord...";
                document.getElementById('result-title').className = "text-4xl font-black mb-4 text-red-400";
            }
        }

        function nextRound() {
            voteP1 = null; voteP2 = null;
            document.getElementById('status-p1').innerText = "Réfléchit...";
            document.getElementById('status-p1').className = "mt-4 font-semibold text-gray-500 h-6 text-center";
            document.getElementById('p1-card').classList.replace('bg-white/10', 'bg-white/5');
            document.getElementById('status-p2').innerText = "Réfléchit...";
            document.getElementById('status-p2').className = "mt-4 font-semibold text-gray-500 h-6 text-center";
            document.getElementById('p2-card').classList.replace('bg-white/10', 'bg-white/5');
            
            document.getElementById('bar-p1').style.height = '0%';
            document.getElementById('bar-p2').style.height = '0%';
            document.getElementById('reveal-text').classList.add('hidden');
            
            setupRound();

            document.getElementById('result-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
        }
    </script>
</body>
</html>