<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'WhatsApp CRM' ?> - Supervision System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --app-bg: #0f172a;
            --panel-bg: #1e293b;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --wa-color: #25d366;
            --wa-hover: #128c7e;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: var(--text-main);
            overflow-x: hidden;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 280px;
            background-color: var(--panel-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            position: relative;
            min-width: 0;
        }

        .btn-wa {
            background-color: var(--wa-color);
            color: white;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-wa:hover {
            background-color: var(--wa-hover);
            color: white;
            transform: translateY(-1px);
        }

        .nav-link {
            color: var(--text-muted);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
        }

        .nav-link i {
            width: 1.5rem;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        /* Chat Room specific styles */
        .chat-container {
            display: flex;
            height: calc(100vh - 70px);
        }
        .contact-list {
            width: 350px;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            background: var(--panel-bg);
        }
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
            background-color: #0b141a;
            background-blend-mode: overlay;
        }
        .contact-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }
        .contact-item:hover, .contact-item.active {
            background: rgba(255,255,255,0.05);
        }
        .message-bubble {
            max-width: 75%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .message-in {
            background-color: var(--panel-bg);
            align-self: flex-start;
            border-bottom-left-radius: 0.25rem;
        }
        .message-out {
            background-color: #005c4b; /* WA dark theme out color */
            align-self: flex-end;
            border-bottom-right-radius: 0.25rem;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        .chat-input {
            padding: 1rem;
            background: var(--panel-bg);
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="wrapper">
