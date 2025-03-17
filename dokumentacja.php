<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ksenofobia.site - Dokumentacja</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
            <a href="index.php" class="btn">Strona główna</a>
            <a href="admin-login.php" class="btn">Admin Panel</a>
        </div>
    </header>

    <div class="container">
        <h2>Dokumentacja</h2>
        <p>Ta strona zawiera dokumentację projektu Ksenofobia.site.</p>
        <h3>Opis projektu</h3>
        <p>Projekt Ksenofobia.site to platforma umożliwiająca dodawanie komentarzy przez użytkowników oraz zarządzanie nimi przez administratora. Zawiera funkcje takie jak:</p>
        <ul>
            <li>Dodawanie komentarzy przez użytkowników i administratora.</li>
            <li>Polubianie komentarzy przez administratora.</li>
            <li>Wyszukiwanie komentarzy. (admin-only)</li>
            <li>Zarządzanie komentarzami (usuwanie, polubianie) w panelu admina.</li>
            
        </ul>
        <h3>Technologie</h3>
        <ul>
            <li>PHP</li>
            <li>MySQL/MariaDB</li>
            <li>HTML/CSS/JavaScript</li>
        </ul>
    </div>
</body>
</html>