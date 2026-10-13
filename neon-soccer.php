<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neon Bumper Soccer - PartyScreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        body { font-family: 'Orbitron', sans-serif; background: #0a0a1a; color: white; min-height: 100vh; margin: 0; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .glass-panel { background: rgba(20, 20, 30, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 0 30px rgba(0, 255, 255, 0.1); }
        .btn-neon-blue { background: transparent; border: 2px solid #00f3ff; color: #00f3ff; text-shadow: 0 0 5px #00f3ff; box-shadow: inset 0 0 10px rgba(0, 243, 255, 0.2), 0 0 10px rgba(0, 243, 255, 0.2); transition: 0.2s; }
        .btn-neon-blue:hover { background: #00f3ff; color: #000; box-shadow: 0 0 20px #00f3ff; }
        .btn-neon-red { background: transparent; border: 2px solid #ff003c; color: #ff003c; text-shadow: 0 0 5px #ff003c; box-shadow: inset 0 0 10px rgba(255, 0, 60, 0.2), 0 0 10px rgba(255, 0, 60, 0.2); transition: 0.2s; }
        .btn-neon-red:hover { background: #ff003c; color: #000; box-shadow: 0 0 20px #ff003c; }

        .btn-home { position: fixed; top: 15px; left: 15px; z-index: 9999; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); color: white; padding: 10px 18px; border-radius: 30px; text-decoration: none; font-family: sans-serif; font-weight: bold; border: 1px solid rgba(255, 255, 255, 0.2); display: flex; align-items: center; gap: 8px; transition: 0.3s; font-size: 14px; }
        .btn-home:hover { background: rgba(255, 255, 255, 0.2); }

        canvas { background: #050510; border-radius: 10px; box-shadow: 0 0 40px rgba(0, 243, 255, 0.1); border: 2px solid rgba(255,255,255,0.05); }

        .countdown-text { font-size: 8rem; font-weight: 900; text-shadow: 0 0 30px rgba(255,255,255,0.8); animation: pulse 1s infinite; }
        @keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.8; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

    <a href="index.php" class="btn-home"><i class="fa-solid fa-house"></i> Accueil</a>

    <!-- ECRAN D'ACCUEIL -->
    <div id="setup-screen" class="glass-panel p-8 rounded-3xl w-full max-w-md text-center">
        <h1 class="text-3xl font-black mb-2 text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500">NEON SOCCER</h1>
        <p class="text-gray-400 text-xs mb-8 font-sans">Bumper Cars Edition</p>

        <div class="mb-6 p-4 bg-white/5 border border-white/10 rounded-xl font-sans text-sm">
            <h3 class="font-bold text-gray-300 mb-2">Contrôles</h3>
            <p><span class="text-cyan-400 font-bold">Joueur 1 (Bleu)</span> : Z Q S D</p>
            <p><span class="text-pink-500 font-bold">Joueur 2 (Rouge)</span> : Flèches directionnelles</p>
        </div>

        <button onclick="startLocal()" class="w-full py-3 mb-6 rounded-xl font-bold font-sans bg-white/10 hover:bg-white/20 transition">Lancer en Local (Sur ce PC)</button>

        <div class="pt-4 border-t border-white/10">
            <h3 class="font-bold text-gray-300 mb-3 font-sans text-sm">Multijoueur en ligne</h3>
            <input type="text" id="room-code" placeholder="Code salon" class="w-full bg-black/60 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none mb-3 text-center tracking-widest font-bold">
            <div class="flex gap-3">
                <button onclick="createRoom()" class="flex-1 btn-neon-blue py-3 rounded-xl font-bold font-sans text-sm">Créer (Hôte)</button>
                <button onclick="joinRoom()" class="flex-1 btn-neon-red py-3 rounded-xl font-bold font-sans text-sm">Rejoindre</button>
            </div>
        </div>
    </div>

    <!-- ECRAN DE JEU -->
    <div id="game-screen" class="hidden flex-col items-center">
        <div class="flex justify-between w-full max-w-[800px] mb-2 px-4 items-center">
            <div class="text-4xl font-black text-cyan-400" id="score-1">0</div>
            
            <div class="flex flex-col items-center">
                <div id="status-badge" class="px-4 py-1 rounded-full bg-white/10 text-xs font-sans tracking-widest uppercase mb-1">Mode Local</div>
                <div id="timer-display" class="text-2xl font-bold tracking-widest text-white font-sans">02:00</div>
            </div>

            <div class="text-4xl font-black text-pink-500" id="score-2">0</div>
        </div>
        
        <div class="relative">
            <!-- Calque d'attente joueur 2 -->
            <div id="waiting-overlay" class="absolute inset-0 flex flex-col items-center justify-center hidden z-40 bg-black/70 rounded-[10px] border border-white/10">
                <i class="fa-solid fa-spinner fa-spin text-4xl text-yellow-400 mb-4"></i>
                <h2 class="text-3xl font-bold text-yellow-400 animate-pulse font-sans">En attente du Joueur 2...</h2>
                <p class="text-gray-400 mt-2 font-sans">Donne le code du salon à ton ami.</p>
            </div>

            <!-- Calque du décompte par dessus le canvas -->
            <div id="countdown-overlay" class="absolute inset-0 flex items-center justify-center hidden z-50 bg-black/50 rounded-[10px]">
                <div id="countdown-number" class="countdown-text">3</div>
            </div>
            
            <canvas id="gameCanvas" width="800" height="500"></canvas>
        </div>
    </div>

    <!-- ECRAN DE RESULTAT (FIN DU MATCH) -->
    <div id="result-screen" class="glass-panel p-8 rounded-3xl w-full max-w-lg text-center hidden flex-col items-center mt-8">
        <div class="text-5xl mb-4"><i class="fa-solid fa-trophy text-yellow-400"></i></div>
        <h2 class="text-4xl font-black mb-2 text-white" id="winner-title">Fin du Match !</h2>
        <p class="text-gray-300 font-sans mb-8" id="winner-desc"></p>

        <div class="flex gap-6 mb-8 text-3xl font-black">
            <span class="text-cyan-400" id="final-score-1">0</span>
            <span class="text-white/30">-</span>
            <span class="text-pink-500" id="final-score-2">0</span>
        </div>

        <button onclick="window.location.reload()" class="w-full bg-white/10 hover:bg-white/20 py-4 rounded-xl font-bold font-sans text-white transition">
            <i class="fa-solid fa-rotate-right"></i> Revenir au menu
        </button>
    </div>

    <script>
        const socket = io('http://localhost:3000');
        let isOnline = false;
        let myRole = 0; 
        let currentRoom = "";
        
        let gameState = 'menu'; // menu, waiting, countdown, playing, ended
        let timeLeft = 120; // 2 minutes
        let gameTimer = null;

        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        
        const GAME_WIDTH = canvas.width;
        const GAME_HEIGHT = canvas.height;
        const GOAL_WIDTH = 140;
        const GOAL_Y_START = (GAME_HEIGHT - GOAL_WIDTH) / 2;

        let score = { p1: 0, p2: 0 };
        
        let car1 = { x: 100, y: 250, vx: 0, vy: 0, radius: 20, color: '#00f3ff', speed: 0.15, friction: 0.95, maxSpeed: 4.5 };
        let car2 = { x: 700, y: 250, vx: 0, vy: 0, radius: 20, color: '#ff003c', speed: 0.15, friction: 0.95, maxSpeed: 4.5 };
        let ball = { x: 400, y: 250, vx: 0, vy: 0, radius: 15, color: '#ffffff', friction: 0.97, maxSpeed: 10 };

        let keys = { z:false, q:false, s:false, d:false, ArrowUp:false, ArrowLeft:false, ArrowDown:false, ArrowRight:false };
        let remoteKeys = { up:false, left:false, down:false, right:false };

        function startLocal() {
            isOnline = false; myRole = 0;
            showGame("MODE LOCAL");
            resetPositions();
            gameLoop();
            startCountdown();
        }

        function createRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 1; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            
            showGame(`SALON : ${code} (HÔTE)`);
            document.getElementById('waiting-overlay').classList.remove('hidden');
            gameState = 'waiting';
            
            resetPositions();
            draw(); // Dessine le terrain une fois en arrière-plan
        }

        function joinRoom() {
            const code = document.getElementById('room-code').value.trim().toUpperCase();
            if(!code) return;
            isOnline = true; myRole = 2; currentRoom = code;
            socket.emit('rejoindreSalon', code);
            
            socket.emit('actionJeu', { room: currentRoom, type: 'j2_ready' });
            
            showGame(`SALON : ${code} (CLIENT)`);
            requestAnimationFrame(renderOnlyLoop); 
        }

        function showGame(text) {
            document.getElementById('setup-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('hidden');
            document.getElementById('game-screen').classList.add('flex');
            document.getElementById('status-badge').innerText = text;
        }

        function startCountdown() {
            gameState = 'countdown';
            document.getElementById('countdown-overlay').classList.remove('hidden');
            
            let count = 3;
            document.getElementById('countdown-number').innerText = count;

            let countInterval = setInterval(() => {
                count--;
                if (count > 0) {
                    document.getElementById('countdown-number').innerText = count;
                } else if (count === 0) {
                    document.getElementById('countdown-number').innerText = "GO !";
                } else {
                    clearInterval(countInterval);
                    document.getElementById('countdown-overlay').classList.add('hidden');
                    gameState = 'playing';
                    if (myRole === 0 || myRole === 1) startGameTimer(); 
                }
            }, 1000);
        }

        function startGameTimer() {
            timeLeft = 120;
            updateTimerUI();
            
            gameTimer = setInterval(() => {
                if (gameState === 'playing') {
                    timeLeft--;
                    updateTimerUI();
                    
                    if(isOnline && myRole === 1) {
                        socket.emit('actionJeu', { room: currentRoom, type: 'timerSync', time: timeLeft });
                    }

                    if (timeLeft <= 0) {
                        clearInterval(gameTimer);
                        triggerEndGame();
                    }
                }
            }, 1000);
        }

        function updateTimerUI() {
            let m = Math.floor(timeLeft / 60);
            let s = timeLeft % 60;
            document.getElementById('timer-display').innerText = `0${m}:${s < 10 ? '0'+s : s}`;
            if(timeLeft <= 10) document.getElementById('timer-display').classList.add('text-red-500', 'animate-pulse');
        }

        function triggerEndGame() {
            gameState = 'ended';
            if(isOnline && myRole === 1) {
                socket.emit('actionJeu', { room: currentRoom, type: 'endGame' });
            }
            showResults();
        }

        function showResults() {
            document.getElementById('game-screen').classList.add('hidden');
            document.getElementById('game-screen').classList.remove('flex');
            document.getElementById('result-screen').classList.remove('hidden');
            document.getElementById('result-screen').classList.add('flex');

            document.getElementById('final-score-1').innerText = score.p1;
            document.getElementById('final-score-2').innerText = score.p2;

            const title = document.getElementById('winner-title');
            const desc = document.getElementById('winner-desc');

            if (score.p1 > score.p2) {
                title.innerText = "VICTOIRE BLEU !";
                title.className = "text-4xl font-black mb-2 text-cyan-400";
                desc.innerText = "Le Joueur 1 a dominé le terrain.";
            } else if (score.p2 > score.p1) {
                title.innerText = "VICTOIRE ROUGE !";
                title.className = "text-4xl font-black mb-2 text-pink-500";
                desc.innerText = "Le Joueur 2 a retourné le match.";
            } else {
                title.innerText = "ÉGALITÉ !";
                title.className = "text-4xl font-black mb-2 text-white";
                desc.innerText = "Un match serré jusqu'à la dernière seconde.";
            }
        }

        window.addEventListener('keydown', (e) => {
            if(keys.hasOwnProperty(e.key) || keys.hasOwnProperty(e.key.toLowerCase())) {
                keys[e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'ArrowLeft' || e.key === 'ArrowRight' ? e.key : e.key.toLowerCase()] = true;
                sendInputsIfClient();
            }
        });
        window.addEventListener('keyup', (e) => {
            if(keys.hasOwnProperty(e.key) || keys.hasOwnProperty(e.key.toLowerCase())) {
                keys[e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'ArrowLeft' || e.key === 'ArrowRight' ? e.key : e.key.toLowerCase()] = false;
                sendInputsIfClient();
            }
        });

        function sendInputsIfClient() {
            if(isOnline && myRole === 2) {
                socket.emit('actionJeu', { 
                    room: currentRoom, 
                    type: 'input', 
                    keys: { up: keys.ArrowUp, down: keys.ArrowDown, left: keys.ArrowLeft, right: keys.ArrowRight }
                });
            }
        }

        function limitVelocity(obj) {
            let speed = Math.sqrt(obj.vx * obj.vx + obj.vy * obj.vy);
            if (speed > obj.maxSpeed) {
                let ratio = obj.maxSpeed / speed;
                obj.vx *= ratio;
                obj.vy *= ratio;
            }
        }

        function updatePhysics() {
            if (gameState === 'playing') {
                if(keys.z) car1.vy -= car1.speed;
                if(keys.s) car1.vy += car1.speed;
                if(keys.q) car1.vx -= car1.speed;
                if(keys.d) car1.vx += car1.speed;

                let j2_up = isOnline ? remoteKeys.up : keys.ArrowUp;
                let j2_down = isOnline ? remoteKeys.down : keys.ArrowDown;
                let j2_left = isOnline ? remoteKeys.left : keys.ArrowLeft;
                let j2_right = isOnline ? remoteKeys.right : keys.ArrowRight;

                if(j2_up) car2.vy -= car2.speed;
                if(j2_down) car2.vy += car2.speed;
                if(j2_left) car2.vx -= car2.speed;
                if(j2_right) car2.vx += car2.speed;
            }

            limitVelocity(car1);
            limitVelocity(car2);
            limitVelocity(ball);

            car1.vx *= car1.friction; car1.vy *= car1.friction;
            car2.vx *= car2.friction; car2.vy *= car2.friction;
            ball.vx *= ball.friction; ball.vy *= ball.friction;

            car1.x += car1.vx; car1.y += car1.vy;
            car2.x += car2.vx; car2.y += car2.vy;
            ball.x += ball.vx; ball.y += ball.vy;

            checkWallCollision(car1);
            checkWallCollision(car2);
            checkBallWallCollision();

            checkCircleCollision(car1, ball, 1.2); 
            checkCircleCollision(car2, ball, 1.2);
            checkCircleCollision(car1, car2, 0.8); 
        }

        function checkWallCollision(obj) {
            if(obj.x - obj.radius < 0) { obj.x = obj.radius; obj.vx *= -0.5; }
            if(obj.x + obj.radius > GAME_WIDTH) { obj.x = GAME_WIDTH - obj.radius; obj.vx *= -0.5; }
            if(obj.y - obj.radius < 0) { obj.y = obj.radius; obj.vy *= -0.5; }
            if(obj.y + obj.radius > GAME_HEIGHT) { obj.y = GAME_HEIGHT - obj.radius; obj.vy *= -0.5; }
        }

        function checkBallWallCollision() {
            if(ball.y - ball.radius < 0) { ball.y = ball.radius; ball.vy *= -0.8; }
            if(ball.y + ball.radius > GAME_HEIGHT) { ball.y = GAME_HEIGHT - ball.radius; ball.vy *= -0.8; }

            if(ball.x - ball.radius < 0) {
                if(ball.y > GOAL_Y_START && ball.y < GOAL_Y_START + GOAL_WIDTH) {
                    score.p2++; document.getElementById('score-2').innerText = score.p2; goalScored();
                } else {
                    ball.x = ball.radius; ball.vx *= -0.8;
                }
            }
            if(ball.x + ball.radius > GAME_WIDTH) {
                if(ball.y > GOAL_Y_START && ball.y < GOAL_Y_START + GOAL_WIDTH) {
                    score.p1++; document.getElementById('score-1').innerText = score.p1; goalScored();
                } else {
                    ball.x = GAME_WIDTH - ball.radius; ball.vx *= -0.8;
                }
            }
        }

        function checkCircleCollision(obj1, obj2, restitution) {
            let dx = obj2.x - obj1.x;
            let dy = obj2.y - obj1.y;
            let distance = Math.sqrt(dx * dx + dy * dy);
            let minDist = obj1.radius + obj2.radius;

            if (distance < minDist) {
                let overlap = minDist - distance;
                let nx = dx / distance;
                let ny = dy / distance;
                
                obj1.x -= nx * (overlap/2); obj1.y -= ny * (overlap/2);
                obj2.x += nx * (overlap/2); obj2.y += ny * (overlap/2);

                let p = 2 * (obj1.vx * nx + obj1.vy * ny - obj2.vx * nx - obj2.vy * ny) / (2);
                obj1.vx = obj1.vx - p * nx * restitution;
                obj1.vy = obj1.vy - p * ny * restitution;
                obj2.vx = obj2.vx + p * nx * restitution;
                obj2.vy = obj2.vy + p * ny * restitution;
            }
        }

        function goalScored() {
            if(isOnline && myRole === 1) broadcastState();
            setTimeout(resetPositions, 1000); 
            ball.x = -100; ball.y = -100; 
            ball.vx = 0; ball.vy = 0;
            car1.vx = 0; car1.vy = 0;
            car2.vx = 0; car2.vy = 0;
        }

        function resetPositions() {
            car1.x = 100; car1.y = 250; car1.vx = 0; car1.vy = 0;
            car2.x = 700; car2.y = 250; car2.vx = 0; car2.vy = 0;
            ball.x = 400; ball.y = 250; ball.vx = 0; ball.vy = 0;
        }

        function draw() {
            ctx.fillStyle = 'rgba(5, 5, 16, 0.5)';
            ctx.fillRect(0, 0, GAME_WIDTH, GAME_HEIGHT);

            ctx.strokeStyle = 'rgba(255,255,255,0.1)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(GAME_WIDTH/2, 0); ctx.lineTo(GAME_WIDTH/2, GAME_HEIGHT); 
            ctx.stroke();
            ctx.beginPath();
            ctx.arc(GAME_WIDTH/2, GAME_HEIGHT/2, 60, 0, Math.PI*2); 
            ctx.stroke();

            ctx.fillStyle = 'rgba(0, 243, 255, 0.2)';
            ctx.fillRect(0, GOAL_Y_START, 10, GOAL_WIDTH);
            ctx.fillStyle = 'rgba(255, 0, 60, 0.2)';
            ctx.fillRect(GAME_WIDTH-10, GOAL_Y_START, 10, GOAL_WIDTH);

            drawCircle(car1.x, car1.y, car1.radius, car1.color);
            drawCircle(car2.x, car2.y, car2.radius, car2.color);
            drawCircle(ball.x, ball.y, ball.radius, ball.color, true);
        }

        function drawCircle(x, y, radius, color, isBall = false) {
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.fillStyle = color;
            ctx.shadowBlur = isBall ? 25 : 15;
            ctx.shadowColor = color;
            ctx.fill();
            ctx.closePath();
            ctx.shadowBlur = 0; 
        }

        function gameLoop() {
            updatePhysics();
            draw();
            if(isOnline && myRole === 1) broadcastState();
            requestAnimationFrame(gameLoop);
        }

        function renderOnlyLoop() {
            draw();
            requestAnimationFrame(renderOnlyLoop);
        }

        function broadcastState() {
            socket.emit('actionJeu', {
                room: currentRoom,
                type: 'sync',
                state: {
                    c1x: car1.x, c1y: car1.y,
                    c2x: car2.x, c2y: car2.y,
                    bx: ball.x, by: ball.y,
                    s1: score.p1, s2: score.p2
                }
            });
        }

        socket.on('actionRecue', (data) => {
            if (data.type === 'j2_ready' && myRole === 1) {
                document.getElementById('waiting-overlay').classList.add('hidden');
                gameLoop();
                startCountdown();
                socket.emit('actionJeu', { room: currentRoom, type: 'start_countdown' });
            }

            if (data.type === 'start_countdown' && myRole === 2) {
                startCountdown();
            }

            if (data.type === 'timerSync' && myRole === 2) {
                timeLeft = data.time;
                updateTimerUI();
            }

            if (data.type === 'endGame' && myRole === 2) {
                gameState = 'ended';
                showResults();
            }

            if (data.type === 'input' && myRole === 1) {
                remoteKeys = data.keys;
            }

            if (data.type === 'sync' && myRole === 2) {
                car1.x = data.state.c1x; car1.y = data.state.c1y;
                car2.x = data.state.c2x; car2.y = data.state.c2y;
                ball.x = data.state.bx; ball.y = data.state.by;
                
                if (score.p1 !== data.state.s1 || score.p2 !== data.state.s2) {
                    score.p1 = data.state.s1; score.p2 = data.state.s2;
                    document.getElementById('score-1').innerText = score.p1;
                    document.getElementById('score-2').innerText = score.p2;
                }
            }
        });
    </script>
</body>
</html>