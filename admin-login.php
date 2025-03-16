<?php
session_start();

// Sprawdź, czy użytkownik jest już zalogowany
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin-panel.php");
    exit;
}

// Dane do połączenia z bazą danych login
$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$input_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $input_password) {
            // Poprawne dane logowania
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $input_username;
            header("Location: admin-panel.php");
            exit;
        } else {
            $error = "Błędne hasło lub login";
        }
    } catch (PDOException $e) {
        $error = "Błąd połączenia z bazą danych: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ksenofobia.site - Logowanie Admina</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="comment-input">
            <h3>Logowanie do panelu admina</h3>
            <?php if ($error): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="username">Login:</label>
                <input type="text" name="username" id="username" required><br><br>
                <label for="password">Hasło:</label>
                <input type="password" name="password" id="password" required><br><br>
                <button type="submit">Zaloguj się</button>
            </form>
        </div>
    </div>
</body>
</html>