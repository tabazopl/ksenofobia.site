<?php
// Połączenie z bazą danych MariaDB
$host = 'localhost';
$dbname = 'basename';
$username = 'username';
$password = 'basepassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obsługa dodawania nowego komentarza
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        if (!empty($comment) && strlen($comment) <= 128) {
            $stmt = $pdo->prepare("INSERT INTO comments (content, created_at) VALUES (?, NOW())");
            $stmt->execute([$comment]);
            header("Location: index.php");
            exit;
        }
    }

    // Pobranie 5 ostatnich komentarzy
    $stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 5");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sprawdzenie, czy jest więcej komentarzy do rozwinięcia
    $totalComments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $hasMore = $totalComments > 5;

    foreach ($comments as $row) {
        echo '<div class="comment">';
        echo '<img src="" alt="Profile Pic">'; // Zmień na własne zdjęcie
        echo '<p>' . htmlspecialchars($row['content']) . '</p>';
        echo '</div>';
    }

    // Ustawienie widoczności przycisku "Rozwiń więcej"
    if ($hasMore) {
        echo '<script>document.getElementById("load-more").style.display = "block";</script>';
    }
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>