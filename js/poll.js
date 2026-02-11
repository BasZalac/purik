import { buildNav, fetchJSON, getQueryParam, renderNotice } from './utils.js';

const questionSelect = document.getElementById('question-select');
const questionTitle = document.getElementById('question-title');
const voteForm = document.getElementById('vote-form');
const notice = document.getElementById('notice');

buildNav();

let currentQid = getQueryParam('qid');

async function loadQuestions() {
  const questions = await fetchJSON('./api/questions.php');
  questionSelect.innerHTML = '';
  questions.forEach((question) => {
    const option = document.createElement('option');
    option.value = question.qid;
    option.textContent = question.qtext;
    questionSelect.appendChild(option);
  });

  if (!questions.length) {
    questionTitle.textContent = 'Nincs elérhető kérdés.';
    voteForm.innerHTML = '';
    return;
  }

  if (!currentQid) {
    currentQid = questions[0].qid;
  }
  questionSelect.value = String(currentQid);
  await loadQuestion(currentQid);
}

async function loadQuestion(qid) {
  renderNotice(notice, '');
  const data = await fetchJSON(`./api/question.php?qid=${qid}`);
  questionTitle.textContent = data.qtext;
  voteForm.innerHTML = '';

  data.answers.forEach((answer) => {
    const wrapper = document.createElement('label');
    wrapper.className = 'inline-radio';
    const input = document.createElement('input');
    input.type = 'radio';
    input.name = 'answer';
    input.value = answer.aid;
    wrapper.append(input, document.createTextNode(answer.atext));
    voteForm.appendChild(wrapper);
  });
}

questionSelect.addEventListener('change', async () => {
  currentQid = Number(questionSelect.value);
  await loadQuestion(currentQid);
});

voteForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  renderNotice(notice, '');
  const selected = voteForm.querySelector('input[name="answer"]:checked');
  if (!selected) {
    renderNotice(notice, 'Válassz egy opciót a szavazáshoz.', true);
    return;
  }

  try {
    await fetchJSON('./api/vote.php', {
      method: 'POST',
      body: JSON.stringify({
        qid: currentQid,
        aid: Number(selected.value),
      }),
    });
    renderNotice(notice, 'Szavazat rögzítve.');
  } catch (error) {
    renderNotice(notice, error.message, true);
  }
});

loadQuestions().catch((error) => {
  renderNotice(notice, error.message, true);
  questionTitle.textContent = 'Nem sikerült betölteni a kérdéseket.';
});
