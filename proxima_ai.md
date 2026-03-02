# Proxima AI — RAG-Powered Platform Assistant

## Purpose
Proxima AI is an intelligent chatbot that answers user questions about Cognisense itself. It uses retrieval-augmented generation (RAG) to provide accurate, context-aware responses based on the platform’s documentation, knowledge base, and architectural details.

## Goals
- Serve as interactive documentation and support
- Answer how-to questions about features and workflows
- Explain scoring logic, rubrics, and evaluation methods
- Assist developers/admins with setup and troubleshooting
- Reduce support burden by providing instant, reliable answers

## How It Works
1. **Knowledge Base**: A curated collection of Markdown files (like these), plus additional docs, FAQs, and setup guides.
2. **Vector Store**: Documents are chunked, embedded using `all-MiniLM-L6-v2`, and stored in ChromaDB.
3. **Retrieval**: When a user asks a question, Proxima performs semantic search to find the most relevant chunks.
4. **Generation**: An LLM (via OpenRouter or OpenAI) generates an answer grounded in the retrieved context.
5. **Response**: The answer is presented in the chat interface, with sources optionally shown.

## Capabilities
- Explain how to use Skill Hub, Insight Streams, etc.
- Guide through scenario submission and scoring breakdown.
- Clarify rubric dimensions and weightings.
- Provide technical details for developers (e.g., API endpoints, environment setup).
- Help with troubleshooting common issues.

## User Flow
1. User clicks Proxima AI icon (e.g., in sidebar or floating button).
2. Opens chat panel.
3. Asks a question like “How do I generate a certificate?”
4. Proxima retrieves relevant knowledge and responds with step-by-step instructions.
5. User can ask follow-ups within the same session.

## Technical Stack
- Backend: FastAPI service (RAG service) with ChromaDB and embedding model.
- Embedding: `sentence-transformers/all-MiniLM-L6-v2`
- LLM: OpenAI/OpenRouter (e.g., gpt-4o-mini)
- Retrieval: ChromaDB vector search
- Integration: Laravel frontend calls RAG service API endpoint (`POST /chat`).

## Assistant Behavior (RAG notes)
When asked:
- “What is Proxima AI?” → RAG chatbot for Cognisense help.
- “How does scoring work?” → retrieves rubric explanation from knowledge base.
- “How do I set up the evaluation service?” → provides developer docs.
- “Can you help me with a bug?” → troubleshooting steps based on known issues.
- “Where are videos stored?” → explains Insight Streams folder structure.

## Knowledge Base Contents
- All feature markdowns (Skill Hub, Insight Streams, etc.)
- Technical architecture docs
- Rubric definitions
- Setup instructions
- FAQ