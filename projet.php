<?php
// ----- Configuration de la base de données -----
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');

// Connexion
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ----- Traitement des formulaires -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;

    if ($action === 'new' && !empty($_POST['title'])) {
        $stmt = $pdo->prepare("INSERT INTO todo (title) VALUES (?)");
        $stmt->execute([$_POST['title']]);
    } elseif ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM todo WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'toggle' && $id) {
        $stmt = $pdo->prepare("UPDATE todo SET done = 1 - done WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Redirection après action pour éviter le re-envoi du formulaire
    header("Location: projet.php");
    exit;
}

// ----- Récupération des tâches -----
$stmt = $pdo->query("SELECT * FROM todo ORDER BY created_at DESC");
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Ma Todo App</a>
    </div>
</nav>

<!-- Contenu -->
<div class="container mt-5">
    <h2>Ajouter une nouvelle tâche</h2>
    <form method="POST" class="mb-4">
        <input type="hidden" name="action" value="new">
        <div class="input-group">
            <input type="text" name="title" class="form-control" placeholder="Ex. Réviser PHP" required>
            <button class="btn btn-primary" type="submit">Ajouter</button>
        </div>
    </form>

    <h3>Liste des tâches</h3>
    <ul class="list-group">
        <?php foreach ($taches as $tache): ?>
            <li class="list-group-item <?= $tache['done'] ? 'list-group-item-success' : 'list-group-item-warning' ?>">
                <?= htmlspecialchars($tache['title']) ?>
                <form method="POST" class="d-inline float-end ms-2">
                    <input type="hidden" name="id" value="<?= $tache['id'] ?>">
                    <button class="btn btn-sm btn-secondary" name="action" value="toggle">✓</button>
                    <button class="btn btn-sm btn-danger" name="action" value="delete">🗑️</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
