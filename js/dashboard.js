import { buildNav, fetchJSON, renderNotice } from './utils.js';

const list = document.getElementById('question-list');
const notice = document.getElementById('notice');

buildNav();

function createAnswerField(value = '') {
  const wrapper = document.createElement('div');
  wrapper.className = 'list-item';

  const input = document.createElement('input');
  input.type = 'text';
  input.value = value;
  input.required = true;

  const removeButton = document.createElement('button');
  removeButton.type = 'button';
  removeButton.textContent = 'Eltávolítás';
  removeButton.className = 'secondary';
  removeButton.addEventListener('click', () => wrapper.remove());

  const actions = document.createElement('div');
  actions.className = 'actions';
  actions.appendChild(removeButton);

  wrapper.append(input, actions);
  return wrapper;
}

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

    const answerList = document.createElement('div');
    answerList.className = 'list';
    (question.answers || []).forEach((answer) => {
      answerList.appendChild(createAnswerField(answer.atext));
    });

    const addAnswerButton = document.createElement('button');
    addAnswerButton.type = 'button';
    addAnswerButton.textContent = '+ Válasz';
    addAnswerButton.className = 'secondary';
    addAnswerButton.addEventListener('click', () => {
      answerList.appendChild(createAnswerField(''));
    });

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

    const answersLocked = Boolean(question.hasVotes);
    if (answersLocked) {
      addAnswerButton.disabled = true;
      answerList.querySelectorAll('input, button').forEach((element) => {
        element.disabled = true;
      });
    }

    saveButton.addEventListener('click', async () => {
      renderNotice(notice, '');
      const answers = [...answerList.querySelectorAll('input')]
        .map((input) => input.value.trim())
        .filter(Boolean);

      if (!answersLocked && answers.length < 2) {
        renderNotice(notice, 'Legalább két válasz szükséges.', true);
        return;
      }

      try {
        await fetchJSON('./api/modquestion.php', {
          method: 'POST',
          body: JSON.stringify({
            qid: question.qid,
            qtext: title.value.trim(),
          }),
        });

        if (!answersLocked) {
          await fetchJSON('./api/modanswers.php', {
            method: 'POST',
            body: JSON.stringify({
              qid: question.qid,
              answers,
            }),
          });
        }

        renderNotice(
          notice,
          answersLocked
            ? 'Kérdés frissítve. A válaszok már nem módosíthatók, mert érkezett szavazat.'
            : 'Kérdés és válaszok frissítve.',
        );
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

    actions.append(saveButton, deleteButton, addAnswerButton);
    item.append(title, answerList, actions, links);
    list.appendChild(item);
  });
}

loadQuestions().catch((error) => {
  renderNotice(notice, error.message, true);
});
