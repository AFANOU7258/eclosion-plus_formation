<?php
/**
 * ╔══════════════════════════════════════════════════════════╗
 * ║           ECLOSION+ — Plateforme LMS                    ║
 * ║           Documentation & Référence Projet               ║
 * ╚══════════════════════════════════════════════════════════╝
 *
 * Accéder à cette doc : http://localhost/eclosion-plus/public/project-doc.php
 * Ou via Artisan : php artisan serve puis /project-doc.php
 */

echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Eclosion+ — Doc Projet</title>
<style>
body{font-family:system-ui,sans-serif;max-width:1100px;margin:0 auto;padding:20px;background:#f8f9fa;color:#1a1a2e}
h1{color:#0d6025;border-bottom:3px solid #0d6025;padding-bottom:10px}
h2{color:#06429a;margin-top:30px;border-left:4px solid #06429a;padding-left:10px}
h3{color:#333}
table{width:100%;border-collapse:collapse;margin:10px 0;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)}
th{background:#0d6025;color:#fff;padding:10px;text-align:left;font-size:14px}
td{padding:10px;border-bottom:1px solid #eee;font-size:13px}
code{background:#e8f5ec;padding:2px 6px;border-radius:4px;font-size:12px;color:#0d6025}
.badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600}
.badge-green{background:#d4edda;color:#155724}
.badge-blue{background:#d1ecf1;color:#0c5460}
.badge-yellow{background:#fff3cd;color:#856404}
.badge-red{background:#f8d7da;color:#721c24}
.section{background:#fff;border-radius:8px;padding:20px;margin:15px 0;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:15px}
.card{background:#fff;border-radius:8px;padding:15px;box-shadow:0 1px 3px rgba(0,0,0,.05);border-left:4px solid #0d6025}
.card.blue{border-left-color:#06429a}
.card.purple{border-left-color:#7c3aed}
</style></head><body>';

echo "<h1>🌱 Eclosion+ — Plateforme de Formation en Ligne</h1>";
echo "<p>LMS complet avec IA, paiement Wave, liens partagés, et administration.</p>";

// === INFOS PROJET ===
echo '<div class="section"><h2>📋 Informations Projet</h2>';
echo "<table>";
echo '<tr><td style="width:200px"><strong>Framework</strong></td><td>Laravel 10.50.2</td></tr>';
echo "<tr><td><strong>PHP</strong></td><td>8.2.12</td></tr>";
echo "<tr><td><strong>Base de données</strong></td><td>MariaDB 10.4.32 (MySQL)</td></tr>";
echo "<tr><td><strong>Frontend</strong></td><td>Tailwind CSS (CDN) + Material Icons</td></tr>";
echo "<tr><td><strong>IA</strong></td><td>DeepSeek API (deepseek-chat)</td></tr>";
echo "<tr><td><strong>Paiement</strong></td><td>Wave Mobile Money</td></tr>";
echo "<tr><td><strong>Devise</strong></td><td>Franc CFA (FCFA)</td></tr>";
echo "<tr><td><strong>Démarrage</strong></td><td><code>php artisan serve --port=8080</code></td></tr>";
echo "</table></div>";

// === BASE DE DONNÉES ===
echo '<div class="section"><h2>🗄️ Base de données</h2>';
$tables = [
    "users" => "Comptes (student, instructor, admin)",
    "courses" => "Formations (titre, prix, statut, catégorie)",
    "categories" => "Catégories de formations",
    "levels" => "Niveaux (Débutant, Intermédiaire, Avancé)",
    "lessons" => "Leçons (vidéo, audio, PDF) avec illustrations",
    "enrollments" => "Inscriptions (en_attente, approuvé, refusé) + Wave",
    "progress" => "Progression par leçon (completed, watched_seconds)",
    "comments" => "Questions/Réponses avec réponse IA automatique",
    "reviews" => "Avis/Notations (1-5 étoiles)",
    "share_links" => 'Liens d\'accès direct (guest access)',
    "ai_conversations" => 'Conversations avec l\'IA',
    "ai_messages" => "Messages dans les conversations IA",
];
echo "<table><tr><th>Table</th><th>Description</th></tr>";
foreach ($tables as $table => $desc) {
    echo "<tr><td><code>{$table}</code></td><td>{$desc}</td></tr>";
}
echo "</table></div>";

// === ROUTES ===
echo '<div class="section"><h2>🔗 Routes</h2>';

echo "<h3>🌐 Publiques</h3>";
$publicRoutes = [
    ["GET", "/", "Accueil (landing page)"],
    ["GET", "/formations", "Catalogue avec recherche + catégories"],
    ["GET", "/formations/{course}", "Détail formation + avis"],
    ["GET", "/acces/{token}", "Accès invité via lien partagé"],
    ["GET", "/login", "Connexion"],
    ["POST", "/login", "Authentification"],
    ["GET", "/register", "Inscription"],
    ["POST", "/register", "Création compte"],
    ["POST", "/logout", "Déconnexion"],
];
echo "<table><tr><th>Méthode</th><th>URL</th><th>Action</th></tr>";
foreach ($publicRoutes as $r) {
    echo "<tr><td>{$r[0]}</td><td><code>{$r[1]}</code></td><td>{$r[2]}</td></tr>";
}
echo "</table>";

echo "<h3>👤 Authentifiées</h3>";
$authRoutes = [
    ["GET", "/mes-cours", "Mes inscriptions"],
    ["GET", "/lecons/{lesson}", "Page leçon + lecteur"],
    ["GET", "/support", "Historique conversations IA"],
    ["GET", "/support/{id}", "Conversation IA"],
    ["POST", "/helpdesk/chat", "Envoyer message IA"],
    ["POST", "/enrollments/{id}/request", "Demander accès + Wave"],
    ["POST", "/progress/{id}/toggle", "Marquer leçon terminée"],
    ["POST", "/lecons/{id}/comments", "Poster question (IA répond)"],
    ["POST", "/formations/{id}/review", "Donner avis/étoiles"],
];
echo "<table><tr><th>Méthode</th><th>URL</th><th>Action</th></tr>";
foreach ($authRoutes as $r) {
    echo "<tr><td>{$r[0]}</td><td><code>{$r[1]}</code></td><td>{$r[2]}</td></tr>";
}
echo "</table>";

echo "<h3>🛡️ Admin</h3>";
$adminRoutes = [
    ["GET", "/admin", "Dashboard avec stats"],
    ["GET/POST", "/admin/courses", "CRUD Formations"],
    ["POST", "/admin/courses/{id}/share", "Générer lien partagé"],
    ["GET", "/admin/enrollments", "Gestion demandes"],
    ["PATCH", "/admin/enrollments/{id}/approve", "Approuver"],
    ["PATCH", "/admin/enrollments/{id}/reject", "Refuser"],
    ["GET", "/admin/users", "Gestion utilisateurs"],
    ["PATCH", "/admin/users/{id}/role", "Changer rôle"],
];
echo "<table><tr><th>Méthode</th><th>URL</th><th>Action</th></tr>";
foreach ($adminRoutes as $r) {
    echo "<tr><td>{$r[0]}</td><td><code>{$r[1]}</code></td><td>{$r[2]}</td></tr>";
}
echo "</table></div>";

// === FONCTIONNALITÉS ===
echo '<div class="section"><h2>⚡ Fonctionnalités</h2>';
echo '<div class="grid">';

$features = [
    ["🎓 Formations", "6 formations avec 14 niveaux et 39 leçons", "green"],
    ["🎬 Médias", "Vidéo MP4, Audio MP3, Documents PDF", "green"],
    ["⭐ Avis", "Notation 1-5 étoiles avec commentaires", "green"],
    ["💬 Commentaires IA", "Questions → réponse auto par DeepSeek", "blue"],
    ["🤖 Chat IA", "Assistant contextuel par leçon", "blue"],
    ["💳 Paiement Wave", "Mobile Money avec référence transaction", "green"],
    ["💰 FCFA", "Devise Franc CFA", "green"],
    ["🔗 Liens partagés", "Accès invité sans compte", "purple"],
    ["🔍 Recherche", "Recherche dans le catalogue", "green"],
    ["🏷️ Catégories", "5 catégories de formations", "green"],
    ["📊 Dashboard", "Stats, revenus, top formations", "blue"],
    ["👥 Utilisateurs", "Gestion rôles (student/instructor/admin)", "purple"],
    ["🖼️ Illustrations", "Images par leçon + image/audio par niveau", "green"],
    ["📱 Responsive", "Mobile-first, adapté smartphone", "blue"],
];
foreach ($features as $f) {
    echo "<div class='card " .
        ($f[2] === "blue" ? "blue" : ($f[2] === "purple" ? "purple" : "")) .
        "'><strong>{$f[0]}</strong><br><small>{$f[1]}</small></div>";
}
echo "</div></div>";

// === COMPTES ===
echo '<div class="section"><h2>🔑 Comptes</h2>';
echo "<table><tr><th>Rôle</th><th>Email</th><th>Mot de passe</th></tr>";
echo '<tr><td><span class="badge badge-red">Admin</span></td><td>Créé lors de l\'installation</td><td>Voir configuration</td></tr>';
echo '<tr><td><span class="badge badge-blue">Formations</span></td><td colspan="2">Alibaba • Marketing Digital • Dropshipping • Facebook Ads • E-commerce • TikTok Shop</td></tr>';
echo "</table></div>";

// === COMMANDES ===
echo '<div class="section"><h2>🖥️ Commandes utiles</h2>';
echo "<table>";
$commands = [
    ["Démarrer le serveur", "<code>php artisan serve --port=8080</code>"],
    [
        "Démarrer MySQL",
        '<code>C:\\xampp\\mysql\\bin\\mysqld --defaults-file=C:\\xampp\\mysql\\bin\\my.ini</code>',
    ],
    ["Voir les routes", "<code>php artisan route:list --except-vendor</code>"],
    ["Accéder au site", "<code>http://127.0.0.1:8080</code>"],
    ['Accéder à l\'admin', "<code>http://127.0.0.1:8080/admin</code>"],
    ["Tinker (test)", "<code>php artisan tinker</code>"],
];
foreach ($commands as $c) {
    echo "<tr><td style='width:200px'>{$c[0]}</td><td>{$c[1]}</td></tr>";
}
echo "</table></div>";

// === STRUCTURE ===
echo '<div class="section"><h2>📁 Structure du projet</h2>';
echo '<pre style="background:#1a1a2e;color:#e0e0e0;padding:15px;border-radius:8px;font-size:12px;line-height:1.6">
eclosion-plus/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php        ← Dashboard + Users + Enrollments
│   │   ├── AdminCourseController.php  ← CRUD Formations + uploads
│   │   ├── CourseController.php       ← Catalogue + recherche
│   │   ├── LessonController.php       ← Page leçon
│   │   ├── EnrollmentController.php   ← Demandes d\'accès + Wave
│   │   ├── CommentController.php      ← Commentaires + IA auto-réponse
│   │   ├── ReviewController.php       ← Avis/Étoiles
│   │   ├── AiHelpdeskController.php   ← Chat IA
│   │   ├── ShareLinkController.php    ← Liens partagés
│   │   ├── ProgressController.php     ← Toggle progression
│   │   └── Auth/AuthController.php    ← Login/Register
│   ├── Models/
│   │   ├── Course.php       ├── Level.php      ├── Lesson.php
│   │   ├── Enrollment.php   ├── Progress.php   ├── Comment.php
│   │   ├── Review.php       ├── Category.php   ├── ShareLink.php
│   │   ├── AiConversation.php └── AiMessage.php
│   └── Services/
│       └── AiHelpdeskService.php     ← DeepSeek API (curl natif)
├── resources/views/
│   ├── layouts/ (app.blade.php, admin.blade.php)
│   ├── welcome.blade.php             ← Landing page
│   ├── courses/ (index, show, guest-access)
│   ├── lessons/ (show avec lecteur + chat + commentaires)
│   ├── helpdesk/ (index, show)
│   ├── enrollments/ (index)
│   ├── admin/ (dashboard, courses, enrollments, users)
│   └── auth/ (login, register)
├── routes/ (web.php, api.php)
├── database/migrations/
├── public/images/logo.png
└── .env (config BD + DeepSeek API key)
</pre></div>';

echo '<p style="text-align:center;margin-top:30px;color:#999">Eclosion+ LMS — Documentation générée le ' .
    date("d/m/Y") .
    "</p>";
echo "</body></html>";
