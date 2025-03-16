// Licznik znaków w textarea
document.addEventListener('DOMContentLoaded', function () {
  const textarea = document.querySelector('textarea');
  const charCount = document.querySelector('.char-count');

  if (textarea && charCount) {
      // Ustaw początkową wartość licznika
      charCount.textContent = `${textarea.value.length}/128 znaków`;

      textarea.addEventListener('input', function () {
          const maxLength = 128;
          const currentLength = this.value.length;
          charCount.textContent = `${currentLength}/${maxLength} znaków`;
      });
  } else {
      console.error('Nie znaleziono textarea lub char-count.');
  }
});

// Funkcja do ładowania więcej komentarzy
document.getElementById('load-more').addEventListener('click', function () {
  fetch('load_more_comments.php')
      .then(response => response.text())
      .then(data => {
          const commentsSection = document.querySelector('.comments-section');
          if (commentsSection) {
              // Usuń przycisk "Rozwiń więcej" przed dodaniem nowych komentarzy
              this.remove();
              // Dodaj nowe komentarze
              commentsSection.innerHTML += data;
          } else {
              console.error('Sekcja komentarzy nie została znaleziona.');
          }
      })
      .catch(error => console.error('Błąd ładowania komentarzy:', error));
});