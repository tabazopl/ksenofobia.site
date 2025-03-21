<?php
session_start(); // Start the session
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ksenofobia.site</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-username {
            color: #FFD700; /* Złoty kolor dla "ADMIN" */
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
            <a href="admin-login.php" class="btn">Admin Panel</a>
            <a href="dokumentacja.php" class="btn">Dokumentacja</a>
        </div>
    </header>

    <div class="container">
        <div class="comment-input">
            <h3>Wpisz swój komentarz!!</h3>
            <form method="POST" action="" id="commentForm">
                <textarea name="comment" maxlength="128" placeholder="Wpisz komentarz (max 128 znaków)..."></textarea>
                <button type="submit">Dodaj</button>
            </form>
            <p class="char-count">0/128 znaków</p>
            <?php
            if (isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
                echo '<script>location.reload();</script>';
            }
            ?>
        </div>

        <div class="comments-section">
            <?php include 'comments.php'; ?>
            <button id="load-more">Rozwiń więcej</button>
        </div>
    </div>

    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script>
        document.getElementById('commentForm').addEventListener('submit', function(event) {
            event.preventDefault();
            fetch(window.location.href, {
                method: 'POST',
                body: new FormData(this)
            }).then(response => {
                if (response.ok) {
                    location.reload();
                }
            });
        });

        const textarea = document.querySelector('textarea[name="comment"]');
        const charCount = document.querySelector('.char-count');
        textarea.addEventListener('input', function() {
            charCount.textContent = `${this.value.length}/128 znaków`;
        });
    </script>
</body>
</html>