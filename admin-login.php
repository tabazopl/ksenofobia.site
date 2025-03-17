<?php
session_start();

// Dane do połączenia z bazą danych
$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Obsługa logowania
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin-panel.php");
            exit;
        } else {
            $error = "Nieprawidłowy login lub hasło.";
        }
    } else {
        $error = "Proszę wypełnić wszystkie pola.";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .top-menu {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error {
            color: red;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        @media (max-width: 600px) {
            .buttons {
                flex-direction: column;
                align-items: center;
            }
            .btn {
                width: 80%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="top-menu">
        <h1>Ksenofobia.site</h1>
        <div class="buttons">
            <a href="index.php" class="btn">Strona główna</a>
            <a href="dokumentacja.php" class="btn">Dokumentacja</a>
        </div>
    </header>

    <div class="container">
        <h2>Logowanie Admina</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Zaloguj się</button>
        </form>
    </div>
</body>
</html>