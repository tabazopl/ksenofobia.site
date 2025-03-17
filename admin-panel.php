<?php
session_start();

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Dane do połączenia z bazą danych komentarzy
$host_comments = 'localhost';
$dbname_comments = 'urdatabase';
$username_comments = 'username';
$password_comments = 'password';

// Dane do połączenia z bazą danych login (dla wylogowania)
$host_login = 'localhost';
$dbname_login = 'DatabaseName';
$username_login = 'login';
$password_login = 'Databasepassword';

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

try {
    $pdo_comments = new PDO("mysql:host=$host_comments;dbname=$dbname_comments", $username_comments, $password_comments);
    $pdo_comments->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obsługa wylogowania
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: admin-login.php");
        exit;
    }

    // Obsługa dodawania komentarza przez admina
    if (isset($_POST['comment']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $comment = trim($_POST['comment']);
        if (!empty($comment) && strlen($comment) <= 128) {
            $stmt = $pdo_comments->prepare("INSERT INTO comments (content, username, created_at) VALUES (?, 'ADMIN', NOW())");
            $stmt->execute([$comment]);
        }
    }

    // Obsługa wyszukiwania
    $search_query = '';
    $search_condition = '';
    if (isset($_POST['search'])) {
        $search_query = trim($_POST['search_query']);
        if (!empty($search_query)) {
            $search_condition = "WHERE content LIKE ?";
            $search_param = "%" . $search_query . "%";
        }
    }

    // Obsługa "Polub zaznaczone komentarze"
    if (isset($_POST['like_comments']) && isset($_POST['selected_comments'])) {
        $selected_comments = $_POST['selected_comments'];
        $stmt = $pdo_comments->prepare("UPDATE comments SET liked = 1 WHERE id = ?");
        foreach ($selected_comments as $comment_id) {
            $stmt->execute([$comment_id]);
        }
        header("Location: admin-panel.php" . (!empty($search_query) ? '?search_query=' . urlencode($search_query) : ''));
        exit;
    }

    // Obsługa "Usuń zaznaczone komentarze"
    if (isset($_POST['delete_comments']) && isset($_POST['selected_comments'])) {
        $selected_comments = $_POST['selected_comments'];
        $stmt = $pdo_comments->prepare("DELETE FROM comments WHERE id = ?");
        foreach ($selected_comments as $comment_id) {
            $stmt->execute([$comment_id]);
        }
        header("Location: admin-panel.php" . (!empty($search_query) ? '?search_query=' . urlencode($search_query) : ''));
        exit;
    }

    // Pobierz wszystkie komentarze
    $sql = "SELECT * FROM comments $search_condition ORDER BY " . (!empty($search_query) ? "content ASC" : "created_at DESC");
    $stmt = $pdo_comments->prepare($sql);
    if (!empty($search_query)) {
        $stmt->execute([$search_param]);
    } else {
        $stmt->execute();
    }
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ksenofobia.site - Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-username {
            color: #FFD700;
        }
        .highlight {
            background-color: yellow;
        }
        .container {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: flex-start;
        }
        .buttons-column {
            width: 200px;
            padding: 20px;
            background-color: #6c757d;
            color: white;
            border-radius: 10px;
            margin-right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .buttons-column button,
        .buttons-column a {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .content-box {
            flex-grow: 1;
            background-color: #6c757d;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .comments-section-admin .comment {
            margin-bottom: 15px;
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
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
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
        <h1>Ksenofobia.site/admin</h1>
        <div class="buttons">
            <form method="POST" style="display: inline;">
                <button type="submit" name="logout" class="btn">Strona główna</button>
            </form>
        </div>
    </header>

    <div class="container">
        <div class="buttons-column">
            <button type="submit" name="like_comments" form="commentsForm" style="background-color: green; color: white;">Polub zaznaczone komentarze</button>
            <button type="submit" name="delete_comments" form="commentsForm" style="background-color: red; color: white;">Usuń zaznaczone komentarze</button>
            <a href="admin-panel.php?show_checkboxes=1<?php echo !empty($search_query) ? '&search_query=' . urlencode($search_query) : ''; ?>" style="background-color: blue; color: white;">Checkboxy</a>
        </div>

        <div class="content-box">
            <h2>Komentarz ADMINA</h2>
            <form method="POST" action="" class="comment-input">
                <textarea name="comment" maxlength="128" placeholder="Treść komentarza wyświetlana na index.php"></textarea>
                <button type="submit" style="background-color: #FFD700; color: black;">Dodaj</button>
            </form>
            <p style="color: #FFD700;">Nazwa użytkownika: <span class="admin-username">ADMIN</span> (żółty kolor)</p>

            <form method="POST" style="margin-top: 20px;">
                <input type="text" name="search_query" placeholder="Wyszukiwarka komentarzy (zaznacza komentarz z danym słowem)" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" name="search">Szukaj</button>
            </form>

            <form method="POST" id="commentsForm" style="margin-top: 20px;">
                <div class="comments-section-admin">
                    <?php foreach ($comments as $row): ?>
                        <div class="comment">
                            <?php if (isset($_GET['show_checkboxes']) && $_GET['show_checkboxes'] == '1'): ?>
                                <input type="checkbox" name="selected_comments[]" value="<?php echo $row['id']; ?>">
                            <?php endif; ?>
                            <p>Komentarz z bazy danych używanego na index.php/comment.php: <strong class="<?php echo ($row['username'] === 'ADMIN') ? 'admin-username' : ''; ?>"><?php echo htmlspecialchars($row['username']); ?>: 
                                <span class="comment-content"><?php echo parseCommentContent($row['content']); ?></span></strong></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchQuery = "<?php echo addslashes(htmlspecialchars($search_query)); ?>";
            if (searchQuery) {
                document.querySelectorAll('.comment-content').forEach(content => {
                    const regex = new RegExp(`(${searchQuery})`, 'gi');
                    content.innerHTML = content.textContent.replace(regex, '<span class="highlight">$1</span>');
                });
            }
        });
    </script>
</body>
</html>