# Laravel AI Chatbot with Long‑Term Memory 🧠

A real‑time AI chatbot built with **Laravel 12**, **Livewire**, and **Reverb**. It features **persistent long‑term memory** using vector embeddings (PostgreSQL + pgvector), **Redis caching** for instant responses, and supports **multiple AI providers** – local Ollama models (local and cloud), cloud OpenRouter, or any OpenAI‑compatible API.  
The bot also integrates with **Telegram**, manages multiple conversations, and allows full‑text search within chat history with result highlighting.

---

## ✨ Features

### 💬 Real‑Time Chat
- Powered by **Livewire** + **Reverb** (Laravel’s first‑party WebSocket server).
- Messages appear instantly; typing indicators and auto‑scroll.

### 🧠 Long‑Term Memory (RAG)
- Automatically extracts **facts** from user messages using a dedicated LLM.
- Stores facts as **vector embeddings** in PostgreSQL (via `pgvector`).
- Retrieves relevant facts with **semantic similarity** (cosine distance).
- Manual fact insertion via `Fact:`, `Remember:`, or `Memorize:` commands.
- Deduplication prevents storing near‑identical facts.

### ⚡ Performance
- **Redis** caching of AI responses – identical questions (with same memory context) return instantly.
- Background jobs (`ProcessAIResponse`, `ExtractFactsFromMessage`) queued via Redis for non‑blocking operation.
- Lazy‑loading of messages (infinite scroll) and pagination for large conversations.

### 🔌 Multi‑Provider AI Support
- **Local** – any model served by Ollama (e.g., `phi3:mini`, `qwen2.5‑coder`, `llama3`).
- **Cloud** – OpenRouter, OpenAI, or any OpenAI‑compatible API via the [Prism](https://prismphp.com/) abstraction.
- Easy switch via `.env` – no code changes required.

### 🗂️ Conversation Management
- Create, delete, and switch between multiple chats (sidebar with toggle).
- Each conversation maintains its own history and memory context.

### 🔍 Search & Navigation
- Full‑text search within a conversation (with debounced input).
- Found terms are **highlighted**; navigate through results with next/previous buttons.
- Automatically scrolls to the selected message.

### 🤖 Telegram Bot
- Full integration using `romanlazko/laravel‑telegram`.
- `/start` welcomes the user; any text message is forwarded to the same AI service.
- Telegram users are mapped to Laravel’s `users` table (guest or authenticated).

### 🛡️ Authentication
- No authentication required – guests can chat immediately (session‑based).
- Ready to add Laravel Breeze / Jetstream for user accounts.

### 🕵️ Detailed logging
- Custom Logs in `logs/laravel.log`
---

## 🧰 Tech Stack & Dependencies

| Component                | Package / Technology                          |
|--------------------------|-----------------------------------------------|
| **Framework**            | Laravel 12                                    |
| **Frontend**             | Livewire, Tailwind CSS, Alpine.js, Vite       |
| **Real‑time**            | Laravel Reverb                                 |
| **Database**             | PostgreSQL + `pgvector` extension              |
| **Caching / Queue**      | Redis                                          |
| **AI Abstraction**       | Prism (EchoLabs)     |
| **Local AI**             | Ollama (any local model as `phi3:mini`, `nomic-embed-text`for embeddings etc.) |
| **Cloud AI**             | OpenRouter (or any OpenAI‑compatible)          |
| **Embeddings**           | Local Ollama with `nomic-embed-text`           |
| **Telegram Bot**         | `romanlazko/laravel-telegram`                  |
| **Vector Search**        | `pgvector` raw SQL with cosine distance        |
| **Queues**               | Laravel queues (database/Redis)                |

---

## 🚀 Installation

### 1. Clone the repository
```bash
git clone https://github.com/cer3us/your-repo.git
cd your-repo
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Environment configuration
```bash
cp .env.example .env 
php artisan key:generate
```
Key variables to configure:
- DB_* – PostgreSQL database.
- REDIS_* – Redis connection (cache & queue).
- REVERB_* – Reverb WebSocket server.
- OLLAMA_* – local Ollama host and models.
- OPENROUTER_API_KEY / OPENAI_API_KEY – for cloud AI (optional).
- TELEGRAM_BOT_TOKEN – if using Telegram.

### 4. Run migrations
```bash
php artisan migrate
```

### 5. Install Node dependencies & build assets
```bash
npm install
npm run dev   # or build for production
```

### 6. Set up Ollama (local models)
```bash
ollama pull phi3:mini          # main chat model (or any other)
ollama pull nomic-embed-text   # embeddings for memory
```
- Install `ollama` info: https://docs.ollama.com/quickstart

### 7. Start the queue worker
```bash
php artisan queue:work redis --tries=3 --timeout=300
```

### 8. Start Reverb
```bash
php artisan reverb:start
```

### 9. Serve the application
```bash
php artisan serve --port=<your_port>
```
- Visit http://localhost:<your_port> – you can start chatting immediately (no login required).


## 🤖 Telegram Bot Setup

1. Create a bot via @BotFather and obtain a token.
2. Add the token to your .env:
```bash
TELEGRAM_BOT_TOKEN=your_token
```
3. Register the bot with the package:
```bash
php artisan telegram:bot your_bot_name
```
4. Set the webhook (uses APP_URL from .env):
```bash
php artisan telegram:set-webhook
```
5. Ensure your APP_URL is publicly accessible (can use `ngrok` for development).
The bot will respond to any text message using the same AIService as the web chat.

## 🧠 How Memory Works
- Every user message is processed by an ExtractFactsFromMessage job.
- The job uses an LLM (configurable, local or cloud) to extract personal facts.
- Facts are stored as records in the memory_facts table with a vector embedding (nomic-embed-text).
- When a new message arrives, the most relevant facts are retrieved via cosine similarity and injected into the system prompt.
- The AI then answers with awareness of the user’s history.
- Explicit commands override automatic extraction:
```bash
    Fact: I love pizza.
    Remember: my birthday is Jan 1.
```
---

📄 License
- MIT

🙏 Acknowledgements
- [Laravel](https://laravel.com/)
- [Livewire](https://livewire.laravel.com/)
- [Reverb](https://laravel.com/docs/11.x/reverb)
- [Prism](https://prismphp.com/)
- [Ollama](https://ollama.com/)
- [PGVector](https://github.com/pgvector/pgvector)
- [romanlazko/laravel-telegram](https://github.com/romanlazko/laravel-telegram)

---

