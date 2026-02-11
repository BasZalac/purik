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

export async function getCurrentUser() {
  try {
    return await fetchJSON('./api/me.php');
  } catch {
    return null;
  }
}

export async function buildNav() {
  const nav = document.querySelector('nav');
  if (!nav) return;

  const user = await getCurrentUser();
  const links = [
    '<a href="index.php">Kezdőlap</a>',
  ];

  if (user) {
    links.push('<a href="poll.html">Szavazás</a>');
    links.push('<a href="result.html">Eredmények</a>');
    if (user.role === 'admin') {
      links.push('<a href="admin.html">Új kérdés</a>');
      links.push('<a href="dashboard.html">Kérdéskezelés</a>');
    }
    links.push('<a href="logout.html">Kijelentkezés</a>');
  } else {
    links.push('<a href="login.html">Bejelentkezés</a>');
  }

  nav.innerHTML = links.join('');
}
