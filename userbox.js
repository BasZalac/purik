document.addEventListener("DOMContentLoaded", () => {
  fetch('./api/me.php', {
    credentials: 'include'
  })
    .then(res => {
      if (!res.ok) throw new Error();
      return res.json();
    })
    .then(user => {
      document.getElementById('userbox').innerHTML = `
        Bejelentkezve: <strong>${user.name}</strong>
        <button onclick="logout()">Kijelentkezés</button>
      `;
    })
    .catch(() => {
      document.getElementById('userbox').innerHTML = `
        <a href="login.html">Bejelentkezés</a>
      `;
    });
});

function logout() {
  fetch('./api/logout.php', {
    credentials: 'include'
  }).then(() => location.href = 'logout.html');
}