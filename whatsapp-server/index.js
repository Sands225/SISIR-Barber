require('dotenv').config();
const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3000;
const LARAVEL_WEBHOOK = process.env.LARAVEL_WEBHOOK_URL || 'http://127.0.0.1:8000/api/webhook/whatsapp';

// Initialize WhatsApp Client with local authentication
const client = new Client({
    authStrategy: new LocalAuth(),
    webVersionCache: {
        type: 'remote',
        remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html',
    },
    puppeteer: {
        executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    }
});

client.on('qr', (qr) => {
    console.log('Scan this QR code in WhatsApp to log in:');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp Web Client is ready!');
});

client.on('authenticated', () => {
    console.log('✅ Authenticated successfully.');
});

client.on('auth_failure', msg => {
    console.error('❌ Authentication failure:', msg);
});

// Listen for incoming messages and forward to Laravel
client.on('message', async (message) => {
    // Only process text messages from users (ignore groups for now)
    if (message.from.endsWith('@g.us')) return;

    // Normalize phone number (strip @c.us or @lid)
    const phone = message.from.split('@')[0];

    console.log(`[INCOMING] From ${phone}: ${message.body}`);

    // --- AUTO-REPLY UNTUK PERCOBAAN ---
    console.log(`[TESTING] Menunggu 5 detik sebelum membalas otomatis...`);
    setTimeout(async () => {
        try {
            // await message.reply("Halo! Ini adalah balasan otomatis statis dari SISIR Botin. (Pesan Anda: " + message.body + ")");
            console.log(`[OUTGOING] Balasan otomatis terkirim ke ${phone}`);
        } catch (err) {
            console.error(`[OUTGOING ERROR] Gagal mengirim balasan: ${err.message}`);
        }
    }, 5000); // 5000 ms = 5 detik
    // ----------------------------------

    try {
        await axios.post(LARAVEL_WEBHOOK, {
            from: message.from, // Send raw ID to Laravel to preserve @lid or @c.us
            text: message.body,
            id: message.id.id
        }, {
            headers: {
                'Content-Type': 'application/json'
            }
        });
        console.log(`[WEBHOOK] Forwarded to Laravel successfully.`);
    } catch (err) {
        console.error(`[WEBHOOK ERROR] Failed to forward to Laravel: ${err.message}`);
    }
});

client.initialize();

// Express Endpoint for Laravel to send messages
app.post('/send', async (req, res) => {
    const { to, message } = req.body;

    if (!to || !message) {
        return res.status(400).json({ success: false, error: 'Missing "to" or "message" fields.' });
    }

    try {
        // If 'to' already contains '@', use it directly. Otherwise, try to resolve it.
        let chatId = to;
        if (!to.includes('@')) {
            // Check if it's an LID (usually 14+ digits and starts with specific country codes, but let's just default to @c.us)
            chatId = `${to}@c.us`;

            // Try to resolve using whatsapp-web.js internal method if possible
            const contactId = await client.getNumberId(to);
            if (contactId) {
                chatId = contactId._serialized;
            }
        }

        const response = await client.sendMessage(chatId, message);

        console.log(`[OUTGOING] Sent to ${chatId}: ${message.substring(0, 50)}...`);
        return res.status(200).json({ success: true, response });
    } catch (err) {
        console.error(`[SEND ERROR] Failed to send to ${to}: ${err.message}`);
        return res.status(500).json({ success: false, error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`🚀 Node.js WhatsApp Server running on port ${PORT}`);
});
