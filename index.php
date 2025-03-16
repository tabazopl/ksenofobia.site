<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ksenofobia.site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="top-menu">
        <h1>Ksenofobia.site</h1>
        <div class="buttons">
            <a href="dokumentacja.php" class="btn">Dokumentacja</a>
        </div>
    </header>

    <div class="container">
        <div class="comment-input">
            <h3>Wpisz swój komentarz!!</h3>
            <form method="POST" action="">
                <textarea name="comment" maxlength="128" placeholder="Wpisz komentarz (max 128 znaków)..."></textarea>
                <button type="submit">Dodaj</button>
            </form>
            <p class="char-count">0/128 znaków</p>
        </div>

        <div class="comments-section">
            <?php include 'comments.php'; ?>
            <button id="load-more" style="display: none;">Rozwiń więcej</button>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>