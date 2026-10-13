<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PartyPoly - La Boîte à Jeux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap');
        
        body { font-family: 'Montserrat', sans-serif; background: #111827; color: white; min-height: 100vh; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; }
        .glass-panel { background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5); }
        
        #board { display: grid; grid-template-columns: repeat(11, 1fr); grid-template-rows: repeat(11, 1fr); gap: 2px; background: #1f2937; padding: 2px; border: 4px solid #374151; border-radius: 12px; width: 90vh; height: 90vh; max-width: 900px; max-height: 900px; }
        
        .tile { background: #cbd5e1; color: #0f172a; display: flex; flex-direction: column; justify-content: space-between; align-items: center; font-size: 0.6rem; text-align: center; position: relative; overflow: hidden; }
        .tile-color { width: 100%; height: 25%; border-bottom: 1px solid #94a3b8; }
        .tile-name { font-weight: bold; padding: 2px; font-size: 0.55rem; line-height: 1.1; }
        .tile-price { font-weight: bold; font-size: 0.65rem; padding-bottom: 2px; }
        
        #center-zone { grid-column: 2 / 11; grid-row: 2 / 11; background: #111827; display: flex; flex-direction: column; justify-content: space-between; padding: 15px; position: relative; }
        
        .tokens-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 2px; padding: 2px; width: 100%; }
        .token { width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5); transition: all 0.3s ease; }
        
        .btn-action { background: linear-gradient(45deg, #3b82f6, #8b5cf6); transition: 0.2s; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4); }
        .btn-action:disabled { background: #4b5563; transform: none; box-shadow: none; cursor: not-allowed; opacity: 0.5; }

        .tile-corner { background: #e2e8f0; font-size: 0.8rem; justify-content: center; padding: 5px; }
        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 14px; }
        
        .card-anim { animation: popup 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes popup { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
    </style>
</head>
<body>

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center">
        <div class="text-6xl mb-4 text-green-400"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        <h1 class="text-4xl font-black mb-2 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-blue-500">PARTYPOLY</h1>
        <p class="text-gray-400 text-sm mb-8">Devenez le maître du plateau (2 à 8 joueurs).</p>

        <div class="mb-6 p-4 bg-white/5 border border-white/10 rounded-xl">
            <h3 class="font-bold text-gray-300 mb-3">Rejoindre un salon</h3>
            <input type="text" id="room-code" placeholder="Code du salon" class="w-full bg-black/60 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none mb-3 text-center tracking-widest font-bold text-xl uppercase">
            <input type="text" id="player-name" placeholder="Ton Pseudo" class="w-full bg-black/60 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none mb-3 text-center font-bold">
            
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 bg-green-600 hover:bg-green-500 py-3 rounded-xl font-bold transition">Créer (Hôte)</button>
                <button onclick="joinRoom()" class="flex-1 bg-blue-600 hover:bg-blue-500 py-3 rounded-xl font-bold transition">Rejoindre</button>
            </div>
        </div>
    </div>

    <!-- ECRAN D'ATTENTE -->
    <div id="lobby-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center hidden">
        <h2 class="text-2xl font-bold mb-4">Salon : <span id="lobby-code" class="text-green-400"></span></h2>
        <div id="lobby-players" class="flex flex-col gap-2 mb-6"></div>
        <button id="btn-start-game" onclick="startGame()" class="w-full btn-action py-4 rounded-xl font-black text-xl hidden">LANCER LA PARTIE</button>
        <p id="lobby-wait-msg" class="text-sm text-yellow-400 animate-pulse">En attente que l'Hôte lance la partie...</p>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="hidden w-full h-screen p-4 flex gap-4">
        
        <div class="w-80 flex flex-col gap-4 h-full">
            <div class="glass-panel rounded-2xl p-4 flex flex-col gap-3 flex-[1] overflow-y-auto" id="players-panel">
                <h3 class="font-black text-center text-xl mb-2 border-b border-white/20 pb-2">Joueurs</h3>
            </div>
            
            <div class="glass-panel rounded-2xl p-4 flex flex-col flex-[1.5] overflow-hidden">
                <h3 class="font-black text-center text-lg mb-2 border-b border-white/20 pb-2 text-yellow-400"><i class="fa-solid fa-wallet"></i> Mon Portefeuille</h3>
                <div id="my-inventory" class="overflow-y-auto flex-1 pr-2 space-y-2 mt-2 text-sm"></div>
            </div>
        </div>

        <div class="flex-1 flex justify-center items-center relative">
            <div id="board">
                <div id="center-zone" class="rounded-xl border-4 border-gray-700">
                    
                    <div class="flex justify-between items-center px-4 pt-2">
                        <h1 class="text-2xl font-black text-white/20 tracking-widest">PARTYPOLY</h1>
                        <div id="game-feed" class="w-72 h-16 bg-black/50 border border-white/10 rounded-lg p-2 text-[0.65rem] overflow-y-auto flex flex-col justify-end text-gray-300">
                            <div class="italic text-gray-500">Début de la partie...</div>
                        </div>
                    </div>

                    <!-- MODALE DE DETTE / CHOIX DE CESSION -->
                    <div id="debt-modal" class="hidden absolute inset-0 z-50 flex items-center justify-center bg-black/85 rounded-xl backdrop-blur-sm">
                        <div class="bg-red-900 border-4 border-red-500 rounded-xl p-6 max-w-md text-center card-anim shadow-2xl">
                            <h2 class="text-2xl font-black mb-2 text-white"><i class="fa-solid fa-triangle-exclamation"></i> DETTE IMMÉDIATE</h2>
                            <p id="debt-text" class="text-sm font-bold text-red-200 mb-4">Tu es en négatif ! Choisis une propriété à céder pour éponger ta dette :</p>
                            <div id="debt-props-list" class="flex flex-col gap-2 max-h-48 overflow-y-auto mb-4"></div>
                        </div>
                    </div>

                    <!-- MODALE CARTE CHANCE -->
                    <div id="chance-card-ui" class="hidden absolute inset-0 z-50 flex items-center justify-center bg-black/80 rounded-xl backdrop-blur-sm">
                        <div class="bg-orange-600 border-4 border-orange-400 rounded-xl p-6 max-w-sm text-center card-anim shadow-2xl">
                            <h2 class="text-2xl font-black mb-4 text-white"><i class="fa-solid fa-question"></i> CARTE CHANCE</h2>
                            <p id="chance-card-text" class="text-lg font-bold text-white mb-6">Texte de la carte...</p>
                            <button id="btn-chance-ok" onclick="sendAction('chance_ok')" class="bg-white text-orange-600 hover:bg-gray-200 px-6 py-2 rounded-xl font-bold hidden w-full shadow-lg">Continuer</button>
                        </div>
                    </div>

                    <div class="bg-black/60 p-5 rounded-2xl border border-white/10 text-center shadow-xl z-10 mx-auto w-full max-w-md self-center">
                        <h2 id="turn-indicator" class="text-xl font-bold mb-2">C'est au tour de...</h2>
                        
                        <div class="flex justify-center gap-4 mb-4 text-4xl">
                            <i id="dice-1" class="fa-solid fa-dice-d6 text-gray-400"></i>
                            <i id="dice-2" class="fa-solid fa-dice-d6 text-gray-400"></i>
                        </div>

                        <div id="action-msg" class="text-base font-bold mb-3 text-yellow-400 min-h-[24px]"></div>

                        <div class="flex gap-2 justify-center">
                            <button id="btn-roll" onclick="sendAction('roll')" class="btn-action px-5 py-2.5 rounded-xl font-bold hidden text-sm"><i class="fa-solid fa-dice"></i> Lancer</button>
                            <button id="btn-buy" onclick="sendAction('buy')" class="bg-green-600 hover:bg-green-500 px-5 py-2.5 rounded-xl font-bold hidden text-sm"><i class="fa-solid fa-cart-shopping"></i> Acheter</button>
                            <button id="btn-end" onclick="sendAction('end')" class="bg-red-600 hover:bg-red-500 px-5 py-2.5 rounded-xl font-bold hidden text-sm"><i class="fa-solid fa-forward"></i> Fin de tour</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const socket = io('https://partyscreen-server.onrender.com');
        let isOnline = false;
        let myRole = 0; 
        let myId = null;
        let currentRoom = "";
        
        const tokenColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
        
        const chanceCards = [
            { text: "Erreur de la banque en votre faveur. Recevez 200€", type: "money", val: 200 },
            { text: "Frais de scolarité. Payez 150€", type: "money", val: -150 },
            { text: "Amende pour excès de vitesse. Payez 50€", type: "money", val: -50 },
            { text: "Vous avez gagné le concours de mots croisés. Recevez 100€", type: "money", val: 100 },
            { text: "Allez directement en prison. Ne passez pas par la case Départ.", type: "goto", val: 10 },
            { text: "Rendez-vous à la case Départ.", type: "goto", val: 0 },
            { text: "Reculez de 3 cases.", type: "move", val: -3 }
        ];

        const groups = [
            { c: '#8B4513', p: 60, r: 10 }, { c: '#8B4513', p: 60, r: 10 }, 
            { c: '#87CEEB', p: 100, r: 15 }, { c: '#87CEEB', p: 100, r: 15 }, { c: '#87CEEB', p: 120, r: 18 }, 
            { c: '#FF1493', p: 140, r: 22 }, { c: '#FF1493', p: 140, r: 22 }, { c: '#FF1493', p: 160, r: 25 }, 
            { c: '#FFA500', p: 180, r: 30 }, { c: '#FFA500', p: 180, r: 30 }, { c: '#FFA500', p: 200, r: 35 }, 
            { c: '#FF0000', p: 220, r: 40 }, { c: '#FF0000', p: 220, r: 40 }, { c: '#FF0000', p: 240, r: 45 }, 
            { c: '#FFFF00', p: 260, r: 50 }, { c: '#FFFF00', p: 260, r: 50 }, { c: '#FFFF00', p: 280, r: 55 }, 
            { c: '#008000', p: 300, r: 65 }, { c: '#008000', p: 300, r: 65 }, { c: '#008000', p: 320, r: 75 }, 
            { c: '#0000FF', p: 350, r: 100 }, { c: '#0000FF', p: 400, r: 125 } 
        ];

        let boardTiles = new Array(40);
        let propIndex = 0;
        
        for (let i = 0; i < 40; i++) {
            if (i === 0) boardTiles[i] = { type: 'corner', name: 'DÉPART<br>+200€' };
            else if (i === 10) boardTiles[i] = { type: 'corner', name: 'PRISON' };
            else if (i === 20) boardTiles[i] = { type: 'corner', name: 'PARC GRATUIT' };
            else if (i === 30) boardTiles[i] = { type: 'corner', name: 'ALLEZ EN PRISON' };
            else if (i === 5 || i === 15 || i === 25 || i === 35) boardTiles[i] = { type: 'station', name: 'Gare ' + i, price: 200, owner: null };
            else if (i === 12 || i === 28) boardTiles[i] = { type: 'station', name: 'Service Pub.', price: 150, rent: 20, owner: null }; 
            else if (i === 4 || i === 38) boardTiles[i] = { type: 'tax', name: 'Taxes', price: 100 };
            else if (i === 2 || i === 7 || i === 17 || i === 22 || i === 33 || i === 36) boardTiles[i] = { type: 'chance', name: 'Chance' };
            else {
                let g = groups[propIndex++];
                boardTiles[i] = { type: 'prop', name: 'Rue ' + i, color: g.c, price: g.p, rent: g.r, houses: 0, owner: null };
            }
        }

        let gameState = {
            players: [], 
            turnIndex: 0,
            phase: 'waiting', 
            activeCard: null,
            winner: null,
            feed: [],
            board: boardTiles
        };

        function addLog(msg) {
            gameState.feed.push(msg);
            if (gameState.feed.length > 20) gameState.feed.shift();
        }

        function getHouseCost(tile) {
            if (tile.houses === 0) return tile.price;
            if (tile.houses === 1) return tile.price * 1.5;
            if (tile.houses === 2) return tile.price * 2;
            if (tile.houses === 3) return tile.price * 2.5;
            return 0;
        }

        function hasMonopoly(playerId, color, state) {
            let colorGroup = state.board.filter(t => t.type === 'prop' && t.color === color);
            let ownedInGroup = colorGroup.filter(t => t.owner === playerId);
            return colorGroup.length > 0 && colorGroup.length === ownedInGroup.length;
        }

        function getRent(tileIndex, state) {
            let tile = state.board[tileIndex];
            if (!tile.owner) return 0;
            
            if (tile.type === 'station' && tile.name.includes('Gare')) {
                let stationCount = state.board.filter(t => t.type === 'station' && t.name.includes('Gare') && t.owner === tile.owner).length;
                if (stationCount === 1) return 25;
                if (stationCount === 2) return 50;
                if (stationCount === 3) return 75;
                if (stationCount >= 4) return 100;
            }

            if (tile.type !== 'prop') return tile.rent || 20; 
            
            if (tile.houses > 0) {
                if (tile.houses === 1) return Math.ceil(tile.rent * 1.5);
                if (tile.houses === 2) return Math.ceil(tile.rent * 2);
                if (tile.houses === 3) return Math.ceil(tile.rent * 2.5);
                if (tile.houses === 4) return Math.ceil(tile.rent * 3);
            }

            if (hasMonopoly(tile.owner, tile.color, state)) {
                let colorGroup = state.board.filter(t => t.type === 'prop' && t.color === tile.color);
                if (colorGroup.length === 2) return Math.ceil(tile.rent * 1.25);
                if (colorGroup.length >= 3) return Math.ceil(tile.rent * 1.5);
            }
            return tile.rent;
        }

        function generateBoardUI() {
            const board = document.getElementById('board');
            for (let i = 0; i < 40; i++) {
                let tile = boardTiles[i];
                let div = document.createElement('div');
                div.className = 'tile';
                div.id = 'tile-' + i;
                
                if (i >= 0 && i <= 10) { div.style.gridRow = '11'; div.style.gridColumn = (11 - i).toString(); }
                else if (i > 10 && i <= 20) { div.style.gridColumn = '1'; div.style.gridRow = (21 - i).toString(); }
                else if (i > 20 && i <= 30) { div.style.gridRow = '1'; div.style.gridColumn = (i - 19).toString(); }
                else { div.style.gridColumn = '11'; div.style.gridRow = (i - 29).toString(); }

                if (tile.type === 'corner') {
                    div.classList.add('tile-corner');
                    div.innerHTML = `<span class="font-black text-center">${tile.name}</span><div class="tokens-container" id="tokens-${i}"></div>`;
                } else if (tile.type === 'prop') {
                    div.innerHTML = `
                        <div class="tile-color" style="background-color: ${tile.color};"></div>
                        <div class="tile-name">${tile.name} <span id="house-badge-${i}" class="text-yellow-400 font-black"></span></div>
                        <div class="tile-price">${tile.price}€</div>
                        <div class="tokens-container" id="tokens-${i}"></div>
                        <div id="owner-bar-${i}" class="w-full h-1 mt-auto"></div>
                    `;
                } else {
                    div.innerHTML = `
                        <div class="tile-name mt-2">${tile.name}</div>
                        ${tile.type === 'chance' ? '<i class="fa-solid fa-question text-orange-500 mt-1"></i>' : ''}
                        ${tile.price ? '<div class="tile-price">'+tile.price+'€</div>' : ''}
                        <div class="tokens-container" id="tokens-${i}"></div>
                        <div id="owner-bar-${i}" class="w-full h-1 mt-auto"></div>
                    `;
                }
                board.appendChild(div);
            }
        }
        generateBoardUI();

        function getPseudo() {
            let p = document.getElementById('player-name').value.trim();
            return p ? p : "Joueur " + Math.floor(Math.random()*1000);
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return alert("Code manquant");
            myRole = 1; 
            myId = socket.id || "host_" + Math.random().toString(36).substr(2, 9); 
            currentRoom = code;
            // 1000€ de départ ici
            gameState.players.push({ id: myId, name: getPseudo(), color: tokenColors[0], money: 1000, pos: 0, isHost: true, bankrupt: false });
            socket.emit('rejoindreSalon', code);
            showLobby(code);
            updateLobbyUI();
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return alert("Code manquant");
            myRole = 2; 
            myId = socket.id || "client_" + Math.random().toString(36).substr(2, 9);
            currentRoom = code;
            socket.emit('rejoindreSalon', code);
            socket.emit('actionJeu', { room: currentRoom, type: 'request_join', name: getPseudo(), id: myId });
            showLobby(code);
        }

        function showLobby(code) {
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('lobby-screen').classList.remove('hidden');
            document.getElementById('lobby-code').innerText = code;
            if(myRole === 1) {
                document.getElementById('btn-start-game').classList.remove('hidden');
                document.getElementById('lobby-wait-msg').classList.add('hidden');
            }
        }

        function updateLobbyUI() {
            const container = document.getElementById('lobby-players');
            container.innerHTML = "";
            gameState.players.forEach(p => {
                container.innerHTML += `<div class="bg-white/10 p-2 rounded flex items-center gap-3"><div class="w-4 h-4 rounded-full" style="background:${p.color};"></div> <span class="font-bold">${p.name}</span></div>`;
            });
        }

        function startGame() {
            if (myRole !== 1) return;
            gameState.phase = 'rolling';
            addLog("La partie commence avec 1000€ !");
            broadcastState();
        }

        function handleDebt(debtor, creditor) {
            if (debtor.money >= 0) return; // Tout va bien
            
            // Vérifie s'il a des propriétés à céder
            let props = gameState.board.map((t, idx) => ({...t, idx})).filter(t => t.owner === debtor.id);
            if (props.length === 0) {
                debtor.bankrupt = true;
                debtor.money = 0;
                addLog(`💥 ${debtor.name} est en faillite définitive !`);
                gameState.phase = 'rolling';
                nextTurn();
                return;
            }

            // S'il est le joueur local, on ouvre la modale de choix
            if (debtor.id === myId) {
                openDebtModal(props, debtor, creditor);
            } else {
                addLog(`⏳ En attente que ${debtor.name} choisisse une propriété à céder...`);
                gameState.phase = 'waiting_debt_choice';
            }
        }

        function openDebtModal(props, debtor, creditor) {
            const modal = document.getElementById('debt-modal');
            const list = document.getElementById('debt-props-list');
            list.innerHTML = "";
            
            document.getElementById('debt-text').innerText = `Tu es à ${debtor.money}€ ! Choisis une propriété à céder pour éponger ta dette :`;

            props.forEach(prop => {
                list.innerHTML += `
                    <button onclick="sendAction('sell_prop', ${prop.idx})" class="bg-red-800 hover:bg-red-700 p-2 rounded font-bold text-sm flex justify-between items-center text-white border border-red-500">
                        <span>${prop.name}</span>
                        <span class="text-yellow-300">+${prop.price}€</span>
                    </button>
                `;
            });
            modal.classList.remove('hidden');
        }

        function processAction(actionType, playerId, extraData) {
            if (myRole !== 1) return; 
            
            let p = gameState.players.find(x => x.id === playerId);
            if (!p || p.bankrupt) return; 

            // Cession choisie par le joueur en dette
            if (actionType === 'sell_prop') {
                let tileIndex = extraData;
                let tile = gameState.board[tileIndex];
                if (tile && tile.owner === p.id) {
                    p.money += tile.price;
                    tile.owner = null; // Remis à la banque (ou on peut l'attribuer au créancier)
                    tile.houses = 0;
                    addLog(`⚖️ ${p.name} a cédé ${tile.name} pour éponger sa dette.`);
                    
                    document.getElementById('debt-modal').classList.add('hidden');

                    if (p.money >= 0) {
                        gameState.phase = 'rolling';
                        nextTurn();
                    } else {
                        // S'il doit encore de l'argent, on ré-ouvre le choix
                        handleDebt(p, null);
                        broadcastState();
                    }
                }
                return;
            }

            if (actionType === 'buy_house') {
                let tileIndex = extraData;
                let tile = gameState.board[tileIndex];
                if (tile && tile.owner === p.id && tile.type === 'prop' && tile.houses < 4) {
                    if (hasMonopoly(p.id, tile.color, gameState)) {
                        let cost = getHouseCost(tile);
                        if (p.money >= cost) {
                            p.money -= cost;
                            tile.houses++;
                            addLog(`🏠 ${p.name} a acheté une maison sur ${tile.name} (${cost}€)`);
                            broadcastState();
                        }
                    }
                }
                return;
            }

            let activePlayer = gameState.players[gameState.turnIndex];
            if (activePlayer.id !== playerId) return; 

            if (actionType === 'roll' && gameState.phase === 'rolling') {
                let d1 = Math.floor(Math.random() * 6) + 1;
                let d2 = Math.floor(Math.random() * 6) + 1;
                
                let oldPos = activePlayer.pos;
                activePlayer.pos = (activePlayer.pos + d1 + d2) % 40;
                
                if (activePlayer.pos < oldPos) {
                    activePlayer.money += 200; 
                    addLog(`💰 ${activePlayer.name} est passé par la case Départ (+200€)`);
                }

                gameState.lastDice = [d1, d2];
                addLog(`🎲 ${activePlayer.name} a fait ${d1} et ${d2}`);
                handleLandedTile(activePlayer);
                broadcastState();
            }
            else if (actionType === 'buy' && gameState.phase === 'acting') {
                let tile = gameState.board[activePlayer.pos];
                if ((tile.type === 'prop' || tile.type === 'station') && !tile.owner && activePlayer.money >= tile.price) {
                    activePlayer.money -= tile.price;
                    tile.owner = activePlayer.id;
                    addLog(`🛒 ${activePlayer.name} a acheté ${tile.name} pour ${tile.price}€`);
                    gameState.phase = 'rolling';
                    nextTurn();
                }
            }
            else if (actionType === 'chance_ok' && gameState.phase === 'chance_drawn') {
                let card = gameState.activeCard;
                gameState.activeCard = null; 
                
                if (card.type === 'money') {
                    if (card.val > 0) {
                        activePlayer.money += card.val;
                        addLog(`✨ Carte Chance : ${activePlayer.name} reçoit ${card.val}€`);
                        nextTurn();
                    } else {
                        activePlayer.money -= Math.abs(card.val);
                        addLog(`💸 Carte Chance : ${activePlayer.name} paie ${Math.abs(card.val)}€`);
                        if (activePlayer.money < 0) {
                            handleDebt(activePlayer, null);
                        } else {
                            nextTurn();
                        }
                    }
                } else if (card.type === 'goto') {
                    activePlayer.pos = card.val;
                    addLog(`🎯 Carte Chance : ${activePlayer.name} se déplace`);
                    if (card.val === 10) nextTurn(); 
                    else handleLandedTile(activePlayer); 
                } else if (card.type === 'move') {
                    activePlayer.pos = (activePlayer.pos + card.val + 40) % 40;
                    addLog(`🔙 Carte Chance : ${activePlayer.name} recule de 3 cases`);
                    handleLandedTile(activePlayer);
                    broadcastState(); 
                }
            }
            else if (actionType === 'end' && gameState.phase === 'acting') {
                addLog(`⏭️ ${activePlayer.name} a passé son tour.`);
                nextTurn();
            }
        }

        function handleLandedTile(p) {
            let tile = gameState.board[p.pos];
            
            if (tile.type === 'prop' || tile.type === 'station') {
                if (tile.owner && tile.owner !== p.id) {
                    let owner = gameState.players.find(x => x.id === tile.owner);
                    if (owner && !owner.bankrupt) {
                        let actualRent = getRent(p.pos, gameState);
                        p.money -= actualRent;
                        owner.money += actualRent;
                        addLog(`${p.name} a payé ${actualRent}€ de loyer à ${owner.name}`);
                        
                        if (p.money < 0) {
                            handleDebt(p, owner);
                            return;
                        }
                    }
                    nextTurn();
                } else if (!tile.owner) {
                    gameState.phase = 'acting'; 
                } else {
                    nextTurn(); 
                }
            } else if (tile.type === 'tax') {
                p.money -= tile.price;
                addLog(`${p.name} a payé ${tile.price}€ de taxes`);
                if (p.money < 0) {
                    handleDebt(p, null);
                } else {
                    nextTurn();
                }
            } else if (tile.type === 'chance') {
                gameState.activeCard = chanceCards[Math.floor(Math.random() * chanceCards.length)];
                gameState.phase = 'chance_drawn';
                addLog(`❓ ${p.name} a tiré une carte Chance !`);
            } else if (p.pos === 30) { 
                p.pos = 10;
                addLog(`👮 ${p.name} est envoyé en Prison !`);
                nextTurn();
            } else {
                nextTurn(); 
            }
        }

        function nextTurn() {
            if (gameState.phase === 'waiting_debt_choice') return; // Bloque si on attend le choix de dette
            
            let safetyLimit = 0;
            do {
                gameState.turnIndex = (gameState.turnIndex + 1) % gameState.players.length;
                safetyLimit++;
            } while (gameState.players[gameState.turnIndex].bankrupt && safetyLimit < 15);
            
            let activePlayers = gameState.players.filter(p => !p.bankrupt);
            if (activePlayers.length === 1) {
                gameState.phase = 'game_over';
                gameState.winner = activePlayers[0];
                addLog(`🏆 Victoire de ${gameState.winner.name} !`);
            } else {
                gameState.phase = 'rolling';
            }
            
            broadcastState();
        }

        function broadcastState() {
            socket.emit('actionJeu', { room: currentRoom, type: 'sync_state', state: gameState });
            renderState(gameState); 
        }

        function sendAction(act, extra) {
            if (myRole === 1) processAction(act, myId, extra);
            else socket.emit('actionJeu', { room: currentRoom, type: 'player_action', action: act, id: myId, extra: extra });
        }

        function renderState(state) {
            gameState = state;
            
            if (document.getElementById('game-screen').classList.contains('hidden')) {
                document.getElementById('lobby-screen').classList.add('hidden');
                document.getElementById('game-screen').classList.remove('hidden');
            }

            const feedContainer = document.getElementById('game-feed');
            feedContainer.innerHTML = "";
            if(state.feed) {
                state.feed.forEach(msg => {
                    feedContainer.innerHTML += `<div>• ${msg}</div>`;
                });
                feedContainer.scrollTop = feedContainer.scrollHeight;
            }

            const panel = document.getElementById('players-panel');
            panel.innerHTML = '<h3 class="font-black text-center text-xl mb-2 border-b border-white/20 pb-2">Joueurs</h3>';
            state.players.forEach((p, idx) => {
                let isTurn = (idx === state.turnIndex);
                let bankruptStyle = p.bankrupt ? "opacity-50 grayscale" : "";
                let bankruptText = p.bankrupt ? "line-through text-red-500" : "";
                let moneyColor = p.bankrupt ? "text-red-500" : "text-green-400";
                
                panel.innerHTML += `
                    <div class="p-3 rounded-xl border ${isTurn ? 'border-green-400 bg-white/10' : 'border-white/10 bg-black/40'} ${bankruptStyle}">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-4 h-4 rounded-full border border-white" style="background:${p.color};"></div>
                            <span class="font-bold truncate ${bankruptText}">${p.name} ${p.bankrupt ? '(Faillite)' : ''}</span>
                        </div>
                        <div class="text-xl font-black ${moneyColor}">${p.money} €</div>
                    </div>
                `;
            });

            const inv = document.getElementById('my-inventory');
            inv.innerHTML = "";
            let hasProp = false;
            let me = state.players.find(x => x.id === myId);
            
            if(me && !me.bankrupt) {
                state.board.forEach((t, i) => {
                    if (t.owner === myId) {
                        hasProp = true;
                        let colorBox = t.color ? `<div class="w-3 h-3 rounded-sm" style="background:${t.color};"></div>` : `<i class="fa-solid fa-train text-gray-400"></i>`;
                        let actualRent = getRent(i, state);
                        let houseIcons = t.houses > 0 ? `<span class="text-yellow-400 font-bold ml-1">${'🏠'.repeat(t.houses)}</span>` : '';
                        
                        let btnHouseHtml = "";
                        if (t.type === 'prop') {
                            if (t.houses < 4 && hasMonopoly(myId, t.color, state)) {
                                let cost = getHouseCost(t);
                                let isAffordable = me.money >= cost;
                                btnHouseHtml = `<button onclick="sendAction('buy_house', ${i})" class="bg-blue-600 hover:bg-blue-500 ${isAffordable ? '' : 'opacity-50 cursor-not-allowed'} px-2 py-1 rounded text-xs font-bold text-white ml-2">+🏠 ${cost}€</button>`;
                            } else if (!hasMonopoly(myId, t.color, state)) {
                                btnHouseHtml = `<span class="text-[9px] text-gray-500 ml-2">🔒 Monopole</span>`;
                            } else if (t.houses === 4) {
                                btnHouseHtml = `<span class="text-[9px] text-yellow-400 ml-2 font-bold">Max 🏠</span>`;
                            }
                        }

                        inv.innerHTML += `
                            <div class="flex items-center justify-between bg-black/40 p-2 rounded border border-white/10 mb-2">
                                <div class="flex items-center gap-1.5 overflow-hidden">
                                    ${colorBox} 
                                    <span class="font-bold truncate text-xs">${t.name} ${houseIcons}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-400 font-bold text-xs whitespace-nowrap">+${actualRent}€</span>
                                    ${btnHouseHtml}
                                </div>
                            </div>
                        `;
                    }
                });
            }
            if (!hasProp) inv.innerHTML = '<p class="text-gray-400 text-center italic mt-4">Aucune propriété.</p>';

            for (let i = 0; i < 40; i++) document.getElementById('tokens-' + i).innerHTML = "";
            state.players.forEach(p => {
                if(!p.bankrupt) {
                    let token = document.createElement('div');
                    token.className = 'token';
                    token.style.backgroundColor = p.color;
                    document.getElementById('tokens-' + p.pos).appendChild(token);
                }
            });

            state.board.forEach((t, i) => {
                let bar = document.getElementById('owner-bar-' + i);
                let badge = document.getElementById('house-badge-' + i);
                if (t.owner) {
                    let owner = state.players.find(x => x.id === t.owner);
                    if(bar && owner) bar.style.backgroundColor = owner.color;
                    if(badge) badge.innerText = t.houses > 0 ? ' ' + '🏠'.repeat(t.houses) : '';
                } else {
                    if(bar) bar.style.backgroundColor = "transparent";
                    if(badge) badge.innerText = '';
                }
            });

            let currentPlayer = state.players[state.turnIndex];
            document.getElementById('turn-indicator').innerText = "Tour de : " + currentPlayer.name;
            document.getElementById('turn-indicator').style.color = currentPlayer.color;

            if (state.lastDice) {
                const diceClasses = ['one','two','three','four','five','six'];
                document.getElementById('dice-1').className = `fa-solid fa-dice-${diceClasses[state.lastDice[0]-1]} text-white`;
                document.getElementById('dice-2').className = `fa-solid fa-dice-${diceClasses[state.lastDice[1]-1]} text-white`;
            }

            let isMyTurn = (currentPlayer.id === myId);
            const btnRoll = document.getElementById('btn-roll');
            const btnBuy = document.getElementById('btn-buy');
            const btnEnd = document.getElementById('btn-end');
            const msgBox = document.getElementById('action-msg');
            const chanceUI = document.getElementById('chance-card-ui');
            const btnChanceOk = document.getElementById('btn-chance-ok');
            
            btnRoll.classList.add('hidden'); btnBuy.classList.add('hidden'); btnEnd.classList.add('hidden');
            chanceUI.classList.add('hidden'); btnChanceOk.classList.add('hidden');
            
            msgBox.className = "text-base font-bold mb-3 text-yellow-400 min-h-[24px]";

            if (state.phase === 'game_over') {
                msgBox.innerText = `VICTOIRE DE ${state.winner.name} !`;
                msgBox.className = "text-xl font-black mb-3 text-green-400 animate-pulse";
            }
            else if (state.phase === 'rolling') {
                // CORRECTION : Les boutons d'action s'affichent UNIQUEMENT pour le joueur dont c'est le tour
                if (isMyTurn) {
                    btnRoll.classList.remove('hidden');
                    msgBox.innerText = "À toi de lancer !";
                } else {
                    msgBox.innerText = `Tour de ${currentPlayer.name}...`;
                }
            } 
            else if (state.phase === 'acting') {
                let tile = state.board[currentPlayer.pos];
                
                if (isMyTurn) {
                    if (currentPlayer.money < tile.price) {
                        msgBox.innerText = `Fonds insuffisants (${tile.price}€)`;
                        msgBox.className = "text-base font-bold mb-3 text-red-500 min-h-[24px]";
                        btnEnd.classList.remove('hidden');
                    } else {
                        msgBox.innerText = `Arrivé sur : ${tile.name} (${tile.price}€)`;
                        btnBuy.classList.remove('hidden');
                        btnEnd.classList.remove('hidden');
                    }
                } else {
                    msgBox.innerText = `${currentPlayer.name} est sur ${tile.name}`;
                }
            }
            else if (state.phase === 'chance_drawn') {
                chanceUI.classList.remove('hidden');
                document.getElementById('chance-card-text').innerText = state.activeCard.text;
                if (isMyTurn) {
                    btnChanceOk.classList.remove('hidden');
                }
            }
        }

        socket.on('actionRecue', (data) => {
            if (myRole === 1) {
                if (data.type === 'request_join' && gameState.phase === 'waiting' && gameState.players.length < 8) {
                    gameState.players.push({
                        id: data.id,
                        name: data.name,
                        color: tokenColors[gameState.players.length],
                        money: 1000, // 1000€ de départ
                        pos: 0,
                        isHost: false,
                        bankrupt: false
                    });
                    updateLobbyUI();
                    socket.emit('actionJeu', { room: currentRoom, type: 'sync_lobby', players: gameState.players });
                }
                if (data.type === 'player_action') {
                    processAction(data.action, data.id, data.extra);
                }
            }
            
            if (data.type === 'sync_lobby') {
                gameState.players = data.players;
                if (!document.getElementById('lobby-screen').classList.contains('hidden')) {
                    updateLobbyUI();
                }
            }
            if (data.type === 'sync_state') renderState(data.state);
        });
    </script>
</body>
</html>