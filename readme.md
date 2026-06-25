# 🌱 Eclosion+ — Plateforme de Formation en Ligne

LMS complet avec IA, paiement Wave, liens partagés, et administration.

---

## 🚀 Démarrage rapide

```bash
# 1. Démarrer MySQL
C:\xampp\mysql\bin\mysqld --defaults-file=C:\xampp\mysql\bin\my.ini

# 2. Démarrer le serveur Laravel
php artisan serve --port=8080

# 3. Ouvrir dans le navigateur
http://127.0.0.1:8080
```

---

## 🔑 Comptes

| Rôle | Email | Accès |
|------|-------|-------|
| **Admin** | Créé lors de l'installation | Voir .env |

---

## 🗄️ Base de données (12 tables)

| Table | Description |
|-------|------------|
| `users` | Comptes (student, instructor, admin) |
| `courses` | Formations |
| `categories` | Catégories (Marketing, E-commerce, etc.) |
| `levels` | Niveaux (Débutant, Intermédiaire, Avancé) |
| `lessons` | Leçons (vidéo, audio, PDF) |
| `enrollments` | Inscriptions + paiement Wave |
| `progress` | Progression par leçon |
| `reviews` | Avis 1-5 étoiles |
| `comments` | Questions avec réponse IA automatique |
| `share_links` | Liens d'accès direct (guest) |
| `ai_conversations` | Conversations IA |
| `ai_messages` | Messages IA |

---

## ⚡ Fonctionnalités

### 🎓 Apprenant
- Catalogue avec **recherche** et **catégories**
- **Avis/étoiles** sur les formations
- **Paiement Wave** (Mobile Money FCFA)
- **Lecteur** vidéo/audio/PDF
- **Chat IA contextuel** par leçon
- **Commentaires** avec réponse IA automatique
- **Progression** suivie leçon par leçon
- **Plan du cours** dans la sidebar

### 🛡️ Admin
- **Dashboard** avec stats, revenus, top formations
- **CRUD Formations** avec uploads médias
- **Gestion utilisateurs** (changement de rôle)
- **Validation demandes** (approuver/refuser)
- **Liens partagés** (accès invité sans compte)
- **Illustrations** par leçon, images/audio par niveau

### 🔗 Liens partagés
- Générer un lien pour chaque formation
- Expiration optionnelle (30j, 90j, illimité)
- Accès **sans compte** ni connexion

---

## 🤖 IA (DeepSeek)

| Paramètre | Valeur |
|-----------|--------|
| Modèle | `deepseek-chat` |
| Endpoint | `https://api.deepseek.com/v1/chat/completions` |
| Contexte | Titre formation + niveau + contenu leçon |

---

## 📁 Structure

```
app/Http/Controllers/  →  Controllers (CRUD, Auth, IA, Wave)
app/Models/            →  Models Eloquent
app/Services/          →  AiHelpdeskService (curl natif)
resources/views/       →  Vues Blade (layouts, courses, lessons, admin)
routes/web.php         →  Routes (publiques + auth + admin)
public/images/logo.png →  Logo
```

---

## 🖥️ Commandes

```bash
php artisan serve --port=8080     # Démarrer
php artisan route:list            # Voir routes
php artisan tinker                # Console interactive
```

---

Documentation complète : http://127.0.0.1:8080/project-doc.php
