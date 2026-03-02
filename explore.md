# Explore — AI-Powered Skill Learning Chatbot

## Purpose
Explore is an on-demand AI chatbot that teaches any skill through structured guidance. It uses a large language model (via OpenRouter) to provide definitions, roadmaps, practice plans, resources, and common mistakes, helping users learn beyond the predefined scenarios.

## Goals
- Offer instant, personalized learning support
- Cover any professional or soft skill topic
- Provide structured, actionable responses (not generic)
- Maintain conversation context for deeper learning

## How It Works
- Powered by OpenRouter with models like GPT-4o-mini
- Conversation history is preserved to allow follow-up questions
- Responses are designed to include:
  - Definition and importance
  - Prerequisites
  - Step-by-step roadmap
  - Practice exercises
  - Recommended resources
  - Common pitfalls

## User Flow
1. User clicks “Explore” in the sidebar.
2. Opens a chat interface.
3. Asks a question like “How do I improve my negotiation skills?”
4. Explore responds with a structured guide.
5. User can ask follow-ups (e.g., “Give me a practice scenario”).
6. Conversation continues with context.

## Technical Stack
- Backend: Laravel controller calling OpenRouter API
- Frontend: Chat UI with message history
- API: OpenRouter with conversation history passed as messages array
- Security: API key stored in environment; no user data exposed

## Example Interaction
- User: “Teach me how to handle customer complaints.”
- Explore: Provides definition, STAR method, example phrases, common mistakes, and a mini-exercise.

## Assistant Behavior (RAG notes)
When asked:
- “What is Explore?” → AI chatbot for learning any skill.
- “How do I use it?” → just type your question in the chat.
- “Can it help with interview prep?” → yes, ask anything.
- “Is it free?” → depends on platform usage limits.