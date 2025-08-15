<?php
define('BOT_TOKEN', 'توکن ربات شما');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('BOT_USERNAME', '@آیدی ربات شما');
define('BOT_NAME', 'اسم ربات شما');

function makeRequest($method, $data = []) {
 $url = API_URL . $method;
 
 $options = [
 'http' => [
 'header' => "Content-type: application/x-www-form-urlencoded\r\n",
 'method' => 'POST',
 'content' => http_build_query($data),
 'timeout' => 30
 ]
 ];
 
 $context = stream_context_create($options);
 $result = @file_get_contents($url, false, $context);
 
 if ($result === FALSE) {
 return ['ok' => false, 'error' => 'Request failed'];
 }
 
 $response = json_decode($result, true);
 
 if (!$response || !isset($response['ok'])) {
 return ['ok' => false, 'error' => 'Invalid response'];
 }
 
 return $response;
}

function getBotInfo() {
 return makeRequest('getMe');
}

function getUpdates($offset = 0, $limit = 100) {
 return makeRequest('getUpdates', [
 'offset' => $offset,
 'limit' => $limit,
 'timeout' => 0
 ]);
}

function sendMessage($chatId, $text) {
 return makeRequest('sendMessage', [
 'chat_id' => $chatId,
 'text' => $text,
 'parse_mode' => 'HTML'
 ]);
}

function formatUserInfo($user) {
 $info = [];
 $info[] = "👤 <b>نام:</b> " . htmlspecialchars($user['first_name'] ?? 'نامشخص');
 
 if (isset($user['last_name'])) {
 $info[] = "👤 <b>نام خانوادگی:</b> " . htmlspecialchars($user['last_name']);
 }
 
 if (isset($user['username'])) {
 $info[] = "🔗 <b>نام کاربری:</b> @" . htmlspecialchars($user['username']);
 }
 
 $info[] = "🆔 <b>Chat ID:</b> <code>" . $user['id'] . "</code>";
 $info[] = "🤖 <b>ربات:</b> " . ($user['is_bot'] ? 'بله' : 'خیر');
 
 if (isset($user['language_code'])) {
 $info[] = "🌐 <b>زبان:</b> " . strtoupper($user['language_code']);
 }
 
 return implode("\n", $info);
}

function formatChatInfo($chat) {
 $info = [];
 $info[] = "💬 <b>نوع چت:</b> " . ucfirst($chat['type']);
 $info[] = "🆔 <b>Chat ID:</b> <code>" . $chat['id'] . "</code>";
 
 if (isset($chat['title'])) {
 $info[] = "📝 <b>عنوان:</b> " . htmlspecialchars($chat['title']);
 }
 
 if (isset($chat['username'])) {
 $info[] = "🔗 <b>نام کاربری:</b> @" . htmlspecialchars($chat['username']);
 }
 
 if (isset($chat['description'])) {
 $info[] = "📄 <b>توضیحات:</b> " . htmlspecialchars(substr($chat['description'], 0, 100));
 }
 
 if (isset($chat['member_count'])) {
 $info[] = "👥 <b>تعداد اعضا:</b> " . number_format($chat['member_count']);
 }
 
 return implode("\n", $info);
}

$action = $_GET['action'] ?? '';
$chatId = $_GET['chat_id'] ?? '';
$message = '';
$messageType = '';

if ($action === 'send_test' && $chatId) {
 $testMessage = "🎉 <b>تست موفقیت‌آمیز!</b>\n\n";
 $testMessage .= "✅ ربات " . BOT_NAME . " با موفقیت به شما متصل شد.\n";
 $testMessage .= "🆔 Chat ID شما: <code>$chatId</code>\n\n";
 $testMessage .= "🤖 حالا می‌توانید از ربات استفاده کنید!";
 
 $result = sendMessage($chatId, $testMessage);
 
 if ($result['ok']) {
 $message = "✅ پیام تست با موفقیت ارسال شد!";
 $messageType = 'success';
 } else {
 $message = "❌ خطا در ارسال پیام: " . ($result['description'] ?? 'خطای نامشخص');
 $messageType = 'error';
 }
}

$botInfo = getBotInfo();
$updates = getUpdates();

?>
<!DOCTYPE html>
<html dir="rtl">
<head>
 <meta charset="UTF-8">
 <title>🔍 Chat ID Finder - <?= BOT_NAME ?></title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 <style>
 * {
 margin: 0;
 padding: 0;
 box-sizing: border-box;
 }
 
 :root {
 --bg-primary: #0a0a0a;
 --bg-secondary: #111111;
 --bg-tertiary: #1a1a1a;
 --bg-card: #1e1e1e;
 --neon-orange: #ff6b35;
 --neon-purple: #8b5cf6;
 --neon-orange-glow: rgba(255, 107, 53, 0.3);
 --neon-purple-glow: rgba(139, 92, 246, 0.3);
 --text-primary: #ffffff;
 --text-secondary: #b0b0b0;
 --text-muted: #666666;
 --border-color: #2a2a2a;
 --success: #10b981;
 --error: #ef4444;
 --warning: #f59e0b;
 --info: #3b82f6;
 }
 
 body {
 font-family: 'Inter', sans-serif;
 background: var(--bg-primary);
 background-image: 
 radial-gradient(circle at 20% 50%, var(--neon-purple-glow) 0%, transparent 50%),
 radial-gradient(circle at 80% 20%, var(--neon-orange-glow) 0%, transparent 50%),
 radial-gradient(circle at 40% 80%, var(--neon-purple-glow) 0%, transparent 50%);
 min-height: 100vh;
 color: var(--text-primary);
 line-height: 1.6;
 overflow-x: hidden;
 }
 
 body::before {
 content: '';
 position: fixed;
 top: 0;
 left: 0;
 right: 0;
 bottom: 0;
 background: 
 linear-gradient(45deg, transparent 30%, var(--neon-orange-glow) 50%, transparent 70%),
 linear-gradient(-45deg, transparent 30%, var(--neon-purple-glow) 50%, transparent 70%);
 opacity: 0.1;
 z-index: -1;
 animation: backgroundMove 20s ease-in-out infinite;
 }
 
 @keyframes backgroundMove {
 0%, 100% { transform: rotate(0deg) scale(1); }
 50% { transform: rotate(180deg) scale(1.1); }
 }
 
 @keyframes glow {
 0%, 100% { box-shadow: 0 0 20px var(--neon-orange-glow), 0 0 40px var(--neon-orange-glow); }
 50% { box-shadow: 0 0 30px var(--neon-purple-glow), 0 0 60px var(--neon-purple-glow); }
 }
 
 @keyframes pulse {
 0%, 100% { opacity: 1; }
 50% { opacity: 0.7; }
 }
 
 @keyframes slideIn {
 from { transform: translateY(20px); opacity: 0; }
 to { transform: translateY(0); opacity: 1; }
 }
 
 .container {
 max-width: 1200px;
 margin: 0 auto;
 padding: 20px;
 animation: slideIn 0.8s ease-out;
 }
 
 .header {
 text-align: center;
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 padding: 40px;
 border-radius: 20px;
 margin-bottom: 30px;
 position: relative;
 overflow: hidden;
 backdrop-filter: blur(20px);
 }
 
 .header::before {
 content: '';
 position: absolute;
 top: 0;
 left: -100%;
 width: 100%;
 height: 2px;
 background: linear-gradient(90deg, transparent, var(--neon-orange), var(--neon-purple), transparent);
 animation: borderMove 3s linear infinite;
 }
 
 @keyframes borderMove {
 0% { left: -100%; }
 100% { left: 100%; }
 }
 
 .header h1 {
 font-size: 3em;
 font-weight: 700;
 margin-bottom: 15px;
 background: linear-gradient(135deg, var(--neon-orange), var(--neon-purple));
 -webkit-background-clip: text;
 -webkit-text-fill-color: transparent;
 background-clip: text;
 text-shadow: 0 0 30px var(--neon-orange-glow);
 animation: glow 4s ease-in-out infinite;
 }
 
 .header p {
 color: var(--text-secondary);
 font-size: 1.2em;
 font-weight: 400;
 }
 
 .bot-info {
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 padding: 30px;
 border-radius: 15px;
 margin-bottom: 30px;
 position: relative;
 overflow: hidden;
 }
 
 .bot-info::after {
 content: '';
 position: absolute;
 top: 0;
 right: 0;
 width: 100px;
 height: 100px;
 background: radial-gradient(circle, var(--neon-purple-glow), transparent);
 opacity: 0.5;
 }
 
 .bot-info h3 {
 color: var(--text-primary);
 margin-bottom: 20px;
 display: flex;
 align-items: center;
 gap: 15px;
 font-size: 1.5em;
 font-weight: 600;
 }
 
 .info-grid {
 display: grid;
 grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
 gap: 20px;
 }
 
 .info-item {
 background: var(--bg-secondary);
 border: 1px solid var(--border-color);
 padding: 20px;
 border-radius: 12px;
 border-left: 4px solid var(--neon-orange);
 transition: all 0.3s ease;
 position: relative;
 }
 
 .info-item:hover {
 border-left-color: var(--neon-purple);
 transform: translateX(5px);
 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
 }
 
 .info-item strong {
 color: var(--neon-orange);
 font-weight: 600;
 }
 
 .updates-section {
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 padding: 30px;
 border-radius: 15px;
 margin-bottom: 30px;
 }
 
 .updates-section h3 {
 color: var(--text-primary);
 margin-bottom: 25px;
 display: flex;
 align-items: center;
 gap: 15px;
 font-size: 1.5em;
 font-weight: 600;
 }
 
 .user-card {
 background: var(--bg-secondary);
 border: 1px solid var(--border-color);
 border-radius: 15px;
 padding: 25px;
 margin-bottom: 20px;
 transition: all 0.4s ease;
 position: relative;
 overflow: hidden;
 }
 
 .user-card::before {
 content: '';
 position: absolute;
 top: 0;
 left: -100%;
 width: 100%;
 height: 100%;
 background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
 transition: left 0.6s ease;
 }
 
 .user-card:hover {
 transform: translateY(-5px);
 border-color: var(--neon-orange);
 box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px var(--neon-orange-glow);
 }
 
 .user-card:hover::before {
 left: 100%;
 }
 
 .user-header {
 display: flex;
 justify-content: space-between;
 align-items: flex-start;
 margin-bottom: 20px;
 flex-wrap: wrap;
 gap: 15px;
 }
 
 .user-details {
 flex: 1;
 }
 
 .user-details h4 {
 color: var(--text-primary);
 margin-bottom: 15px;
 font-size: 1.3em;
 font-weight: 600;
 }
 
 .user-actions {
 display: flex;
 gap: 12px;
 flex-wrap: wrap;
 }
 
 .chat-id {
 background: linear-gradient(135deg, var(--neon-orange), var(--neon-purple));
 color: var(--text-primary);
 padding: 12px 20px;
 border-radius: 25px;
 font-family: 'JetBrains Mono', monospace;
 font-weight: 600;
 font-size: 14px;
 cursor: pointer;
 transition: all 0.3s ease;
 user-select: all;
 position: relative;
 overflow: hidden;
 }
 
 .chat-id::before {
 content: '';
 position: absolute;
 top: 0;
 left: -100%;
 width: 100%;
 height: 100%;
 background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
 transition: left 0.5s ease;
 }
 
 .chat-id:hover {
 transform: scale(1.05);
 box-shadow: 0 10px 25px var(--neon-orange-glow);
 }
 
 .chat-id:hover::before {
 left: 100%;
 }
 
 .btn {
 background: linear-gradient(135deg, var(--neon-purple), var(--neon-orange));
 color: var(--text-primary);
 border: none;
 padding: 12px 24px;
 border-radius: 10px;
 cursor: pointer;
 text-decoration: none;
 display: inline-block;
 transition: all 0.3s ease;
 font-size: 14px;
 font-weight: 600;
 position: relative;
 overflow: hidden;
 }
 
 .btn::before {
 content: '';
 position: absolute;
 top: 0;
 left: -100%;
 width: 100%;
 height: 100%;
 background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
 transition: left 0.5s ease;
 }
 
 .btn:hover {
 transform: translateY(-2px);
 box-shadow: 0 15px 30px var(--neon-purple-glow);
 }
 
 .btn:hover::before {
 left: 100%;
 }
 
 .btn-copy {
 background: linear-gradient(135deg, var(--info), var(--neon-purple));
 padding: 10px 16px;
 font-size: 12px;
 }
 
 .refresh-btn {
 background: linear-gradient(135deg, var(--warning), var(--neon-orange));
 margin-bottom: 25px;
 }
 
 .message {
 padding: 18px 25px;
 border-radius: 12px;
 margin-bottom: 25px;
 font-weight: 500;
 border-left: 4px solid;
 backdrop-filter: blur(10px);
 }
 
 .success {
 background: rgba(16, 185, 129, 0.1);
 color: var(--success);
 border-left-color: var(--success);
 box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
 }
 
 .error {
 background: rgba(239, 68, 68, 0.1);
 color: var(--error);
 border-left-color: var(--error);
 box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
 }
 
 .info {
 background: rgba(59, 130, 246, 0.1);
 color: var(--info);
 border-left-color: var(--info);
 box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
 }
 
 .warning {
 background: rgba(245, 158, 11, 0.1);
 color: var(--warning);
 border-left-color: var(--warning);
 box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
 }
 
 .no-updates {
 text-align: center;
 padding: 50px;
 color: var(--text-secondary);
 }
 
 .no-updates h3 {
 color: var(--text-primary);
 margin-bottom: 15px;
 font-size: 1.5em;
 }
 
 .instructions {
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 padding: 30px;
 border-radius: 15px;
 margin-bottom: 30px;
 }
 
 .instructions h3 {
 color: var(--neon-orange);
 margin-bottom: 20px;
 font-size: 1.4em;
 font-weight: 600;
 }
 
 .instructions ol {
 padding-right: 25px;
 }
 
 .instructions li {
 margin-bottom: 12px;
 color: var(--text-secondary);
 line-height: 1.7;
 }
 
 .code {
 background: var(--bg-primary);
 color: var(--neon-orange);
 padding: 4px 12px;
 border-radius: 6px;
 font-family: 'JetBrains Mono', monospace;
 font-size: 0.9em;
 border: 1px solid var(--border-color);
 }
 
 .timestamp {
 color: var(--text-muted);
 font-size: 0.85em;
 margin-top: 12px;
 font-family: 'JetBrains Mono', monospace;
 }
 
 .footer {
 text-align: center;
 padding: 40px;
 color: var(--text-secondary);
 border-top: 1px solid var(--border-color);
 margin-top: 50px;
 background: var(--bg-card);
 }
 
 .footer a {
 color: var(--neon-orange);
 text-decoration: none;
 transition: all 0.3s ease;
 font-weight: 500;
 }
 
 .footer a:hover {
 color: var(--neon-purple);
 text-shadow: 0 0 10px var(--neon-purple-glow);
 }
 
 .github-link {
 display: inline-flex;
 align-items: center;
 gap: 8px;
 margin-top: 15px;
 padding: 10px 20px;
 background: var(--bg-secondary);
 border: 1px solid var(--border-color);
 border-radius: 10px;
 text-decoration: none;
 color: var(--text-secondary);
 transition: all 0.3s ease;
 }
 
 .github-link:hover {
 color: var(--neon-orange);
 border-color: var(--neon-orange);
 box-shadow: 0 5px 15px var(--neon-orange-glow);
 transform: translateY(-2px);
 }
 
 @media (max-width: 768px) {
 .container {
 padding: 15px;
 }
 
 .header h1 {
 font-size: 2.2em;
 }
 
 .header {
 padding: 30px 20px;
 }
 
 .user-header {
 flex-direction: column;
 }
 
 .user-actions {
 width: 100%;
 justify-content: center;
 }
 
 .info-grid {
 grid-template-columns: 1fr;
 }
 
 .bot-info, .updates-section, .instructions {
 padding: 20px;
 }
 }
 
 .copy-notification {
 position: fixed;
 top: 25px;
 right: 25px;
 background: linear-gradient(135deg, var(--success), var(--neon-orange));
 color: var(--text-primary);
 padding: 15px 25px;
 border-radius: 12px;
 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3), 0 0 20px var(--neon-orange-glow);
 transform: translateX(400px);
 transition: transform 0.4s ease;
 z-index: 1000;
 font-weight: 600;
 }
 
 .copy-notification.show {
 transform: translateX(0);
 }
 
 .message-content {
 background: var(--bg-primary);
 padding: 18px;
 border-radius: 10px;
 margin-top: 15px;
 border: 1px solid var(--border-color);
 }
 
 .message-content strong {
 color: var(--neon-purple);
 }
 
 .message-content p {
 margin: 8px 0;
 color: var(--text-secondary);
 }
 
 .chat-info {
 margin-top: 12px;
 padding-top: 12px;
 border-top: 1px solid var(--border-color);
 }
 
 .chat-info strong {
 color: var(--neon-orange);
 }
 
 .status-online {
 display: inline-block;
 width: 10px;
 height: 10px;
 background: var(--success);
 border-radius: 50%;
 margin-right: 8px;
 animation: pulse 2s infinite;
 box-shadow: 0 0 10px var(--success);
 }
 
 .neon-text {
 background: linear-gradient(135deg, var(--neon-orange), var(--neon-purple));
 -webkit-background-clip: text;
 -webkit-text-fill-color: transparent;
 background-clip: text;
 font-weight: 600;
 }
 
 .floating-particles {
 position: fixed;
 top: 0;
 left: 0;
 width: 100%;
 height: 100%;
 pointer-events: none;
 z-index: -1;
 }
 
 .particle {
 position: absolute;
 width: 4px;
 height: 4px;
 background: var(--neon-orange);
 border-radius: 50%;
 opacity: 0.6;
 animation: float 6s linear infinite;
 }
 
 .particle:nth-child(even) {
 background: var(--neon-purple);
 animation-duration: 8s;
 }
 
 @keyframes float {
 0% {
 transform: translateY(100vh) rotate(0deg);
 opacity: 0;
 }
 10% {
 opacity: 0.6;
 }
 90% {
 opacity: 0.6;
 }
 100% {
 transform: translateY(-100px) rotate(360deg);
 opacity: 0;
 }
 }
 </style>
</head>
<body>
 <div class="floating-particles">
 <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
 <div class="particle" style="left: 20%; animation-delay: 1s;"></div>
 <div class="particle" style="left: 30%; animation-delay: 2s;"></div>
 <div class="particle" style="left: 40%; animation-delay: 3s;"></div>
 <div class="particle" style="left: 50%; animation-delay: 4s;"></div>
 <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
 <div class="particle" style="left: 70%; animation-delay: 6s;"></div>
 <div class="particle" style="left: 80%; animation-delay: 7s;"></div>
 <div class="particle" style="left: 90%; animation-delay: 8s;"></div>
 </div>

 <div class="container">
 <div class="header">
 <h1>🔍 Chat ID Finder</h1>
 <p>ابزار پیشرفته پیدا کردن Chat ID برای ربات <span class="neon-text"><?= BOT_NAME ?></span></p>
 </div>
 
 <?php if ($message): ?>
 <div class="message <?= $messageType ?>">
 <?= $message ?>
 </div>
 <?php endif; ?>
 
 <?php if ($botInfo['ok']): ?>
 <div class="bot-info">
 <h3>🤖 اطلاعات ربات</h3>
 <div class="info-grid">
 <div class="info-item">
 <strong>نام ربات:</strong> <?= htmlspecialchars($botInfo['result']['first_name']) ?>
 </div>
 <div class="info-item">
 <strong>نام کاربری:</strong> @<?= htmlspecialchars($botInfo['result']['username']) ?>
 </div>
 <div class="info-item">
 <strong>شناسه ربات:</strong> <span class="code"><?= $botInfo['result']['id'] ?></span>
 </div>
 <div class="info-item">
 <strong>وضعیت:</strong> <span class="status-online"></span>آنلاین
 </div>
 </div>
 </div>
 <?php else: ?>
 <div class="message error">
 ❌ خطا در اتصال به ربات: <?= $botInfo['description'] ?? 'خطای نامشخص' ?>
 </div>
 <?php endif; ?>
 
 <div class="instructions">
 <h3>📋 راهنمای استفاده</h3>
 <ol>
 <li>به ربات <strong><?= BOT_USERNAME ?></strong> در تلگرام پیام دهید</li>
 <li>دستور <span class="code">/start</span> یا هر پیام دیگری ارسال کنید</li>
 <li>صفحه را رفرش کنید تا Chat ID شما نمایش داده شود</li>
 <li>روی Chat ID کلیک کنید تا کپی شود</li>
 <li>از دکمه "ارسال پیام تست" برای تست استفاده کنید</li>
 </ol>
 </div>
 
 <div class="updates-section">
 <h3>👥 کاربران و Chat ID ها</h3>
 
 <a href="?action=refresh" class="btn refresh-btn">🔄 بروزرسانی لیست</a>
 
 <?php if ($updates['ok'] && !empty($updates['result'])): ?>
 <?php 
 $uniqueUsers = [];
 foreach (array_reverse($updates['result']) as $update) {
 $user = null;
 $chat = null;
 $messageText = '';
 $updateTime = '';
 
 if (isset($update['message'])) {
 $user = $update['message']['from'];
 $chat = $update['message']['chat'];
 $messageText = $update['message']['text'] ?? $update['message']['caption'] ?? '[پیام غیر متنی]';
 $updateTime = date('Y-m-d H:i:s', $update['message']['date']);
 } elseif (isset($update['callback_query'])) {
 $user = $update['callback_query']['from'];
 $chat = $update['callback_query']['message']['chat'];
 $messageText = 'کلیک روی دکمه: ' . ($update['callback_query']['data'] ?? '');
 $updateTime = date('Y-m-d H:i:s', $update['callback_query']['message']['date']);
 } elseif (isset($update['inline_query'])) {
 $user = $update['inline_query']['from'];
 $messageText = 'Inline Query: ' . ($update['inline_query']['query'] ?? '');
 $updateTime = 'اکنون';
 }
 
 if ($user && !isset($uniqueUsers[$user['id']])) {
 $uniqueUsers[$user['id']] = [
 'user' => $user,
 'chat' => $chat,
 'message' => $messageText,
 'time' => $updateTime
 ];
 }
 }
 ?>
 
 <?php foreach ($uniqueUsers as $userData): ?>
 <div class="user-card">
 <div class="user-header">
 <div class="user-details">
 <h4>
 <?= htmlspecialchars($userData['user']['first_name'] ?? 'کاربر ناشناس') ?>
 <?= isset($userData['user']['last_name']) ? ' ' . htmlspecialchars($userData['user']['last_name']) : '' ?>
 <?= isset($userData['user']['username']) ? ' <span class="neon-text">(@' . htmlspecialchars($userData['user']['username']) . ')</span>' : '' ?>
 </h4>
 
 <div class="chat-id" onclick="copyToClipboard('<?= $userData['user']['id'] ?>')" title="کلیک کنید تا کپی شود">
 💳 Chat ID: <?= $userData['user']['id'] ?>
 </div>
 </div>
 
 <div class="user-actions">
 <button onclick="copyToClipboard('<?= $userData['user']['id'] ?>')" class="btn btn-copy">
 📋 کپی ID
 </button>
 <a href="?action=send_test&chat_id=<?= $userData['user']['id'] ?>" class="btn">
 📤 ارسال تست
 </a>
 </div>
 </div>
 
 <div class="message-content">
 <strong>آخرین پیام:</strong>
 <p><?= htmlspecialchars(substr($userData['message'], 0, 150)) ?><?= strlen($userData['message']) > 150 ? '...' : '' ?></p>
 
 <?php if ($userData['chat'] && $userData['chat']['type'] !== 'private'): ?>
 <div class="chat-info">
 <strong>اطلاعات چت:</strong><br>
 نوع: <span class="neon-text"><?= ucfirst($userData['chat']['type']) ?></span><br>
 Chat ID: <span class="code"><?= $userData['chat']['id'] ?></span><br>
 <?php if (isset($userData['chat']['title'])): ?>
 عنوان: <?= htmlspecialchars($userData['chat']['title']) ?><br>
 <?php endif; ?>
 </div>
 <?php endif; ?>
 
 <div class="timestamp">
 ⏰ زمان: <?= $userData['time'] ?>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 
 <?php else: ?>
 <div class="no-updates">
 <h3>😔 هیچ پیامی یافت نشد</h3>
 <p>برای نمایش Chat ID، ابتدا به ربات <?= BOT_USERNAME ?> پیام دهید</p>
 <br>
 <a href="https://t.me/<?= str_replace('@', '', BOT_USERNAME) ?>" target="_blank" class="btn">
 🚀 رفتن به ربات
 </a>
 </div>
 <?php endif; ?>
 </div>
 
 <div class="bot-info">
 <h3>ℹ️ اطلاعات تکمیلی</h3>
 <div class="info-grid">
 <div class="info-item">
 <strong>لینک ربات:</strong> 
 <a href="https://t.me/<?= str_replace('@', '', BOT_USERNAME) ?>" target="_blank" class="neon-text">
 <?= BOT_USERNAME ?>
 </a>
 </div>
 <div class="info-item">
 <strong>توکن ربات:</strong> 
 <span class="code"><?= substr(BOT_TOKEN, 0, 15) ?>...</span>
 </div>
 <div class="info-item">
 <strong>آخرین بروزرسانی:</strong> 
 <span class="neon-text"><?= date('Y-m-d H:i:s') ?></span>
 </div>
 <div class="info-item">
 <strong>تعداد آپدیت‌ها:</strong> 
 <span class="neon-text"><?= $updates['ok'] ? count($updates['result']) : 0 ?></span>
 </div>
 </div>
 </div>
 </div>
 
 <div class="footer">
 <p>🤖 <span class="neon-text">Chat ID Finder</span> برای <?= BOT_NAME ?></p>
 <p>ساخته شده با ❤️ برای تسهیل کار توسعه‌دهندگان</p>
 
 <div style="margin-top: 20px;">
 <p><strong class="neon-text">Dev Guru</strong></p>
 <a href="https://github.com/ftepic" target="_blank" class="github-link">
 <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
 <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
 </svg>
 github.com/ftepic
 </a>
 </div>
 </div>
 
 <div id="copyNotification" class="copy-notification">
 ✅ Chat ID کپی شد!
 </div>
 
 <script>
 function copyToClipboard(text) {
 navigator.clipboard.writeText(text).then(function() {
 showNotification('✅ Chat ID کپی شد: ' + text);
 }).catch(function() {
 const textArea = document.createElement('textarea');
 textArea.value = text;
 document.body.appendChild(textArea);
 textArea.select();
 document.execCommand('copy');
 document.body.removeChild(textArea);
 showNotification('✅ Chat ID کپی شد: ' + text);
 });
 }
 
 function showNotification(message) {
 const notification = document.getElementById('copyNotification');
 notification.textContent = message;
 notification.classList.add('show');
 
 setTimeout(() => {
 notification.classList.remove('show');
 }, 3500);
 }
 
 setInterval(() => {
 if (document.hidden === false) {
 window.location.reload();
 }
 }, 45000);
 
 document.addEventListener('DOMContentLoaded', function() {
 const chatIds = document.querySelectorAll('.chat-id');
 chatIds.forEach(element => {
 element.addEventListener('click', function() {
 const chatId = this.textContent.replace('💳 Chat ID: ', '');
 copyToClipboard(chatId);
 });
 });
 
 const userCards = document.querySelectorAll('.user-card');
 userCards.forEach((card, index) => {
 setTimeout(() => {
 card.style.animation = 'slideIn 0.6s ease-out forwards';
 }, index * 100);
 });
 });
 
 function createFloatingParticle() {
 const particle = document.createElement('div');
 particle.className = 'particle';
 particle.style.left = Math.random() * 100 + '%';
 particle.style.animationDelay = Math.random() * 2 + 's';
 document.querySelector('.floating-particles').appendChild(particle);
 
 setTimeout(() => {
 particle.remove();
 }, 8000);
 }
 
 setInterval(createFloatingParticle, 2000);
 </script>
</body>
</html>
