import { buildNav, fetchJSON, renderNotice } from './utils.js';

const answersContainer = document.getElementById('answers');
const addAnswerButton = document.getElementById('add-answer');
const form = document.getElementById('question-form');
const notice = document.getElementById('notice');

buildNav();

function addAnswerField(value = '') {
  const wrapper = document.createElement('div');
  wrapper.className = 'list-item';

  const label = document.createElement('label');
  label.textContent = 'Válasz';

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

  wrapper.append(label, input, actions);
  answersContainer.appendChild(wrapper);
}

addAnswerButton.addEventListener('click', () => addAnswerField());

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  renderNotice(notice, '');

  const answers = [...answersContainer.querySelectorAll('input')]
    .map((input) => input.value.trim())
    .filter(Boolean);

  if (answers.length < 2) {
    renderNotice(notice, 'Legalább két válasz szükséges.', true);
    return;
  }

  try {
    const result = await fetchJSON('./api/admin.php', {
      method: 'POST',
      body: JSON.stringify({
        qtext: form.qtext.value.trim(),
        answers,
      }),
    });
    renderNotice(notice, `Mentve. Új kérdés azonosítója: ${result.qid}.`);
    form.reset();
    answersContainer.innerHTML = '';
    addAnswerField();
    addAnswerField();
  } catch (error) {
    renderNotice(notice, error.message, true);
  }
});

addAnswerField();
addAnswerField();
