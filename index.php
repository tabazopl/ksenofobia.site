<?php
session_start();

// Funkcja do generowania user_id
function getUserId() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_id_timestamp'])) {
        $sessionUniquePart = substr(md5(session_id() . microtime()), 0, 5);
        $randomPart = rand(10000, 99999);
        $_SESSION['user_id'] = 'ksenofob' . $sessionUniquePart . $randomPart;
        $_SESSION['user_id_timestamp'] = time();
    } else {
        $currentTime = time();
        $lastGenerated = $_SESSION['user_id_timestamp'];
        if (($currentTime - $lastGenerated) >= 900) {
            $sessionUniquePart = substr(md5(session_id() . microtime()), 0, 5);
            $randomPart = rand(10000, 99999);
            $_SESSION['user_id'] = 'ksenofob' . $sessionUniquePart . $randomPart;
            $_SESSION['user_id_timestamp'] = $currentTime;
        }
    }
    return $_SESSION['user_id'];
}

// Funkcja do walidacji i parsowania linków do obrazów
function parseCommentContent($content) {
    $pattern = '/<link href="([^"]+)">/';
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    preg_match_all($pattern, $content, $matches);

    if (!empty($matches[1])) {
        foreach ($matches[1] as $index => $url) {
            $errorMessage = '';
            $isValid = false;

            if (strpos($url, '/src/online') === 0) {
                $isValid = true;
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $url;
                if (!file_exists($fullPath) || !is_readable($fullPath)) {
                    $errorMessage = '[Błąd: Plik nie istnieje lub nie można go odczytać]';
                    $isValid = false;
                }
            } elseif (preg_match('/^https?:\/\//', $url)) {
                $isValid = true;
                $imageInfo = @getimagesize($url);
                if ($imageInfo === false) {
                    $errorMessage = '[Błąd: Nieprawidłowy obraz lub niedostępny URL]';
                    $isValid = false;
                }
            } else {
                $errorMessage = '[Błąd: Niedozwolony format linku]';
            }

            if ($isValid) {
                $imgTag = '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="Comment Image" style="max-width: 128px; max-height: 128px; object-fit: contain; vertical-align: middle;">';
                $content = str_replace($matches[0][$index], $imgTag, $content);
            } else {
                $content = str_replace($matches[0][$index], $errorMessage, $content);
            }
        }
    }
    return $content;
}

// Połączenie z bazą danych komentarzy
$host = 'host';
$dbname = 'database';
$username = 'login';
$password = 'password';     // am to lazy add yourself _comments after host for example 


// Połączenie z bazą danych linków (tylko dla Opcji 5)
$host = 'host';
$dbname = 'database';
$username = 'login';
$password = 'password'; /// same  but add _links


try {
    // Połączenie z bazą komentarzy
    $pdo_comments = new PDO("mysql:host=$host_comments;dbname=$dbname_comments", $username_comments, $password_comments);
    $pdo_comments->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Połączenie z bazą linków (dla Opcji 5)
    $pdo_links = new PDO("mysql:host=$host_links;dbname=$dbname_links", $username_links, $password_links);
    $pdo_links->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Funkcja do pobierania losowego linku dla Opcji 5
    function getLinkForOption5($pdo) {
        $stmt = $pdo->prepare("SELECT link FROM links WHERE option_name = 'Option 5' ORDER BY RAND() LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['link'] : 'https://example.com/fallback';
    }

    // Linki dla opcji 1-4 są teraz hardcoded w kodzie
    $links = [
        'Option 1' => 'https://github.com/tabazopl/ksenofobia.site', // Tutaj link: Zmień na inny URL dla GitHub (Opcja 1)
        'Option 2' => 'http://ksenofobia.site/opcja2.php', // Tutaj link: Zmień na inny URL dla Opcji 2
        'Option 3' => 'http://ksenofobia.site/opcja3.php', // Tutaj link: Zmień na inny URL dla Opcji 3
        'Option 4' => 'http://ksenofobia.site/opcja4.php', // Tutaj link: Zmień na inny URL dla Opcji 4
        'Option 5' => getLinkForOption5($pdo_links) // Link dla Opcji 5 pobierany z bazy danych
    ];

    // Stałe przypisanie logo do opcji (ścieżka ustawiona na /src/online)
    $optionLogos = [
        'Option 1' => '/src/online/logo1.png', // Ścieżka do logo dla GitHub (Opcja 1)
        'Option 2' => '/src/online/logo2.png', // Ścieżka do logo dla Opcji 2
        'Option 3' => '/src/online/logo3.png', // Ścieżka do logo dla Opcji 3
        'Option 4' => '/src/online/logo4.png', // Ścieżka do logo dla Opcji 4
        'Option 5' => '/src/online/logo5.png'  // Ścieżka do logo dla Opcji 5
    ];

    // Obsługa dodawania komentarza
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        if (!empty($comment) && strlen($comment) <= 128) {
            $stmt = $pdo_comments->prepare("INSERT INTO comments (content, username, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$comment, getUserId()]);
            // Przekierowanie, aby uniknąć ponownego wysyłania formularza
            header("Location: index.php");
            exit;
        }
    }

    // Pobierz komentarze
    $userId = getUserId();
    $stmt = $pdo_comments->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 5");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Ksenofobia.site</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .top-menu {
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
        .dropdown-menu {
            position: absolute;
            top: 50px;
            left: 20px;
            background-color: #007bff;
            color: white;
            width: 200px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
        .dropdown-menu.show {
            display: block;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
            color: white;
        }
        .menu-item:hover {
            background-color: #0056b3;
        }
        .menu-item img {
            width: 32px; /* Skalowanie logo do 32px szerokości */
            height: 32px; /* Skalowanie logo do 32px wysokości */
            object-fit: contain; /* Zachowanie proporcji obrazu */
            margin-right: 10px;
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
        .content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .content h2 {
            margin-top: 0;
        }
        .comment-input {
            background-color: #6c757d;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .comment-input textarea {
            width: 100%;
            height: 60px;
            resize: none;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .comment-input button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .comment-input button:hover {
            background-color: #0056b3;
        }
        .comment {
            margin-bottom: 15px;
        }
        .comment p {
            margin: 0;
            color: white;
        }
        .comment strong {
            color: #007bff; /* Domyślny kolor dla "ksenofob" - niebieski */
        }
        .comment .admin-username {
            color: #FFD700; /* Kolor dla "ADMIN" - złoty */
        }
        .comment small {
            display: block;
            margin-left: 10px;
            color: #ccc;
        }
        #loadMore {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        #loadMore:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header class="top-menu">
        <div>
            <button class="menu-button" id="menuButton">
                <span>☰</span> Ksenofobia.site
            </button>
        </div>
        <div class="buttons">
            <a href="dokumentacja.php">Dokumentacja</a>
            <a href="admin-panel.php">Admin Panel</a>
        </div>
    </header>

    <div class="dropdown-menu" id="dropdownMenu">
        <!-- Możesz zmienić nazwy opcji i linki poniżej -->
        <a href="<?php echo htmlspecialchars($links['Option 1']); ?>" class="menu-item">
            <img src="<?php echo htmlspecialchars($optionLogos['Option 1']); ?>" alt="GitHub Logo">
            GitHub <!-- Tutaj nazwa: Zmieniono 'Opcja 1' na 'GitHub' -->
        </a>
        <a href="<?php echo htmlspecialchars($links['Option 2']); ?>" class="menu-item">
            <img src="<?php echo htmlspecialchars($optionLogos['Option 2']); ?>" alt="Option 2 Logo">
            Propozycje <!-- Tutaj nazwa: Zmień 'Opcja 2' na inną nazwę, jeśli potrzebujesz -->
        </a>
        <a href="<?php echo htmlspecialchars($links['Option 3']); ?>" class="menu-item">
            <img src="<?php echo htmlspecialchars($optionLogos['Option 3']); ?>" alt="Option 3 Logo">
            Niedostępne <!-- Tutaj nazwa: Zmień 'Opcja 3' na inną nazwę, jeśli potrzebujesz -->
        </a>
        <a href="<?php echo htmlspecialchars($links['Option 4']); ?>" class="menu-item">
            <img src="<?php echo htmlspecialchars($optionLogos['Option 4']); ?>" alt="Option 4 Logo">
            Niedostępne <!-- Tutaj nazwa: Zmień 'Opcja 4' na inną nazwę, jeśli potrzebujesz -->
        </a>
        <a href="<?php echo htmlspecialchars($links['Option 5']); ?>" class="menu-item">
            <img src="<?php echo htmlspecialchars($optionLogos['Option 5']); ?>" alt="Option 5 Logo">
            Losowy Link <!-- Tutaj nazwa: Zmień 'Opcja 5' na inną nazwę, jeśli potrzebujesz -->
        </a>
    </div>

    <div class="content">
        <h2>Komentarze</h2>
        <form method="POST" action="" class="comment-input">
            <textarea name="comment" maxlength="128" placeholder="Dodaj komentarz (max 128 znaków)"></textarea>
            <button type="submit">Wyślij</button>
        </form>

        <div id="comments">
            <?php
            foreach ($comments as $row) {
                echo '<div class="comment">';
                $displayUsername = ($row['username'] === 'ADMIN') ? '<strong class="admin-username">ADMIN</strong>' : '<strong>' . htmlspecialchars($userId) . '</strong>';
                $parsedContent = parseCommentContent($row['content']);
                echo '<p>' . $displayUsername . ': ' . $parsedContent;
                if ($row['liked'] == 1) {
                    echo ' <img src="skull_heart.png" alt="Liked" style="width: 20px; height: 20px; vertical-align: middle;">';
                }
                echo '</p>';
                $dateTime = new DateTime($row['created_at'], new DateTimeZone('Europe/Warsaw'));
                $dateTime->modify('+1 hour');
                $formattedDate = $dateTime->format('H:i d-m-Y');
                echo '<small>Wstawiono: ' . htmlspecialchars($formattedDate) . '</small>';
                echo '</div>';
            }
            ?>
        </div>

        <button id="loadMore">Załaduj więcej</button>
    </div>

    <script>
        const menuButton = document.getElementById('menuButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        let isMenuPinned = false;

        // Funkcja pokazująca/ukrywająca menu
        function toggleMenu(show) {
            if (show || isMenuPinned) {
                dropdownMenu.classList.add('show');
            } else {
                dropdownMenu.classList.remove('show');
            }
        }

        // Pokazuj menu przy najechaniu na przycisk
        menuButton.addEventListener('mouseenter', () => {
            toggleMenu(true);
        });

        // Ukrywaj menu, gdy kursor opuszcza menu i przycisk (jeśli nie jest przypięte)
        dropdownMenu.addEventListener('mouseleave', () => {
            if (!isMenuPinned && !menuButton.matches(':hover')) {
                toggleMenu(false);
            }
        });

        menuButton.addEventListener('mouseleave', () => {
            if (!isMenuPinned && !dropdownMenu.matches(':hover')) {
                toggleMenu(false);
            }
        });

        // Przypinaj menu po kliknięciu
        menuButton.addEventListener('click', () => {
            isMenuPinned = !isMenuPinned;
            toggleMenu(isMenuPinned);
        });

        // Załaduj więcej komentarzy
        document.getElementById('loadMore').addEventListener('click', () => {
            fetch('load_more_comments.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('comments').innerHTML += data;
                })
                .catch(error => console.error('Błąd podczas ładowania komentarzy:', error));
        });
    </script>
</body>
</html>