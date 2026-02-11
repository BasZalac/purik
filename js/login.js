import { fetchJSON, renderNotice } from './utils.js';

const form = document.getElementById('login-form');
const notice = document.getElementById('notice');

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
    await fetchJSON('./api/login.php', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    window.location.href = 'index.php';
  } catch (error) {
    renderNotice(notice, error.message, true);
  }
});
