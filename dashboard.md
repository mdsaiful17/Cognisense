# Dashboard — Central Hub of Cognisense

## Purpose
The Dashboard serves as the main landing page after login, providing quick access to all Cognisense modules, progress overview, and personalized recommendations. It ties together the entire learning ecosystem.

## Goals
- Offer a unified entry point to all features
- Display user progress and recent activity
- Enable easy navigation via collapsible sidebar
- Support dark/light theme toggle for user preference
- Show key metrics and notifications

## Components
- **Sidebar**: Collapsible navigation menu with links to Skill Hub, Insight Streams, Certificate, CV Builder, Cogni Connect, Explore, AspireIELTS, and Proxima AI.
- **Theme Toggle**: Switch between dark and light modes (persisted in localStorage).
- **Welcome Section**: Greeting with user name and motivational message.
- **Progress Overview**: Graphs or cards showing overall band scores, skill completion, recent attempts.
- **Module Cards**: Quick-launch cards for each major module with descriptions.
- **Recent Activity**: List of recent scenario attempts or certificates earned.
- **Notifications**: Real-time alerts for messages or feedback.

## Design Identity
- Fonts: Orbitron (headings), Poppins (body)
- Visuals: Gradient backgrounds, glass-style cards, subtle shadows
- Responsive: Adapts to mobile and desktop

## User Flow
1. User logs in and lands on Dashboard.
2. Views summary of progress.
3. Clicks a module card or sidebar link to navigate.
4. Theme toggle adjusts appearance instantly.
5. Sidebar can be collapsed for more screen space.

## Data Integration
- Fetches user data, recent attempts, certificates from database.
- Progress graphs use historical attempt scores.
- Notifications come from Cogni Connect and system alerts.

## Assistant Behavior (RAG notes)
When asked:
- “What’s on the dashboard?” → overview of modules, progress, quick links.
- “How do I change theme?” → click the sun/moon icon in sidebar or top bar.
- “Where is my recent activity?” → scroll down on dashboard.
- “Can I customize the dashboard?” → future feature.