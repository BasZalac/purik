import { buildNav, fetchJSON, getCurrentUser, renderNotice } from './utils.js';

const list = document.getElementById('question-list');
const notice = document.getElementById('notice');

async function loadQuestions() {
  const questions = await fetchJSON('./api/questions.php');
  list.innerHTML = '';

  if (!questions.length) {
    list.innerHTML = '<p class="small">Nincs elérhető kérdés.</p>';
    return;
  }

  questions.forEach((question) => {
    const item = document.createElement('div');
    item.className = 'list-item';

    const title = document.createElement('input');
    title.type = 'text';
    title.value = question.qtext;

    const actions = document.createElement('div');
    actions.className = 'actions';

    const saveButton = document.createElement('button');
    saveButton.textContent = 'Mentés';

    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'Törlés';
    deleteButton.className = 'danger';

    const links = document.createElement('div');
    links.className = 'actions';
    links.innerHTML = `
      <a href="poll.html?qid=${question.qid}">Szavazás</a>
      <a href="result.html?qid=${question.qid}">Eredmények</a>
    `;

    saveButton.addEventListener('click', async () => {
      renderNotice(notice, '');
      try {
        await fetchJSON('./api/modquestion.php', {
          method: 'POST',
          body: JSON.stringify({
            qid: question.qid,
            qtext: title.value.trim(),
          }),
        });
        renderNotice(notice, 'Kérdés frissítve.');
      } catch (error) {
        renderNotice(notice, error.message, true);
      }
    });

    deleteButton.addEventListener('click', async () => {
      const confirmDelete = window.confirm('Biztosan törlöd ezt a kérdést?');
      if (!confirmDelete) return;

      renderNotice(notice, '');
      try {
        await fetchJSON('./api/delquestion.php', {
          method: 'POST',
          body: JSON.stringify({ qid: question.qid }),
        });
        renderNotice(notice, 'Kérdés törölve.');
        item.remove();
      } catch (error) {
        renderNotice(notice, error.message, true);
      }
    });

    actions.append(saveButton, deleteButton);
    item.append(title, actions, links);
    list.appendChild(item);
  });
}

async function init() {
  await buildNav();
  const user = await getCurrentUser();
  if (!user || user.role !== 'admin') {
    list.innerHTML = '';
    renderNotice(notice, 'Ehhez az oldalhoz admin jogosultság kell.', true);
    return;
  }

  await loadQuestions();
}

init().catch((error) => {
  renderNotice(notice, error.message, true);
});
