# Cogni Connect — Real-Time Messaging for Collaboration

## Purpose
Cogni Connect provides a real-time messaging environment within Cognisense, enabling users to communicate, collaborate, and share insights. It functions similarly to Slack or Discord, with channels and threaded conversations.

## Goals
- Facilitate peer-to-peer learning and discussion
- Enable team collaboration on skill development
- Provide instant notifications and updates via WebSockets
- Maintain conversation history with searchable messages

## Structure
- **Channels**: Organized topics or groups (e.g., #interview-prep, #general)
- **Messages**: Text content with optional attachments
- **Reactions**: Emoji reactions to messages
- **Threads**: Nested replies to keep conversations organized
- **Read Tracking**: Last-read timestamps for unread indicators

## Data Model (Simplified)
- `cc_channels`: id, name, type, created_by, created_at
- `cc_messages`: id, channel_id, user_id, content, attachment_url, created_at
- `cc_message_attachments`: id, message_id, file_path, file_type
- `cc_message_reactions`: id, message_id, user_id, reaction
- `cc_read_status`: user_id, channel_id, last_read_at

## Real-Time Behavior
- WebSocket support via Laravel Reverb and Pusher
- Messages broadcast instantly to channel participants
- Unread counts update in real time
- Typing indicators and online presence (optional)

## User Flow
1. User navigates to Cogni Connect from the dashboard.
2. Sees list of available channels (public/private).
3. Clicks a channel to view message history.
4. Types a message, optionally attaches files.
5. Message appears instantly to all online users in that channel.
6. Users can reply in threads or react to messages.
7. Notifications appear for mentions or new messages in subscribed channels.

## Integration with Other Modules
- Users can share certificates or progress in channels
- Links to Skill Hub scenarios or Insight Streams videos can be embedded
- Proxima AI may be integrated for channel Q&A (future)

## Assistant Behavior (RAG notes)
When asked:
- “How do I use Cogni Connect?” → explain channels, messaging, real-time features.
- “Can I create a private channel?” → yes, if implemented (admin or user permissions).
- “How do I get notified?” → real-time via WebSockets; unread badges.
- “Where are my messages stored?” → in database; accessible from any device.