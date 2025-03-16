<?php
session_start();

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Dane do połączenia z bazą danych komentarzy
$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';

// Dane do połączenia z bazą danych login (dla wylogowania)
$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';

try {
    $pdo_comments = new PDO("mysql:host=$host_comments;dbname=$dbname_comments", $username_comments, $password_comments);
    $pdo_comments->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obsługa wylogowania
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: admin-login.php");
        exit;
    }

    // Obsługa dodawania komentarza przez admina (pozostajemy na panelu)
    if (isset($_POST['comment']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $comment = trim($_POST['comment']);
        if (!empty($comment) && strlen($comment) <= 128) {
            $stmt = $pdo_comments->prepare("INSERT INTO comments (content, username, created_at) VALUES (?, 'ADMIN', NOW())");
            $stmt->execute([$comment]);
            // Nie przekierowujemy, pozostajemy na panelu
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

    // Obsługa "Polub zaznaczone komentarze" (dodaj serduszko)
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

    // Pobierz wszystkie komentarze (bez limitu)
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
            color: #FFD700; /* Złoty kolor dla "ADMIN" */
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
    </style>
</head>
<body>
    <header class="top-menu">
        <h1>Ksenofobia.site/admin</h1>
        <form method="POST" style="display: inline;">
            <button type="submit" name="logout" class="btn">Wyloguj się</button>
        </form>
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
                                <span class="comment-content"><?php echo htmlspecialchars($row['content']); ?></span></strong></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Highlight search term
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