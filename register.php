<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: files.php');
    exit();
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $pass2 = $_POST['password2'];
    if (!$user || !$pass || !$pass2) {
        $err = 'Completează toate câmpurile!';
    } elseif ($pass !== $pass2) {
        $err = 'Parolele nu coincid!';
    } else {
        $users_file = __DIR__ . '/users.json';
        $users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
        if (isset($users[$user])) {
            $err = 'Utilizator deja existent!';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $users[$user] = $hash;
            file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
            $_SESSION['username'] = $user;
            header('Location: files.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Partajare fișiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Înregistrare</h4>
                    <?php if ($err): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Utilizator</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parolă</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmă parola</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100">Înregistrare</button>
                    </form>
                    <hr>
                    <a href="index.php" class="btn btn-link w-100">Ai deja cont? Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
