// Licznik znaków w textarea
document.querySelector('textarea').addEventListener('input', function() {
  const charCount = this.value.length;
  document.querySelector('.char-count').textContent = `${charCount}/128 znaków`;
});

// Funkcja do ładowania więcej komentarzy
document.getElementById('load-more').addEventListener('click', function() {
  fetch('load_more_comments.php')
      .then(response => response.text())
      .then(data => {
          document.querySelector('.comments-section').innerHTML += data;
          this.style.display = 'none'; // Ukryj przycisk po załadowaniu
      });
});