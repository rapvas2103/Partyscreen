// Initialisation du serveur WebSocket sur le port 3000
const io = require('socket.io')(3000, {
    cors: {
        origin: "*", // En local, on autorise toutes les origines
        methods: ["GET", "POST"]
    }
});

console.log("=========================================");
console.log("🚀 Serveur PartyScreen en ligne (Port 3000)");
console.log("=========================================\n");

// Écoute des connexions entrantes
io.on('connection', (socket) => {
    console.log(`[+] Nouveau joueur connecté (ID: ${socket.id})`);

    // 1. Quand un joueur rejoint un salon spécifique
    socket.on('rejoindreSalon', (codeSalon) => {
        socket.join(codeSalon);
        console.log(`[ROOM] Le joueur ${socket.id} a rejoint le salon : ${codeSalon}`);
    });

    // 2. Quand un joueur fait une action (ex: appuie sur une touche)
    socket.on('actionJeu', (data) => {
        // data contient { room: '1234', touche: 'A', joueur: 1 }
        // On transfère instantanément l'action aux AUTRES joueurs du salon
        socket.to(data.room).emit('actionRecue', data);
        console.log(`[JEU] Action relayée dans le salon ${data.room}`);
    });

    // 3. Quand un joueur ferme sa page
    socket.on('disconnect', () => {
        console.log(`[-] Joueur déconnecté (ID: ${socket.id})`);
    });
});