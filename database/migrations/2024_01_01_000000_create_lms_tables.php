<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Ordre de création respectant les dépendances de clés étrangères :
     * users (existe déjà) → courses → levels → lessons → enrollments → ai_*
     */
    public function up(): void
    {
        // ================================================================
        // 1. USERS — enrichissement de la table existante
        // ================================================================
        Schema::table("users", function (Blueprint $table) {
            if (!Schema::hasColumn("users", "role")) {
                $table
                    ->enum("role", ["student", "instructor", "admin"])
                    ->default("student")
                    ->after("password");
            }
            if (!Schema::hasColumn("users", "avatar")) {
                $table->string("avatar")->nullable()->after("role");
            }
            if (!Schema::hasColumn("users", "bio")) {
                $table->text("bio")->nullable()->after("avatar");
            }
        });

        // ================================================================
        // 2. COURSES — Formations
        // ================================================================
        Schema::create("courses", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("instructor_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->string("title");
            $table->string("slug")->unique();
            $table->string("thumbnail")->nullable();
            $table->text("description");
            $table->decimal("price", 8, 2)->default(0);
            $table->enum("status", ["draft", "published"])->default("draft");
            $table->timestamps();
            $table->softDeletes();

            // Index pour les listes publiques
            $table->index(["status", "created_at"]);
            $table->index(["instructor_id", "status"]);
        });

        // ================================================================
        // 3. LEVELS — Niveaux (Débutant, Intermédiaire, Avancé…)
        // ================================================================
        Schema::create("levels", function (Blueprint $table) {
            $table->id();
            $table->foreignId("course_id")->constrained()->cascadeOnDelete();
            $table->string("title"); // ex: "Niveau 1 – Débutant"
            $table->text("description"); // description détaillée du niveau
            $table->unsignedInteger("order")->default(0);
            $table->timestamps();

            $table->index(["course_id", "order"]);
        });

        // ================================================================
        // 4. LESSONS — Leçons rattachées à un niveau
        // ================================================================
        Schema::create("lessons", function (Blueprint $table) {
            $table->id();
            $table->foreignId("level_id")->constrained()->cascadeOnDelete();
            $table->string("title");
            $table->text("content")->nullable(); // texte / résumé
            $table
                ->enum("media_type", ["video", "audio", "pdf"])
                ->default("video");
            $table->string("media_path")->nullable(); // chemin stockage
            $table->unsignedInteger("duration_minutes")->nullable();
            $table->unsignedInteger("order")->default(0);
            $table->timestamps();

            $table->index(["level_id", "order"]);
        });

        // ================================================================
        // 5. ENROLLMENTS — Demandes d'accès avec workflow de validation
        // ================================================================
        Schema::create("enrollments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("course_id")->constrained()->cascadeOnDelete();
            $table
                ->enum("status", ["en_attente", "approuvé", "refusé"])
                ->default("en_attente");
            $table
                ->foreignId("approved_by")
                ->nullable()
                ->constrained("users")
                ->nullOnDelete();
            $table->timestamp("approved_at")->nullable();
            $table->timestamps();

            // Un étudiant ne peut faire qu'une seule demande par formation
            $table->unique(["user_id", "course_id"]);

            // Pour le tableau de bord admin : liste des demandes en attente
            $table->index(["status", "created_at"]);
        });

        // ================================================================
        // 6. PROGRESS — Suivi de progression par leçon
        // ================================================================
        Schema::create("progress", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("lesson_id")->constrained()->cascadeOnDelete();
            $table->boolean("completed")->default(false);
            $table->timestamp("completed_at")->nullable();
            $table->unsignedInteger("watched_seconds")->default(0);
            $table->timestamps();

            $table->unique(["user_id", "lesson_id"]);
        });

        // ================================================================
        // 7. AI_CONVERSATIONS — Conversations du Helpdesk IA
        // ================================================================
        Schema::create("ai_conversations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            // Contexte : à quel endroit de la formation l'étudiant pose sa question
            $table
                ->foreignId("level_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table
                ->foreignId("lesson_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string("title")->nullable(); // résumé auto de la conversation
            $table->timestamps();

            $table->index(["user_id", "created_at"]);
        });

        // ================================================================
        // 8. AI_MESSAGES — Messages individuels dans une conversation
        // ================================================================
        Schema::create("ai_messages", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("conversation_id")
                ->constrained("ai_conversations")
                ->cascadeOnDelete();
            $table->enum("role", ["user", "assistant", "system"]);
            $table->text("content");
            $table->unsignedInteger("tokens_used")->nullable();
            $table->timestamps();

            $table->index(["conversation_id", "created_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("ai_messages");
        Schema::dropIfExists("ai_conversations");
        Schema::dropIfExists("progress");
        Schema::dropIfExists("enrollments");
        Schema::dropIfExists("lessons");
        Schema::dropIfExists("levels");
        Schema::dropIfExists("courses");

        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["role", "avatar", "bio"]);
        });
    }
};
