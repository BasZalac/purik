export function getQueryParam(name) {
  const params = new URLSearchParams(window.location.search);
  const value = params.get(name);
  return value ? Number(value) : null;
}

export async function fetchJSON(url, options = {}) {
  const headers = new Headers(options.headers || {});
  if (options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const response = await fetch(url, {
    credentials: 'include',
    ...options,
    headers,
  });

  let data = null;
  const text = await response.text();
  if (text) {
    data = JSON.parse(text);
  }

  if (!response.ok) {
    const message = data?.error || data?.message || 'Hiba történt.';
    throw new Error(message);
  }

  return data;
}

export function renderNotice(target, message, isError = false) {
  target.textContent = message;
  target.classList.toggle('error', isError);
  target.hidden = !message;
}

export function buildNav() {
  const nav = document.querySelector('nav');
  if (!nav) return;
  nav.innerHTML = `
    <a href="index.php">Kezdőlap</a>
    <a href="poll.html">Szavazás</a>
    <a href="result.html">Eredmények</a>
    <a href="admin.html">Új kérdés</a>
    <a href="dashboard.html">Kérdéskezelés</a>
    <a href="logout.html">Kijelentkezés</a>
  `;
}
