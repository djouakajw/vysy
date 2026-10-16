// clone.js - Script Node.js pour cloner votre compte WhatsApp
// Bibliothèque : @open-wa/wa-automate
// Installation : npm install @open-wa/wa-automate

const { create, Client } = require('@open-wa/wa-automate');

// Délai aléatoire entre 2 et 5 secondes pour éviter le bannissement
function randomDelay(min = 2000, max = 5000) {
  const ms = Math.floor(Math.random() * (max - min + 1)) + min;
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// Texte de présentation de vos services (modifiable ici)
const INFO_TEXT =
  "Bonjour ! 👋 Je suis l'assistant automatisé de ce compte.\n\n" +
  "Mes services :\n" +
  "• Réponse automatique 24/7\n" +
  "• Qualification de leads\n" +
  "• Prise de rendez-vous\n\n" +
  "Envoyez-moi un message pour en savoir plus !";

async function start() {
  const client = await create({
    sessionId: 'mon_clone', // Session multi-appareils
    multiDevice: true,
    authTimeout: 60,
    blockCrashLogs: true,
    disableSpins: true,
    headless: true,
    logConsole: false,
    popup: false,
  });

  console.log('✅ Session "mon_clone" démarrée avec succès.');

  // Écouter tous les messages entrants
  client.onMessage(async (message) => {
    // Ignorer les messages envoyés par nous-mêmes
    if (message.fromMe) return;

    const text = (message.body || '').toLowerCase().trim();

    // Mot-clé !info
    if (text.includes('!info')) {
      await randomDelay(2000, 5000); // Délai aléatoire anti-ban
      await client.sendText(message.from, INFO_TEXT);
      console.log(`📩 Réponse !info envoyée à ${message.from}`);
    }
  });
}

start().catch((err) => {
  console.error('❌ Erreur au démarrage :', err);
  process.exit(1);
});
