<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
$user = $_SESSION['username'];
$err = '';
$success = '';
// ...existing code...
$blocked_ext = [
    'php','php3','php4','php5','phtml',
    'asp','aspx','jsp','js','py','pl','cgi',
    'sh','bat','exe','com','wsf','vbs','rb'
];
$max_size = 5 * 1024 * 1024; // 5MB
$user_dir = "public/";
if (!is_dir($user_dir)) mkdir($user_dir);
// Upload multiple files
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $files = $_FILES['file'];
    $upload_count = count($files['name']);
    $upload_errors = [];
    $upload_success = [];
    for ($i = 0; $i < $upload_count; $i++) {
        $name = $files['name'][$i];
        $tmp = $files['tmp_name'][$i];
        $size = $files['size'][$i];
        $error = $files['error'][$i];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($error !== UPLOAD_ERR_OK) {
            $upload_errors[] = "$name: Eroare la upload.";
        } elseif (in_array($ext, $blocked_ext)) {
            $upload_errors[] = "$name: Extensie nepermisă!";
        } elseif ($size > $max_size) {
            $upload_errors[] = "$name: Fișier prea mare (max 5MB)!";
        } else {
            $base_name = pathinfo($name, PATHINFO_FILENAME);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $timestamp = date('Ymd_His');
            $dest_name = $user . "_" . $base_name . "_" . $timestamp . ($ext ? "." . $ext : "");
            $dest = "$user_dir/" . basename($dest_name);
            if (move_uploaded_file($tmp, $dest)) {
                $upload_success[] = "$name";
                // Salvează în files.json
                $files_json_path = __DIR__ . '/files.json';
                $files_db = file_exists($files_json_path) ? json_decode(file_get_contents($files_json_path), true) : [];
                $files_db[] = [
                    'user' => $user,
                    'original_name' => $name,
                    'saved_name' => basename($dest),
                    'size' => $size,
                    'timestamp' => $timestamp
                ];
                file_put_contents($files_json_path, json_encode($files_db, JSON_PRETTY_PRINT));
            } else {
                $upload_errors[] = "$name: Nu s-a putut salva fișierul.";
            }
        }
    }
    if ($upload_success) {
        $success = 'Fișiere încărcate: ' . implode(', ', $upload_success);
    }
    if ($upload_errors) {
        $err = implode('<br>', $upload_errors);
    }
}
// Delete
if (isset($_GET['del'])) {
    $file = basename($_GET['del']);
    $path = "$user_dir/$file";
    if (is_file($path)) {
        unlink($path);
        $success = 'Fișier șters!';
    }
}
// Download
if (isset($_GET['down'])) {
    $file = basename($_GET['down']);
    $path = "$user_dir/$file";
    if (is_file($path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit();
    }
}
$all_files = array_diff(scandir($user_dir), ['.','..']);
$files = [];
foreach ($all_files as $f) {
    if (strpos($f, $user . '_') === 0) {
        $files[] = $f;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Fișierele mele</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Fișierele mele</h3>
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
    <?php if ($err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="mb-4">
        <div class="input-group">
            <input type="file" name="file[]" class="form-control" required multiple>
            <button class="btn btn-primary" type="submit">Încarcă</button>
        </div>
        <div class="form-text">Extensii permise: orice tip, inafara de scripturi (.py, .js, .sh, .bat) s.a. Max 5MB per fișier. Poți selecta mai multe fișiere.</div>
    </form>
    <table class="table table-bordered bg-white">
        <thead><tr><th>Fișier</th><th>Mărime</th><th>Acțiuni</th></tr></thead>
        <tbody>
        <?php foreach ($files as $f): ?>
            <?php
            // Extrage numele original: user_filename_timestamp.ext
            $pattern = '/^' . preg_quote($user, '/') . '_(.+)_\d{8}_\d{6}(\.[^.]+)?$/';
            if (preg_match($pattern, $f, $matches)) {
                $original_name = $matches[1] . (isset($matches[2]) ? $matches[2] : '');
            } else {
                $original_name = $f;
            }
            ?>
            <tr>
                <td><?= htmlspecialchars($original_name) ?></td>
                <td><?= round(filesize("$user_dir/$f")/1024,2) ?> KB</td>
                <td>
                    <a href="?down=<?= urlencode($f) ?>" class="btn btn-sm btn-success">Descarcă</a>
                    <a href="?del=<?= urlencode($f) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ștergi fișierul?')">Șterge</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>