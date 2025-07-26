/**
 * 生徒管理システム - 生徒一覧画面 JS
 * 目次:
 *   1. DOM要素取得
 *   2. データ・状態
 *   3. テーブル・ページネーション描画
 *   4. 検索・プルダウン生成
 *   5. 成績テーブル操作
 *   6. イベントリスナー
 *   7. 初期化
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. DOM要素取得
  const studentTableBody = document.getElementById("student-table-body");
  const pageButtonsContainer = document.getElementById("page-buttons");
  const searchInput = document.getElementById("search-name");
  const searchBtn = document.getElementById("search-btn");
  const logoutBtn = document.getElementById("logout-btn");
  const tabList = document.getElementById("tab-list");
  const tabRegister = document.getElementById("tab-register");
  const tabContentList = document.getElementById("tab-content-list");
  const tabContentRegister = document.getElementById("tab-content-register");
  const scoreTableBody = document.getElementById('score-table-body');
  const addScoreBtn = document.getElementById('add-score-btn');

  // 2. データ・状態
  const students = [
    { class: "A", number: 1, gender: "男", birthday: "2000/1/1", nameKanji: "相川奏", nameKana: "あいかわ そう" },
    { class: "A", number: 2, gender: "女", birthday: "2000/2/1", nameKanji: "石川海", nameKana: "いしかわ うみ" },
    { class: "A", number: 3, gender: "男", birthday: "2000/3/1", nameKanji: "内田瑛二", nameKana: "うちだ えいじ" },
    ...Array.from({ length: 147 }, () => ({ class: "", number: "", gender: "", nameKanji: "", nameKana: "" }))
  ];
  let currentPage = 1;
  const itemsPerPage = 10;
  let filteredStudents = students;
  const totalPages = () => Math.ceil(filteredStudents.length / itemsPerPage);

  // 3. テーブル・ページネーション描画
  function renderTable(page) {
    studentTableBody.innerHTML = "";
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageData = filteredStudents.slice(start, end);
    pageData.forEach(s => {
      const tr = document.createElement("tr");
      const birthdayDisplay = s.birthday ? s.birthday : '';
      tr.innerHTML = `
        <td>${s.class}</td>
        <td>${s.number}</td>
        <td>${s.gender}</td>
        <td>${birthdayDisplay}</td>
        <td>${s.nameKanji}</td>
        <td>${s.nameKana}</td>
        <td><button class="action detail">詳細</button></td>
        <td><button class="action delete">削除</button></td>
      `;
      studentTableBody.appendChild(tr);
    });
  }
  function renderPageButtons() {
    pageButtonsContainer.innerHTML = "";
    const pages = totalPages();
    if (pages <= 1) return;
    const createButton = (text, onClick, isDisabled = false, isActive = false) => {
      const btn = document.createElement("button");
      btn.textContent = text;
      btn.disabled = isDisabled;
      if (isActive) btn.classList.add('active-page');
      btn.addEventListener('click', onClick);
      pageButtonsContainer.appendChild(btn);
    };
    createButton("最初へ", () => handlePageChange(1), currentPage === 1);
    let pageNumbers = [];
    if (pages <= 5) {
      pageNumbers = Array.from({ length: pages }, (_, i) => i + 1);
    } else {
      if (currentPage > 2) pageNumbers.push("...");
      const start = Math.max(1, currentPage - 1);
      const end = Math.min(pages, currentPage + 1);
      for (let i = start; i <= end; i++) pageNumbers.push(i);
      if (currentPage < pages - 1) pageNumbers.push("...");
    }
    pageNumbers.forEach(item => {
      if (typeof item === 'number') {
        createButton(getCircledNumber(item), () => handlePageChange(item), false, item === currentPage);
      } else {
        createButton(item, () => {}, true);
      }
    });
    createButton("最後へ", () => handlePageChange(pages), currentPage === pages);
  }
  function handlePageChange(page) {
    currentPage = page;
    renderTable(currentPage);
    renderPageButtons();
  }
  function getCircledNumber(n) {
    const circled = "①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳";
    return circled[n - 1] || String(n);
  }

  // 4. 検索・プルダウン生成
  function searchStudents() {
    const keyword = searchInput.value.trim().toLowerCase();
    filteredStudents = students.filter(s =>
      (s.nameKanji && s.nameKanji.toLowerCase().includes(keyword)) ||
      (s.nameKana && s.nameKana.toLowerCase().includes(keyword))
    );
    handlePageChange(1);
  }
  function fillSelectOptions() {
    const createOptions = (id, start, end, placeholder) => {
      const select = document.getElementById(id);
      if (select) {
        let optionsHtml = `<option value="">${placeholder}</option>`;
        for (let i = start; i <= end; i++) {
          optionsHtml += `<option value="${i}">${i}</option>`;
        }
        select.innerHTML = optionsHtml;
      }
    };
    createOptions('class-number', 1, 30, 'クラス番号');
    createOptions('birth-year', 1990, 2020, '年');
    createOptions('birth-month', 1, 12, '月');
    createOptions('birth-day', 1, 31, '日');
  }

  // 5. 成績テーブル操作
  const testTypes = ['期末試験', '中間試験', 'その他'];
  function createScoreRow() {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="date" class="score-date" style="width:120px;"></td>
      <td>
        <select class="score-type">${testTypes.map(type => `<option value="${type}">${type}</option>`).join('')}</select>
      </td>
      <td><input type="number" class="score-input" min="0" max="100" style="width:60px;"></td>
      <td><input type="number" class="score-input" min="0" max="100" style="width:60px;"></td>
      <td><input type="number" class="score-input" min="0" max="100" style="width:60px;"></td>
      <td><input type="number" class="score-input" min="0" max="100" style="width:60px;"></td>
      <td><input type="number" class="score-input" min="0" max="100" style="width:60px;"></td>
      <td><input type="text" class="score-avg" readonly style="width:60px;"></td>
      <td><input type="text" class="score-sum" readonly style="width:60px;"></td>
      <td><button type="button" class="score-save action">保存</button></td>
      <td><button type="button" class="score-delete action">削除</button></td>
    `;
    tr.querySelector('.score-type').addEventListener('change', function(e) {
      if (e.target.value === 'その他') {
        tr.querySelectorAll('.score-input').forEach(input => {
          input.value = 0;
        });
        calcScore(tr);
      }
    });
    tr.querySelectorAll('.score-input').forEach(input => input.addEventListener('input', () => calcScore(tr)));
    tr.querySelector('.score-delete').addEventListener('click', () => { if (confirm('本当に削除しますか？')) tr.remove(); });
    tr.querySelector('.score-save').addEventListener('click', () => alert('保存しました（ダミー）'));
    return tr;
  }
  function calcScore(tr) {
    const inputs = Array.from(tr.querySelectorAll('.score-input'));
    const nums = inputs.map(i => parseInt(i.value, 10)).filter(n => !isNaN(n));
    const sum = nums.reduce((a, b) => a + b, 0);
    const avg = nums.length ? (sum / nums.length).toFixed(1) : '';
    tr.querySelector('.score-sum').value = nums.length ? sum : '';
    tr.querySelector('.score-avg').value = nums.length ? avg : '';
  }

  // 6. イベントリスナー
  logoutBtn.addEventListener('click', () => { window.location.href = '../login/login.html'; });
  searchBtn.addEventListener('click', searchStudents);
  searchInput.addEventListener('input', searchStudents);
  addScoreBtn.addEventListener('click', () => scoreTableBody.appendChild(createScoreRow()));
  studentTableBody.addEventListener('click', e => {
    if (e.target.classList.contains("delete")) {
      if (confirm("本当に削除しますか？")) {
        e.target.closest("tr").remove();
      }
    } else if (e.target.classList.contains("detail")) {
      window.location.href = "student_detail.html";
    }
  });
  tabList.addEventListener('click', () => {
    tabList.classList.add('active');
    tabRegister.classList.remove('active');
    tabContentList.classList.remove('hidden');
    tabContentRegister.classList.add('hidden');
  });
  tabRegister.addEventListener('click', () => {
    tabRegister.classList.add('active');
    tabList.classList.remove('active');
    tabContentList.classList.add('hidden');
    tabContentRegister.classList.remove('hidden');
  });

  // 7. 初期化
  function init() {
    fillSelectOptions();
    renderTable(currentPage);
    renderPageButtons();
  }
  init();
});
