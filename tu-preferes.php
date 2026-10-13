<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Préfères ? - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Importation de Socket.io pour la connexion au serveur Node.js -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');
        
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%); color: white; min-height: 100vh; margin: 0; overflow-x: hidden; }
        .glass-panel { background: rgba(30, 30, 40, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3); }
        .choice-card { background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); }
        .btn-neutral { background: linear-gradient(45deg, #ff416c, #ff4b2b); transition: transform 0.2s, box-shadow 0.2s; }
        .btn-neutral:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(255, 65, 108, 0.4); }

        .key-btn { background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 12px; text-align: center; flex: 1; }
        .key-cap { display: inline-block; background: #2d3748; color: #fff; border-radius: 6px; padding: 4px 12px; font-size: 1.2rem; font-weight: 800; border-bottom: 4px solid #1a202c; margin-bottom: 8px; font-family: monospace; }
        
        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="flex flex-col items-center justify-center p-4">

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL ET DE CONNEXION RESEAU -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center mt-12">
        <div class="flex justify-center gap-4 text-5xl mb-6">
            <span class="text-orange-400"><i class="fa-solid fa-fire"></i></span>
            <span class="text-blue-400"><i class="fa-solid fa-droplet"></i></span>
        </div>
        <h1 class="text-4xl font-bold mb-8">Tu préfères ?</h1>
        
        <!-- Mode Local -->
        <div class="mb-6 p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-gray-300 mb-3"><i class="fa-solid fa-desktop"></i> Sur le même écran</h3>
            <button onclick="startLocal()" class="btn-neutral w-full py-3 rounded-xl font-bold text-white">Lancer en Local</button>
        </div>

        <!-- Mode En Ligne -->
        <div class="p-5 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-cyan-300 mb-3"><i class="fa-solid fa-globe"></i> En ligne avec un ami</h3>
            <input type="text" id="room-code" placeholder="Code du salon (ex: 1234)" class="w-full bg-black/40 border-2 border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-400 mb-3 text-center tracking-widest font-bold text-xl uppercase">
            
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 bg-cyan-600 hover:bg-cyan-500 py-3 rounded-xl font-bold text-white transition text-sm">Créer (J1)</button>
                <button onclick="joinRoom()" class="flex-1 bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold text-white transition text-sm">Rejoindre (J2)</button>
            </div>
            <p class="text-xs text-gray-400 mt-4 text-left"><i class="fa-solid fa-circle-info"></i> Le Joueur 1 gère l'affichage des résultats et le passage des manches avec Espace.</p>
        </div>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="w-full max-w-5xl hidden flex-col items-center mt-8">
        
        <div class="bg-black/50 border border-white/20 px-6 py-2 rounded-full flex items-center gap-4 shadow-xl mb-6">
            <span id="mode-badge" class="text-xs font-bold px-3 py-1 bg-white/20 rounded-full"></span>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-heart text-red-500"></i>
                <span id="score-display" class="font-bold text-white text-lg">0 Match</span>
            </div>
        </div>

        <!-- Les deux choix -->
        <div class="flex flex-col md:flex-row w-full gap-4 mb-8">
            <div class="flex-1 glass-panel choice-card p-6 rounded-3xl flex flex-col justify-center items-center text-center min-h-[200px] border-orange-500/30">
                <span class="text-orange-400 font-bold tracking-widest uppercase text-sm mb-3">Choix 1</span>
                <h2 id="choice-1-text" class="text-2xl font-bold text-white">En attente du Joueur 1...</h2>
            </div>
            <div class="flex items-center justify-center font-black text-4xl text-white/30 italic">OU</div>
            <div class="flex-1 glass-panel choice-card p-6 rounded-3xl flex flex-col justify-center items-center text-center min-h-[200px] border-blue-500/30">
                <span class="text-blue-400 font-bold tracking-widest uppercase text-sm mb-3">Choix 2</span>
                <h2 id="choice-2-text" class="text-2xl font-bold text-white">En attente du Joueur 1...</h2>
            </div>
        </div>

        <!-- Claviers -->
        <div class="flex flex-col sm:flex-row gap-6 w-full max-w-3xl">
            <div id="p1-card" class="flex-1 bg-white/5 p-4 rounded-2xl border border-white/10 transition-colors">
                <h3 class="text-orange-300 font-bold mb-4 text-center">Joueur 1</h3>
                <div class="flex gap-3">
                    <div class="key-btn"><div class="key-cap text-orange-400 border-b-orange-700">A</div><div class="text-xs font-semibold text-gray-400">Choix 1</div></div>
                    <div class="key-btn"><div class="key-cap text-blue-400 border-b-blue-700">Z</div><div class="text-xs font-semibold text-gray-400">Choix 2</div></div>
                </div>
                <div id="status-p1" class="mt-4 font-semibold text-gray-500 h-6 text-center">Réfléchit...</div>
            </div>

            <div id="p2-card" class="flex-1 bg-white/5 p-4 rounded-2xl border border-white/10 transition-colors">
                <h3 class="text-blue-300 font-bold mb-4 text-center">Joueur 2</h3>
                <div class="flex gap-3">
                    <div class="key-btn"><div class="key-cap text-orange-400 border-b-orange-700">L</div><div class="text-xs font-semibold text-gray-400">Choix 1</div></div>
                    <div class="key-btn"><div class="key-cap text-blue-400 border-b-blue-700">M</div><div class="text-xs font-semibold text-gray-400">Choix 2</div></div>
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
        <div id="result-icon" class="text-7xl mb-4"></div>
        <h2 id="result-title" class="text-3xl font-bold mb-2 text-white"></h2>
        <p id="result-msg" class="text-gray-300 mb-8 font-medium"></p>

        <div class="flex flex-col sm:flex-row gap-4 mb-8">
            <div class="flex-1 p-4 rounded-xl border flex flex-col justify-center items-center min-h-[120px]" id="res-box-1">
                <span class="text-sm font-bold opacity-70 mb-2">Joueur 1</span>
                <span id="res-vote-1" class="text-lg font-bold text-white leading-tight"></span>
            </div>
            <div class="flex items-center justify-center font-bold text-2xl text-white/30">
                <i id="vs-icon" class="fa-solid fa-bolt"></i>
            </div>
            <div class="flex-1 p-4 rounded-xl border flex flex-col justify-center items-center min-h-[120px]" id="res-box-2">
                <span class="text-sm font-bold opacity-70 mb-2">Joueur 2</span>
                <span id="res-vote-2" class="text-lg font-bold text-white leading-tight"></span>
            </div>
        </div>

        <div id="next-text" class="text-gray-400 font-bold mt-4 animate-pulse">Le Joueur 1 appuie sur ESPACE pour continuer</div>
    </div>

    <script>
        // Connexion au serveur Node.js local
        const socket = io('https://partyscreen-server.onrender.com');
        
        let isOnline = false;
        let myRole = 0; // 0 = Local, 1 = J1 (Hôte), 2 = J2 (Client)
        let currentRoom = "";

        // Raccourci pour éviter de remettre toutes les questions ici.
        const allQuestions = [
            // Voitures / Mécanique / Trajets
            ["Avoir une voiture magnifique mais qui se traîne à 90km/h max", "Avoir une voiture cabossée mais avec un moteur surpuissant (reprog)"],
            ["Avoir de l'essence gratuite à vie", "Avoir un mécanicien gratuit à vie pour toutes les pannes"],
            ["Faire un road trip jusqu'à Barcelone sans aucune musique", "Faire le même road trip avec la même chanson en boucle"],
            ["Avoir la clim en panne en plein été", "Avoir le chauffage bloqué à fond en plein été"],
            ["Crever un pneu sous une pluie battante", "Tomber en panne d'essence au milieu de nulle part de nuit"],
            ["Devenir pilote de F1 mais ne jamais gagner", "Gagner toutes tes courses sur Mario Kart mais personne ne le sait"],
            ["Passer le permis bateau mais ne jamais naviguer", "Naviguer illégalement toute ta vie en fuyant les gardes-côtes"],
            
            // Cocktails / Soirées
            ["Ne plus jamais pouvoir boire de cocktail de ta vie", "Devoir boire tous tes cocktails sans aucune glace"],
            ["Mettre du sel au lieu du sucre dans ton cocktail sans t'en rendre compte", "Renverser ton shaker entier sur tes vêtements avant de sortir"],
            ["Ne pouvoir boire que des choses très sucrées à vie", "Ne pouvoir boire que des choses très amères à vie"],
            ["Être le/la seul(e) sobre dans une soirée", "Être le/la seul(e) ivre dans une soirée"],
            ["Organiser une soirée où personne ne vient", "Aller à une soirée où tu ne connais absolument personne"],
            
            // Informatique / Réseaux / Jeux Vidéo
            ["Ne plus jamais avoir accès à Internet", "Avoir un Internet bridé à 56k (très très lent) à vie"],
            ["Te faire rouler dessus sur Rocket League à chaque partie", "Jouer à Minecraft mais tous les blocs explosent aléatoirement"],
            ["Vivre dans le monde de Minecraft", "Vivre dans le monde chaotique de GTA Online"],
            ["Avoir 300 de ping constant sur tous tes jeux", "Avoir toujours des coéquipiers qui quittent la partie au milieu"],
            ["Faire une erreur dans ton code et ne jamais trouver d'où ça vient", "Le code marche du premier coup mais tu ne sais absolument pas pourquoi"],
            ["Jouer en coop avec quelqu'un qui veut tout diriger", "Jouer en coop avec quelqu'un qui fait exprès de mourir"],
            
            // Action / Magasins / Achats
            ["Passer une nuit entière enfermé(e) dans un magasin Action", "Ne plus jamais pouvoir y remettre les pieds"],
            ["Acheter plein de trucs inutiles sans pouvoir les jeter", "Ne pouvoir acheter que l'essentiel, plus aucun petit plaisir"],
            ["Avoir un budget illimité mais 5 minutes pour faire tes courses", "Avoir 10€ et 3 heures pour choisir"],
            ["Que ton aspirateur fasse un bruit de moteur d'avion", "Que ton aspirateur parle et te juge quand tu fais le ménage"],
            
            // Psychologie / Relations / Pouvoirs
            ["Pouvoir lire dans les pensées des gens (psychologie ultime)", "Pouvoir voir le futur à 5 minutes"],
            ["Toujours dire ce que tu penses à voix haute", "Ne plus jamais pouvoir parler, seulement écrire"],
            ["Savoir exactement comment tu vas mourir", "Savoir exactement quand tu vas mourir"],
            ["Perdre tous tes souvenirs d'avant tes 15 ans", "Ne plus pouvoir créer de nouveaux souvenirs à partir d'aujourd'hui"],
            ["Être toujours en retard de 30 minutes", "Arriver toujours 1 heure en avance à chaque rendez-vous"],
            ["Avoir un partenaire qui analyse tous tes faits et gestes", "Avoir un partenaire qui ne remarque jamais rien"],
            ["Ne ressentir que de la joie h24 (mais fausse)", "Ressentir toutes les émotions normales mais intensifiées x10"],
            
            // Survie / Absurde / Corps
            ["Avoir des doigts à la place des orteils", "Avoir des orteils à la place des doigts"],
            ["Mesurer 50 cm de haut", "Mesurer 3 mètres de haut"],
            ["Transpirer de la mayonnaise", "Pleurer du ketchup"],
            ["Avoir le hoquet en permanence", "Avoir l'impression d'éternuer sans jamais y arriver"],
            ["Devoir te battre contre un canard de la taille d'un cheval", "Devoir te battre contre 100 chevaux de la taille d'un canard"],
            ["Avoir une voix qui porte à 10km", "Devoir toujours chuchoter pour qu'on t'entende"],
            ["Ne plus avoir de genoux", "Ne plus avoir de coudes"],
            ["Te réveiller chaque jour dans un pays différent", "Ne plus jamais pouvoir quitter ta ville actuelle"],
            
            // Quotidien / Couple
            ["Toujours dormir avec des chaussettes mouillées", "Dormir avec un moustique introuvable dans la chambre"],
            ["Manger le même plat tous les jours à vie", "Ne plus jamais pouvoir manger deux fois le même plat"],
            ["Ne plus jamais pouvoir prendre de douche chaude", "Ne plus jamais pouvoir te sécher avec une serviette"],
            ["Te brosser les dents avec de la moutarde", "Te laver les cheveux avec du liquide vaisselle"],
            ["Que ton/ta partenaire change de visage tous les ans", "Qu'il/elle garde le même visage mais change de personnalité"],
            ["Vivre sans chauffage l'hiver", "Vivre sans ventilateur/clim l'été"],
            ["Regarder un film génial mais avec un son décalé d'une seconde", "Regarder un film nul mais techniquement parfait"],
            ["Oublier ton téléphone avant de partir aux toilettes", "Oublier de fermer la porte à clé quand il y a des invités"],
            ["Perdre ton téléphone", "Perdre ton portefeuille contenant tous tes papiers"],
            ["Trouver un cheveu dans tous tes plats au resto", "Que le serveur se trompe de commande à chaque fois"],
            
            // Animaux / Nature
            ["Avoir un chien qui parle avec la voix de ton patron", "Avoir un chat qui te juge ouvertement toute la journée"],
            ["Vivre entouré d'araignées inoffensives", "Vivre avec une seule abeille super agressive dans ta chambre"],
            ["Pouvoir respirer sous l'eau", "Pouvoir voler mais à 2 mètres du sol maximum"],
            ["Te transformer en animal de compagnie luxueux", "Vivre libre mais comme un pigeon en ville"],
            
            // Et on continue sur de la variété...
            ["Trouver l'amour fou mais être pauvre", "Être milliardaire mais seul(e) à vie"],
            ["Avoir toujours l'impression d'avoir un caillou dans la chaussure", "Avoir toujours une étiquette qui gratte dans le dos"],
            ["Ne plus jamais pouvoir utiliser de GPS", "Devoir utiliser le GPS même pour aller chercher le pain"],
            ["Avoir un téléphone avec 5% de batterie bloqué à vie", "Avoir 100% de batterie mais l'écran est fissuré en plein milieu"],
            ["Que toutes tes séries soient annulées sans fin", "Que tu sois spoilé(e) de tout avant même de regarder"],
            ["Manger une pizza hawaïenne (ananas) par obligation", "Manger une pizza avec des anchois"],
            ["Ne plus pouvoir porter que des vêtements jaune fluo", "Ne plus pouvoir porter que du velours côtelé marron"],
            ["Devoir rire bruyamment à chaque fois que quelqu'un dit un mot précis", "Devoir pleurer sur commande dès qu'on te dit bonjour"],
            ["Vivre une semaine dans une télé-réalité ridicule", "Vivre un an enfermé dans un monastère silencieux"],
            ["Avoir les cheveux qui poussent de 10cm par jour", "Devenir complètement chauve à 20 ans"],
            ["Pouvoir te téléporter mais arriver tout nu", "Pouvoir voler mais seulement quand tu chantes à tue-tête"],
            ["N'avoir plus aucun ami mais une famille soudée", "Avoir plein d'amis mais plus aucune famille"],
            ["Être un génie incompris de tous", "Être très bête mais tout le monde t'adore"],
            ["Revivre la pire journée de ta vie", "Ne pas connaître le plus beau jour de ta vie"],
            ["Avoir 10 millions d'euros mais tu ne peux acheter que des trucs que tu détestes", "Avoir le SMIC toute ta vie mais tu es toujours heureux de tes achats"],
            ["Ne plus pouvoir manger de sucre", "Ne plus pouvoir manger de sel"],
            ["Travailler 4 jours par semaine mais 12h par jour", "Travailler 6 jours par semaine mais 5h par jour"],
            ["Devoir te déplacer uniquement en roller", "Devoir te déplacer uniquement en marchant sur les mains"],
            ["Que ta vie soit une comédie musicale (les gens se mettent à chanter dans la rue)", "Que ta vie soit un sitcom (rires enregistrés à chaque phrase)"],
            ["Pouvoir arrêter le temps", "Pouvoir remonter le temps de 10 secondes"],
            ["Vivre dans un monde sans musique", "Vivre dans un monde sans cinéma/séries"],
            ["Toujours avoir une chanson pourrie en tête", "Ne jamais pouvoir te souvenir du titre de tes chansons préférées"],
            ["Avoir le pouvoir de rendre les gens malades en les regardant", "Avoir le pouvoir de les guérir mais prendre leur maladie"],
            ["Oublier instantanément le nom de chaque personne que tu rencontres", "Oublier ton propre prénom 5 fois par jour"],
            ["Ne plus jamais manger de fromage", "Ne plus jamais manger de chocolat"],
            ["Manger un pot entier de mayonnaise à la petite cuillère", "Boire un verre d'huile d'olive d'un trait"],
            ["Devoir refaire toute ta scolarité depuis le CP", "Travailler jusqu'à 80 ans sans jamais prendre la retraite"],
            ["Être poursuivi par un escargot immortel qui te tue s'il te touche", "Être poursuivi par un ours un jour aléatoire dans l'année"],
            ["Avoir un nez de clown rouge qui fait *pouet* en permanence", "Avoir des grandes chaussures de clown qu'on ne peut pas enlever"],
            ["Ne jamais pouvoir utiliser de papier toilette (seulement de l'eau)", "N'avoir que du papier de verre à disposition"],
            ["Ne plus avoir de cils ni de sourcils", "Avoir un mono-sourcil impossible à raser"],
            ["Être coincé(e) dans un ascenseur avec ton pire ennemi", "Être coincé(e) dans un ascenseur seul pendant 3 jours"],
            ["Parler couramment toutes les langues du monde", "Pouvoir jouer parfaitement de tous les instruments de musique"],
            ["Gagner un voyage all-inclusive mais tu y vas seul(e)", "Gagner un voyage avec tous tes amis mais tu dors par terre dans une tente"],
            ["Avoir une maison auto-nettoyante", "Avoir un chef cuisinier personnel à vie"],
            ["Ne plus pouvoir utiliser d'emojis par message", "Devoir répondre à chaque message UNIQUEMENT par des emojis"],
            ["Être célèbre pour quelque chose de très gênant", "Faire un exploit mondial et que personne ne te croie jamais"],
            ["Avoir toujours 10 degrés de trop", "Avoir toujours 10 degrés de moins"],
            ["Vivre sans soleil pendant 1 an (nuit polaire)", "Vivre sans nuit pendant 1 an (jour permanent)"],
            ["Devoir porter des chaussettes dans des sandales à vie", "Devoir porter une doudoune même en plein mois d'août"],
            ["Ne pouvoir chuchoter", "Ne pouvoir que crier"],
            ["Que la batterie de ton téléphone descende 3x plus vite", "Que la connexion internet soit 3x plus lente"],
            ["Ne plus jamais pouvoir utiliser YouTube", "Ne plus jamais pouvoir utiliser Netflix/Prime"],
            ["Découvrir que les extraterrestres existent et sont hostiles", "Découvrir que nous sommes complètement seuls dans tout l'univers"],
            ["Avoir une voiture dont le klaxon s'active tout seul", "Avoir une voiture où les essuie-glaces marchent tout le temps"]
        ];

        let questionsDeck = [...allQuestions].sort(() => Math.random() - 0.5);
        let matchCount = 0;
        let choice1 = "", choice2 = "";
        let voteP1 = null, voteP2 = null; 
        let gameState = 'setup'; // 'setup', 'voting', 'ready', 'result'

        // --- GESTION DES MODES DE JEU ---
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
            myRole = 1; // Hôte
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
            myRole = 2; // Client
            currentRoom = code;
            socket.emit('rejoindreSalon', code);
            
            document.getElementById('mode-badge').innerText = "SALON : " + code + " (TU ES J2)";
            document.getElementById('mode-badge').className = "text-xs font-bold px-3 py-1 bg-blue-500/30 text-blue-300 rounded-full border border-blue-500/50";
            
            // J2 masque les textes indiquant d'appuyer sur espace (car c'est J1 qui gère)
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
            // Si on est J2 en ligne, on attend que J1 nous envoie la question
            if (isOnline && myRole === 2) {
                gameState = 'voting';
                return; 
            }

            // J1 ou mode local génère la question
            if(questionsDeck.length === 0) questionsDeck = [...allQuestions].sort(() => Math.random() - 0.5);
            const dilemma = questionsDeck.pop();
            choice1 = dilemma[0];
            choice2 = dilemma[1];

            updateUIWithDilemma(choice1, choice2);
            gameState = 'voting';

            // J1 envoie la question au J2
            if (isOnline && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'newRound', c1: choice1, c2: choice2 });
            }
        }

        function updateUIWithDilemma(c1, c2) {
            document.getElementById('choice-1-text').innerText = c1;
            document.getElementById('choice-2-text').innerText = c2;
        }

        // --- GESTION DU CLAVIER ET DU RESEAU ---
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            
            if (gameState === 'voting' || gameState === 'ready') {
                // J1 utilise A/Z
                if (!isOnline || myRole === 1) {
                    if (key === 'a' || key === 'q') handleVote(1, 1);
                    if (key === 'z' || key === 'w') handleVote(1, 2);
                }
                // J2 utilise L/M
                if (!isOnline || myRole === 2) {
                    if (key === 'l') handleVote(2, 1);
                    if (key === 'm' || key === ';') handleVote(2, 2);
                }
            }

            // Barre Espace (Contrôle du flux)
            if (key === ' ') {
                if (gameState === 'ready') {
                    e.preventDefault();
                    // Seul J1 ou le mode Local peut déclencher la révélation
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
            // Si on joue en ligne, on transmet l'info au serveur
            if (isOnline) {
                socket.emit('actionJeu', { room: currentRoom, type: 'vote', player: playerNum, vote: votedFor });
            }
        }

        function castVoteLocal(playerNum, votedFor) {
            if(playerNum === 1) {
                voteP1 = votedFor;
                document.getElementById('status-p1').innerText = "A choisi ! 🔒";
                document.getElementById('status-p1').className = "mt-4 font-bold text-green-400 h-6 text-center";
                document.getElementById('p1-card').classList.replace('bg-white/5', 'bg-white/10');
            } else {
                voteP2 = votedFor;
                document.getElementById('status-p2').innerText = "A choisi ! 🔒";
                document.getElementById('status-p2').className = "mt-4 font-bold text-green-400 h-6 text-center";
                document.getElementById('p2-card').classList.replace('bg-white/5', 'bg-white/10');
            }

            if(voteP1 !== null && voteP2 !== null) {
                gameState = 'ready';
                document.getElementById('reveal-text').classList.remove('hidden');
            }
        }

        // --- ECOUTE DES MESSAGES DU SERVEUR ---
        socket.on('actionRecue', (data) => {
            if (data.type === 'newRound') {
                choice1 = data.c1;
                choice2 = data.c2;
                updateUIWithDilemma(choice1, choice2);
                gameState = 'voting';
            }
            if (data.type === 'vote') {
                castVoteLocal(data.player, data.vote);
            }
            if (data.type === 'reveal') {
                reveal();
            }
            if (data.type === 'next') {
                nextRound();
            }
        });

        function reveal() {
            gameState = 'result';
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('result-screen').classList.remove('hidden');

            const textVoteP1 = voteP1 === 1 ? choice1 : choice2;
            const textVoteP2 = voteP2 === 1 ? choice1 : choice2;
            const colorClassP1 = voteP1 === 1 ? 'border-orange-500 bg-orange-900/30 text-orange-200' : 'border-blue-500 bg-blue-900/30 text-blue-200';
            const colorClassP2 = voteP2 === 1 ? 'border-orange-500 bg-orange-900/30 text-orange-200' : 'border-blue-500 bg-blue-900/30 text-blue-200';

            document.getElementById('res-vote-1').innerText = textVoteP1;
            document.getElementById('res-box-1').className = `flex-1 p-4 rounded-xl border-2 flex flex-col justify-center items-center min-h-[120px] ${colorClassP1}`;
            
            document.getElementById('res-vote-2').innerText = textVoteP2;
            document.getElementById('res-box-2').className = `flex-1 p-4 rounded-xl border-2 flex flex-col justify-center items-center min-h-[120px] ${colorClassP2}`;

            if (voteP1 === voteP2) {
                matchCount++;
                document.getElementById('score-display').innerText = `${matchCount} Match${matchCount > 1 ? 's' : ''}`;
                document.getElementById('result-icon').innerHTML = "🤝";
                document.getElementById('result-title').innerText = "Accord Parfait !";
                document.getElementById('result-title').className = "text-3xl font-bold mb-2 text-green-400";
                document.getElementById('result-msg').innerText = "Vous avez fait exactement le même choix.";
                document.getElementById('vs-icon').className = "fa-solid fa-heart text-green-500";
            } else {
                document.getElementById('result-icon').innerHTML = "🔥";
                document.getElementById('result-title').innerText = "Désaccord total !";
                document.getElementById('result-title').className = "text-3xl font-bold mb-2 text-red-400";
                document.getElementById('result-msg').innerText = "Défendez votre choix, le débat est ouvert !";
                document.getElementById('vs-icon').className = "fa-solid fa-bolt text-red-500";
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
            document.getElementById('reveal-text').classList.add('hidden');
            
            setupRound();

            document.getElementById('result-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
        }
    </script>
</body>
</html>