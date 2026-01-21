{{-- resources/views/explore.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Explore - Cognisense</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bg-light: #f1f3f7;
            --bg-dark: #121212;
            --text-light: #2c3e50;
            --text-dark: #f1f1f1;
            --card-light: #ffffff;
            --card-dark: #1e1e1e;
            --accent: #3498db;
            --sidebar-width: 220px;
            --sidebar-collapsed-width: 60px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(145deg, #e0eaff, #f0f4ff);
            color: var(--text-light);
            transition: background 0.5s, color 0.5s;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Smooth theme swap pulse */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity .28s ease;
            z-index: 9999;
            background:
                radial-gradient(900px 480px at 20% 15%, rgba(56, 189, 248, .14), transparent 60%),
                radial-gradient(900px 520px at 85% 20%, rgba(79, 70, 229, .12), transparent 62%);
            mix-blend-mode: soft-light;
        }

        body.theme-swap::before {
            opacity: 1;
        }

        body.dark-mode::before {
            mix-blend-mode: screen;
            opacity: 0;
        }

        body.dark-mode.theme-swap::before {
            opacity: .95;
        }


        body.dark-mode {
            background: linear-gradient(145deg, #0c0c0c, #050505);
            color: var(--text-dark);
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100%;
            background: var(--card-light);
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            transition: width 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }

        body.dark-mode .sidebar {
            background: var(--card-dark);
            box-shadow: 2px 0 12px rgba(255, 255, 255, 0.1);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .logo {
            padding: 1px;
            margin-bottom: 1px;
            margin-top: auto;
            text-align: center;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .logo img {
            height: 250px;
            width: 190px;
            object-fit: fill;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .logo img {
                height: 60px;
            }
        }

        .sidebar.collapsed .logo {
            opacity: 0;
            transform: translateX(-20px);
            pointer-events: none;
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: none;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar.collapsed ul {
            margin-top: 55px;
        }

        .sidebar ul li {
            padding: 15px 25px;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            color: var(--text-light);
            transition: background 0.2s ease, transform 0.15s ease;
        }

        body.dark-mode .sidebar ul li {
            color: var(--text-dark);
        }

        .sidebar ul li:hover {
            background: linear-gradient(135deg, #d9d4e4ff, #8e2de2);
            color: white;
            box-shadow: inset 2px 2px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            transform: translateX(2px);
        }

        .sidebar ul li.active {
            background: linear-gradient(135deg, #752dfbff, #00c6ff);
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 18px rgba(74, 0, 224, 0.45);
        }

        .sidebar ul li a {
            color: inherit;
            text-decoration: none;
            flex-grow: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar.collapsed ul li span.text {
            display: none;
        }

        /* PNG ICONS + ANIMATION */
        .sidebar ul li .icon {
            width: 30px;
            height: 30px;
            object-fit: contain;
            transform-origin: center;
            display: block;
            transition: transform 0.25s ease, filter 0.25s ease;
            filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.2));
        }

        .sidebar ul li:hover .icon {
            filter: drop-shadow(0 0 6px rgba(0, 198, 255, 0.75)) drop-shadow(0 0 10px rgba(74, 0, 224, 0.8));
            animation: navIconWiggle 0.45s ease-out;
        }

        .sidebar ul li.active .icon {
            filter: drop-shadow(0 0 8px rgba(0, 198, 255, 0.95)) drop-shadow(0 0 12px rgba(74, 0, 224, 0.9));
            animation: navIconPulse 1.4s ease-in-out infinite;
        }

        @keyframes navIconWiggle {
            0% {
                transform: translateX(0) rotate(0deg) scale(1);
            }

            25% {
                transform: translateX(2px) rotate(8deg) scale(1.1);
            }

            50% {
                transform: translateX(-1px) rotate(-6deg) scale(1.05);
            }

            75% {
                transform: translateX(1px) rotate(3deg) scale(1.08);
            }

            100% {
                transform: translateX(0) rotate(0deg) scale(1);
            }
        }

        @keyframes navIconPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.13);
            }

            100% {
                transform: scale(1);
            }
        }

        .sidebar.collapsed ul li {
            justify-content: center;
        }

        /* SIDEBAR TOGGLE BUTTON */
        .sidebar-toggle-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            z-index: 1100;
        }

        .sidebar-toggle-btn:hover {
            background: linear-gradient(135deg, rgb(5, 3, 9), rgb(37, 7, 62));
            box-shadow: 0 3px 8px rgba(19, 1, 1, 0.35);
            transform: scale(1.05);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
            width: calc(100% - var(--sidebar-width));
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .sidebar.collapsed+.main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* TOP BAR */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            padding: 15px 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            height: 80px;
        }

        body.dark-mode .top-bar {
            background: rgba(18, 18, 18, 0.65);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* ABOUT BUTTON (Top Right) */
        .about-btn.icon-only {
            width: 50px;
            height: 50px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148, 163, 184, .55);
            background: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: 0.2s;
        }

        .about-btn.icon-only img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        body.dark-mode .about-btn.icon-only {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .about-btn.icon-only:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        }

        /* PROFILE DROPDOWN */
        .profile-wrap {
            position: relative;
            display: inline-flex;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 14px);
            right: 0;
            width: 320px;
            border-radius: 16px;
            padding: 16px;
            z-index: 12000;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: 0.2s ease;
        }

        body.dark-mode .profile-dropdown {
            background: #1e1e1e;
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .profile-dropdown.open {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .profile-head {
            display: flex;
            gap: 12px;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        body.dark-mode .profile-head {
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .profile-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #3b82f6;
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .profile-name {
            font-weight: 600;
            font-size: 1rem;
        }

        .profile-email {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        /* THEME TOGGLE */
        .theme-toggle {
            cursor: pointer;
            margin-right: 12px;
        }

        .theme-toggle input {
            display: none;
        }

        .toggle-track {
            width: 60px;
            height: 32px;
            background: rgba(0, 0, 0, 0.06);
            border-radius: 99px;
            position: relative;
            transition: 0.3s;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .theme-toggle input:checked+.toggle-track {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .toggle-thumb {
            width: 24px;
            height: 24px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 4px;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle input:checked+.toggle-track .toggle-thumb {
            left: 30px;
            background: #3b82f6;
        }


        /* ================================
           EXPLORE CHAT - PROFESSIONAL UI 
           ================================ */

        .skills-header {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 20px;
            animation: fadeInDown 0.8s ease;
        }

        .skills-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.8rem;
            color: #3b82f6;
            margin: 0;
            letter-spacing: 2px;
            background: -webkit-linear-gradient(45deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .skills-subtitle {
            margin-top: 8px;
            color: #64748b;
            font-size: 0.95rem;
            display: flex;
            gap: 15px;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .skills-subtitle span {
            position: relative;
        }

        .skills-subtitle span:not(:last-child)::after {
            content: "•";
            position: absolute;
            right: -10px;
            color: #ccc;
        }

        body.dark-mode .skills-subtitle {
            color: #94a3b8;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CHAT CONTAINER */
        .skill-preview-card {
            flex: 1;
            margin: 0 40px 40px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            transition: 0.3s;
        }

        body.dark-mode .skill-preview-card {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .chat-box {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* MESSAGES AREA */
        .messages-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            scroll-behavior: smooth;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .messages-area::-webkit-scrollbar {
            width: 8px;
        }

        .messages-area::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        body.dark-mode .messages-area::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
        }

        /* MESSAGE BUBBLES */
        .msg {
            max-width: 75%;
            padding: 16px 22px;
            border-radius: 20px;
            font-size: 1rem;
            line-height: 1.6;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            animation: msgPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            word-wrap: break-word;
        }

        @keyframes msgPop {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* BOT MESSAGE */
        .msg.bot {
            align-self: flex-start;
            background: #ffffff;
            color: #334155;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        body.dark-mode .msg.bot {
            background: #334155;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.05);
        }

        /* USER MESSAGE */
        .msg.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        /* Message Icon/Avatar */
        .msg::before {
            content: '';
            position: absolute;
            bottom: -5px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-size: cover;
            display: block;
        }

        /* Bot Avatar placement */
        .msg.bot::before {
            left: -32px;
            background-image: url("{{ asset('img/Cognix.png') }}");
            /* Using logo as bot avatar */
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* User Avatar placement */
        .msg.user::before {
            right: -32px;
            background-image: url("{{ asset('img/user.png') }}");
            background-color: #f1f5f9;
        }

        /* INPUT AREA */
        .input-area {
            padding: 24px 30px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 12px;
            align-items: center;
        }

        body.dark-mode .input-area {
            background: rgba(0, 0, 0, 0.2);
            border-top-color: rgba(255, 255, 255, 0.05);
        }

        .chat-input {
            flex: 1;
            height: 54px;
            padding: 0 24px;
            border-radius: 99px;
            border: 2px solid transparent;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            font-size: 1rem;
            color: #334155;
            transition: all 0.3s;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }

        body.dark-mode .chat-input {
            background: #1e293b;
            color: #f8fafc;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .chat-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }

        .send-btn {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            transition: all 0.3s;
            display: grid;
            place-items: center;
        }

        .send-btn:hover {
            transform: scale(1.05) rotate(-10deg);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
        }

        .send-btn:active {
            transform: scale(0.95);
        }

        /* LOADING DOTS */
        .typing-indicator {
            display: inline-flex;
            gap: 4px;
            padding: 4px 8px;
        }

        .typing-indicator span {
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    {{-- SIDEBAR --}}
    <nav class="sidebar" id="sidebar">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-angles-left"></i>
        </button>

        <div class="logo" id="sidebarLogo">
            <img src="{{ asset('img/Cognix.png') }}" alt="Cognisense Logo" />
        </div>

        <ul>
            <li>
                <a href="{{ url('/dashboard') }}">
                    <img src="{{ asset('img/dashboard.png') }}" class="icon" alt="Dashboard">
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('skill.hub') }}">
                    <img src="{{ asset('img/skill.png') }}" class="icon" alt="Skill Hub">
                    <span class="text">Skill Hub</span>
                </a>
            </li>
            <li>
                <a href="{{ route('insight.streams') }}">
                    <img src="{{ asset('img/learning.png') }}" class="icon" alt="Learning Hub">
                    <span class="text">Insight Streams</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <img src="{{ asset('img/community.png') }}" class="icon" alt="Community">
                    <span class="text">Community</span>
                </a>
            </li>
            <li class="active">
                <a href="{{ route('explore.index') }}">
                    <img src="{{ asset('img/explore.png') }}" class="icon" alt="Explore">
                    <span class="text">Explore</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <img src="{{ asset('img/certificate.png') }}" class="icon" alt="Certificate">
                    <span class="text">Certificate</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <img src="{{ asset('img/cv.png') }}" class="icon" alt="Generate CV">
                    <span class="text">Generate CV</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <img src="{{ asset('img/ielts.png') }}" class="icon" alt="AspireIELTS">
                    <span class="text">AspireIELTS</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <img src="{{ asset('img/logout.png') }}" class="icon" alt="Logout">
                    <span class="text">Logout</span>
                </a>
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="main-content" id="main-content">
        <div class="top-bar">
            {{-- Breadcrumb or Left content --}}
            <div class="top-bar-left" style="font-weight: 600; font-size: 1.1rem; color: var(--text-light);">
                Explore / Chat
            </div>

            <div style="display: flex; align-items: center; gap: 20px;">
                {{-- Theme toggle --}}
                <label class="theme-toggle">
                    <input type="checkbox" id="darkModeToggle">
                    <div class="toggle-track">
                        <div class="toggle-thumb"></div>
                    </div>
                </label>

                {{-- PROFILE --}}
                <div class="profile-wrap" id="profileWrap">
                    <button type="button" class="about-btn icon-only profile-btn" id="profileBtn">
                        <img src="{{ asset('img/user.png') }}" alt="User">
                    </button>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-head">
                            <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="profile-meta">
                                <div class="profile-name">{{ auth()->user()->full_name ?? 'User' }}</div>
                                <div class="profile-email">{{ auth()->user()->email ?? '' }}</div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <form method="POST" action="{{ url('/logout') }}">
                                @csrf
                                <button type="submit"
                                    style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer;">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EXPLORE HEADER -->
        <div class="skills-header">
            <h1 class="skills-title">EXPLORE AI</h1>
            <p class="skills-subtitle">
                <span>Personal Assistant</span>
                <span>Powered by OpenRouter</span>
                <span>Ask Anything</span>
            </p>
        </div>

        <!-- CHAT INTERFACE -->
        <div class="skill-preview-card">
            <div class="chat-box">
                <div class="messages-area" id="chatArea">
                    <div class="msg bot">
                        Hello! I am your AI assistant. How can I help you today?
                    </div>
                </div>

                <form class="input-area" id="chatForm">
                    <input type="text" class="chat-input" id="chatInput" placeholder="Type your message..."
                        autocomplete="off">
                    <button type="submit" class="send-btn" id="sendBtn" disabled>
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>


    {{-- SCRIPTS --}}
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        toggleBtn.addEventListener('click', () => sidebar.classList.toggle('collapsed'));

        // Profile Dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => profileDropdown.classList.remove('open'));

        // Dark Mode
        const themeToggle = document.getElementById('darkModeToggle');

        // Check local storage
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.checked = true;
        }

        themeToggle.addEventListener('change', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });

        // Chat Functionality
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatArea = document.getElementById('chatArea');
        const sendBtn = document.getElementById('sendBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        chatInput.addEventListener('input', () => {
            sendBtn.disabled = chatInput.value.trim() === '';
        });

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // Append User Message
            appendMsg(message, 'user');
            chatInput.value = '';
            sendBtn.disabled = true;

            // Show Typing Indicator
            const loaderId = appendLoader();

            try {
                const res = await fetch('{{ route('explore.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                removeLoader(loaderId);

                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();

                if (data.error) {
                    appendMsg("⚠️ " + data.error, 'bot');
                } else {
                    appendMsg(data.reply, 'bot');
                }

            } catch (error) {
                removeLoader(loaderId);
                appendMsg("⚠️ Sorry, something went wrong. Please try again.", 'bot');
                console.error(error);
            }
        });

        function appendMsg(text, type) {
            const div = document.createElement('div');
            div.className = `msg ${type}`;
            // Simple markdown-like replacement for newlines
            div.innerHTML = text.replace(/\n/g, '<br>');
            chatArea.appendChild(div);
            scrollToBottom();
        }

        function appendLoader() {
            const id = 'loader-' + Date.now();
            const div = document.createElement('div');
            div.className = 'msg bot';
            div.id = id;
            div.innerHTML = `<div class="typing-indicator"><span></span><span></span><span></span></div>`;
            chatArea.appendChild(div);
            scrollToBottom();
            return id;
        }

        function removeLoader(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function scrollToBottom() {
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    </script>
</body>

</html>