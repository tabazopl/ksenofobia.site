<?php
$host = 'localhost';
$dbname = 'basename';
$username = 'user';
$password = 'basepasword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobranie pozostałych komentarzy (poza pierwszymi 5)
    $stmt = $pdo->query("SELECT * FROM (SELECT * FROM comments ORDER BY created_at DESC LIMIT 18446744073709551615 OFFSET 5) AS subquery");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="comment">';
        echo '<img src="https://via.placeholder.com/50" alt="Profile Pic">';
        echo '<p>' . htmlspecialchars($row['content']) . '</p>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?>