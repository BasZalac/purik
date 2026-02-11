import { fetchJSON, renderNotice } from './utils.js';

const form = document.getElementById('auth-form');
const notice = document.getElementById('notice');
const modeLoginButton = document.getElementById('mode-login');
const modeRegisterButton = document.getElementById('mode-register');
const submitButton = document.getElementById('submit-button');
const knownUsers = document.getElementById('known-users');
const title = document.getElementById('login-title');
const subtitle = document.getElementById('login-subtitle');

let mode = 'login';

function syncMode() {
  const loginMode = mode === 'login';
  submitButton.textContent = loginMode ? 'Bejelentkezés' : 'Regisztráció';
  modeLoginButton.className = loginMode ? '' : 'secondary';
  modeRegisterButton.className = loginMode ? 'secondary' : '';
}

function setMode(nextMode) {
  mode = nextMode;
  syncMode();
  renderNotice(notice, '');
}

function fillKnownUsers(names = []) {
  knownUsers.innerHTML = '';
  if (!names.length) {
    knownUsers.innerHTML = '<span class="small">Nincs még regisztrált felhasználó.</span>';
    return;
  }

  const label = document.createElement('span');
  label.className = 'small';
  label.textContent = 'Ismert felhasználók az adatbázisból:';
  knownUsers.appendChild(label);

  names.forEach((name) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'secondary';
    button.textContent = name;
    button.addEventListener('click', () => {
      form.name.value = name;
      form.pass.focus();
    });
    knownUsers.appendChild(button);
  });
}

async function loadLoginInfo() {
  try {
    const info = await fetchJSON('./api/login_info.php');
    title.textContent = info.title || title.textContent;
    subtitle.textContent = info.subtitle || subtitle.textContent;
    fillKnownUsers(info.knownUsers || []);
  } catch {
    fillKnownUsers([]);
  }
}

modeLoginButton.addEventListener('click', () => setMode('login'));
modeRegisterButton.addEventListener('click', () => setMode('register'));

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  renderNotice(notice, '');

  const payload = {
    name: form.name.value.trim(),
    pass: form.pass.value,
  };

  if (!payload.name || !payload.pass) {
    renderNotice(notice, 'Add meg a nevet és a jelszót.', true);
    return;
  }

  try {
    if (mode === 'login') {
      await fetchJSON('./api/login.php', {
        method: 'POST',
        body: JSON.stringify(payload),
      });
      window.location.href = 'index.php';
      return;
    }

    await fetchJSON('./api/register.php', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    renderNotice(notice, 'Sikeres regisztráció. Most jelentkezz be.');
    setMode('login');
    form.pass.value = '';
    await loadLoginInfo();
  } catch (error) {
    renderNotice(notice, error.message, true);
  }
});

syncMode();
loadLoginInfo();
