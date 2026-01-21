{{-- resources/views/insight_streams.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insight Streams - Cognisense</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@400;500;600&display=swap"
          rel="stylesheet">

    <style>
        /* =========================
           INSIGHT STREAMS (PAGE CSS)
           ========================= */
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

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins','Segoe UI', sans-serif;
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
        body::before{
          content:"";
          position: fixed;
          inset: 0;
          pointer-events: none;
          opacity: 0;
          transition: opacity .28s ease;
          z-index: 9999;
          background:
            radial-gradient(900px 480px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
            radial-gradient(900px 520px at 85% 20%, rgba(79,70,229,.12), transparent 62%);
          mix-blend-mode: soft-light;
        }
        body.theme-swap::before{ opacity: 1; }
        body.dark-mode::before{ mix-blend-mode: screen; opacity: 0; }
        body.dark-mode.theme-swap::before{ opacity: .95; }

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
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }

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
        @media (max-width: 768px) { .logo img { height: 60px; } }
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

        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar.collapsed ul { margin-top: 55px; }

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
        body.dark-mode .sidebar ul li { color: var(--text-dark); }

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
        .sidebar.collapsed ul li span.text { display: none; }

        /* PNG ICONS + ANIMATION */
        .sidebar ul li .icon {
            width: 30px;
            height: 30px;
            object-fit: contain;
            transform-origin: center;
            display: block;
            transition: transform 0.25s ease, filter 0.25s ease;
            filter: drop-shadow(0 0 2px rgba(0,0,0,0.2));
        }
        .sidebar ul li:hover .icon {
            filter:
                drop-shadow(0 0 6px rgba(0, 198, 255, 0.75))
                drop-shadow(0 0 10px rgba(74, 0, 224, 0.8));
            animation: navIconWiggle 0.45s ease-out;
        }
        .sidebar ul li.active .icon {
            filter:
                drop-shadow(0 0 8px rgba(0, 198, 255, 0.95))
                drop-shadow(0 0 12px rgba(74, 0, 224, 0.9));
            animation: navIconPulse 1.4s ease-in-out infinite;
        }
        @keyframes navIconWiggle {
            0%   { transform: translateX(0) rotate(0deg) scale(1); }
            25%  { transform: translateX(2px) rotate(8deg)  scale(1.1); }
            50%  { transform: translateX(-1px) rotate(-6deg) scale(1.05); }
            75%  { transform: translateX(1px) rotate(3deg)  scale(1.08); }
            100% { transform: translateX(0) rotate(0deg) scale(1); }
        }
        @keyframes navIconPulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.13); }
            100% { transform: scale(1); }
        }

        .sidebar.collapsed ul li { justify-content: center; }

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
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            overflow-y: auto;
            transition: margin-left 0.3s ease, width 0.3s ease;
            height: 100vh;
        }
        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* TOP BAR */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .top-bar-left { display: flex; align-items: center; gap: 15px; }
        .top-bar button, .top-bar a button {
            padding: 10px 15px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .top-bar button:hover, .top-bar a button:hover { background: #2980b9; }

        /* TIME DISPLAY */
        .time-display {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            font-family: 'Orbitron', 'Poppins', monospace;
            font-size: 1.4rem;
            letter-spacing: 0.14em;
            color: #0b1120;
            padding: 18px 32px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(241, 245, 249, 0.99));
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.7);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.26), 0 0 0 1px rgba(255, 255, 255, 0.8);
            animation: floatTimer 10s ease-in-out infinite;
            transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .time-main { font-size: 2rem; font-weight: 700; letter-spacing: 0.18em; }
        .time-sub {
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.8;
            margin-left: 4px;
            white-space: nowrap;
        }
        .time-display::before {
            content: "";
            width: 32px;
            height: 32px;
            border-radius: 999px;
            border: 3px solid rgba(59, 130, 246, 0.9);
            border-top-color: transparent;
            border-right-color: transparent;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.9), 0 0 22px rgba(59, 130, 246, 0.7);
            animation: spinClock 4s linear infinite, glowClock 2.4s ease-in-out infinite;
        }
        @keyframes floatTimer { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }
        @keyframes spinClock { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
        @keyframes glowClock {
            0%,100%{ box-shadow: 0 0 5px rgba(56,189,248,.6), 0 0 12px rgba(59,130,246,.5); }
            50%{ box-shadow: 0 0 14px rgba(56,189,248,1), 0 0 28px rgba(59,130,246,.98); }
        }
        body.dark-mode .time-display {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.99));
            color: #e5e7eb;
            border-color: rgba(37, 99, 235, 0.9);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.98), 0 0 22px rgba(30, 64, 175, 0.7);
        }
        body.dark-mode .time-display::before {
            border-color: rgba(129, 140, 248, 0.98);
            border-top-color: transparent;
            border-right-color: transparent;
            box-shadow: 0 0 14px rgba(129, 140, 248, 1), 0 0 30px rgba(56, 189, 248, 0.98);
        }

        /* THEME TOGGLE */
        .theme-toggle { position: relative; display: inline-block; cursor: pointer; margin-right: 16px; user-select: none; }
        .theme-toggle input { display: none; }
        .toggle-track {
            width: 150px;
            height: 64px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.35), inset 0 0 3px rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(148, 163, 184, 0.55);
            transition: background 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }
        .toggle-track img {
            width: 34px; height: 34px; object-fit: contain;
            position: relative; z-index: 2;
            transition: opacity 0.25s ease, transform 0.25s ease, filter 0.25s ease;
        }
        .toggle-thumb {
            position: absolute; top: 8px; left: 9px;
            width: 48px; height: 48px; border-radius: 999px;
            background: transparent;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.8), 0 6px 16px rgba(15, 23, 42, 0.55);
            transition: transform 0.32s cubic-bezier(.4,0,.2,1), box-shadow 0.32s ease;
            z-index: 1;
        }
        .theme-toggle input:not(:checked) + .toggle-track .icon-sun {
            opacity: 1;
            filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.95)) drop-shadow(0 0 18px rgba(245, 158, 11, 0.75));
            transform: scale(1.12);
        }
        .theme-toggle input:not(:checked) + .toggle-track .icon-moon { opacity: 0.35; transform: scale(0.9); filter: none; }
        .theme-toggle input:checked + .toggle-track {
            background: rgba(15, 23, 42, 0.12);
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.85), inset 0 0 5px rgba(15, 23, 42, 0.7);
            border-color: rgba(30, 64, 175, 0.7);
        }
        .theme-toggle input:checked + .toggle-track .toggle-thumb {
            transform: translateX(83px);
            background: transparent;
            box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.9), 0 10px 24px rgba(15, 23, 42, 0.95), 0 0 22px rgba(56, 189, 248, 0.85);
        }
        .theme-toggle input:checked + .toggle-track .icon-sun { opacity: 0.25; transform: scale(0.9); filter: none; }
        .theme-toggle input:checked + .toggle-track .icon-moon {
            opacity: 1;
            transform: scale(1.14);
            filter: drop-shadow(0 0 10px rgba(129, 140, 248, 1)) drop-shadow(0 0 24px rgba(56, 189, 248, 0.95));
        }

/* =========================
   ABOUT OVERLAY (Intro style)
   ========================= */

/* (recommended) lower this so modals can safely sit above it */
body::before { z-index: 9000; }

body.modal-open .sidebar,
body.modal-open .main-content{
  filter: blur(8px);
  transform: scale(0.995);
  pointer-events: none; /* block clicks behind modal */
}

.about-overlay{
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 10050;

  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity .22s ease, visibility .22s ease;

  background:
    radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.22), transparent 60%),
    radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.20), transparent 62%),
    rgba(15, 23, 42, 0.28);

  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.about-overlay.open{
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
}

body.modal-open { overflow: hidden; }

/* Bigger modal, no scroll */
.about-modal{
  position: relative;
  width: min(1120px, 96vw);
  height: min(86vh, 780px);   /* ✅ bigger */
  overflow: hidden;           /* ✅ no scrolling */
  border-radius: 26px;
  padding: 26px 26px 18px;

  display: grid;
  grid-template-rows: auto 1fr auto; /* hero + grid + footer */

  transform: translateY(14px) scale(.985);
  opacity: 0;
  transition: transform .26s ease, opacity .26s ease;

  transform-style: preserve-3d;
  background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
  border: 1px solid rgba(148,163,184,.55);
  box-shadow:
    0 30px 80px rgba(15,23,42,.34),
    0 0 0 1px rgba(255,255,255,.75);
}

.about-overlay.open .about-modal{
  transform: translateY(0) scale(1);
  opacity: 1;
}

/* Close stays fixed top-right */
.about-close{
  position: absolute;
  top: 18px;
  right: 18px;
  display: grid;
  place-items: center;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.55);
  background: rgba(255,255,255,.55);
  cursor: pointer;
  box-shadow: 0 10px 22px rgba(15,23,42,.18);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
  z-index: 5;
}
.about-close:hover{
  transform: translateY(-1px) scale(1.03);
  box-shadow: 0 16px 30px rgba(15,23,42,.22);
  background: rgba(255,255,255,.75);
}

.about-hero{
  position: relative;
  padding: 10px 8px 18px;
}

.about-hero-badge{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(56,189,248,.12);
  border: 1px solid rgba(56,189,248,.40);
  color: #0b2a4a;
  font-size: .72rem;
  letter-spacing: .18em;
  text-transform: uppercase;
}

.about-dot{
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
  box-shadow: 0 0 12px rgba(56,189,248,.75);
}

.about-title{
  font-family: 'Orbitron', 'Poppins', sans-serif; /* keep your fonts */
  margin: 14px 0 6px;
  font-size: 2.2rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #0b1120;
  text-shadow: 2px 6px 18px rgba(30,58,138,.18);
}

.about-subtitle{
  margin: 0;
  font-size: 1.02rem;
  opacity: .85;
  color: #1f2937;
  max-width: 70ch;
}

.about-tags{
  margin-top: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.about-tag{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 999px;
  font-size: .82rem;
  background: rgba(255,255,255,.55);
  border: 1px solid rgba(148,163,184,.45);
  color: #0b1120;
  box-shadow: 0 10px 20px rgba(15,23,42,.10);
}

.about-grid{
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  margin-top: 14px;
  padding: 6px 6px 14px;
}

.about-card{
  background: rgba(255,255,255,.65);
  border: 1px solid rgba(148,163,184,.40);
  border-radius: 18px;
  padding: 14px 14px 12px;
  box-shadow:
    0 16px 34px rgba(15,23,42,.12),
    inset 0 0 0 1px rgba(255,255,255,.55);
  transform-style: preserve-3d;
  transition: transform .18s ease, box-shadow .18s ease;
}

.about-card:hover{
  transform: translateY(-2px) rotateX(5deg) rotateY(-6deg);
  box-shadow: 0 22px 46px rgba(15,23,42,.16);
}

.about-card-head{
  display:flex;
  align-items:center;
  gap: 10px;
  margin-bottom: 6px;
}
.about-card h3{
  margin: 0;
  font-size: 1.05rem;
  color: #0b1120;
}
.about-card p{
  margin: 0;
  color: #334155;
  opacity: .92;
  line-height: 1.5;
  font-size: .95rem;
}

.about-ic{
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display:grid;
  place-items:center;
  color: #0b1120;
  background: radial-gradient(circle at 30% 10%, rgba(254,249,195,.95), rgba(56,189,248,.55), rgba(79,70,229,.45));
  box-shadow: 0 14px 26px rgba(37,99,235,.16);
  transform: translateZ(12px);
}

.about-footer{
  padding: 10px 10px 0;
  border-top: 1px solid rgba(148,163,184,.25);
  margin-top: 8px;
}
.about-mini{
  font-size: .9rem;
  opacity: .82;
  color: #334155;
}

/* Dark mode */
body.dark-mode .about-overlay{
  background:
    radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.18), transparent 60%),
    radial-gradient(900px 560px at 85% 22%, rgba(129,140,248,.16), transparent 62%),
    rgba(2, 6, 23, 0.62);
}

body.dark-mode .about-modal{
  background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
  border-color: rgba(37,99,235,.70);
  box-shadow:
    0 34px 90px rgba(0,0,0,.70),
    0 0 22px rgba(30,64,175,.40);
}

body.dark-mode .about-close{
  background: rgba(15,23,42,.55);
  border-color: rgba(129,140,248,.55);
  color: #e5e7eb;
}

body.dark-mode .about-hero-badge{
  background: rgba(129,140,248,.12);
  border-color: rgba(129,140,248,.35);
  color: #e5e7eb;
}

body.dark-mode .about-title{ color: #e5e7eb; }
body.dark-mode .about-subtitle{ color: #cbd5e1; }

body.dark-mode .about-tag{
  background: rgba(15,23,42,.55);
  border-color: rgba(129,140,248,.35);
  color: #e5e7eb;
}

body.dark-mode .about-card{
  background: rgba(15,23,42,.62);
  border-color: rgba(129,140,248,.30);
}
body.dark-mode .about-card h3{ color: #e5e7eb; }
body.dark-mode .about-card p{ color: #cbd5e1; }

body.dark-mode .about-mini{ color: #cbd5e1; }

@media (max-width: 820px){
  .about-grid{ grid-template-columns: 1fr; }
}

/* ===== TOP RIGHT: theme toggle + icon buttons ===== */
.top-bar-right{
  display: flex;
  align-items: center;
  gap: 14px;           /* space between toggle, about, profile */
}

/* ===== TOP RIGHT ICON BUTTONS — bigger + premium ===== */
.about-btn.icon-only{
  width: 78px;                 /* bigger */
  height: 72px;                /* bigger */
  padding: 10px;
  border-radius: 22px;         /* unique “squircle”, not a pill */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0;

  border: 1px solid rgba(148,163,184,.55);
  background: linear-gradient(135deg, rgba(255,255,255,.88), rgba(226,232,240,.88));
  box-shadow:
    0 16px 40px rgba(15,23,42,.18),
    inset 0 0 0 1px rgba(255,255,255,.72);

  position: relative;
  overflow: hidden;
  -webkit-tap-highlight-color: transparent;

  transition: transform .18s ease, box-shadow .22s ease, border-color .22s ease;
}

/* soft “glass” highlight */
.about-btn.icon-only::before{
  content:"";
  position:absolute;
  inset:0;
  background:
    radial-gradient(560px 260px at 20% 10%, rgba(56,189,248,.18), transparent 55%),
    radial-gradient(520px 240px at 85% 15%, rgba(250,204,21,.10), transparent 60%);
  opacity: .9;
  pointer-events:none;
}

/* gradient border ring (beautiful + unique) */
.about-btn.icon-only::after{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  pointer-events:none;
  opacity: .35;
  transition: opacity .22s ease;

  background: linear-gradient(135deg,
    rgba(56,189,248,.70),
    rgba(79,70,229,.60),
    rgba(250,204,21,.45)
  );

  /* makes it a “ring” */
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

.about-btn.icon-only:hover{
  transform: translateY(-2px);
  box-shadow:
    0 20px 52px rgba(15,23,42,.22),
    inset 0 0 0 1px rgba(255,255,255,.80);
  border-color: rgba(191,219,254,.95);
}
.about-btn.icon-only:hover::after{ opacity: .80; }

.about-btn.icon-only:active{
  transform: translateY(0px) scale(.98);
}

.about-btn.icon-only:focus-visible{
  outline: none;
  box-shadow:
    0 0 0 3px rgba(56,189,248,.35),
    0 20px 52px rgba(15,23,42,.22);
}

/* bigger inner orb */
.about-btn.icon-only .about-icon-circle{
  width: 54px;                 /* bigger */
  height: 54px;                /* bigger */
  border-radius: 18px;         /* squircle orb */
  box-shadow:
    0 14px 28px rgba(37,99,235,.16),
    0 0 18px rgba(56,189,248,.40);
}

/* bigger icon */
.about-btn.icon-only .about-icon{
  width: 50px;
  height: 50px;
}

/* Profile gets a slightly different “signature” look */
.about-btn.icon-only.profile-btn::after{
  background: linear-gradient(135deg,
    rgba(219,234,254,.70),
    rgba(56,189,248,.70),
    rgba(79,70,229,.60)
  );
}

/* Keep your profile orb gradient */
.profile-btn .about-icon-circle{
  background: radial-gradient(circle at 30% 10%,
      #dbeafe 0,
      #38bdf8 45%,
      #4f46e5 100%);
}

/* Dark mode */
body.dark-mode .about-btn.icon-only{
  background: linear-gradient(135deg, rgba(15,23,42,.78), rgba(2,6,23,.84));
  border-color: rgba(37,99,235,.55);
  box-shadow:
    0 22px 60px rgba(0,0,0,.70),
    inset 0 0 0 1px rgba(30,64,175,.32);
}
body.dark-mode .about-btn.icon-only::after{ opacity: .55; }


/* If you keep the label in HTML, this hides it (safe fallback) */
.about-btn.icon-only .about-label{
  display: none;
}


/* ===== STOP the rotating/spin animation on the icon ===== */
.about-btn .about-icon-circle,
.profile-btn .about-icon-circle,
.about-btn .about-icon-circle::before,
.about-btn .about-icon-circle::after,
.profile-btn .about-icon-circle::before,
.profile-btn .about-icon-circle::after,
.about-btn .about-icon,
.profile-btn .about-icon{
  animation: none !important;
}

/* If rotation is done on hover via transform/transition */
.about-btn:hover .about-icon-circle,
.profile-btn:hover .about-icon-circle,
.about-btn:hover .about-icon,
.profile-btn:hover .about-icon{
  transform: none !important;
}

/* ===== PROFILE DROPDOWN (under profile icon) ===== */
.profile-wrap{
  position: relative;
  display: inline-flex;
  align-items: center;
}

/* the panel */
.profile-dropdown{
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 340px;
  border-radius: 18px;
  padding: 14px;
  z-index: 12000;

  background: linear-gradient(135deg, rgba(255,255,255,.95), rgba(226,232,240,.95));
  border: 1px solid rgba(148,163,184,.55);
  box-shadow: 0 22px 55px rgba(15,23,42,.22), 0 0 0 1px rgba(255,255,255,.7);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);

  /* closed state */
  opacity: 0;
  transform: translateY(-10px) scale(.985);
  pointer-events: none;
  transition: opacity .18s ease, transform .22s ease;
}

/* little arrow */
.profile-dropdown::before{
  content:"";
  position:absolute;
  top: -8px;
  right: 22px;
  width: 16px;
  height: 16px;
  background: inherit;
  border-left: 1px solid rgba(148,163,184,.45);
  border-top: 1px solid rgba(148,163,184,.45);
  transform: rotate(45deg);
}

/* open state */
.profile-dropdown.open{
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.profile-head{
  display:flex;
  gap: 12px;
  align-items:center;
  padding-bottom: 12px;
  margin-bottom: 12px;
  border-bottom: 1px solid rgba(148,163,184,.25);
}

.profile-avatar{
  width: 46px;
  height: 46px;
  border-radius: 16px;
  display:grid;
  place-items:center;
  font-weight: 800;
  letter-spacing: .08em;
  color: #0b1120;
  background: radial-gradient(circle at 30% 10%,
      rgba(254,249,195,.95),
      rgba(56,189,248,.60),
      rgba(79,70,229,.55));
  box-shadow: 0 14px 26px rgba(37,99,235,.16);
}

.profile-name{ font-weight: 700; color: #0b1120; }
.profile-email{ font-size: .85rem; opacity: .8; }

.profile-rows{ display:flex; flex-direction: column; gap: 10px; }
.profile-row{
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap: 14px;
  font-size: .92rem;
  color: #334155;
}
.mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; font-size: .82rem; opacity: .9; }

.pill{
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(56,189,248,.14);
  border: 1px solid rgba(56,189,248,.35);
  color: #0b2a4a;
  font-size: .78rem;
  letter-spacing: .08em;
  text-transform: uppercase;
}
.pill-ok{ background: rgba(34,197,94,.14); border-color: rgba(34,197,94,.35); color:#064e3b; }
.pill-off{ background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.30); color:#7f1d1d; }

.profile-actions{
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(148,163,184,.25);
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap: 10px;
}

.profile-link{
  text-decoration: none;
  font-weight: 700;
  color: #1d4ed8;
}
.profile-link:hover{ text-decoration: underline; }

.profile-logout button{
  border: none;
  cursor: pointer;
  padding: 10px 14px;
  border-radius: 12px;
  background: linear-gradient(135deg, #4a00e0, #8e2de2);
  color: white;
  font-weight: 700;
}
.profile-logout button:hover{
  filter: brightness(1.05);
}

/* Dark mode */
body.dark-mode .profile-dropdown{
  background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
  border-color: rgba(37,99,235,.70);
  box-shadow: 0 28px 70px rgba(0,0,0,.70), 0 0 22px rgba(30,64,175,.35);
}
body.dark-mode .profile-name{ color:#e5e7eb; }
body.dark-mode .profile-email{ color:#cbd5e1; opacity:.85; }
body.dark-mode .profile-row{ color:#cbd5e1; }
body.dark-mode .pill{ color:#e5e7eb; }


        /* DASHBOARD CARD (REUSED) */
        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px 60px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform-style: preserve-3d;
            animation: floatCard 5s ease-in-out infinite;
            transition: transform 0.3s, background 0.5s, box-shadow 0.5s;
            color: #333;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 10px;
        }
        @keyframes floatCard {
            0%, 100% { transform: rotateX(0deg) rotateY(0deg) translateY(0px); }
            50% { transform: rotateX(5deg) rotateY(5deg) translateY(-10px); }
        }
        .dashboard-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.2rem;
            font-weight: 700;
            color: #00bfff;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 16px;
            text-shadow: 2px 4px 8px rgba(30, 58, 138, 0.4);
        }
        .welcome {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            gap: 6px;
        }
        .welcome strong {
            border-right: 2px solid #00bfff;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            min-width: max-content;
            animation: blinkCursor 0.8s infinite;
        }
        @keyframes blinkCursor { 0%,100%{border-color:transparent} 50%{border-color:#00bfff} }

        body.dark-mode .container { background: #121627; box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1); color: #ccc; }
        body.dark-mode .dashboard-title { color: #66aaff; text-shadow: 2px 4px 8px rgba(102, 170, 255, 0.7); }
        body.dark-mode .welcome { color: #bbb; text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.1); }

        /* FOOTER */
        .footer {
            margin-top: 32px;
            padding: 16px 0 12px;
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            font-size: 0.9rem;
            color: #6b7280;
        }
        .footer-socials { display: flex; gap: 40px; }
        .footer-social-link {
            position: relative;
            width: 77px;
            height: 77px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            box-shadow: 0 6px 16px rgba(15,23,42,0.18), 0 0 0 1px rgba(148,163,184,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transform: translateY(0) scale(1);
            transition: transform 0.35s ease, box-shadow 0.35s ease, background 0.35s ease;
        }
        .footer-social-link::before {
            content: "";
            position: absolute;
            inset: -30%;
            border-radius: inherit;
            background: radial-gradient(circle at 20% 0%,
                        rgba(96,165,250,0.0) 0,
                        rgba(129,140,248,0.45) 35%,
                        rgba(56,189,248,0.0) 75%);
            opacity: 0;
            transform: scale(0.7);
            pointer-events: none;
            transition: opacity 0.35s ease, transform 0.35s ease;
        }
        .footer-social-link img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 0 4px rgba(15,23,42,0.35));
            transition: transform 0.35s ease, opacity 0.35s ease;
        }
        .footer-social-label {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 0.35s ease, transform 0.35s ease;
            color: #4b5563;
            text-align: center;
        }
        .footer-social-link:hover {
            transform: translateY(-6px) scale(1.06);
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            box-shadow: 0 14px 32px rgba(15,23,42,0.75), 0 0 0 1px rgba(129,140,248,0.95);
        }
        .footer-social-link:hover::before { opacity: 1; transform: scale(1); }
        .footer-social-link:hover img { transform: scale(0.7) rotate(-6deg); opacity: 0.15; }
        .footer-social-link:hover .footer-social-label { opacity: 1; transform: translateY(0); color: #e5e7eb; }
        .footer-copy { font-size: 1rem; opacity: 0.85; gap: 2px; }

        body.dark-mode .footer { border-top-color: rgba(31,41,55,0.9); color: #9ca3af; }
        body.dark-mode .footer-social-link {
            background: rgba(15,23,42,0.95);
            box-shadow: 0 8px 20px rgba(0,0,0,0.85), 0 0 0 1px rgba(30,64,175,0.7);
        }
        body.dark-mode .footer-social-link::before {
            background: radial-gradient(circle at 20% 0%,
                        rgba(56,189,248,0.05) 0,
                        rgba(129,140,248,0.6) 35%,
                        rgba(15,23,42,0.0) 80%);
        }
        body.dark-mode .footer-copy { color: #9ca3af; }

        /* DHAKA WEATHER */
        .top-bar-left { flex-wrap: wrap; }
        .weather-display{
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(226, 232, 240, 0.95));
            border: 1px solid rgba(148, 163, 184, 0.65);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.20), 0 0 0 1px rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transform-style: preserve-3d;
            perspective: 900px;
            overflow: hidden;
            animation: wxFloat 8.5s ease-in-out infinite;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
        }
        .weather-display:hover{
            transform: translateY(-3px) rotateX(7deg) rotateY(-9deg);
            box-shadow: 0 22px 46px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(191, 219, 254, 0.95);
        }
        .weather-display::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(from 160deg,
                rgba(56,189,248,0),
                rgba(56,189,248,.32),
                rgba(79,70,229,.22),
                rgba(34,197,94,.14),
                rgba(56,189,248,0)
            );
            filter: blur(10px);
            opacity: .55;
            pointer-events:none;
        }
        .weather-display::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius: inherit;
            background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.24) 18%, transparent 36%);
            transform: translateX(-140%);
            opacity: .35;
            pointer-events:none;
            animation: wxShine 4.8s ease-in-out infinite;
        }
        @keyframes wxShine{ 0%,55%{transform:translateX(-140%)} 75%,100%{transform:translateX(140%)} }
        @keyframes wxFloat{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }

        .wx-icon{
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            position: relative;
            background: radial-gradient(circle at 30% 10%,
                rgba(254, 249, 195, 0.95),
                rgba(56, 189, 248, 0.70),
                rgba(79, 70, 229, 0.60)
            );
            box-shadow: 0 12px 24px rgba(37,99,235,.22), inset 0 0 0 1px rgba(255,255,255,.55);
            transform: translateZ(14px);
            overflow: hidden;
        }
        .wx-icon::before{
            content:"";
            position:absolute;
            inset:-40%;
            background: conic-gradient(from 0deg, rgba(255,255,255,.70), rgba(255,255,255,0), rgba(255,255,255,.45), rgba(255,255,255,0));
            opacity:.28;
            transform: scale(1.02);
            animation: wxBreathe 3.8s ease-in-out infinite;
        }
        @keyframes wxBreathe{ 0%,100%{opacity:.18; transform:scale(1.00)} 50%{opacity:.38; transform:scale(1.06)} }
        .wx-emoji{ position: relative; z-index: 1; font-size: 1.2rem; filter: drop-shadow(0 6px 12px rgba(15,23,42,.25)); }
        .wx-text{ transform: translateZ(10px); }
        .wx-top{ display:flex; align-items:center; gap:10px; margin-bottom: 4px; }
        .wx-city{ font-family: 'Orbitron','Poppins',sans-serif; letter-spacing:.08em; text-transform: uppercase; font-size:.85rem; opacity:.9; }
        .wx-chip{
            font-size: .68rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(56,189,248,.16);
            border: 1px solid rgba(56,189,248,.55);
            color: #0b2a4a;
        }
        .wx-temp{ font-size: 1.35rem; font-weight: 700; letter-spacing: .06em; line-height: 1.1; }
        .wx-meta{ font-size: .82rem; opacity: .82; white-space: nowrap; }

        body.dark-mode .weather-display{
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98));
            border-color: rgba(37, 99, 235, 0.90);
            box-shadow: 0 18px 40px rgba(0,0,0,.92), 0 0 22px rgba(30,64,175,.55);
        }
        body.dark-mode .weather-display::after{
            background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.10) 18%, transparent 36%);
            opacity: .26;
        }
        body.dark-mode .wx-chip{
            background: rgba(129,140,248,.14);
            border-color: rgba(129,140,248,.55);
            color: #e5e7eb;
        }

        /* ABOUT OVERLAY */
        body::before { z-index: 9000; }
        body.modal-open .sidebar,
        body.modal-open .main-content{
          filter: blur(8px);
          transform: scale(0.995);
          pointer-events: none;
        }
        .about-overlay{
          position: fixed;
          inset: 0;
          display: grid;
          place-items: center;
          padding: 24px;
          z-index: 10050;
          opacity: 0;
          visibility: hidden;
          pointer-events: none;
          transition: opacity .22s ease, visibility .22s ease;
          background:
            radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.22), transparent 60%),
            radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.20), transparent 62%),
            rgba(15, 23, 42, 0.28);
          backdrop-filter: blur(14px);
          -webkit-backdrop-filter: blur(14px);
        }
        .about-overlay.open{ opacity: 1; visibility: visible; pointer-events: auto; }
        .about-modal{
          position: relative;
          width: min(1120px, 96vw);
          height: min(86vh, 780px);
          overflow: hidden;
          border-radius: 26px;
          padding: 26px 26px 18px;
          display: grid;
          grid-template-rows: auto 1fr auto;
          transform: translateY(14px) scale(.985);
          opacity: 0;
          transition: transform .26s ease, opacity .26s ease;
          transform-style: preserve-3d;
          background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
          border: 1px solid rgba(148,163,184,.55);
          box-shadow: 0 30px 80px rgba(15,23,42,.34), 0 0 0 1px rgba(255,255,255,.75);
        }
        .about-overlay.open .about-modal{ transform: translateY(0) scale(1); opacity: 1; }
        .about-close{
          position: absolute;
          top: 18px;
          right: 18px;
          display: grid;
          place-items: center;
          width: 44px;
          height: 44px;
          border-radius: 14px;
          border: 1px solid rgba(148,163,184,.55);
          background: rgba(255,255,255,.55);
          cursor: pointer;
          box-shadow: 0 10px 22px rgba(15,23,42,.18);
          transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
          z-index: 5;
        }
        .about-close:hover{ transform: translateY(-1px) scale(1.03); box-shadow: 0 16px 30px rgba(15,23,42,.22); background: rgba(255,255,255,.75); }
        .about-hero{ position: relative; padding: 10px 8px 18px; }
        .about-hero-badge{
          display: inline-flex;
          align-items: center;
          gap: 10px;
          padding: 8px 14px;
          border-radius: 999px;
          background: rgba(56,189,248,.12);
          border: 1px solid rgba(56,189,248,.40);
          color: #0b2a4a;
          font-size: .72rem;
          letter-spacing: .18em;
          text-transform: uppercase;
        }
        .about-dot{
          width: 10px;
          height: 10px;
          border-radius: 999px;
          background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
          box-shadow: 0 0 12px rgba(56,189,248,.75);
        }
        .about-title{
          font-family: 'Orbitron','Poppins',sans-serif;
          margin: 14px 0 6px;
          font-size: 2.2rem;
          letter-spacing: .12em;
          text-transform: uppercase;
          color: #0b1120;
          text-shadow: 2px 6px 18px rgba(30,58,138,.18);
        }
        .about-subtitle{
          margin: 0;
          font-size: 1.02rem;
          opacity: .85;
          color: #1f2937;
          max-width: 70ch;
        }
        .about-tags{ margin-top: 16px; display:flex; flex-wrap: wrap; gap: 10px; }
        .about-tag{
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 9px 12px;
          border-radius: 999px;
          font-size: .82rem;
          background: rgba(255,255,255,.55);
          border: 1px solid rgba(148,163,184,.45);
          color: #0b1120;
          box-shadow: 0 10px 20px rgba(15,23,42,.10);
        }
        .about-grid{ display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 14px; padding: 6px 6px 14px; }
        .about-card{
          background: rgba(255,255,255,.65);
          border: 1px solid rgba(148,163,184,.40);
          border-radius: 18px;
          padding: 14px 14px 12px;
          box-shadow: 0 16px 34px rgba(15,23,42,.12), inset 0 0 0 1px rgba(255,255,255,.55);
          transform-style: preserve-3d;
          transition: transform .18s ease, box-shadow .18s ease;
        }
        .about-card:hover{ transform: translateY(-2px) rotateX(5deg) rotateY(-6deg); box-shadow: 0 22px 46px rgba(15,23,42,.16); }
        .about-card-head{ display:flex; align-items:center; gap: 10px; margin-bottom: 6px; }
        .about-card h3{ margin: 0; font-size: 1.05rem; color: #0b1120; }
        .about-card p{ margin: 0; color: #334155; opacity: .92; line-height: 1.5; font-size: .95rem; }
        .about-ic{
          width: 42px;
          height: 42px;
          border-radius: 14px;
          display:grid;
          place-items:center;
          color: #0b1120;
          background: radial-gradient(circle at 30% 10%, rgba(254,249,195,.95), rgba(56,189,248,.55), rgba(79,70,229,.45));
          box-shadow: 0 14px 26px rgba(37,99,235,.16);
          transform: translateZ(12px);
        }
        .about-footer{ padding: 10px 10px 0; border-top: 1px solid rgba(148,163,184,.25); margin-top: 8px; }
        .about-mini{ font-size: .9rem; opacity: .82; color: #334155; }

        body.dark-mode .about-overlay{
          background:
            radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.18), transparent 60%),
            radial-gradient(900px 560px at 85% 22%, rgba(129,140,248,.16), transparent 62%),
            rgba(2, 6, 23, 0.62);
        }
        body.dark-mode .about-modal{
          background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
          border-color: rgba(37,99,235,.70);
          box-shadow: 0 34px 90px rgba(0,0,0,.70), 0 0 22px rgba(30,64,175,.40);
        }
        body.dark-mode .about-close{
          background: rgba(15,23,42,.55);
          border-color: rgba(129,140,248,.55);
          color: #e5e7eb;
        }
        body.dark-mode .about-hero-badge{
          background: rgba(129,140,248,.12);
          border-color: rgba(129,140,248,.35);
          color: #e5e7eb;
        }
        body.dark-mode .about-title{ color: #e5e7eb; }
        body.dark-mode .about-subtitle{ color: #cbd5e1; }
        body.dark-mode .about-tag{
          background: rgba(15,23,42,.55);
          border-color: rgba(129,140,248,.35);
          color: #e5e7eb;
        }
        body.dark-mode .about-card{ background: rgba(15,23,42,.62); border-color: rgba(129,140,248,.30); }
        body.dark-mode .about-card h3{ color: #e5e7eb; }
        body.dark-mode .about-card p{ color: #cbd5e1; }
        body.dark-mode .about-mini{ color: #cbd5e1; }
        @media (max-width: 820px){ .about-grid{ grid-template-columns: 1fr; } }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { position: fixed; width: var(--sidebar-collapsed-width); }
            .sidebar.collapsed { width: 0; }
            .main-content { margin-left: var(--sidebar-collapsed-width); width: calc(100% - var(--sidebar-collapsed-width)); padding: 20px; }
            .sidebar.collapsed + .main-content { margin-left: 0; width: 100%; }
            .time-display { font-size: 1.4rem; padding: 10px 16px; }
            .container { padding: 30px 20px; }
            .dashboard-title { font-size: 2.4rem; }
        }

        /* =========================
           INSIGHT STREAMS UI
           ========================= */
        .streams-wrap{
            max-width: 1320px;
            margin: 0 auto;
            padding: 16px 0 34px;
        }

        .streams-hero{
            position: relative;
            border-radius: 24px;
            padding: 22px 22px 18px;
            overflow: hidden;

            background: linear-gradient(135deg, rgba(255,255,255,.88), rgba(226,232,240,.88));
            border: 1px solid rgba(148,163,184,.55);
            box-shadow: 0 22px 60px rgba(15,23,42,.18), inset 0 0 0 1px rgba(255,255,255,.72);

            transform-style: preserve-3d;
            perspective: 1200px;
        }
        body.dark-mode .streams-hero{
            background: linear-gradient(135deg, rgba(15,23,42,.90), rgba(2,6,23,.94));
            border-color: rgba(37,99,235,.65);
            box-shadow: 0 30px 70px rgba(0,0,0,.70), 0 0 22px rgba(30,64,175,.28);
        }
        .streams-hero::before{
            content:"";
            position:absolute;
            inset:-30%;
            background:
                radial-gradient(900px 520px at 15% 15%, rgba(56,189,248,.28), transparent 60%),
                radial-gradient(880px 520px at 85% 18%, rgba(79,70,229,.22), transparent 62%),
                radial-gradient(700px 420px at 45% 95%, rgba(34,197,94,.12), transparent 55%);
            filter: blur(6px);
            opacity: .9;
            pointer-events:none;
        }
        .streams-hero-head{
            position: relative;
            display:flex;
            align-items:flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            z-index: 1;
        }
        .streams-title{
            font-family: 'Orbitron', 'Poppins', sans-serif;
            font-size: 2.1rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin: 0;
            color: #0b1120;
            text-shadow: 2px 10px 28px rgba(30,58,138,.18);
        }
        body.dark-mode .streams-title{ color: #e5e7eb; }
        .streams-sub{
            margin: 8px 0 0;
            opacity: .9;
            max-width: 72ch;
            color: #1f2937;
            position: relative;
            z-index: 1;
        }
        body.dark-mode .streams-sub{ color:#cbd5e1; opacity:.92; }

        .streams-controls{
            position: relative;
            z-index: 1;
            display:flex;
            gap: 12px;
            align-items:center;
            flex-wrap: wrap;
        }
        .search-pill{
            position: relative;
            display:flex;
            align-items:center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 999px;
            min-width: min(460px, 82vw);
            background: rgba(255,255,255,.55);
            border: 1px solid rgba(148,163,184,.50);
            box-shadow: 0 12px 30px rgba(15,23,42,.12);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        body.dark-mode .search-pill{ background: rgba(15,23,42,.50); border-color: rgba(129,140,248,.35); }
        .search-pill i{ opacity:.75; }
        body.dark-mode .search-pill i{ color:#e5e7eb; opacity:.85; }
        .search-pill input{
            border:none;
            outline:none;
            width: 100%;
            background: transparent;
            font-size: .98rem;
            color: #0b1120;
        }
        body.dark-mode .search-pill input{ color:#e5e7eb; }

        .chip-btn{
            border: none;
            cursor: pointer;
            padding: 12px 14px;
            border-radius: 999px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .72rem;
            color: #0b1120;
            background: linear-gradient(135deg, rgba(254,249,195,.85), rgba(56,189,248,.38), rgba(79,70,229,.32));
            border: 1px solid rgba(56,189,248,.55);
            box-shadow: 0 16px 34px rgba(15,23,42,.16);
            transition: transform .18s ease, filter .18s ease;
        }
        .chip-btn:hover{ transform: translateY(-2px); filter: brightness(1.03); }
        body.dark-mode .chip-btn{ color:#e5e7eb; border-color: rgba(129,140,248,.55); }

        .skills-grid{
            margin-top: 18px;
            display:grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        @media (max-width: 1100px){ .skills-grid{ grid-template-columns: repeat(2, 1fr);} }
        @media (max-width: 740px){ .skills-grid{ grid-template-columns: 1fr;} }

        .skill-holo{
            position: relative;
            border-radius: 22px;
            padding: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
            border: 1px solid rgba(148,163,184,.55);
            box-shadow: 0 20px 55px rgba(15,23,42,.14), inset 0 0 0 1px rgba(255,255,255,.70);
            transform-style: preserve-3d;
            perspective: 1000px;
            transform: rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) translateZ(0);
            transition: transform .18s ease, box-shadow .22s ease, border-color .22s ease;
            opacity: 0;
            transform-origin: center;
        }
        .skill-holo.is-visible{ opacity: 1; animation: streamIn .55s ease forwards; }
        @keyframes streamIn{
            from{ transform: translateY(10px) rotateX(0) rotateY(0); opacity: 0; }
            to  { transform: translateY(0) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)); opacity: 1; }
        }
        body.dark-mode .skill-holo{
            background: linear-gradient(135deg, rgba(15,23,42,.90), rgba(2,6,23,.94));
            border-color: rgba(37,99,235,.65);
            box-shadow: 0 28px 70px rgba(0,0,0,.72), 0 0 18px rgba(30,64,175,.20);
        }
        .skill-holo::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            padding: 2px;
            opacity: .45;
            background: linear-gradient(135deg, rgba(56,189,248,.70), rgba(79,70,229,.65), rgba(250,204,21,.40));
            pointer-events:none;
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            transition: opacity .18s ease;
        }
        .skill-holo::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius: inherit;
            background: radial-gradient(420px 260px at var(--mx, 50%) var(--my, 40%), rgba(255,255,255,.28), transparent 60%);
            opacity: .55;
            pointer-events:none;
            mix-blend-mode: soft-light;
        }
        body.dark-mode .skill-holo::after{
            background: radial-gradient(420px 260px at var(--mx, 50%) var(--my, 40%), rgba(56,189,248,.18), transparent 62%);
            mix-blend-mode: screen;
            opacity: .45;
        }
        .skill-holo:hover{
            box-shadow: 0 30px 82px rgba(15,23,42,.22), inset 0 0 0 1px rgba(255,255,255,.72);
            border-color: rgba(191,219,254,.95);
        }
        .skill-holo:hover::before{ opacity: .85; }

        .skill-top{
            position: relative;
            z-index: 1;
            display:flex;
            align-items:flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .skill-name{
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: #0b1120;
        }
        body.dark-mode .skill-name{ color:#e5e7eb; }
        .skill-code{
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: .75rem;
            opacity: .75;
        }
        body.dark-mode .skill-code{ color:#cbd5e1; opacity:.8; }
        .skill-desc{
            position: relative;
            z-index: 1;
            margin: 8px 0 12px;
            color: #334155;
            opacity: .92;
            line-height: 1.55;
            font-size: .93rem;
            min-height: 2.9em;
        }
        body.dark-mode .skill-desc{ color:#cbd5e1; opacity:.92; }

        .preview-row{
            position: relative;
            z-index: 1;
            display:flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 10px 0 12px;
        }
        .vid-chip{
            display:flex;
            align-items:center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .78rem;
            background: rgba(56,189,248,.12);
            border: 1px solid rgba(56,189,248,.35);
            color: #0b2a4a;
        }
        body.dark-mode .vid-chip{
            background: rgba(129,140,248,.12);
            border-color: rgba(129,140,248,.35);
            color:#e5e7eb;
        }

        .skill-actions{
            position: relative;
            z-index: 1;
            display:flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .btn-primary{
            border: none;
            cursor: pointer;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .72rem;
            color: white;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            box-shadow: 0 18px 40px rgba(15,23,42,.18);
            transition: transform .18s ease, filter .18s ease;
        }
        .btn-primary:hover{ transform: translateY(-2px); filter: brightness(1.04); }
        .btn-ghost{
            border: 1px solid rgba(148,163,184,.55);
            cursor: pointer;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .72rem;
            background: rgba(255,255,255,.55);
            color: #0b1120;
            box-shadow: 0 14px 34px rgba(15,23,42,.10);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: transform .18s ease;
        }
        .btn-ghost:hover{ transform: translateY(-2px); }
        body.dark-mode .btn-ghost{
            background: rgba(15,23,42,.50);
            color:#e5e7eb;
            border-color: rgba(129,140,248,.35);
        }

        /* Stream Modal */
        .streams-overlay{
            position: fixed;
            inset: 0;
            z-index: 15000;
            display: grid;
            place-items: center;
            padding: 22px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .18s ease, visibility .18s ease;
            background:
                radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.18), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.16), transparent 62%),
                rgba(2, 6, 23, 0.52);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .streams-overlay.open{ opacity: 1; visibility: visible; pointer-events: auto; }

        .streams-modal{
            width: min(1120px, 96vw);
            height: min(86vh, 820px);
            border-radius: 26px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(255,255,255,.94), rgba(226,232,240,.94));
            border: 1px solid rgba(148,163,184,.55);
            box-shadow: 0 34px 90px rgba(15,23,42,.34);
            display: grid;
            grid-template-rows: auto 1fr;
            transform: translateY(10px) scale(.99);
            opacity: 0;
            transition: transform .22s ease, opacity .22s ease;
        }
        body.dark-mode .streams-modal{
            background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            border-color: rgba(37,99,235,.65);
            box-shadow: 0 34px 90px rgba(0,0,0,.72);
        }
        .streams-overlay.open .streams-modal{ transform: translateY(0) scale(1); opacity: 1; }

        .streams-modal-head{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(148,163,184,.25);
        }
        .modal-title{
            margin: 0;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: 1rem;
            color: #0b1120;
        }
        body.dark-mode .modal-title{ color:#e5e7eb; }
        .modal-meta{ font-size: .85rem; opacity: .85; }
        body.dark-mode .modal-meta{ color:#cbd5e1; }

        .modal-close{
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(148,163,184,.55);
            background: rgba(255,255,255,.55);
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(15,23,42,.18);
        }
        body.dark-mode .modal-close{
            background: rgba(15,23,42,.55);
            border-color: rgba(129,140,248,.35);
            color:#e5e7eb;
        }

        .videos-grid{
            padding: 16px;
            overflow: auto;
            display:grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }
        @media (max-width: 1100px){ .videos-grid{ grid-template-columns: repeat(3, 1fr);} }
        @media (max-width: 700px){ .videos-grid{ grid-template-columns: repeat(2, 1fr);} }

        .video-card{
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(148,163,184,.45);
            background: rgba(255,255,255,.65);
            box-shadow: 0 16px 38px rgba(15,23,42,.10);
            transform-style: preserve-3d;
            transition: transform .16s ease;
            display:flex;
            flex-direction: column;
        }
        .video-card:hover{ transform: translateY(-2px) rotateX(4deg) rotateY(-5deg); }
        body.dark-mode .video-card{ background: rgba(15,23,42,.62); border-color: rgba(129,140,248,.25); }

        .video-thumb{
            position: relative;
            height: 110px;
            background:
                radial-gradient(420px 240px at 20% 10%, rgba(56,189,248,.20), transparent 55%),
                radial-gradient(420px 240px at 90% 20%, rgba(79,70,229,.16), transparent 60%),
                linear-gradient(135deg, rgba(15,23,42,.06), rgba(15,23,42,.02));
        }
        body.dark-mode .video-thumb{
            background:
                radial-gradient(420px 240px at 20% 10%, rgba(56,189,248,.14), transparent 58%),
                radial-gradient(420px 240px at 90% 20%, rgba(129,140,248,.12), transparent 62%),
                linear-gradient(135deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
        }
        .play-badge{
            position:absolute;
            inset: 0;
            display:grid;
            place-items:center;
            font-size: 1.4rem;
            color: rgba(255,255,255,.92);
            text-shadow: 0 10px 20px rgba(0,0,0,.35);
        }
        .video-body{
            padding: 10px 10px 12px;
            display:flex;
            flex-direction: column;
            gap: 8px;
        }
        .video-title{
            font-size: .9rem;
            font-weight: 800;
            color:#0b1120;
            line-height: 1.25;
            min-height: 2.4em;
        }
        body.dark-mode .video-title{ color:#e5e7eb; }
        .video-mini{
            font-size: .78rem;
            opacity: .78;
            display:flex;
            justify-content: space-between;
            gap: 10px;
        }
        body.dark-mode .video-mini{ color:#cbd5e1; opacity:.85; }
        .video-watch{
            margin-top: 4px;
            border: none;
            cursor: pointer;
            padding: 10px 12px;
            border-radius: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: .7rem;
            color: #fff;
            background: linear-gradient(135deg, #06b6d4, #4f46e5);
        }

        /* Player modal */
        .player-overlay{
            position: fixed;
            inset: 0;
            z-index: 16000;
            display:grid;
            place-items:center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .18s ease, visibility .18s ease;
            background: rgba(2,6,23,.62);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .player-overlay.open{ opacity: 1; visibility: visible; pointer-events: auto; }
        .player-modal{
            width: min(980px, 96vw);
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(148,163,184,.55);
            background: rgba(15,23,42,.92);
            box-shadow: 0 34px 90px rgba(0,0,0,.70);
        }
        .player-head{
            display:flex;
            justify-content: space-between;
            align-items:center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(148,163,184,.25);
            color:#e5e7eb;
        }
        .player-title{ font-weight: 900; letter-spacing: .12em; text-transform: uppercase; font-size: .9rem; }
        .player-close{
            width: 44px; height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(129,140,248,.35);
            background: rgba(15,23,42,.55);
            color:#e5e7eb;
            cursor: pointer;
        }
        .player-body{ padding: 18px; color:#cbd5e1; }
        .empty-note{ margin: 0; opacity: .9; line-height: 1.6; }

        @media (prefers-reduced-motion: reduce){
            .skill-holo, .skill-holo:hover, .video-card:hover { transition: none !important; }
        }

        /* =========================================================
   INSIGHT STREAMS — Premium / Unique / Professional (CSS only)
   Paste this BELOW your existing CSS (overrides Insight section)
   ========================================================= */

/* Scope: only Insight Streams area + its modals */
.streams-wrap{
  /* local design tokens */
  --is-bg: rgba(255,255,255,.72);
  --is-card: rgba(255,255,255,.78);
  --is-border: rgba(148,163,184,.50);
  --is-text: #0b1120;
  --is-muted: rgba(30,41,59,.78);
  --is-shadow: 0 22px 60px rgba(15,23,42,.14);
  --is-shadow-2: 0 34px 90px rgba(15,23,42,.20);
  --is-ring: 0 0 0 3px rgba(56,189,248,.22);
  --is-grad: linear-gradient(135deg, #38bdf8 0%, #4f46e5 45%, #a855f7 100%);
  --is-grad-soft: linear-gradient(135deg, rgba(56,189,248,.24), rgba(79,70,229,.18), rgba(168,85,247,.12));

  max-width: 1400px;
  margin: 0 auto;
  padding: 22px 0 46px;
  position: relative;
  isolation: isolate;
}

/* subtle section aura (doesn't touch topbar/sidebar) */
.streams-wrap::before{
  content:"";
  position:absolute;
  inset: -40px -24px -24px -24px;
  pointer-events:none;
  z-index:-1;
  background:
    radial-gradient(900px 420px at 18% 10%, rgba(56,189,248,.14), transparent 58%),
    radial-gradient(820px 420px at 86% 12%, rgba(79,70,229,.12), transparent 60%),
    radial-gradient(720px 380px at 50% 95%, rgba(34,197,94,.08), transparent 60%);
  filter: blur(2px);
  opacity: .9;
}

body.dark-mode .streams-wrap{
  --is-bg: rgba(2,6,23,.62);
  --is-card: rgba(15,23,42,.62);
  --is-border: rgba(129,140,248,.28);
  --is-text: #e5e7eb;
  --is-muted: rgba(203,213,225,.86);
  --is-shadow: 0 28px 80px rgba(0,0,0,.62);
  --is-shadow-2: 0 42px 110px rgba(0,0,0,.70);
  --is-ring: 0 0 0 3px rgba(129,140,248,.22);
  --is-grad: linear-gradient(135deg, #60a5fa 0%, #818cf8 45%, #22d3ee 100%);
  --is-grad-soft: linear-gradient(135deg, rgba(96,165,250,.18), rgba(129,140,248,.14), rgba(34,211,238,.10));
}

/* =========================
   HERO (title + search)
   ========================= */
.streams-hero{
  border-radius: 30px;
  padding: 28px 28px 22px;
  background: var(--is-bg);
  border: 1px solid var(--is-border);
  box-shadow: var(--is-shadow);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
  transform-style: preserve-3d;
  perspective: 1200px;
  position: relative;
}

.streams-hero::before{
  content:"";
  position:absolute;
  inset:-35%;
  pointer-events:none;
  background:
    radial-gradient(980px 520px at 14% 18%, rgba(56,189,248,.24), transparent 62%),
    radial-gradient(980px 520px at 86% 22%, rgba(79,70,229,.20), transparent 64%),
    radial-gradient(900px 460px at 50% 110%, rgba(168,85,247,.12), transparent 64%);
  filter: blur(7px);
  opacity: .95;
}

.streams-hero::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  opacity: .55;
  background:
    linear-gradient(120deg, transparent 0%, rgba(255,255,255,.22) 18%, transparent 36%) ,
    repeating-linear-gradient(90deg, rgba(255,255,255,.05) 0 1px, transparent 1px 10px);
  mix-blend-mode: soft-light;
  transform: translateX(-140%);
  animation: isHeroShine 6.8s ease-in-out infinite;
}
@keyframes isHeroShine{
  0%,55%{ transform: translateX(-140%); }
  75%,100%{ transform: translateX(140%); }
}

.streams-hero-head{
  position: relative;
  z-index: 1;
  display:flex;
  align-items:flex-end;
  justify-content: space-between;
  gap: 18px;
  flex-wrap: wrap;
}

.streams-title{
  margin: 0;
  font-family: 'Orbitron','Poppins',sans-serif;
  font-size: clamp(1.7rem, 2.2vw, 2.35rem);
  letter-spacing: .14em;
  text-transform: uppercase;
  line-height: 1.08;
  color: var(--is-text);
  text-shadow: 0 18px 40px rgba(30,58,138,.14);
  position: relative;
}

/* gradient headline (with safe fallback) */
@supports (-webkit-background-clip:text){
  .streams-title{
    background: var(--is-grad);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
}

.streams-sub{
  margin: 10px 0 0;
  max-width: 76ch;
  color: var(--is-muted);
  opacity: .98;
  line-height: 1.55;
  font-size: 1.02rem;
}

/* =========================
   Controls (search + clear)
   ========================= */
.streams-controls{
  display:flex;
  gap: 12px;
  align-items:center;
  flex-wrap: wrap;
}

.search-pill{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 999px;
  min-width: min(520px, 82vw);
  background: var(--is-card);
  border: 1px solid var(--is-border);
  box-shadow: 0 16px 40px rgba(15,23,42,.10);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
}

.search-pill i{
  opacity: .78;
  color: var(--is-text);
}

.search-pill input{
  border: none;
  outline: none;
  background: transparent;
  width: 100%;
  color: var(--is-text);
  font-size: 1rem;
  letter-spacing: .01em;
}

.search-pill input::placeholder{
  color: rgba(100,116,139,.85);
}

body.dark-mode .search-pill input::placeholder{
  color: rgba(203,213,225,.55);
}

/* focus glow without touching other inputs on the page */
.search-pill:focus-within{
  border-color: rgba(56,189,248,.75);
  box-shadow: 0 18px 44px rgba(15,23,42,.14), var(--is-ring);
  transform: translateY(-1px);
}

.chip-btn{
  position: relative;
  border: 1px solid rgba(56,189,248,.55);
  cursor: pointer;
  padding: 12px 16px;
  border-radius: 999px;
  font-weight: 900;
  letter-spacing: .14em;
  text-transform: uppercase;
  font-size: .72rem;
  color: #0b1120;
  background: linear-gradient(135deg, rgba(254,249,195,.88), rgba(56,189,248,.35), rgba(79,70,229,.28));
  box-shadow: 0 16px 38px rgba(15,23,42,.14);
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
  overflow: hidden;
  -webkit-tap-highlight-color: transparent;
}

.chip-btn::after{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  opacity: .30;
  background: var(--is-grad);
  pointer-events:none;
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  transition: opacity .18s ease;
}

.chip-btn:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 22px 54px rgba(15,23,42,.18);
}
.chip-btn:hover::after{ opacity: .70; }

.chip-btn:active{ transform: translateY(0) scale(.98); }

.chip-btn:focus-visible{
  outline: none;
  box-shadow: 0 18px 44px rgba(15,23,42,.16), var(--is-ring);
}

body.dark-mode .chip-btn{
  color: #e5e7eb;
  border-color: rgba(129,140,248,.55);
  background: linear-gradient(135deg, rgba(15,23,42,.70), rgba(79,70,229,.22), rgba(56,189,248,.18));
}

/* =========================
   Skills grid + cards
   ========================= */
.skills-grid{
  margin-top: 18px;
  display:grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 1120px){ .skills-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 760px){ .skills-grid{ grid-template-columns: 1fr; } }

.skill-holo{
  border-radius: 24px;
  padding: 18px;
  background: var(--is-card);
  border: 1px solid var(--is-border);
  box-shadow: 0 22px 55px rgba(15,23,42,.12), inset 0 0 0 1px rgba(255,255,255,.55);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  will-change: transform;
  transition: box-shadow .22s ease, border-color .22s ease, filter .22s ease;
}

/* stronger premium ring + soft holo wash */
.skill-holo::before{
  opacity: .55;
  background: linear-gradient(135deg, rgba(56,189,248,.75), rgba(79,70,229,.68), rgba(168,85,247,.42));
}
.skill-holo::after{
  opacity: .65;
  background:
    radial-gradient(520px 300px at var(--mx, 50%) var(--my, 40%), rgba(255,255,255,.34), transparent 62%),
    radial-gradient(520px 300px at 90% 10%, rgba(56,189,248,.16), transparent 62%);
}

body.dark-mode .skill-holo{
  background: var(--is-card);
  border-color: var(--is-border);
  box-shadow: 0 30px 80px rgba(0,0,0,.62), inset 0 0 0 1px rgba(30,64,175,.22);
}
body.dark-mode .skill-holo::after{
  opacity: .55;
  background:
    radial-gradient(520px 300px at var(--mx, 50%) var(--my, 40%), rgba(56,189,248,.20), transparent 64%),
    radial-gradient(520px 300px at 85% 12%, rgba(129,140,248,.16), transparent 62%);
  mix-blend-mode: screen;
}

.skill-holo:hover{
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 34px 92px rgba(15,23,42,.18), inset 0 0 0 1px rgba(255,255,255,.68);
  filter: saturate(1.06);
}
body.dark-mode .skill-holo:hover{
  border-color: rgba(129,140,248,.55);
  box-shadow: 0 40px 110px rgba(0,0,0,.72), 0 0 28px rgba(56,189,248,.12);
}

.skill-top{ gap: 12px; }

.skill-name{
  font-size: 1.12rem;
  font-weight: 900;
  letter-spacing: .01em;
  color: var(--is-text);
}

.skill-code{
  display: inline-flex;
  align-items:center;
  gap: 8px;
  margin-top: 6px;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(56,189,248,.12);
  border: 1px solid rgba(56,189,248,.26);
  color: rgba(30,41,59,.85);
  font-size: .74rem;
  font-weight: 700;
}
body.dark-mode .skill-code{
  background: rgba(129,140,248,.10);
  border-color: rgba(129,140,248,.26);
  color: rgba(226,232,240,.88);
}

.skill-desc{
  margin: 12px 0 12px;
  color: var(--is-muted);
  font-size: .96rem;
  line-height: 1.6;
}

/* chips */
.vid-chip{
  background: rgba(2,132,199,.10);
  border: 1px solid rgba(56,189,248,.26);
  color: rgba(12,74,110,.92);
  font-weight: 700;
}
body.dark-mode .vid-chip{
  background: rgba(56,189,248,.10);
  border-color: rgba(129,140,248,.26);
  color: rgba(226,232,240,.88);
}

.preview-row{ gap: 10px; opacity: .95; }

/* buttons */
.skill-actions{ gap: 12px; }

.btn-primary,
.btn-ghost{
  min-height: 44px;
  border-radius: 16px;
  letter-spacing: .14em;
  font-weight: 950;
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease, border-color .18s ease;
}

.btn-primary{
  background: var(--is-grad);
  box-shadow: 0 18px 44px rgba(15,23,42,.18);
}
.btn-primary:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 24px 60px rgba(15,23,42,.22);
}
.btn-primary:active{ transform: translateY(0) scale(.99); }

.btn-ghost{
  background: rgba(255,255,255,.55);
  border: 1px solid var(--is-border);
  color: var(--is-text);
  box-shadow: 0 14px 34px rgba(15,23,42,.10);
}
.btn-ghost:hover{
  transform: translateY(-2px);
  border-color: rgba(56,189,248,.45);
  box-shadow: 0 20px 48px rgba(15,23,42,.14);
}
body.dark-mode .btn-ghost{
  background: rgba(15,23,42,.46);
  border-color: rgba(129,140,248,.26);
  color: #e5e7eb;
}

/* focus accessibility (only these buttons) */
.btn-primary:focus-visible,
.btn-ghost:focus-visible{
  outline: none;
  box-shadow: 0 0 0 3px rgba(56,189,248,.22), 0 20px 52px rgba(15,23,42,.18);
}

/* =========================
   Stream modal (10 videos)
   ========================= */
.streams-overlay{
  background:
    radial-gradient(1100px 620px at 18% 18%, rgba(56,189,248,.18), transparent 62%),
    radial-gradient(1100px 620px at 85% 20%, rgba(79,70,229,.16), transparent 64%),
    rgba(2, 6, 23, 0.58);
}

.streams-modal{
  border-radius: 30px;
  border: 1px solid var(--is-border);
  background: var(--is-bg);
  box-shadow: var(--is-shadow-2);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
}

body.dark-mode .streams-modal{
  background: rgba(2,6,23,.78);
  border-color: rgba(129,140,248,.30);
}

.streams-modal-head{
  padding: 18px 18px;
  border-bottom: 1px solid rgba(148,163,184,.22);
}

.modal-title{
  color: var(--is-text);
  letter-spacing: .16em;
  font-size: 1.02rem;
}
.modal-meta{ color: var(--is-muted); }

.modal-close{
  border-radius: 16px;
  background: rgba(255,255,255,.55);
  border: 1px solid var(--is-border);
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}
.modal-close:hover{
  transform: translateY(-1px);
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 16px 34px rgba(15,23,42,.16);
}
body.dark-mode .modal-close{
  background: rgba(15,23,42,.55);
  border-color: rgba(129,140,248,.26);
  color: #e5e7eb;
}

/* videos grid + scrollbar polish */
.videos-grid{
  padding: 18px;
  gap: 14px;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  scrollbar-width: thin;
  scrollbar-color: rgba(56,189,248,.38) transparent;
}
.videos-grid::-webkit-scrollbar{ width: 10px; height: 10px; }
.videos-grid::-webkit-scrollbar-thumb{
  background: linear-gradient(180deg, rgba(56,189,248,.42), rgba(79,70,229,.34));
  border-radius: 999px;
  border: 2px solid transparent;
  background-clip: padding-box;
}
.videos-grid::-webkit-scrollbar-track{ background: transparent; }

.video-card{
  border-radius: 20px;
  background: rgba(255,255,255,.62);
  border: 1px solid rgba(148,163,184,.40);
  box-shadow: 0 18px 44px rgba(15,23,42,.10);
  overflow: hidden;
}
body.dark-mode .video-card{
  background: rgba(15,23,42,.60);
  border-color: rgba(129,140,248,.22);
  box-shadow: 0 22px 55px rgba(0,0,0,.55);
}

.video-thumb{
  height: 120px;
  background:
    radial-gradient(520px 280px at 18% 10%, rgba(56,189,248,.22), transparent 58%),
    radial-gradient(520px 280px at 88% 18%, rgba(79,70,229,.18), transparent 62%),
    linear-gradient(135deg, rgba(15,23,42,.08), rgba(15,23,42,.02));
  position: relative;
}

.play-badge{
  font-size: 1.55rem;
  color: rgba(255,255,255,.94);
}
.video-thumb::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background: linear-gradient(180deg, rgba(2,6,23,.00), rgba(2,6,23,.18));
}

.video-body{ padding: 12px 12px 14px; }

.video-title{
  color: var(--is-text);
  font-weight: 900;
  letter-spacing: .01em;
}
.video-mini{ color: var(--is-muted); }

.video-watch{
  border-radius: 14px;
  background: linear-gradient(135deg, #06b6d4, #4f46e5);
  box-shadow: 0 16px 38px rgba(15,23,42,.16);
  transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
}
.video-watch:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 22px 55px rgba(15,23,42,.20);
}
.video-watch:active{ transform: translateY(0) scale(.99); }

/* =========================
   Player modal (placeholder)
   ========================= */
.player-overlay{
  background:
    radial-gradient(1100px 620px at 18% 18%, rgba(56,189,248,.16), transparent 62%),
    radial-gradient(1100px 620px at 85% 20%, rgba(79,70,229,.14), transparent 64%),
    rgba(2,6,23,.70);
}

.player-modal{
  border-radius: 28px;
  border: 1px solid rgba(129,140,248,.26);
  box-shadow: 0 44px 120px rgba(0,0,0,.72);
}

.player-head{
  border-bottom: 1px solid rgba(129,140,248,.18);
}

.player-close{
  border-radius: 16px;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}
.player-close:hover{
  transform: translateY(-1px);
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 16px 34px rgba(0,0,0,.45);
}

/* Reduce motion */
@media (prefers-reduced-motion: reduce){
  .streams-hero::after{ animation: none !important; transform: none !important; opacity: .25; }
  .chip-btn, .btn-primary, .btn-ghost, .video-watch, .modal-close, .search-pill{ transition: none !important; }
}

/* =========================================================
   INSIGHT STREAMS — “Aurora Glass + Prism Border” (CSS only)
   Append this at the VERY bottom of your <style> to override.
   ✅ Does NOT change any font-family (keeps your writing font)
   ========================================================= */

/* ---------- Local tokens (scoped) ---------- */
body .streams-wrap{
  --is-bg: rgba(255,255,255,.70);
  --is-card: rgba(255,255,255,.78);
  --is-border: rgba(148,163,184,.50);
  --is-text: #0b1120;
  --is-muted: rgba(30,41,59,.78);

  --is-prism: linear-gradient(135deg,
      rgba(56,189,248,.95),
      rgba(79,70,229,.90),
      rgba(168,85,247,.70),
      rgba(250,204,21,.55)
  );
  --is-prism-soft: linear-gradient(135deg,
      rgba(56,189,248,.20),
      rgba(79,70,229,.16),
      rgba(168,85,247,.12),
      rgba(250,204,21,.10)
  );

  --is-shadow: 0 26px 70px rgba(15,23,42,.14);
  --is-shadow-2: 0 42px 110px rgba(15,23,42,.18);
  --is-ring: 0 0 0 3px rgba(56,189,248,.22);

  max-width: 1440px;
  padding: 26px 0 54px;
  position: relative;
  isolation: isolate;
}

/* subtle section “aurora” */
body .streams-wrap::before{
  content:"";
  position:absolute;
  inset: -60px -28px -32px -28px;
  z-index:-2;
  pointer-events:none;
  background:
    radial-gradient(900px 420px at 15% 12%, rgba(56,189,248,.16), transparent 60%),
    radial-gradient(900px 420px at 85% 18%, rgba(79,70,229,.14), transparent 62%),
    radial-gradient(820px 380px at 50% 110%, rgba(168,85,247,.12), transparent 64%),
    radial-gradient(700px 340px at 18% 95%, rgba(34,197,94,.09), transparent 62%);
  filter: blur(3px);
  opacity: .95;
}

body.dark-mode .streams-wrap{
  --is-bg: rgba(2,6,23,.62);
  --is-card: rgba(15,23,42,.60);
  --is-border: rgba(129,140,248,.26);
  --is-text: #e5e7eb;
  --is-muted: rgba(203,213,225,.82);
  --is-shadow: 0 36px 100px rgba(0,0,0,.62);
  --is-shadow-2: 0 52px 140px rgba(0,0,0,.70);
  --is-ring: 0 0 0 3px rgba(129,140,248,.20);
}

/* ---------- HERO (title + search) ---------- */
body .streams-wrap .streams-hero{
  border-radius: 34px;
  padding: 30px 30px 24px;
  background: var(--is-bg);
  border: 1px solid var(--is-border);
  box-shadow: var(--is-shadow);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  position: relative;
  overflow: hidden;
}

/* prism border ring */
body .streams-wrap .streams-hero::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  background: var(--is-prism);
  opacity: .42;
  pointer-events:none;
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

/* moving “shine” layer */
body .streams-wrap .streams-hero::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  opacity: .55;
  background:
    radial-gradient(560px 260px at 18% 8%, rgba(255,255,255,.26), transparent 62%),
    radial-gradient(520px 240px at 85% 18%, rgba(255,255,255,.20), transparent 64%),
    linear-gradient(120deg, transparent 0%, rgba(255,255,255,.22) 18%, transparent 36%);
  transform: translateX(-140%);
  animation: isAuroraShine 7.5s ease-in-out infinite;
  mix-blend-mode: soft-light;
}
@keyframes isAuroraShine{
  0%,55%{ transform: translateX(-140%); }
  75%,100%{ transform: translateX(140%); }
}

body .streams-wrap .streams-hero-head{ gap: 18px; }
body .streams-wrap .streams-title{
  color: var(--is-text);
  text-shadow: 0 22px 48px rgba(30,58,138,.12);
}
body .streams-wrap .streams-sub{
  color: var(--is-muted);
  opacity: .98;
  font-size: 1.02rem;
  line-height: 1.6;
}

/* ---------- Controls ---------- */
body .streams-wrap .search-pill{
  background: var(--is-card);
  border: 1px solid var(--is-border);
  box-shadow: 0 18px 46px rgba(15,23,42,.10);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
body .streams-wrap .search-pill:focus-within{
  transform: translateY(-1px);
  border-color: rgba(56,189,248,.70);
  box-shadow: 0 20px 52px rgba(15,23,42,.14), var(--is-ring);
}
body .streams-wrap .search-pill i{ opacity: .78; color: var(--is-text); }
body .streams-wrap .search-pill input{ color: var(--is-text); }
body .streams-wrap .search-pill input::placeholder{ color: rgba(100,116,139,.80); }
body.dark-mode .streams-wrap .search-pill input::placeholder{ color: rgba(203,213,225,.56); }

body .streams-wrap .chip-btn{
  border: 1px solid rgba(56,189,248,.50);
  background: var(--is-prism-soft);
  color: var(--is-text);
  box-shadow: 0 18px 46px rgba(15,23,42,.12);
  position: relative;
  overflow: hidden;
}
body .streams-wrap .chip-btn::before{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.26) 20%, transparent 40%);
  transform: translateX(-140%);
  opacity: .55;
  pointer-events:none;
}
body .streams-wrap .chip-btn:hover::before{
  animation: isBtnShine .9s ease forwards;
}
@keyframes isBtnShine{
  to { transform: translateX(140%); }
}
body .streams-wrap .chip-btn:hover{
  transform: translateY(-2px);
  box-shadow: 0 26px 70px rgba(15,23,42,.16);
}

/* ---------- Grid + cards ---------- */
body .streams-wrap .skills-grid{ gap: 18px; }

body .streams-wrap .skill-holo{
  background: var(--is-card);
  border: 1px solid var(--is-border);
  border-radius: 26px;
  box-shadow: 0 24px 70px rgba(15,23,42,.12), inset 0 0 0 1px rgba(255,255,255,.50);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  overflow: hidden;
  position: relative;
}

/* prism edge + soft inner glow */
body .streams-wrap .skill-holo::before{
  opacity: .55;
  background: var(--is-prism);
}
body .streams-wrap .skill-holo::after{
  content:"";
  position:absolute;
  inset:0;
  border-radius: inherit;
  pointer-events:none;
  opacity: .75;
  background:
    radial-gradient(560px 300px at var(--mx, 50%) var(--my, 38%), rgba(255,255,255,.30), transparent 62%),
    radial-gradient(520px 300px at 88% 12%, rgba(56,189,248,.14), transparent 64%),
    radial-gradient(520px 300px at 12% 90%, rgba(168,85,247,.10), transparent 64%);
  mix-blend-mode: soft-light;
}

body.dark-mode .streams-wrap .skill-holo{
  box-shadow: 0 34px 90px rgba(0,0,0,.62), inset 0 0 0 1px rgba(30,64,175,.18);
}
body.dark-mode .streams-wrap .skill-holo::after{
  opacity: .55;
  background:
    radial-gradient(560px 300px at var(--mx, 50%) var(--my, 38%), rgba(56,189,248,.18), transparent 64%),
    radial-gradient(520px 300px at 85% 12%, rgba(129,140,248,.14), transparent 64%),
    radial-gradient(520px 300px at 12% 90%, rgba(34,211,238,.10), transparent 66%);
  mix-blend-mode: screen;
}

body .streams-wrap .skill-holo:hover{
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 38px 110px rgba(15,23,42,.18), inset 0 0 0 1px rgba(255,255,255,.62);
  filter: saturate(1.05);
}
body.dark-mode .streams-wrap .skill-holo:hover{
  border-color: rgba(129,140,248,.52);
  box-shadow: 0 46px 140px rgba(0,0,0,.72), 0 0 26px rgba(56,189,248,.10);
}

body .streams-wrap .skill-name{ color: var(--is-text); }
body .streams-wrap .skill-desc{
  color: var(--is-muted);
  opacity: .98;
  line-height: 1.65;
}

/* “10 Videos” pill — richer */
body .streams-wrap .vid-chip{
  border-radius: 999px;
  padding: 9px 12px;
  background: rgba(2,132,199,.10);
  border: 1px solid rgba(56,189,248,.25);
  color: rgba(12,74,110,.92);
  box-shadow: 0 14px 30px rgba(15,23,42,.08);
  font-weight: 800;
}
body.dark-mode .streams-wrap .vid-chip{
  background: rgba(56,189,248,.10);
  border-color: rgba(129,140,248,.22);
  color: rgba(226,232,240,.88);
}

/* Buttons */
body .streams-wrap .btn-primary,
body .streams-wrap .btn-ghost{
  min-height: 46px;
  border-radius: 18px;
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease, border-color .18s ease;
}

body .streams-wrap .btn-primary{
  background: var(--is-prism);
  box-shadow: 0 20px 58px rgba(15,23,42,.18);
  position: relative;
  overflow: hidden;
}
body .streams-wrap .btn-primary::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.24) 18%, transparent 36%);
  transform: translateX(-140%);
  opacity: .55;
  pointer-events:none;
}
body .streams-wrap .btn-primary:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 30px 86px rgba(15,23,42,.22);
}
body .streams-wrap .btn-primary:hover::after{
  animation: isBtnShine 1s ease forwards;
}

body .streams-wrap .btn-ghost{
  background: rgba(255,255,255,.55);
  border: 1px solid var(--is-border);
  color: var(--is-text);
  box-shadow: 0 16px 44px rgba(15,23,42,.10);
}
body.dark-mode .streams-wrap .btn-ghost{
  background: rgba(15,23,42,.46);
  border-color: rgba(129,140,248,.22);
  color: #e5e7eb;
}

/* ---------- Stream modal ---------- */
body .streams-overlay{
  background:
    radial-gradient(1100px 640px at 18% 18%, rgba(56,189,248,.18), transparent 62%),
    radial-gradient(1100px 640px at 85% 20%, rgba(79,70,229,.16), transparent 64%),
    rgba(2, 6, 23, 0.62);
}

body .streams-overlay .streams-modal{
  border-radius: 34px;
  background: var(--is-bg);
  border: 1px solid var(--is-border);
  box-shadow: var(--is-shadow-2);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  overflow: hidden;
  position: relative;
}

/* modal prism ring */
body .streams-overlay .streams-modal::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  background: var(--is-prism);
  opacity: .30;
  pointer-events:none;
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

body .streams-overlay .streams-modal-head{
  border-bottom: 1px solid rgba(148,163,184,.22);
}
body.dark-mode .streams-overlay .streams-modal-head{
  border-bottom-color: rgba(129,140,248,.18);
}

body .streams-overlay .modal-title{ color: var(--is-text); }
body .streams-overlay .modal-meta{ color: var(--is-muted); }

body .streams-overlay .modal-close{
  border-radius: 16px;
  background: rgba(255,255,255,.55);
  border: 1px solid var(--is-border);
  box-shadow: 0 14px 34px rgba(15,23,42,.14);
}
body .streams-overlay .modal-close:hover{
  transform: translateY(-1px);
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 20px 52px rgba(15,23,42,.18);
}
body.dark-mode .streams-overlay .modal-close{
  background: rgba(15,23,42,.55);
  border-color: rgba(129,140,248,.22);
  color: #e5e7eb;
}

/* ---------- Videos grid/cards ---------- */
body .streams-overlay .videos-grid{
  padding: 18px;
  gap: 14px;
  scrollbar-width: thin;
  scrollbar-color: rgba(56,189,248,.36) transparent;
}
body .streams-overlay .videos-grid::-webkit-scrollbar{ width: 10px; height: 10px; }
body .streams-overlay .videos-grid::-webkit-scrollbar-thumb{
  background: linear-gradient(180deg, rgba(56,189,248,.42), rgba(79,70,229,.34));
  border-radius: 999px;
  border: 2px solid transparent;
  background-clip: padding-box;
}

body .streams-overlay .video-card{
  border-radius: 22px;
  border: 1px solid rgba(148,163,184,.40);
  background: rgba(255,255,255,.62);
  box-shadow: 0 20px 58px rgba(15,23,42,.10);
  overflow: hidden;
}
body.dark-mode .streams-overlay .video-card{
  background: rgba(15,23,42,.58);
  border-color: rgba(129,140,248,.20);
  box-shadow: 0 26px 72px rgba(0,0,0,.60);
}

body .streams-overlay .video-thumb{
  height: 128px;
  background:
    radial-gradient(560px 300px at 18% 10%, rgba(56,189,248,.22), transparent 60%),
    radial-gradient(560px 300px at 88% 18%, rgba(79,70,229,.18), transparent 64%),
    radial-gradient(520px 280px at 40% 120%, rgba(168,85,247,.12), transparent 62%),
    linear-gradient(135deg, rgba(15,23,42,.10), rgba(15,23,42,.02));
  position: relative;
}
body .streams-overlay .video-thumb::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background: linear-gradient(180deg, rgba(2,6,23,0), rgba(2,6,23,.22));
}

body .streams-overlay .play-badge{
  font-size: 1.65rem;
  color: rgba(255,255,255,.94);
  text-shadow: 0 14px 30px rgba(0,0,0,.35);
}

body .streams-overlay .video-title{ color: var(--is-text); font-weight: 900; }
body .streams-overlay .video-mini{ color: var(--is-muted); }

body .streams-overlay .video-watch{
  border-radius: 16px;
  background: var(--is-prism);
  box-shadow: 0 18px 46px rgba(15,23,42,.16);
  transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
}
body .streams-overlay .video-watch:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 26px 72px rgba(15,23,42,.20);
}

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce){
  body .streams-wrap .streams-hero::after{ animation: none !important; transform: none !important; opacity: .22; }
  body .streams-wrap .chip-btn::before,
  body .streams-wrap .btn-primary::after{ animation: none !important; }
  body .streams-wrap .chip-btn,
  body .streams-wrap .btn-primary,
  body .streams-wrap .search-pill{ transition: none !important; }
}

/* =========================================================
   INSIGHT STREAMS — STREAM MODAL (Beautiful + Unique + Pro)
   Append this at the VERY bottom of your <style>
   (No font-family changes; supports Light + Dark)
   ========================================================= */

/* ---------- Overlay (background behind modal) ---------- */
#streamsOverlay.streams-overlay{
  background:
    radial-gradient(1100px 620px at 18% 18%, rgba(56,189,248,.20), transparent 62%),
    radial-gradient(1000px 600px at 86% 22%, rgba(79,70,229,.18), transparent 64%),
    radial-gradient(900px 520px at 50% 110%, rgba(168,85,247,.12), transparent 66%),
    rgba(2, 6, 23, 0.58);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
body.dark-mode #streamsOverlay.streams-overlay{
  background:
    radial-gradient(1100px 620px at 18% 18%, rgba(56,189,248,.16), transparent 62%),
    radial-gradient(1000px 600px at 86% 22%, rgba(129,140,248,.14), transparent 64%),
    radial-gradient(900px 520px at 50% 110%, rgba(34,211,238,.10), transparent 66%),
    rgba(2, 6, 23, 0.72);
}

/* ---------- Modal shell ---------- */
#streamsOverlay .streams-modal{
  /* local tokens */
  --sm-bg: rgba(255,255,255,.80);
  --sm-panel: rgba(255,255,255,.72);
  --sm-border: rgba(148,163,184,.55);
  --sm-text: #0b1120;
  --sm-muted: rgba(30,41,59,.75);

  --sm-prism: linear-gradient(135deg,
      rgba(56,189,248,.95),
      rgba(79,70,229,.90),
      rgba(168,85,247,.70),
      rgba(250,204,21,.55)
  );

  border-radius: 34px;
  background: var(--sm-bg);
  border: 1px solid var(--sm-border);
  box-shadow:
    0 44px 120px rgba(15,23,42,.28),
    0 0 0 1px rgba(255,255,255,.45) inset;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  overflow: hidden;
  position: relative;
  transform: translateY(10px) scale(.99);
}

/* Prism ring */
#streamsOverlay .streams-modal::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  background: var(--sm-prism);
  opacity: .40;
  pointer-events:none;
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

/* Subtle texture + soft highlight */
#streamsOverlay .streams-modal::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  opacity:.55;
  background:
    radial-gradient(700px 340px at 18% 10%, rgba(255,255,255,.35), transparent 60%),
    radial-gradient(700px 340px at 85% 18%, rgba(255,255,255,.22), transparent 64%),
    repeating-linear-gradient(90deg, rgba(255,255,255,.06) 0 1px, transparent 1px 12px);
  mix-blend-mode: soft-light;
}

/* Dark mode modal */
body.dark-mode #streamsOverlay .streams-modal{
  --sm-bg: rgba(2,6,23,.78);
  --sm-panel: rgba(15,23,42,.62);
  --sm-border: rgba(129,140,248,.26);
  --sm-text: #e5e7eb;
  --sm-muted: rgba(203,213,225,.82);

  box-shadow:
    0 56px 150px rgba(0,0,0,.70),
    0 0 0 1px rgba(30,64,175,.22) inset;
}
body.dark-mode #streamsOverlay .streams-modal::after{
  opacity:.42;
  background:
    radial-gradient(700px 340px at 18% 10%, rgba(56,189,248,.16), transparent 62%),
    radial-gradient(700px 340px at 85% 18%, rgba(129,140,248,.12), transparent 64%),
    repeating-linear-gradient(90deg, rgba(255,255,255,.05) 0 1px, transparent 1px 12px);
  mix-blend-mode: screen;
}

/* ---------- Header ---------- */
#streamsOverlay .streams-modal-head{
  position: relative;
  z-index: 1;
  padding: 18px 18px;
  background: linear-gradient(135deg, rgba(255,255,255,.55), rgba(226,232,240,.35));
  border-bottom: 1px solid rgba(148,163,184,.22);
}
body.dark-mode #streamsOverlay .streams-modal-head{
  background: linear-gradient(135deg, rgba(15,23,42,.58), rgba(2,6,23,.42));
  border-bottom-color: rgba(129,140,248,.18);
}

#streamsOverlay .modal-title{
  color: var(--sm-text);
  letter-spacing: .16em;
  font-size: 1.02rem;
  text-shadow: 0 14px 30px rgba(30,58,138,.10);
}
#streamsOverlay .modal-meta{
  color: var(--sm-muted);
  opacity: .95;
}

/* Close button (premium) */
#streamsOverlay .modal-close{
  border-radius: 16px;
  width: 46px;
  height: 46px;
  background: rgba(255,255,255,.55);
  border: 1px solid rgba(148,163,184,.45);
  box-shadow: 0 16px 38px rgba(15,23,42,.14);
  transition: transform .16s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
  position: relative;
  overflow: hidden;
}
#streamsOverlay .modal-close::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.30) 22%, transparent 44%);
  transform: translateX(-140%);
  opacity: .60;
  pointer-events:none;
}
#streamsOverlay .modal-close:hover{
  transform: translateY(-1px);
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 22px 60px rgba(15,23,42,.18), 0 0 0 3px rgba(56,189,248,.14);
}
#streamsOverlay .modal-close:hover::after{ animation: smShine .9s ease forwards; }
@keyframes smShine{ to{ transform: translateX(140%);} }

body.dark-mode #streamsOverlay .modal-close{
  background: rgba(15,23,42,.52);
  border-color: rgba(129,140,248,.22);
  color: #e5e7eb;
  box-shadow: 0 22px 70px rgba(0,0,0,.55);
}
body.dark-mode #streamsOverlay .modal-close:hover{
  border-color: rgba(129,140,248,.45);
  box-shadow: 0 28px 90px rgba(0,0,0,.62), 0 0 0 3px rgba(129,140,248,.16);
}

/* ---------- Videos area ---------- */
#streamsOverlay .videos-grid{
  position: relative;
  z-index: 1;
  padding: 18px;
  gap: 14px;
  overflow: auto;

  /* nicer scrollbar */
  scrollbar-width: thin;
  scrollbar-color: rgba(56,189,248,.36) transparent;
}
#streamsOverlay .videos-grid::-webkit-scrollbar{ width: 10px; height: 10px; }
#streamsOverlay .videos-grid::-webkit-scrollbar-thumb{
  background: linear-gradient(180deg, rgba(56,189,248,.42), rgba(79,70,229,.34));
  border-radius: 999px;
  border: 2px solid transparent;
  background-clip: padding-box;
}
#streamsOverlay .videos-grid::-webkit-scrollbar-track{ background: transparent; }

/* ---------- Video cards (unique glass tiles) ---------- */
#streamsOverlay .video-card{
  border-radius: 22px;
  background: var(--sm-panel);
  border: 1px solid rgba(148,163,184,.40);
  box-shadow: 0 20px 60px rgba(15,23,42,.10);
  overflow: hidden;
  position: relative;
  transition: transform .16s ease, box-shadow .18s ease, border-color .18s ease, filter .18s ease;
}

/* Prism edge on hover */
#streamsOverlay .video-card::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  padding: 2px;
  background: var(--sm-prism);
  opacity: 0;
  pointer-events:none;
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  transition: opacity .18s ease;
}

#streamsOverlay .video-card:hover{
  transform: translateY(-2px);
  border-color: rgba(56,189,248,.45);
  box-shadow: 0 30px 86px rgba(15,23,42,.14);
  filter: saturate(1.04);
}
#streamsOverlay .video-card:hover::before{ opacity: .55; }

body.dark-mode #streamsOverlay .video-card{
  background: rgba(15,23,42,.58);
  border-color: rgba(129,140,248,.20);
  box-shadow: 0 26px 80px rgba(0,0,0,.58);
}
body.dark-mode #streamsOverlay .video-card:hover{
  border-color: rgba(129,140,248,.35);
  box-shadow: 0 36px 110px rgba(0,0,0,.68);
}

/* Thumb becomes more cinematic */
#streamsOverlay .video-thumb{
  height: 132px;
  position: relative;
  background:
    radial-gradient(560px 300px at 18% 12%, rgba(56,189,248,.22), transparent 60%),
    radial-gradient(560px 300px at 88% 18%, rgba(79,70,229,.18), transparent 64%),
    radial-gradient(520px 280px at 50% 120%, rgba(168,85,247,.12), transparent 64%),
    linear-gradient(135deg, rgba(15,23,42,.10), rgba(15,23,42,.02));
}
body.dark-mode #streamsOverlay .video-thumb{
  background:
    radial-gradient(560px 300px at 18% 12%, rgba(56,189,248,.16), transparent 62%),
    radial-gradient(560px 300px at 88% 18%, rgba(129,140,248,.12), transparent 66%),
    radial-gradient(520px 280px at 50% 120%, rgba(34,211,238,.10), transparent 66%),
    linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
}
#streamsOverlay .video-thumb::after{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background: linear-gradient(180deg, rgba(2,6,23,0), rgba(2,6,23,.25));
}

/* Play badge becomes a glowing orb */
#streamsOverlay .play-badge{
  position:absolute;
  inset:0;
  display:grid;
  place-items:center;
  color: rgba(255,255,255,.94);
  text-shadow: 0 16px 34px rgba(0,0,0,.40);
}
#streamsOverlay .play-badge i{
  width: 54px;
  height: 54px;
  display:grid;
  place-items:center;
  border-radius: 18px;
  background: rgba(255,255,255,.14);
  border: 1px solid rgba(255,255,255,.20);
  box-shadow:
    0 18px 44px rgba(0,0,0,.22),
    0 0 26px rgba(56,189,248,.22);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: transform .18s ease, box-shadow .18s ease;
}
#streamsOverlay .video-card:hover .play-badge i{
  transform: scale(1.04);
  box-shadow:
    0 22px 60px rgba(0,0,0,.26),
    0 0 34px rgba(56,189,248,.30);
}

/* Text */
#streamsOverlay .video-body{ padding: 12px 12px 14px; }
#streamsOverlay .video-title{
  color: var(--sm-text);
  font-weight: 900;
  letter-spacing: .01em;
}
#streamsOverlay .video-mini{
  color: var(--sm-muted);
  opacity: .95;
}

/* Watch button — pro gradient + focus */
#streamsOverlay .video-watch{
  border-radius: 16px;
  background: var(--sm-prism);
  box-shadow: 0 18px 46px rgba(15,23,42,.16);
  transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
}
#streamsOverlay .video-watch:hover{
  transform: translateY(-2px);
  filter: brightness(1.03);
  box-shadow: 0 26px 80px rgba(15,23,42,.20);
}
#streamsOverlay .video-watch:focus-visible{
  outline: none;
  box-shadow: 0 0 0 3px rgba(56,189,248,.22), 0 26px 80px rgba(15,23,42,.20);
}
body.dark-mode #streamsOverlay .video-watch:focus-visible{
  box-shadow: 0 0 0 3px rgba(129,140,248,.20), 0 30px 90px rgba(0,0,0,.55);
}

/* ---------- Responsive polish ---------- */
@media (max-width: 700px){
  #streamsOverlay .streams-modal{ border-radius: 26px; }
  #streamsOverlay .streams-modal-head{ padding: 16px 14px; }
  #streamsOverlay .videos-grid{ padding: 14px; gap: 12px; }
  #streamsOverlay .video-thumb{ height: 124px; }
}

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce){
  #streamsOverlay .streams-modal,
  #streamsOverlay .video-card,
  #streamsOverlay .modal-close,
  #streamsOverlay .video-watch,
  #streamsOverlay .play-badge i{ transition: none !important; }
}

/* Thumbnail video inside the card */
.video-thumb{
  background: #000;            /* fallback if preview can't load */
  overflow: hidden;
}

.video-thumb video.thumb-video{
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  pointer-events: none;        /* so clicks go to buttons, not the video */
}

/* =========================
   COGNISENSE CHATBOT WIDGET
   (Bottom-right + 3D panel)
   ========================= */

:root{
  --cs-chat-radius: 22px;
  --cs-chat-shadow: 0 28px 70px rgba(15,23,42,.22);
  --cs-chat-border: rgba(148,163,184,.55);

  --cs-chat-bg: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
  --cs-chat-text: #0b1120;
  --cs-chat-subtext: rgba(15,23,42,.62);
  --cs-bubble-user: linear-gradient(135deg, #4a00e0, #06b6d4);
  --cs-bubble-bot: rgba(255,255,255,.72);
  --bot-glow-cyan: rgba(56,189,248,.35);
--bot-glow-indigo: rgba(79,70,229,.28);
--bot-glow-gold: rgba(250,204,21,.18);

}

body.dark-mode{
  --cs-chat-bg: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
  --cs-chat-text: #e5e7eb;
  --cs-chat-subtext: rgba(229,231,235,.72);
  --cs-chat-border: rgba(37,99,235,.70);
  --cs-bubble-bot: rgba(15,23,42,.62);
}

/* Floating bot image only (NO container) */
.cs-chat-fab{
  position: fixed;
  right: 22px;
  bottom: 22px;

  /* bigger click area but invisible */
  width: 110px;
  height: 110px;

  background: transparent;   /* ✅ no container */
  border: none;              /* ✅ no border */
  box-shadow: none;          /* ✅ no shadow */
  padding: 0;
  margin: 0;

  display: grid;
  place-items: center;
  cursor: pointer;
  z-index: 10041;

  transition: transform .18s ease, filter .18s ease;
  -webkit-tap-highlight-color: transparent;
}

.cs-chat-fab:hover{
  transform: translateY(-4px) scale(1.04);
  filter: drop-shadow(0 18px 30px rgba(15,23,42,.30));
}

/* smaller + softer halo */
.cs-chat-fab::after{
  content:"";
  position:absolute;
  inset: -10px;                 /* was -16px (smaller halo) */
  border-radius: 999px;
  pointer-events:none;
  z-index: -1;

  background:
    radial-gradient(circle at 35% 25%, var(--bot-glow-gold), transparent 60%),
    radial-gradient(circle at 30% 70%, var(--bot-glow-cyan), transparent 65%),
    radial-gradient(circle at 75% 35%, var(--bot-glow-indigo), transparent 68%);
  filter: blur(14px);           /* was 18px */
  opacity: .45;                 /* was ~.75 */
  transform: scale(.98);
  animation: botHaloPulse 4.6s ease-in-out infinite; /* slower + calmer */
}

.cs-chat-fab:active{
  transform: translateY(0px) scale(.98);
}

/* increase visible bot size */
.cs-chat-fab img{
  width: 110px;              /* ✅ bot size (increase if you want) */
  height: 110px;
  object-fit: contain;
  pointer-events: none;     /* click goes to button */
  filter: drop-shadow(0 12px 26px rgba(15,23,42,.28));
  filter:
  drop-shadow(0 10px 20px rgba(15,23,42,.18))
  drop-shadow(0 0 10px rgba(56,189,248,.16))
  drop-shadow(0 0 14px rgba(79,70,229,.12));

}

/* remove ping ring completely */
.cs-chat-fab::before{ content:none !important; }

@keyframes csPing{
  0%   { opacity: 0; transform: scale(.92); }
  35%  { opacity: .55; }
  70%  { opacity: 0; transform: scale(1.14); }
  100% { opacity: 0; transform: scale(1.14); }
}

/* Chat panel */
.cs-chat{
  position: fixed;
  right: 22px;
  bottom: 130px;              /* sits above the bot */
  width: min(380px, calc(100vw - 44px));
  height: 520px;
  border-radius: var(--cs-chat-radius);
  background: var(--cs-chat-bg);
  border: 1px solid var(--cs-chat-border);
  box-shadow: var(--cs-chat-shadow);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
  z-index: 10040;

  transform-origin: 92% 100%;
  transform: perspective(900px) translateY(14px) rotateX(10deg) rotateY(-10deg) scale(.92);
  opacity: 0;
  pointer-events: none;
  transition: transform .24s ease, opacity .20s ease;
}

.cs-chat.open{
  opacity: 1;
  pointer-events: auto;
  transform: perspective(900px) translateY(0) rotateX(0) rotateY(0) scale(1);
}

/* Header */
.cs-chat-header{
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 12px 12px 14px;
  border-bottom: 1px solid rgba(148,163,184,.20);
}

.cs-chat-title{
  display:flex;
  align-items:center;
  gap: 10px;
  color: var(--cs-chat-text);
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  font-size: .78rem;
}

.cs-chat-title .dot{
  width: 10px; height: 10px; border-radius: 999px;
  background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
  box-shadow: 0 0 12px rgba(56,189,248,.7);
}

.cs-chat-actions{
  display:flex;
  align-items:center;
  gap: 8px;
}

/* Small icon buttons in header */
.cs-ico-btn{
  width: 40px;
  height: 40px;
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.35);
  background: rgba(255,255,255,.45);
  color: var(--cs-chat-text);
  display:grid;
  place-items:center;
  cursor:pointer;
  transition: transform .16s ease, filter .2s ease, background .2s ease;
}
body.dark-mode .cs-ico-btn{ background: rgba(15,23,42,.45); border-color: rgba(129,140,248,.35); }

.cs-ico-btn:hover{ transform: translateY(-1px); filter: drop-shadow(0 8px 18px rgba(15,23,42,.18)); }
.cs-ico-btn:active{ transform: translateY(0) scale(.98); }

/* Body messages */
.cs-chat-body{
  height: calc(520px - 64px - 74px);
  padding: 14px;
  overflow-y: auto;
  display:flex;
  flex-direction: column;
  gap: 10px;
}

.cs-msg{
  max-width: 84%;
  padding: 10px 12px;
  border-radius: 16px;
  line-height: 1.35;
  font-size: .92rem;
  color: var(--cs-chat-text);
  border: 1px solid rgba(148,163,184,.18);
}

.cs-msg.bot{
  align-self: flex-start;
  background: var(--cs-bubble-bot);
}

.cs-msg.user{
  align-self: flex-end;
  background: var(--cs-bubble-user);
  color: #fff;
  border: none;
}

/* Composer */
.cs-chat-compose{
  height: 74px;
  border-top: 1px solid rgba(148,163,184,.20);
  padding: 12px;
  display:flex;
  gap: 10px;
  align-items: center;
}

.cs-input{
  flex: 1;
  height: 46px;
  border-radius: 14px;
  border: 1px solid rgba(148,163,184,.35);
  background: rgba(255,255,255,.55);
  color: var(--cs-chat-text);
  padding: 0 14px;
  outline: none;
}
body.dark-mode .cs-input{ background: rgba(15,23,42,.45); border-color: rgba(129,140,248,.35); }

.cs-send{
  width: 56px;
  height: 46px;
  border-radius: 14px;
  border: none;
  cursor: pointer;
  background: linear-gradient(135deg, #4a00e0, #06b6d4);
  color: #fff;
  font-weight: 800;
  box-shadow: 0 14px 30px rgba(37,99,235,.22);
  transition: transform .16s ease, filter .2s ease;
}
.cs-send:hover{ transform: translateY(-1px); filter: brightness(1.05); }
.cs-send:active{ transform: translateY(0) scale(.98); }

/* Mobile friendliness */
@media (max-width: 520px){
  .cs-chat{ height: 72vh; bottom: 100px; }
  .cs-chat-body{ height: calc(72vh - 64px - 74px); }
}


/* =========================
   Chatbot rotating nudge bubble
   ========================= */

.cs-chat-nudge{
  position: fixed;
  right: 22px;            /* keep same right as bot */
  bottom: 128px;          /* adjust so it sits above your bigger bot */

  max-width: 280px;
  padding: 12px 14px;
  border-radius: 16px;

  font-family: 'Poppins','Segoe UI',sans-serif;
  font-size: 0.92rem;
  font-weight: 600;
  line-height: 1.35;

  color: #0b1120;
  background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
  border: 1px solid rgba(148,163,184,.55);

  box-shadow:
    0 18px 40px rgba(15,23,42,.18),
    0 0 0 1px rgba(255,255,255,.65);

  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);

  opacity: 0;
  transform: translateY(10px) scale(.98);
  pointer-events: none;
  z-index: 10042;         /* above bot */

  /* nice “premium” glow edge */
  outline: 1px solid rgba(56,189,248,.20);
}

/* little arrow pointing to the bot */
.cs-chat-nudge::after{
  content:"";
  position:absolute;
  right: 18px;
  bottom: -8px;
  width: 16px;
  height: 16px;
  background: inherit;
  border-right: 1px solid rgba(148,163,184,.35);
  border-bottom: 1px solid rgba(148,163,184,.35);
  transform: rotate(45deg);
  border-bottom-right-radius: 4px;
}

/* Smooth continuous show/hide */
.cs-chat-nudge{
  opacity: 0;
  transform: translateY(10px) scale(.98);
  transition: opacity .22s ease, transform .22s ease;
}

.cs-chat-nudge.show{
  opacity: 1;
  transform: translateY(0) scale(1);
}


/* DARK MODE */
body.dark-mode .cs-chat-nudge{
  color: #e5e7eb;
  background: linear-gradient(135deg, rgba(15,23,42,.92), rgba(2,6,23,.94));
  border: 1px solid rgba(37,99,235,.70);
  box-shadow:
    0 22px 55px rgba(0,0,0,.60),
    0 0 18px rgba(56,189,248,.12);
  outline: 1px solid rgba(129,140,248,.22);
}

body.dark-mode .cs-chat-nudge::after{
  border-right: 1px solid rgba(129,140,248,.30);
  border-bottom: 1px solid rgba(129,140,248,.30);
}

/* ===== Chat "thinking" (typing dots) ===== */
.cs-typing {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.cs-typing span {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: currentColor;
  opacity: .25;
  animation: csDot 1.1s infinite ease-in-out;
}

.cs-typing span:nth-child(2) { animation-delay: .15s; }
.cs-typing span:nth-child(3) { animation-delay: .30s; }

@keyframes csDot {
  0%, 80%, 100% { transform: translateY(0); opacity: .25; }
  40% { transform: translateY(-4px); opacity: .9; }
}

/* optional: make the "thinking" bot bubble slightly softer */
.cs-msg.bot.thinking {
  opacity: .95;
}

/* =========================
   PROXIMA — Premium UI Upgrades
   Paste under your chatbot CSS
   ========================= */

/* panel gets a subtle gradient ring + micro texture */
.cs-chat{
  position: fixed;
  isolation: isolate; /* lets pseudo layers behave nicely */
}

.cs-chat::before{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: calc(var(--cs-chat-radius) + 2px);
  background: linear-gradient(135deg,
    rgba(56,189,248,.55),
    rgba(79,70,229,.45),
    rgba(250,204,21,.25)
  );
  opacity: .35;
  z-index: -1;
  pointer-events:none;
}

.cs-chat::after{
  content:"";
  position:absolute;
  inset:0;
  border-radius: var(--cs-chat-radius);
  background:
    radial-gradient(900px 400px at 15% 10%, rgba(56,189,248,.12), transparent 55%),
    radial-gradient(900px 420px at 85% 15%, rgba(79,70,229,.10), transparent 60%);
  opacity: .9;
  pointer-events:none;
  z-index: 0;
}

/* make sure content sits above */
.cs-chat > *{ position: relative; z-index: 1; }

/* header becomes more “product” */
.cs-chat-header{
  background:
    linear-gradient(135deg, rgba(255,255,255,.72), rgba(226,232,240,.64));
  border-bottom: 1px solid rgba(148,163,184,.18);
}

body.dark-mode .cs-chat-header{
  background: linear-gradient(135deg, rgba(15,23,42,.72), rgba(2,6,23,.70));
  border-bottom-color: rgba(129,140,248,.20);
}

/* brand block */
.cs-chat-brand{
  gap: 12px;
  align-items: center;
  text-transform: none;         /* stop forced uppercase */
  letter-spacing: normal;
}

.cs-chat-avatar{
  width: 55px;
  height: 55px;
  border-radius: 14px;
  object-fit: cover;
  background: rgba(255,255,255,.45);
  border: 1px solid rgba(148,163,184,.25);
  box-shadow: 0 10px 22px rgba(15,23,42,.12);
}

body.dark-mode .cs-chat-avatar{
  background: rgba(15,23,42,.50);
  border-color: rgba(129,140,248,.25);
}

.cs-chat-name{
  font-family: 'Orbitron','Poppins',sans-serif;
  font-weight: 800;
  font-size: .90rem;
  letter-spacing: .10em;
}

.cs-chat-status{
  margin-top: 2px;
  font-size: .78rem;
  color: var(--cs-chat-subtext);
  display:flex;
  align-items:center;
  gap: 8px;
  white-space: nowrap;
}

.cs-status-dot{
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #22c55e;
  box-shadow: 0 0 10px rgba(34,197,94,.65);
}

/* smoother scroll area + nicer scrollbar */
.cs-chat-body{
  scroll-behavior: smooth;
  padding: 14px 14px 16px;
}

.cs-chat-body::-webkit-scrollbar{ width: 10px; }
.cs-chat-body::-webkit-scrollbar-thumb{
  background: rgba(148,163,184,.35);
  border-radius: 999px;
  border: 2px solid rgba(255,255,255,.25);
}
body.dark-mode .cs-chat-body::-webkit-scrollbar-thumb{
  background: rgba(129,140,248,.25);
  border-color: rgba(2,6,23,.45);
}

/* message bubbles: more depth + “tail” */
.cs-msg{
  position: relative;
  animation: csPop .16s ease-out;
  box-shadow: 0 12px 26px rgba(15,23,42,.08);
}

@keyframes csPop{
  from{ transform: translateY(6px); opacity: 0; }
  to  { transform: translateY(0); opacity: 1; }
}

/* bot bubble: soft gradient + border */
.cs-msg.bot{
  background:
    linear-gradient(135deg, rgba(255,255,255,.78), rgba(226,232,240,.70));
  border: 1px solid rgba(148,163,184,.18);
}

body.dark-mode .cs-msg.bot{
  background: rgba(15,23,42,.62);
  border-color: rgba(129,140,248,.16);
}

/* user bubble: stronger depth */
.cs-msg.user{
  box-shadow: 0 14px 30px rgba(37,99,235,.22);
}

/* bubble tails */
.cs-msg.bot::after{
  content:"";
  position:absolute;
  left: -6px;
  top: 14px;
  width: 14px;
  height: 14px;
  background: inherit;
  border-left: 1px solid rgba(148,163,184,.18);
  border-bottom: 1px solid rgba(148,163,184,.18);
  transform: rotate(45deg);
  border-bottom-left-radius: 6px;
  opacity: .95;
}

.cs-msg.user::after{
  content:"";
  position:absolute;
  right: -6px;
  top: 14px;
  width: 14px;
  height: 14px;
  background: inherit;
  transform: rotate(45deg);
  border-bottom-right-radius: 6px;
  opacity: .95;
}

/* links look professional inside replies */
.cs-msg a{
  color: #2563eb;
  font-weight: 700;
  text-decoration: none;
}
.cs-msg a:hover{ text-decoration: underline; }
body.dark-mode .cs-msg a{ color: #60a5fa; }

/* nicer composer: focus ring + subtle shine on send */
.cs-chat-compose{
  background: rgba(255,255,255,.35);
}
body.dark-mode .cs-chat-compose{
  background: rgba(2,6,23,.40);
}

.cs-input:focus{
  border-color: rgba(56,189,248,.55);
  box-shadow: 0 0 0 3px rgba(56,189,248,.20);
}

.cs-send{
  position: relative;
  overflow: hidden;
}

.cs-send::after{
  content:"";
  position:absolute;
  inset: 0;
  background: linear-gradient(120deg,
    transparent 0%,
    rgba(255,255,255,.22) 18%,
    transparent 36%
  );
  transform: translateX(-140%);
  opacity: .55;
  transition: transform .45s ease;
}

.cs-send:hover::after{ transform: translateX(140%); }

/* === CLOCK FIX: stack time + date, keep ring on left === */
.time-display{
  /* keep your existing styles; these are safe adds/overrides */
  align-items: center;
}

/* wrapper we’ll inject via JS */
.time-text{
  display: flex;
  flex-direction: column;
  align-items: flex-start;      /* center date under time */
  justify-content: center;
  gap: 6px;
  line-height: 1.05;
}

/* remove the “beside” behavior */
.time-sub{
  margin-left: 0 !important;
  text-align: left;
  white-space: nowrap;
}

/* === CLOCK FIX: prevent width jitter as digits change === */
.time-main{
  /* Use equal-width numerals where supported */
  font-variant-numeric: tabular-nums;
  font-feature-settings: "tnum" 1, "lnum" 1;

  /* Reserve a stable width for HH : MM : SS */
  min-width: 9ch;          /* tweak 14–16ch if you change spacing */
  text-align: left;
  white-space: nowrap;
}

/* =========================================================
   CLOCK v4 — Aurora Prism + Progress Ring + Sparkle Sheen
   ✅ keeps same size + same fonts (no padding/font changes)
   Paste at VERY bottom of your <style>
   ========================================================= */

.time-display{
  position: relative;
  isolation: isolate;                 /* clean layers */
  transform-style: preserve-3d;
  perspective: 900px;
}

/* Ensure text stays above all overlays */
.time-display .time-text{ position: relative; z-index: 3; }

/* ===== Replace your simple spinner with a “turbine ring” ===== */
.time-display::before{
  content:"";
  width: 32px;
  height: 32px;
  border-radius: 999px;
  position: relative;
  z-index: 4;

  /* segmented turbine + glow */
  background:
    conic-gradient(
      from 0deg,
      rgba(56,189,248,.0) 0 18deg,
      rgba(56,189,248,.95) 18deg 26deg,
      rgba(79,70,229,.0) 26deg 52deg,
      rgba(79,70,229,.9) 52deg 60deg,
      rgba(168,85,247,.0) 60deg 88deg,
      rgba(168,85,247,.85) 88deg 96deg,
      rgba(250,204,21,.0) 96deg 126deg,
      rgba(250,204,21,.75) 126deg 134deg,
      rgba(56,189,248,.0) 134deg 360deg
    );

  /* ring mask */
  -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));
  mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));

  box-shadow:
    0 0 14px rgba(56,189,248,.55),
    0 0 26px rgba(79,70,229,.35);

  animation:
    timeTurbine 2.6s linear infinite,
    timeGlow 2.2s ease-in-out infinite;
}

@keyframes timeTurbine{
  to{ transform: rotate(360deg); }
}
@keyframes timeGlow{
  0%,100%{ filter: drop-shadow(0 0 0 rgba(56,189,248,0)); opacity: .92; }
  50%{ filter: drop-shadow(0 0 12px rgba(56,189,248,.55)); opacity: 1; }
}

/* ===== Main premium layer: prism border + seconds progress + sheen + sparkles ===== */
.time-display::after{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  pointer-events:none;
  z-index: 1;

  /* LAYER STACK:
     1) seconds progress ring (uses --pdeg)
     2) rotating prism border
     3) moving sheen
     4) sparkle glints
     5) micro texture
  */
  background:
    /* 1) seconds progress ring */
    conic-gradient(from -90deg,
      rgba(56,189,248,.00) 0deg,
      rgba(56,189,248,.00) calc(var(--pdeg, 0deg) - 14deg),
      rgba(56,189,248,.80) calc(var(--pdeg, 0deg) - 6deg),
      rgba(79,70,229,.65) var(--pdeg, 0deg),
      rgba(56,189,248,.00) calc(var(--pdeg, 0deg) + 10deg),
      rgba(56,189,248,.00) 360deg
    ),

    /* 2) prism border wash */
    conic-gradient(from 180deg,
      rgba(56,189,248,.65),
      rgba(79,70,229,.55),
      rgba(168,85,247,.42),
      rgba(250,204,21,.28),
      rgba(56,189,248,.65)
    ),

    /* 3) moving sheen */
    linear-gradient(120deg,
      transparent 0%,
      rgba(255,255,255,.22) 18%,
      transparent 36%
    ),

    /* 4) sparkle glints (follows mouse via --mx/--my if JS added) */
    radial-gradient(220px 140px at var(--mx, 35%) var(--my, 30%),
      rgba(255,255,255,.22), transparent 62%
    ),
    radial-gradient(160px 120px at 78% 22%,
      rgba(56,189,248,.16), transparent 64%
    ),

    /* 5) micro texture */
    repeating-linear-gradient(90deg,
      rgba(255,255,255,.045) 0 1px,
      transparent 1px 12px
    );

  /* Make it a BORDER RING (not a fill) */
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  padding: 2px;

  opacity: .62;
  mix-blend-mode: soft-light;

  animation:
    timePrismSpin 7.8s linear infinite,
    timeSheenMove 6.2s ease-in-out infinite,
    timeHueShift 10s ease-in-out infinite;
}

@keyframes timePrismSpin{ to{ transform: rotate(360deg); } }
@keyframes timeSheenMove{
  0%,55%{ background-position: 0 0, 0 0, -140% 0, 0 0, 0 0, 0 0; }
  75%,100%{ background-position: 0 0, 0 0, 140% 0, 0 0, 0 0, 0 0; }
}
@keyframes timeHueShift{
  0%,100%{ filter: hue-rotate(0deg); }
  50%{ filter: hue-rotate(18deg); }
}

/* ===== Animated separators (professional but alive) ===== */
.time-main .t-sep{
  display:inline-block;
  opacity: .62;
  transform: translateY(-1px);
  text-shadow: 0 0 12px rgba(56,189,248,.18);
  animation: sepBlink 1s steps(2, end) infinite;
}
@keyframes sepBlink{
  0%,100%{ opacity:.25; }
  50%{ opacity:.95; }
}

/* Keep your tabular nums (no jitter) */
.time-main{
  font-variant-numeric: tabular-nums;
  font-feature-settings: "tnum" 1, "lnum" 1;
}

/* Make date line look like a “status strip” (no size change) */
.time-sub{
  position: relative;
  padding-top: 6px;
}
.time-sub::before{
  content:"";
  position:absolute;
  left:0;
  top:0;
  width: 82%;
  height: 1px;
  background: linear-gradient(90deg, rgba(56,189,248,.55), rgba(79,70,229,.35), transparent);
  opacity: .55;
}

/* Hover = richer glow only (no size/transform changes) */
.time-display:hover{
  box-shadow:
    0 14px 34px rgba(15,23,42,.32),
    0 0 0 1px rgba(255,255,255,.85);
}

/* ===== Dark mode tuning ===== */
body.dark-mode .time-display::after{
  opacity: .55;
  mix-blend-mode: screen;
  filter: hue-rotate(0deg);
}
body.dark-mode .time-sub::before{
  background: linear-gradient(90deg, rgba(129,140,248,.55), rgba(56,189,248,.30), transparent);
  opacity: .45;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce){
  .time-display::before,
  .time-display::after,
  .time-main .t-sep{
    animation: none !important;
  }
}

/* ===== CLOCK v4 — LIGHT MODE BOOST (make effects visible) ===== */

/* Make the prism + progress ring visible on light backgrounds */
body:not(.dark-mode) .time-display::after{
  opacity: .92;              /* stronger */
  mix-blend-mode: normal;    /* soft-light is too subtle in light mode */
  filter: saturate(1.12) contrast(1.06);
}

/* Make the turbine ring glow more in light mode */
body:not(.dark-mode) .time-display::before{
  box-shadow:
    0 0 18px rgba(56,189,248,.75),
    0 0 34px rgba(79,70,229,.45);
}

/* IMPORTANT: ensure the sheen layer actually "moves"
   (3rd layer in ::after background is the sheen) */
.time-display::after{
  background-size:
    auto,          /* seconds ring */
    auto,          /* prism ring */
    220% 100%,     /* sheen (must be wide to travel) */
    auto,          /* sparkle 1 */
    auto,          /* sparkle 2 */
    auto;          /* texture */
}

/* Optional: slightly stronger hover glow in light mode (no size change) */
body:not(.dark-mode) .time-display:hover{
  box-shadow:
    0 16px 44px rgba(15,23,42,.28),
    0 0 0 1px rgba(255,255,255,.92);
  border-color: rgba(56,189,248,.55);
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
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
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

            <li class="{{ request()->routeIs('insight.streams') ? 'active' : '' }}">
                <a href="{{ route('insight.streams') }}">
                    <img src="{{ asset('img/learning.png') }}" class="icon" alt="Learning Hub">
                    <span class="text">Insight Streams</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('cv.builder') ? 'active' : '' }}">
                <a href="{{ route('cv.builder') }}">
                    <img src="{{ asset('img/cv.png') }}" class="icon" alt="Generate CV">
                    <span class="text">Generate CV</span>
                </a>
            </li>

            <li><a href="#"><img src="{{ asset('img/community.png') }}" class="icon" alt="Community"><span class="text">Community</span></a></li>
            <li><a href="#"><img src="{{ asset('img/explore.png') }}" class="icon" alt="Explore"><span class="text">Explore</span></a></li>
            <li><a href="#"><img src="{{ asset('img/certificate.png') }}" class="icon" alt="Certificate"><span class="text">Certificate</span></a></li>
            <li><a href="#"><img src="{{ asset('img/ielts.png') }}" class="icon" alt="AspireIELTS"><span class="text">AspireIELTS</span></a></li>

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

        {{-- TOP BAR (from dashboard) --}}
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="time-display" id="time"></div>

                <div class="weather-display" id="weather" aria-live="polite">
                    <div class="wx-icon" aria-hidden="true">
                        <span class="wx-emoji">⛅</span>
                    </div>

                    <div class="wx-text">
                        <div class="wx-top">
                            <span class="wx-city">Dhaka</span>
                            <span class="wx-chip" id="wxStatus">Loading</span>
                        </div>

                        <div class="wx-temp" id="wxTemp">--°C</div>
                        <div class="wx-meta" id="wxMeta">Fetching weather…</div>
                    </div>
                </div>
            </div>

            <div class="top-bar-right">
                {{-- Theme toggle --}}
                <label class="theme-toggle">
                    <input type="checkbox" id="darkModeToggle">
                    <div class="toggle-track">
                        <img src="{{ asset('img/sun.png') }}" alt="Light Mode" class="icon-sun">
                        <img src="{{ asset('img/moon.png') }}" alt="Dark Mode" class="icon-moon">
                        <div class="toggle-thumb"></div>
                    </div>
                </label>

                {{-- ABOUT (icon only) --}}
                <button type="button"
                        class="about-btn icon-only"
                        id="aboutOpenBtn"
                        aria-label="About">
                    <span class="about-icon-circle">
                        <img src="{{ asset('img/about.png') }}" class="about-icon" alt="">
                    </span>
                </button>

                {{-- PROFILE (icon only) + DROPDOWN --}}
                <div class="profile-wrap" id="profileWrap">
                    <button type="button"
                            class="about-btn icon-only profile-btn"
                            id="profileBtn"
                            aria-label="Profile"
                            aria-haspopup="dialog"
                            aria-expanded="false">
                        <span class="about-icon-circle">
                            <img src="{{ asset('img/user.png') }}" class="about-icon" alt="">
                        </span>
                    </button>

                    <div class="profile-dropdown" id="profileDropdown" aria-hidden="true">
                        <div class="profile-head">
                            <div class="profile-avatar">
                                {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="profile-meta">
                                <div class="profile-name">{{ auth()->user()->full_name ?? 'Cognisense User' }}</div>
                                <div class="profile-email">{{ auth()->user()->email ?? '' }}</div>
                            </div>
                        </div>

                        <div class="profile-rows">
                            <div class="profile-row">
                                <span>Role</span>
                                <span class="pill">{{ auth()->user()->role ?? 'student' }}</span>
                            </div>
                            <div class="profile-row">
                                <span>Status</span>
                                <span class="pill {{ (auth()->user()->is_active ?? 0) ? 'pill-ok' : 'pill-off' }}">
                                    {{ (auth()->user()->is_active ?? 0) ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="profile-row">
                                <span>UUID</span>
                                <span class="mono">{{ auth()->user()->uuid ?? '—' }}</span>
                            </div>
                            <div class="profile-row">
                                <span>Joined</span>
                                <span>{{ optional(auth()->user()->created_at)->format('d M Y') ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="profile-actions" style="justify-content: flex-end;">
                            <form method="POST" action="{{ url('/logout') }}" class="profile-logout">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        {{-- INSIGHT STREAMS CONTENT --}}
        <div class="streams-wrap">
            <section class="streams-hero">
                <div class="streams-hero-head">
                    <div>
                        <h1 class="streams-title">Insight Streams</h1>
                        <p class="streams-sub">
                            A video-only learning space: pick a skill, open its stream, and watch 10 curated videos.
                        </p>
                    </div>

                    <div class="streams-controls">
                        <div class="search-pill">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input id="skillSearch" type="text" placeholder="Search skills (e.g., Interview, Email, Negotiation)…">
                        </div>
                        <button class="chip-btn" id="clearSearch" type="button">Clear</button>
                    </div>
                </div>
            </section>

            <section class="skills-grid" id="skillsGrid">
                @php $fallbackDesc = "Open the stream to view 10 videos and learn the core techniques fast."; @endphp

                @foreach($skillCards as $i => $card)
                    @php
                        $title = $card->title ?? $card->name ?? $card->skill_name ?? 'Skill';
                        $desc  = $card->short_description ?? $card->description ?? $fallbackDesc;
                        $code  = $card->code ?? ('SKILL_'.$i);
                    @endphp

                    <article class="skill-holo" data-tilt data-skill="{{ strtolower($title.' '.$code) }}"
                             data-skill-title="{{ $title }}" data-skill-code="{{ $code }}" data-video-folder="{{ $title }}" >
                        <div class="skill-top">
                            <div>
                                <h3 class="skill-name">{{ $title }}</h3>

                            </div>
                            <div class="vid-chip"><i class="fa-solid fa-film"></i> 10 Videos</div>
                        </div>

                        <p class="skill-desc">{{ $desc }}</p>


                        <div class="skill-actions">
                            <button class="btn-primary" data-open-stream type="button">Open Stream</button>
                            
                        </div>
                    </article>
                @endforeach
            </section>
        </div>

        {{-- FOOTER (from dashboard) --}}
        <footer class="footer">
            <div class="footer-socials">
                <div class="footer-social-link">
                    <img src="{{ asset('img/facebook.png') }}" alt="Facebook">
                    <span class="footer-social-label">Facebook</span>
                </div>
                <div class="footer-social-link">
                    <img src="{{ asset('img/instagram.png') }}" alt="Instagram">
                    <span class="footer-social-label">Instagram</span>
                </div>
                <div class="footer-social-link">
                    <img src="{{ asset('img/twitter.png') }}" alt="Twitter">
                    <span class="footer-social-label">Twitter</span>
                </div>
                <div class="footer-social-link">
                    <img src="{{ asset('img/github.png') }}" alt="GitHub">
                    <span class="footer-social-label">GitHub</span>
                </div>
            </div>

            <div class="footer-copy">
                © {{ date('Y') }} Cognisense. All rights reserved.
            </div>
        </footer>
    </div>

    {{-- STREAM MODAL --}}
    <div class="streams-overlay" id="streamsOverlay" aria-hidden="true">
        <div class="streams-modal" role="dialog" aria-modal="true" aria-labelledby="streamTitle">
            <div class="streams-modal-head">
                <div>
                    <p class="modal-title" id="streamTitle">Stream</p>
                    <div class="modal-meta" id="streamMeta">10 videos</div>
                </div>
                <button class="modal-close" id="streamClose" type="button" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="videos-grid" id="videosGrid"></div>
        </div>
    </div>

    {{-- PLAYER MODAL (placeholder for now) --}}
    <div class="player-overlay" id="playerOverlay" aria-hidden="true">
        <div class="player-modal" role="dialog" aria-modal="true">
            <div class="player-head">
                <div class="player-title" id="playerTitle">Video</div>
                <button class="player-close" id="playerClose" type="button" aria-label="Close Player">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="player-body">
                <p class="empty-note" id="playerNote">
                    Video player is ready. When you add real video URLs, this modal will play them here.
                </p>
            </div>
        </div>
    </div>

    {{-- ABOUT OVERLAY --}}
    <div class="about-overlay" id="aboutOverlay" aria-hidden="true">
        <div class="about-modal" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" tabindex="-1">

            <button class="about-close" id="aboutCloseBtn" type="button" aria-label="Close About">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="about-hero">
                <div class="about-hero-left">
                    <div class="about-hero-badge">
                        <span class="about-dot"></span>
                        <span class="about-badge-text">COGNISENSE • PROJECT OVERVIEW</span>
                    </div>

                    <h2 class="about-title" id="aboutTitle">Cognisense</h2>

                    <p class="about-subtitle">
                        A modern learning + skill-building platform designed to keep everything in one place:
                        skill development, structured learning, community, certificates, CV building, and IELTS mock practice.
                    </p>

                    <div class="about-tags">
                        <span class="about-tag"><i class="fa-solid fa-brain"></i> Skill Hub</span>
                        <span class="about-tag"><i class="fa-solid fa-graduation-cap"></i> Learning Hub</span>
                        <span class="about-tag"><i class="fa-solid fa-people-group"></i> Community</span>
                        <span class="about-tag"><i class="fa-solid fa-compass"></i> Explore</span>
                        <span class="about-tag"><i class="fa-solid fa-certificate"></i> Certificates</span>
                        <span class="about-tag"><i class="fa-solid fa-file-lines"></i> CV Builder</span>
                        <span class="about-tag"><i class="fa-solid fa-language"></i> AspireIELTS Mock</span>
                    </div>
                </div>
            </div>

            <div class="about-grid">
                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-brain"></i></span>
                        <h3>Skill Hub</h3>
                    </div>
                    <p>Build skills with structured steps, consistent practice, and a clear progression mindset.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-graduation-cap"></i></span>
                        <h3>Learning Hub</h3>
                    </div>
                    <p>Organized learning space for lessons, resources, and guided study flows.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-people-group"></i></span>
                        <h3>Community</h3>
                    </div>
                    <p>Connect with learners, share updates, and keep motivation high through collaboration.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-compass"></i></span>
                        <h3>Explore</h3>
                    </div>
                    <p>Discover content and opportunities that support your skills and learning goals.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-certificate"></i></span>
                        <h3>Certificates</h3>
                    </div>
                    <p>Recognize milestones and achievements to keep progress tangible and rewarding.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-head">
                        <span class="about-ic"><i class="fa-solid fa-language"></i></span>
                        <h3>AspireIELTS Mock + CV</h3>
                    </div>
                    <p>Practice IELTS in an exam-like UI and convert skills into a clean CV-ready profile.</p>
                </div>
            </div>

            <div class="about-footer">
                <div class="about-footer-left">
                    <span class="about-mini">
                        Built for clarity, speed, and consistency — a single place to learn, practice, showcase, and prepare.
                    </span>
                </div>
            </div>

        </div>
    </div>

    <script>
  // =========================
  // About Overlay Controls
  // =========================
  const aboutOpenBtn = document.getElementById('aboutOpenBtn');
  const aboutOverlay = document.getElementById('aboutOverlay');
  const aboutCloseBtn = document.getElementById('aboutCloseBtn');
  const aboutModal = aboutOverlay?.querySelector('.about-modal');

  function openAbout(){
    if (!aboutOverlay) return;
    aboutOverlay.classList.add('open');
    aboutOverlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    // focus for accessibility
    setTimeout(() => {
      aboutCloseBtn?.focus();
      aboutModal?.scrollTo({ top: 0, behavior: 'smooth' });
    }, 10);
  }

  function closeAbout(){
    if (!aboutOverlay) return;
    aboutOverlay.classList.remove('open');
    aboutOverlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    setTimeout(() => aboutOpenBtn?.focus(), 10);
  }

  aboutOpenBtn?.addEventListener('click', openAbout);
  aboutCloseBtn?.addEventListener('click', closeAbout);

  // close when clicking outside the modal
  aboutOverlay?.addEventListener('click', (e) => {
    if (e.target === aboutOverlay) closeAbout();
  });

  // close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && aboutOverlay?.classList.contains('open')) {
      closeAbout();
    }
  });
</script>

<script>
  // =========================
  // Profile Dropdown Controls
  // =========================
  const profileBtn = document.getElementById('profileBtn');
  const profileDropdown = document.getElementById('profileDropdown');
  const profileWrap = document.getElementById('profileWrap');

  function openProfile(){
    if (!profileDropdown) return;
    profileDropdown.classList.add('open');
    profileDropdown.setAttribute('aria-hidden', 'false');
    profileBtn?.setAttribute('aria-expanded', 'true');
  }

  function closeProfile(){
    if (!profileDropdown) return;
    profileDropdown.classList.remove('open');
    profileDropdown.setAttribute('aria-hidden', 'true');
    profileBtn?.setAttribute('aria-expanded', 'false');
  }

  function toggleProfile(){
    if (!profileDropdown) return;
    profileDropdown.classList.contains('open') ? closeProfile() : openProfile();
  }

  profileBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleProfile();
  });

  // click outside closes it
  document.addEventListener('click', (e) => {
    if (!profileWrap || !profileDropdown) return;
    if (!profileWrap.contains(e.target)) closeProfile();
  });

  // ESC closes it
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeProfile();
  });

  // OPTIONAL: if About overlay opens, close profile dropdown to avoid overlap
  // (only if openAbout exists in your page scope)
  if (typeof openAbout === 'function') {
    const _openAbout = openAbout;
    window.openAbout = function(){
      closeProfile();
      return _openAbout();
    }
  }
</script>

    <script>
function updateBDTime() {
  const timeElement = document.getElementById("time");
  if (!timeElement) return;

  // Create nodes once
  let mainEl = timeElement.querySelector(".time-main");
  let subEl  = timeElement.querySelector(".time-sub");

  if (!mainEl || !subEl) {
    timeElement.innerHTML = `
      <div class="time-text">
        <span class="time-main" aria-label="Bangladesh time"></span>
        <span class="time-sub" aria-label="Bangladesh date"></span>
      </div>
    `;
    mainEl = timeElement.querySelector(".time-main");
    subEl  = timeElement.querySelector(".time-sub");
  }

  const now = new Date();

  const optionsDate = {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    timeZone: 'Asia/Dhaka'
  };

  const optionsTime = {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    timeZone: 'Asia/Dhaka'
  };

  const datePart = now.toLocaleDateString('en-GB', optionsDate);

  // HH:MM:SS (in Dhaka)
  const rawTime = now.toLocaleTimeString('en-GB', optionsTime);
  const [hh, mm, ss] = rawTime.split(':');

  // separators that animate
  mainEl.innerHTML = `${hh}<span class="t-sep"> : </span>${mm}<span class="t-sep"> : </span>${ss}`;
  subEl.textContent  = `${datePart} · BD`;

  // seconds progress ring (0..360deg)
  const s = Number(ss) || 0;
  const ms = now.getMilliseconds();
  const pdeg = (s + ms / 1000) * 6; // 60s => 360deg
  timeElement.style.setProperty('--pdeg', `${pdeg.toFixed(1)}deg`);
}

/* Optional: mouse sparkle tracking (super premium, zero size change) */
(function attachClockSparkle(){
  const el = document.getElementById("time");
  if (!el) return;

  el.addEventListener("mousemove", (e) => {
    const r = el.getBoundingClientRect();
    const px = ((e.clientX - r.left) / r.width) * 100;
    const py = ((e.clientY - r.top) / r.height) * 100;
    el.style.setProperty("--mx", px.toFixed(1) + "%");
    el.style.setProperty("--my", py.toFixed(1) + "%");
  });

  el.addEventListener("mouseleave", () => {
    el.style.setProperty("--mx", "35%");
    el.style.setProperty("--my", "30%");
  });
})();

        updateBDTime(); setInterval(updateBDTime, 1000);

        // ========= Sidebar toggle =========
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleIcon = toggleBtn?.querySelector('i');

        function updateSidebarToggleIcon() {
            if (!toggleIcon) return;
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('fa-angles-left');
                toggleIcon.classList.add('fa-angles-right');
            } else {
                toggleIcon.classList.remove('fa-angles-right');
                toggleIcon.classList.add('fa-angles-left');
            }
        }
        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            updateSidebarToggleIcon();
        });
        updateSidebarToggleIcon();

        // ========= Dark mode (same logic as your dashboard) =========
        function pulseTheme() {
            document.body.classList.add('theme-swap');
            clearTimeout(window.__themeSwapT);
            window.__themeSwapT = setTimeout(() => document.body.classList.remove('theme-swap'), 260);
        }
        function applyTheme(pulse = false) {
            const stored = localStorage.getItem('darkMode');
            const enabled = stored === 'enabled';
            document.body.classList.toggle('dark-mode', enabled);

            const toggle = document.getElementById('darkModeToggle');
            if (toggle) toggle.checked = enabled;

            if (pulse) pulseTheme();
        }
        function setTheme(enabled) {
            localStorage.setItem('darkMode', enabled ? 'enabled' : 'disabled');
            applyTheme(true);
        }
        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(false);
            document.getElementById('darkModeToggle')?.addEventListener('change', e => setTheme(e.target.checked));
        });
        window.addEventListener('storage', (e) => { if (e.key === 'darkMode') applyTheme(true); });

        // ========= Dhaka Weather (Open-Meteo, no key) =========
        const wxStatus = document.getElementById('wxStatus');
        const wxTemp   = document.getElementById('wxTemp');
        const wxMeta   = document.getElementById('wxMeta');
        const wxEmoji  = document.querySelector('#weather .wx-emoji');

        function wxFromCode(code, isDay){
            const map = {
                0:["Clear",isDay?"☀️":"🌙"], 1:["Mostly clear",isDay?"🌤️":"🌙"], 2:["Partly cloudy","⛅"], 3:["Cloudy","☁️"],
                45:["Fog","🌫️"],48:["Fog","🌫️"], 51:["Drizzle","🌦️"],53:["Drizzle","🌦️"],55:["Drizzle","🌦️"],
                61:["Rain","🌧️"],63:["Rain","🌧️"],65:["Heavy rain","⛈️"], 80:["Showers","🌦️"],81:["Showers","🌦️"],82:["Heavy showers","⛈️"],
                95:["Thunder","⛈️"],96:["Thunder","⛈️"],99:["Thunder","⛈️"],
            };
            return map[code] || ["Weather","🌡️"];
        }

        async function updateDhakaWeather(){
            try{
                wxStatus.textContent = "LIVE";
                const url = "https://api.open-meteo.com/v1/forecast?latitude=23.8103&longitude=90.4125&current=temperature_2m,apparent_temperature,relative_humidity_2m,is_day,weather_code,wind_speed_10m&timezone=Asia%2FDhaka&temperature_unit=celsius&wind_speed_unit=kmh";
                const res = await fetch(url, { cache: "no-store" });
                if(!res.ok) throw new Error("Weather failed");
                const data = await res.json();
                const c = data.current;

                const temp = Math.round(c.temperature_2m);
                const feels = Math.round(c.apparent_temperature);
                const hum = Math.round(c.relative_humidity_2m);
                const wind = Math.round(c.wind_speed_10m);
                const isDay = !!c.is_day;
                const [desc, emoji] = wxFromCode(c.weather_code, isDay);

                wxTemp.textContent = `${temp}°C`;
                wxMeta.textContent = `${desc} • feels ${feels}° • hum ${hum}% • wind ${wind} km/h`;
                if (wxEmoji) wxEmoji.textContent = emoji;

            }catch(e){
                wxStatus.textContent = "OFFLINE";
                wxTemp.textContent = "--°C";
                wxMeta.textContent = "Weather unavailable";
                if (wxEmoji) wxEmoji.textContent = "🌡️";
            }
        }
        updateDhakaWeather();
        setInterval(updateDhakaWeather, 10 * 60 * 1000);
        window.addEventListener('focus', updateDhakaWeather);

        // ========= 3D Tilt (very smooth + light) =========
        document.querySelectorAll('[data-tilt]').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const r = card.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width;
                const py = (e.clientY - r.top) / r.height;

                const ry = (px - 0.5) * 10;      // -5..+5
                const rx = -(py - 0.5) * 8;      // -4..+4

                card.style.setProperty('--rx', rx.toFixed(2) + 'deg');
                card.style.setProperty('--ry', ry.toFixed(2) + 'deg');
                card.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
                card.style.setProperty('--my', (py * 100).toFixed(1) + '%');
            });

            card.addEventListener('mouseleave', () => {
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
                card.style.setProperty('--mx', '50%');
                card.style.setProperty('--my', '40%');
            });
        });

        // ========= Reveal on scroll =========
        const io = new IntersectionObserver((entries) => {
            entries.forEach(en => { if (en.isIntersecting) en.target.classList.add('is-visible'); });
        }, { threshold: 0.12 });
        document.querySelectorAll('.skill-holo').forEach(el => io.observe(el));

        // ========= Search filter =========
        const skillSearch = document.getElementById('skillSearch');
        const clearSearch = document.getElementById('clearSearch');
        const skillsGrid  = document.getElementById('skillsGrid');

        function runFilter(){
            const q = (skillSearch.value || '').trim().toLowerCase();
            skillsGrid.querySelectorAll('.skill-holo').forEach(card => {
                const hay = (card.getAttribute('data-skill') || '');
                card.style.display = hay.includes(q) ? '' : 'none';
            });
        }
        skillSearch?.addEventListener('input', runFilter);
        clearSearch?.addEventListener('click', () => { skillSearch.value = ''; runFilter(); });

        // ========= Stream modal: build 10 placeholder videos per skill =========
        const overlay = document.getElementById('streamsOverlay');
        const closeBtn = document.getElementById('streamClose');
        const videosGrid = document.getElementById('videosGrid');
        const streamTitle = document.getElementById('streamTitle');
        const streamMeta  = document.getElementById('streamMeta');

async function openStream(title, folder){
  streamTitle.textContent = `${title} • Stream`;
  streamMeta.textContent  = `Loading videos…`;
  videosGrid.innerHTML = `<p class="empty-note">Loading…</p>`;

  try{
    const url = `{{ route('insight.streams.videos') }}?folder=${encodeURIComponent(folder)}`;
    const res = await fetch(url, { cache: "no-store" });
    const data = await res.json();
    const videos = data.videos || [];

    videosGrid.innerHTML = '';

    if(!videos.length){
      videosGrid.innerHTML = `<p class="empty-note">No videos found in folder: <b>${folder}</b></p>`;
      streamMeta.textContent = `0 videos`;
    } else {
      streamMeta.textContent = `${videos.length} videos`;

      videos.forEach(v => {
        const el = document.createElement('div');
        el.className = 'video-card';
el.innerHTML = `
<div class="video-thumb">
  <video class="thumb-video" preload="metadata" muted playsinline>
    <source src="${v.url}" type="video/mp4">
  </video>
  <div class="play-badge"><i class="fa-solid fa-play"></i></div>
</div>

  <div class="video-body">
    <div class="video-title">Lesson ${v.index}</div>
    <div class="video-mini">
      <span>Video ${v.index}/${videos.length}</span>
    </div>
    <button class="video-watch"
      data-play-title="${title} • Lesson ${v.index}"
      data-play-url="${v.url}">
      Watch
    </button>
  </div>
`;

        videosGrid.appendChild(el);
        const vid = el.querySelector('.thumb-video');
if (vid) {
  vid.addEventListener('loadedmetadata', () => {
    // seek a tiny bit to ensure a frame is available
    const t = Math.min(0.1, Math.max(0, (vid.duration || 1) - 0.1));
    try { vid.currentTime = t; } catch(e) {}
  });

  vid.addEventListener('seeked', () => {
    vid.pause();
  });
}

      });
    }

    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

  }catch(e){
    videosGrid.innerHTML = `<p class="empty-note">Failed to load videos.</p>`;
    streamMeta.textContent = `Error`;
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }
}


        function closeStream(){
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        closeBtn?.addEventListener('click', closeStream);
        overlay?.addEventListener('click', (e) => { if(e.target === overlay) closeStream(); });
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeStream(); });

// Button handlers (event delegation)
document.addEventListener('click', (e) => {
  // 1) Open Stream button
  const open = e.target.closest('[data-open-stream]');
  if (open) {
    const card = open.closest('.skill-holo');

    // ✅ pass BOTH title + folder (your card already has data-video-folder)
    openStream(card.dataset.skillTitle, card.dataset.videoFolder);
    return;
  }

  // 2) Watch button inside the stream modal
  const watch = e.target.closest('.video-watch');
  if (watch) {
    openPlayer(watch.dataset.playTitle, watch.dataset.playUrl);
  }
});


        // ========= Player modal (placeholder now, real URLs later) =========
        const playerOverlay = document.getElementById('playerOverlay');
        const playerClose   = document.getElementById('playerClose');
        const playerTitle   = document.getElementById('playerTitle');

function openPlayer(title, videoUrl){
  playerTitle.textContent = title;

  const body = playerOverlay.querySelector('.player-body');
  body.innerHTML = `
    <video id="videoEl" controls autoplay style="width:100%; border-radius:16px;">
      <source src="${videoUrl}" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  `;

  playerOverlay.classList.add('open');
  playerOverlay.setAttribute('aria-hidden', 'false');
}

function closePlayer(){
  const v = document.getElementById('videoEl');
  if(v){ v.pause(); v.remove(); }

  const body = playerOverlay.querySelector('.player-body');
  body.innerHTML = `<p class="empty-note" id="playerNote">Video player is ready.</p>`;

  playerOverlay.classList.remove('open');
  playerOverlay.setAttribute('aria-hidden', 'true');
}

        playerClose?.addEventListener('click', closePlayer);
        playerOverlay?.addEventListener('click', (e) => { if(e.target === playerOverlay) closePlayer(); });
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closePlayer(); });
    </script>

    <!-- Chatbot rotating nudge bubble -->
<div class="cs-chat-nudge" id="csChatNudge" aria-hidden="true"></div>

<!-- Bot button (ONLY ONE) -->
<button class="cs-chat-fab" id="csChatFab" aria-label="Open Cognisense Chat" aria-haspopup="dialog" aria-expanded="false">
  <img src="{{ asset('img/bot.png') }}" alt="Cognisense Bot">
</button>

<!-- Chat panel -->
<div class="cs-chat" id="csChat" role="dialog" aria-modal="false" aria-hidden="true">
  <div class="cs-chat-header">
<div class="cs-chat-title cs-chat-brand">
  <img class="cs-chat-avatar" src="{{ asset('img/bot.png') }}" alt="Proxima" />
  <div class="cs-chat-brand-text">
    <div class="cs-chat-name">Proxima</div>
    <div class="cs-chat-status">
      <span class="cs-status-dot"></span>
      Online • Cognisense
    </div>
  </div>
</div>


    <div class="cs-chat-actions">
      <button class="cs-ico-btn" id="csChatThemeBtn" type="button" title="Toggle theme" aria-label="Toggle theme">
        <i class="fa-solid fa-moon"></i>
      </button>

      <button class="cs-ico-btn" id="csChatClose" type="button" title="Close" aria-label="Close chat">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>

  <div class="cs-chat-body" id="csChatBody">
 <div class="cs-msg bot">
  Hi! I’m your Cognisense assistant 👋<br>
  Ask me anything about Cognisense, Skill Hub, Insight Streams, Certificates, CV Builder, or AspireIELTS.<br>
  Theme commands still work: <b>/dark</b> or <b>/light</b>.
</div>
  </div>

  <form class="cs-chat-compose" id="csChatForm" autocomplete="off">
    <input class="cs-input" id="csChatInput" type="text" placeholder="Type a message…" />
    <button class="cs-send" type="submit" aria-label="Send">
      <i class="fa-solid fa-paper-plane"></i>
    </button>
  </form>
</div>


<script>
  // =========================
  // COGNISENSE CHATBOT (UI logic) — now wired to RAG bridge
  // =========================
  (function(){
    const BRIDGE_URL = "http://localhost/Cognisense/cognisense-rag/chat_bridge.php";
    // If your dashboard is served from the SAME Apache site, you can also use:
    // const BRIDGE_URL = "/Cognisense/cognisense-rag/chat_bridge.php";

    const fab      = document.getElementById('csChatFab');
    const panel    = document.getElementById('csChat');
    const closeBtn = document.getElementById('csChatClose');
    const themeBtn = document.getElementById('csChatThemeBtn');

    const bodyEl   = document.getElementById('csChatBody');
    const form     = document.getElementById('csChatForm');
    const input    = document.getElementById('csChatInput');

    if(!fab || !panel || !bodyEl || !form || !input){
      console.warn("Chatbot UI missing required elements. Check IDs: csChatFab, csChat, csChatBody, csChatForm, csChatInput");
      return;
    }

    function isOpen(){ return panel.classList.contains('open'); }

    function escapeHtml(s){
      return String(s)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    function formatAnswer(text){
      // Keep it safe + preserve line breaks
      return escapeHtml(text).replace(/\n/g, "<br>");
    }

    // ✅ animated thinking bubble (matches the CSS you pasted)
    function thinkingBubbleHtml(){
      return '<div class="cs-typing" aria-label="Thinking"><span></span><span></span><span></span></div>';
    }

    function updateThemeIcon(){
      if(!themeBtn) return;
      const dark = document.body.classList.contains('dark-mode');
      themeBtn.innerHTML = dark
        ? '<i class="fa-solid fa-sun"></i>'
        : '<i class="fa-solid fa-moon"></i>';
    }

    function openChat(){
      panel.classList.add('open');
      panel.setAttribute('aria-hidden', 'false');
      fab.setAttribute('aria-expanded', 'true');
      updateThemeIcon();

      // stop nudges while open
      window.CS_CHAT_NUDGE?.pause?.();

      setTimeout(() => input.focus(), 60);
    }

    function closeChat(){
      panel.classList.remove('open');
      panel.setAttribute('aria-hidden', 'true');
      fab.setAttribute('aria-expanded', 'false');

      // resume nudges after close
      window.CS_CHAT_NUDGE?.resume?.();
    }

    function toggleChat(){ isOpen() ? closeChat() : openChat(); }

    function addMsgHtml(html, who='bot'){
      const div = document.createElement('div');
      div.className = `cs-msg ${who}`;
      div.innerHTML = html;
      bodyEl.appendChild(div);
      bodyEl.scrollTop = bodyEl.scrollHeight;
      return div;
    }

    function handleCommand(raw){
      const msg = raw.trim().toLowerCase();
      if(msg === '/dark' || msg === 'dark'){
        if (typeof setTheme === 'function') setTheme(true);
        updateThemeIcon();
        addMsgHtml("Switched to <b>Dark</b> mode ✅", "bot");
        return true;
      }
      if(msg === '/light' || msg === 'light'){
        if (typeof setTheme === 'function') setTheme(false);
        updateThemeIcon();
        addMsgHtml("Switched to <b>Light</b> mode ✅", "bot");
        return true;
      }
      return false;
    }

    async function callRagBridge(message){
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 60000);

      try {
        const res = await fetch(BRIDGE_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ message }),
          signal: controller.signal
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch { throw new Error(`Bridge returned non-JSON: ${text.slice(0,180)}`); }

        if (!res.ok) {
          // if your PHP returns {ok:false,error:"..."} or {answer:"..."}
          const msg = data.error || data.answer || `HTTP ${res.status}`;
          throw new Error(msg);
        }

        return data; // expected: { answer: "...", sources: [...] }
      } finally {
        clearTimeout(timeout);
      }
    }

    // FAB click
    fab.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleChat();
    });

    // close button
    if(closeBtn){
      closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeChat();
      });
    }

    // theme button
    if(themeBtn){
      themeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const darkNow = document.body.classList.contains('dark-mode');
        if (typeof setTheme === 'function') setTheme(!darkNow);
        updateThemeIcon();
        addMsgHtml(`Theme updated ✅`, "bot");
      });
    }

    // Send message -> NOW CALLS RAG
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const text = input.value.trim();
      if(!text) return;

      // show user bubble
      addMsgHtml(escapeHtml(text), 'user');
      input.value = '';

      // theme commands still local
      if(handleCommand(text)) return;

      // ✅ placeholder bot bubble (animated dots)
      const pending = addMsgHtml(thinkingBubbleHtml(), "bot");
      pending.classList.add('thinking');

      try {
        const data = await callRagBridge(text);
        const answerHtml = formatAnswer(data.answer || "No answer returned.");

        pending.classList.remove('thinking');
        pending.innerHTML = answerHtml;

        bodyEl.scrollTop = bodyEl.scrollHeight;

      } catch (err) {
        pending.classList.remove('thinking');
        pending.innerHTML = `<b>Error:</b> ${escapeHtml(err.message || String(err))}`;
      }
    });

    // ESC closes
    document.addEventListener('keydown', (e) => {
      if(e.key === 'Escape' && isOpen()) closeChat();
    });

    // Click outside closes
    document.addEventListener('click', (e) => {
      if(!isOpen()) return;
      if(panel.contains(e.target) || fab.contains(e.target)) return;
      closeChat();
    });

    // Keep icon synced when your theme changes
    const _applyTheme = window.applyTheme;
    window.applyTheme = function(pulse){
      if (typeof _applyTheme === 'function') _applyTheme(pulse);
      updateThemeIcon();
    };

    updateThemeIcon();
  })();
</script>



<script>
  // =========================
  // Rotating pop-up messages ABOVE chatbot:
  // - Wait 5s -> show msg1 for 5s -> hide
  // - Wait 5s -> show msg2 for 5s -> hide
  // - Wait 5s -> show msg3 for 5s -> hide
  // - Loop forever
  // Runs ONLY when chat is closed
  // =========================
  (function(){
    const nudge = document.getElementById('csChatNudge');
    const panel = document.getElementById('csChat'); // chat panel

    if (!nudge) {
      console.warn("csChatNudge not found. Add <div id='csChatNudge' class='cs-chat-nudge'></div> above the bot button.");
      return;
    }

    const msgs = [
      "Heyy I’m your personal AI assistant.",
      "My name is Proxima AI.",
      "Do you need guidance in anything?",
      "Feel free to ask me anything."
    ];

    const START_DELAY = 5000; // before first message
    const SHOW_MS     = 5000; // how long message stays visible
    const GAP_MS      = 5000; // gap after it leaves
    const CHECK_OPEN_MS = 800; // when chat open, re-check periodically

    let idx = 0;
    let tA = null;
    let tB = null;

    function clearTimers(){
      if(tA) clearTimeout(tA);
      if(tB) clearTimeout(tB);
      tA = tB = null;
    }

    function chatIsOpen(){
      return !!(panel && panel.classList.contains('open'));
    }

    function hide(){
      nudge.classList.remove('show');
      nudge.setAttribute('aria-hidden','true');
    }

    function step(){
      clearTimers();

      // ✅ if chat is open, do NOTHING (hide + keep checking)
      if(chatIsOpen()){
        hide();
        tA = setTimeout(step, CHECK_OPEN_MS);
        return;
      }

      // show current message
      nudge.textContent = msgs[idx];
      nudge.classList.add('show');
      nudge.setAttribute('aria-hidden','false');

      // after 5s, hide it
      tA = setTimeout(() => {
        hide();

        // after 5s gap, move to next message and repeat
        tB = setTimeout(() => {
          idx = (idx + 1) % msgs.length;
          step();
        }, GAP_MS);

      }, SHOW_MS);
    }

    function start(){
      hide();
      clearTimers();
      tA = setTimeout(step, START_DELAY);
    }

    // ✅ expose controls to the chat script
    window.CS_CHAT_NUDGE = {
      pause: function(){
        // if it was visible when opening chat, advance so next time it doesn't repeat
        if(nudge.classList.contains('show')) idx = (idx + 1) % msgs.length;
        clearTimers();
        hide();
      },
      resume: function(){
        // restart with your rule: after closing chat, wait 5s then show next
        hide();
        clearTimers();
        if(chatIsOpen()) return;
        tA = setTimeout(step, START_DELAY);
      }
    };

    start();
  })();
</script>
</body>
</html>
