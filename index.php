<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: files.php');
    exit();
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    if ($user && $pass && file_exists("users/$user.txt")) {
        $hash = trim(file_get_contents("users/$user.txt"));
        if (password_verify($pass, $hash)) {
            $_SESSION['username'] = $user;
            header('Location: files.php');
            exit();
        } else {
            $err = 'Parolă incorectă!';
        }
    } else {
        $err = 'Utilizator inexistent!';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - Partajare fișiere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Autentificare</h4>
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
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                    <hr>
                    <a href="register.php" class="btn btn-link w-100">Nu ai cont? Înregistrează-te</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
