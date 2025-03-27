<?php
session_start();

// Połączenie z bazą danych linków
$host = 'host';
$dbname = 'database';
$username = 'login';
$password = 'password';


try {
    $pdo_links = new PDO("mysql:host=$host_links;dbname=$dbname_links", $username_links, $password_links);
    $pdo_links->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obsługa przesyłania linku
    $error_message = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['youtube_link'])) {
        $youtube_link = trim($_POST['youtube_link']);

        // Sprawdzenie długości linku (max 44 znaki)
        if (strlen($youtube_link) > 44) {
            $error_message = 'Link jest za długi! Maksymalnie 44 znaki.';
        } else {
            // Sprawdzenie formatu linku
            $pattern = '/^https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]{1,12})$/';
            if (preg_match($pattern, $youtube_link, $matches)) {
                // Link poprawny, zapis do bazy danych
                $stmt = $pdo_links->prepare("INSERT INTO links (option_name, link) VALUES (?, ?)");
                $stmt->execute(['Option 5', $youtube_link]);
                // Przekierowanie, aby uniknąć ponownego wysyłania formularza
                header("Location: opcja2.php?success=1");
                exit;
            } else {
                $error_message = 'Zły format linku! Tylko https://www.youtube.com/watch?v=xxxxxxxxxxxx jest akceptowalne.';
            }
        }
    }

} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propozycje - Ksenofobia.site</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .top-menu {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .menu-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 16px;
        }
        .menu-button:hover {
            background-color: #0056b3;
        }
        .top-menu .buttons {
            display: flex;
            gap: 10px;
        }
        .top-menu .buttons a {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .top-menu .buttons a:hover {
            background-color: #45a049;
        }
        .container {
            background-color: #5a6268;
            border-radius: 10px;
            padding: 20px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .proposal-box {
            background-color: #343a40;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }
        .proposal-box h2 {
            margin: 0 0 10px;
            font-size: 24px;
        }
        .proposal-box p {
            margin: 0;
            font-size: 16px;
        }
        .input-box {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .input-box input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .input-box .char-counter {
            text-align: right;
            font-size: 12px;
            color: #6c757d;
        }
        .input-box .error-message {
            color: red;
            font-size: 14px;
            text-align: center;
        }
        .submit-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            display: block;
            width: 100%;
        }
        .submit-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header class="top-menu">
        <div>
            <button class="menu-button" onclick="window.location.href='index.php'">
                <span>☰</span> Ksenofobia.site
            </button>
        </div>
        <div class="buttons">
            <a href="dokumentacja.php">Dokumentacja</a>
            <a href="admin-panel.php">Admin Panel</a>
        </div>
    </header>

    <div class="container">
        <div class="proposal-box">
            <h2>Propozycje</h2>
            <p>Tutaj możesz wysłać propozycje linków do opcji 5<br>które mogą pojawić się jako forma losowego linku<br>oczywiście po weryfikacji linku :DD</p>
        </div>
        <form method="POST" action="">
            <div class="input-box">
                <input type="text" name="youtube_link" id="youtubeLink" placeholder="Tyko link z youtuba!!!" maxlength="44" oninput="updateCharCounter()">
                <div class="char-counter" id="charCounter">0/44</div>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="submit-button">Prześlij</button>
        </form>
    </div>

    <script>
        function updateCharCounter() {
            const input = document.getElementById('youtubeLink');
            const charCounter = document.getElementById('charCounter');
            const charCount = input.value.length;
            charCounter.textContent = `${charCount}/44`;
        }
    </script>
</body>
</html>