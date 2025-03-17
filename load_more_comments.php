<?php
session_start(); // Start the session

function getUserId() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_id_timestamp'])) {
        $sessionUniquePart = substr(md5(session_id() . microtime()), 0, 5);
        $randomPart = rand(10000, 99999);
        $_SESSION['user_id'] = 'ksenofob-' . $sessionUniquePart . $randomPart;
        $_SESSION['user_id_timestamp'] = time();
    } else {
        $currentTime = time();
        $lastGenerated = $_SESSION['user_id_timestamp'];
        if (($currentTime - $lastGenerated) >= 900) {
            $sessionUniquePart = substr(md5(session_id() . microtime()), 0, 5);
            $randomPart = rand(10000, 99999);
            $_SESSION['user_id'] = 'ksenofob-' . $sessionUniquePart . $randomPart;
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

$host = 'localhost';
$dbname = 'DatabaseName';
$username = 'login';
$password = 'Databasepassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = getUserId();

    $stmt = $pdo->query("SELECT * FROM comments ORDER BY created_at DESC LIMIT 18446744073709551615 OFFSET 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        echo '<small style="display:block; margin-left: 10px;">Wstawiono: ' . htmlspecialchars($formattedDate) . '</small>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?>