<?php
session_start(); // Start the session

// Funkcja do generowania i zarządzania user ID
function getUserId() {
    // Sprawdź, czy user_id i timestamp istnieją w sesji
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_id_timestamp'])) {
        // Utwórz unikalny ID na podstawie session ID i losowej liczby
        $sessionUniquePart = substr(md5(session_id()), 0, 5); // Pierwsze 5 znaków z MD5 session ID
        $randomPart = rand(10000, 99999); // Losowa 5-cyfrowa liczba
        $_SESSION['user_id'] = 'ksenofob' . $sessionUniquePart . $randomPart;
        $_SESSION['user_id_timestamp'] = time();
    } else {
        // Sprawdź, czy minęło 15 minut (900 sekund)
        $currentTime = time();
        $lastGenerated = $_SESSION['user_id_timestamp'];
        if (($currentTime - $lastGenerated) >= 900) {
            // Minęło 15 minut, wygeneruj nowy ID
            $sessionUniquePart = substr(md5(session_id()), 0, 5);
            $randomPart = rand(10000, 99999);
            $_SESSION['user_id'] = 'ksenofob' . $sessionUniquePart . $randomPart;
            $_SESSION['user_id_timestamp'] = $currentTime;
        }
    }
    return $_SESSION['user_id'];
}

// Połączenie z bazą danych MariaDB
$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobierz user ID
    $userId = getUserId();

    // Pobranie wszystkich komentarzy poza pierwszymi 5
    $stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 18446744073709551615 OFFSET 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="comment">';
        // Temporarily remove image to avoid network error
        // echo '<img src="https://via.placeholder.com/50" alt="Profile Pic">';
        $displayUsername = ($row['username'] === 'ADMIN') ? '<strong class="admin-username">ADMIN</strong>' : '<strong>' . htmlspecialchars($userId) . '</strong>';
        echo '<p>' . $displayUsername . ': ' . htmlspecialchars($row['content']);
        if ($row['liked'] == 1) {
            echo ' <span style="color: red;">♥</span>'; // Serduszko dla polubionych komentarzy
        }
        echo '</p>';
        // Dodaj datę i godzinę w formacie "godzina:minuta dzień-miesiąc-rok" z czasem polskim
        $dateTime = new DateTime($row['created_at'], new DateTimeZone('Europe/Warsaw'));
        $formattedDate = $dateTime->format('H:i d-m-Y');
        echo '<small style="display:block; margin-left: 10px;">Wstawiono: ' . htmlspecialchars($formattedDate) . '</small>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?>