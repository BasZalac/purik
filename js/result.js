import { buildNav, fetchJSON, getQueryParam, renderNotice } from './utils.js';

const questionSelect = document.getElementById('question-select');
const questionTitle = document.getElementById('question-title');
const resultsContainer = document.getElementById('results');
const notice = document.getElementById('notice');
const refreshButton = document.getElementById('refresh-button');
const autoRefresh = document.getElementById('auto-refresh');

buildNav();

let currentQid = getQueryParam('qid');
let refreshTimer = null;

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
    resultsContainer.innerHTML = '';
    return;
  }

  if (!currentQid) {
    currentQid = questions[0].qid;
  }
  questionSelect.value = String(currentQid);
  await loadResults(currentQid);
}

function renderResults(data) {
  questionTitle.textContent = data.qtext;
  resultsContainer.innerHTML = '';
  const totalVotes = data.answers.reduce((sum, answer) => sum + answer.votes, 0);

  data.answers.forEach((answer) => {
    const item = document.createElement('div');
    item.className = 'list-item';
    const percentage = totalVotes ? Math.round((answer.votes / totalVotes) * 100) : 0;

    item.innerHTML = `
      <div><strong>${answer.atext}</strong></div>
      <div class="small">Szavazatok: ${answer.votes} (${percentage}%)</div>
      <div class="result-bar"><span style="width:${percentage}%"></span></div>
    `;
    resultsContainer.appendChild(item);
  });

  if (!data.answers.length) {
    resultsContainer.innerHTML = '<p class="small">Nincs elérhető válasz.</p>';
  }
}

async function loadResults(qid) {
  renderNotice(notice, '');
  const data = await fetchJSON(`./api/result.php?qid=${qid}`);
  renderResults(data);
}

function scheduleRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer);
  }
  if (autoRefresh.checked) {
    refreshTimer = setInterval(() => loadResults(currentQid), 10000);
  }
}

questionSelect.addEventListener('change', async () => {
  currentQid = Number(questionSelect.value);
  await loadResults(currentQid);
});

refreshButton.addEventListener('click', () => {
  loadResults(currentQid).catch((error) => renderNotice(notice, error.message, true));
});

autoRefresh.addEventListener('change', scheduleRefresh);

loadQuestions()
  .then(scheduleRefresh)
  .catch((error) => {
    renderNotice(notice, error.message, true);
    questionTitle.textContent = 'Nem sikerült betölteni az eredményeket.';
  });
