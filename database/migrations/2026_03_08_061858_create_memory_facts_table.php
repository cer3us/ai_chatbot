<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         // Ensure the vector extension is enabled
//         DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

//         Schema::create('memory_facts', function (Blueprint $table) {
//             $table->id();
//             $table->string('session_id')->index();
//             $table->text('content');
//             $table->vector('embedding', 768); // 768 dimensions for nomic-embed-text
//             $table->jsonb('metadata')->nullable();
//             $table->timestamps();
//         });

//         // Create HNSW index with the correct operator class for cosine distance
//         // (since we use the <=> operator in queries)
//         DB::statement('
//             CREATE INDEX memory_facts_embedding_idx 
//             ON memory_facts 
//             USING hnsw (embedding vector_cosine_ops)
//         ');
//     }

//     public function down(): void
//     {
//         Schema::dropIfExists('memory_facts');
//     }
// };

return new class extends Migration
{
    public function up(): void
    {
        // Enable the pgvector extension (Laravel 12 native method)
        Schema::ensureVectorExtensionExists();

        Schema::create('memory_facts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->text('content');
            
            // ✅ CORRECT: Use the vector() method with dimensions parameter
            $table->vector('embedding', 768)->index(); // 768 for nomic-embed-text
            
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_facts');
    }
};