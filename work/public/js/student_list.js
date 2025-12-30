/*
生徒一覧画面の機能

1. 要素取得
2. データ管理
3. 一覧表示
4. 検索
5. 写真アップロード
6. 入力チェック
7. 成績管理
8. イベント
9. 初期化
*/

document.addEventListener("DOMContentLoaded", function () {
  // 1. 要素取得
  const studentTableBody = document.getElementById("student-table-body");
  const pageButtonsContainer = document.getElementById("pege-btn");
  const searchInput = document.getElementById("search-name");
  const searchBtn = document.getElementById("search-btn");
  const logoutBtn = document.getElementById("logout-btn");
  const tabList = document.getElementById("tab-list");
  const tabRegister = document.getElementById("tab-register");
  const tabContentList = document.getElementById("tab-content-list");
  const tabContentRegister = document.getElementById("tab-content-register");
  const scoreTableBody = document.getElementById('score-table-body');
  const photoBtn = document.getElementById("photo-btn");
  const photoInput = document.getElementById("photo-input");
  const studentPhoto = document.getElementById("student-photo");
  const photoError = document.getElementById("photo-error");
  const studentForm = document.getElementById("student-register-form");
  const validationError = document.getElementById("validation-error");


  // 2. データ管理
  let scoreData = [];
  let students = [];
  let currentPage = 1;
  const itemsPerPage = 10;
  let filteredStudents = students;
  const totalPages = () => Math.ceil(filteredStudents.length / itemsPerPage);


  const existingScores = window.existingScores || [];
  let deletedTestIds = [];
  try {
    deletedTestIds = JSON.parse(localStorage.getItem('deletedTestIds') || '[]');
  } catch (_) { deletedTestIds = []; }
  const deletedIdSet = new Set(deletedTestIds.map(id => 'existing_' + String(id)));
  existingScores.forEach(score => {
    const rowId = 'existing_' + score.test_id;
    if (deletedIdSet.has(rowId)) return;
    scoreData.push({
      rowId,
      date: score.test_date,
      type: score.test_type,
      scores: [score.english || '', score.math || '', score.japanese || '', score.science || '', score.social || ''],
      isSaved: true,
      isExisting: true
    });
  });

  // ページ読み込み時に既存の成績データを日付順で表示
  if (scoreData.length > 0) {
    scoreData.sort((a, b) => new Date(b.date) - new Date(a.date)); // 降順（新しい日付から古い日付）
    redrawAllScoreTable();
  }


  // 3. 一覧表示
  function renderTable(page) {
    studentTableBody.innerHTML = "";
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageData = filteredStudents.slice(start, end);
    pageData.forEach(s => {
      const template = document.getElementById('student-row-template');
      const tr = template.content.cloneNode(true);
      const tds = tr.querySelectorAll('td');
      tds[0].textContent = s[1];
      tds[1].textContent = s[2];
      tds[2].textContent = s[7];
      tds[3].textContent = s[8] ? s[8] : '';
      tds[4].textContent = `${s[3]} ${s[4]}`;
      tds[5].textContent = `${s[5]} ${s[6]}`;
      studentTableBody.appendChild(tr);
    });
  }
  // ページネーション
  function renderPageButtons() {
    pageButtonsContainer.innerHTML = "";
    const pages = totalPages();
    if (pages <= 1) {
      return;
    }
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
    // 20ページ以降は通常の数字を表示
    if (n <= 20) {
      return circled[n - 1] || String(n);
    }
    return String(n);
  }

  // PHPから生徒データ読込
  function loadStudentsFromPHP() {
    if (window.studentsData) {
      // PHPから渡されたデータを配列形式に変換
      students = window.studentsData.map(student => [
        student.id,
        student.class,
        student.class_no,
        student.last_name,
        student.first_name,
        student.last_name_kana,
        student.first_name_kana,
        student.gender == 1 ? '男' : '女', // 性別を文字列に変換
        student.birth_date
      ]);
      filteredStudents = students;
      // PHPから生徒データを受領
      return true;
    } else {
      console.error('PHPから生徒データが取得できませんでした');
      return false;
    }
  }


  // 4. 検索
  function searchStudents() {
    const keyword = searchInput.value.trim().toLowerCase();
    filteredStudents = students.filter(s =>
      (s[3] && s[3].toLowerCase().includes(keyword)) ||
      (s[4] && s[4].toLowerCase().includes(keyword)) ||
      (s[5] && s[5].toLowerCase().includes(keyword)) ||
      (s[6] && s[6].toLowerCase().includes(keyword))
    );
    handlePageChange(1);
  }



  // 5. 写真アップロード
  function handlePhotoUpload(file) {
    // ファイルサイズチェック - 3MB以下じゃないとダメ
    if (file.size > 3145728) {
      showPhotoError('ファイルサイズは3MB以下にしてください');
      return;
    }

    // ファイル形式チェック - JPEGだけOK
    if (!file.type.match('image/jpeg') && !file.type.match('image/jpg')) {
      showPhotoError('JPEG形式のファイルを選択してください');
      return;
    }

    // 画像表示 - 選んだ写真を画面に表示
    const reader = new FileReader();
    reader.onload = function(e) {
      studentPhoto.src = e.target.result;
      hidePhotoError();
    };
    reader.readAsDataURL(file);
  }

  // エラーメッセージ表示
  function showPhotoError(message) {
    photoError.textContent = message;
    photoError.classList.remove('hidden');
  }

  function hidePhotoError() {
    photoError.classList.add('hidden');
  }


  // 6. 入力チェック
  function validateForm() {
    let isValid = true;
    const requiredFields = [
      { id: 'last-name', type: 'input' },
      { id: 'first-name', type: 'input' },
      { id: 'last-name-kana', type: 'input' },
      { id: 'first-name-kana', type: 'input' },
      { id: 'class-select', type: 'select' },
      { id: 'gender-select', type: 'select' },
      { id: 'class-number', type: 'select' },
      { id: 'birth-year', type: 'select' },
      { id: 'birth-month', type: 'select' },
      { id: 'birth-day', type: 'select' }
    ];

    // エラー表示をリセット
    resetValidationErrors();

    // 必須項目チェック - 入力されてないとエラー
    requiredFields.forEach(field => {
      const element = document.getElementById(field.id);
      if (!element.value.trim()) {
        isValid = false;
        showFieldError(element, field.type);
      }
    });


    // エラーメッセージの表示・非表示
    if (!isValid) {
      validationError.classList.remove('hidden');
    } else {
      validationError.classList.add('hidden');
    }

    return isValid;
  }

  // フィールドエラー表示
  function showFieldError(element, type) {
    if (type === 'input') {
      element.classList.add('input-error');
    } else if (type === 'select') {
      element.classList.add('select-error');
    }
  }

  // バリデーションエラーリセット
  function resetValidationErrors() {
    const errorElements = document.querySelectorAll('.input-error, .select-error');
    errorElements.forEach(element => {
      element.classList.remove('input-error', 'select-error');
    });
    validationError.classList.add('hidden');
  }


  // 7. 成績管理
  function createScoreRow() {
    const template = document.getElementById('score-row-template');
    const tr = template.content.cloneNode(true);
    const trElement = tr.querySelector('tr'); // <tr> 要素

    if (!trElement) {
      console.error('score-row-templateから<tr>要素が見つかりません');
      return null;
    }

    const rowId = Date.now() + Math.random();
    trElement.setAttribute('data-row-id', rowId);

    trElement.querySelectorAll('.score-input').forEach(input => {
      input.addEventListener('input', () => {
        calcScore(trElement);
        // エラー表示をリセット
        if (input.classList.contains('input-error')) {
          input.classList.remove('input-error');
        }
      });
    });

    // 実施日と種別の変更時にもエラー表示をリセット
    const dateInput = trElement.querySelector('.score-date');
    if (dateInput) {
      dateInput.addEventListener('input', function() {
        if (this.classList.contains('input-error')) {
          this.classList.remove('input-error');
        }
      });
    }

    // 重複防止

    return trElement; // DocumentFragmentではなく<tr>要素を返す
  }
  function calcScore(tr) {
    const inputs = Array.from(tr.querySelectorAll('.score-input'));
    const nums = inputs.map(i => parseInt(i.value, 10)).filter(n => !isNaN(n));
    const sum = nums.reduce((a, b) => a + b, 0);
    const avg = nums.length ? (sum / nums.length).toFixed(1) : '';
    tr.querySelector('.score-sum').value = nums.length ? sum : '';
    tr.querySelector('.score-avg').value = nums.length ? avg : '';
  }

  // 成績保存時のバリデーション関数
  function validateAndSaveScore(tr) {
    // エラー表示をリセット
    resetScoreValidationErrors(tr);

    const dateInput = tr.querySelector('.score-date');
    const typeSelect = tr.querySelector('.score-type');
    const scoreInputs = tr.querySelectorAll('.score-input');

    // 要素の存在チェック
    if (!dateInput || !typeSelect) {
      console.error('必要な要素が見つかりません:', { dateInput, typeSelect });
      return;
    }

    let isValid = true;

    // 実施日のチェック
    if (!dateInput.value.trim()) {
      dateInput.classList.add('input-error');
      isValid = false;
    }

    // 種別が「未受験」の場合、種別プルダウンを赤く表示
    if (typeSelect.value === '未受験') {
      typeSelect.classList.add('select-error');
      isValid = false;
    }

    // 生徒情報の必須項目チェック
    if (!validateStudentInfo()) {
      isValid = false;
    }

    if (isValid) {
      // 成績が未入力相当（実施日未入力 または 種別が未受験 かつ 点数がすべて空）の場合の確認
      const allScoresEmpty = Array.from(scoreInputs).every(input => !input.value || input.value.trim() === '');
      if ((!dateInput.value.trim() || typeSelect.value === '未受験') && allScoresEmpty) {
        const proceed = confirm('テスト情報が未入力です、生徒情報のみ保存しますか');
        if (!proceed) {
          return;
        }
        // 成績の保存は行わず、画面のみ整える
        sortAndRedrawScoreTable();
        return;
      }

      // 保存前の状態を確認（更新か新規保存かを判定）
      const rowId = tr.getAttribute('data-row-id');
      const existingData = scoreData.find(item => item.rowId === rowId);
      const isUpdate = existingData && existingData.isSaved;

      // 実際の保存処理を実行
      saveScoreData(tr);

      // ローカルデータも更新
      if (existingData) {
        existingData.date = dateInput.value;
        existingData.type = typeSelect.value;
        existingData.scores = Array.from(tr.querySelectorAll('.score-input')).map(input => input.value);
        existingData.isSaved = true;
      } else {
        scoreData.push({
          rowId: rowId,
          date: dateInput.value,
          type: typeSelect.value,
          scores: Array.from(tr.querySelectorAll('.score-input')).map(input => input.value),
          isSaved: true
        });
      }

      // 日付順にソートして再描画
      sortAndRedrawScoreTable();
      // 保存後、実施日とテスト種別を変更不可にする
      dateInput.disabled = true;
      typeSelect.disabled = true;

      if (isUpdate) {
        alert('テスト情報を更新しました。');
      } else {
        alert('テスト情報を保存しました。');
      }
    } else {
      if (!dateInput.value.trim() || typeSelect.value === '未受験') {
        alert('未入力の項目があります。入力内容を確認してください。');
      }
    }
  }

  // 生徒情報の必須項目をチェックする関数
  function validateStudentInfo() {
    const requiredFields = [
      { id: 'last-name', name: '氏名(姓)' },
      { id: 'first-name', name: '氏名(名)' },
      { id: 'last-name-kana', name: '氏名(せい)' },
      { id: 'first-name-kana', name: '氏名(めい)' },
      { id: 'class-select', name: 'クラス' },
      { id: 'gender-select', name: '性別' },
      { id: 'class-number', name: 'クラス番号' },
      { id: 'birth-year', name: '生年月日(年)' },
      { id: 'birth-month', name: '生年月日(月)' },
      { id: 'birth-day', name: '生年月日(日)' }
    ];

    let isValid = true;

    requiredFields.forEach(field => {
      const element = document.getElementById(field.id);
      if (element && (!element.value.trim() || element.value === '')) {
        isValid = false;
      }
    });

    if (!isValid) {
      alert('生徒情報に未入力の項目があります');
    }

    return isValid;
  }

  // 成績データを保存する関数（Ajax）
  function saveScoreData(tr) {
    const rowId = tr.getAttribute('data-row-id');
    const dateInput = tr.querySelector('.score-date');
    const typeSelect = tr.querySelector('.score-type');
    const scoreInputs = tr.querySelectorAll('.score-input');

    // 要素の存在チェック
    if (!dateInput || !typeSelect || scoreInputs.length === 0) {
      console.error('必要な要素が見つかりません:', { dateInput, typeSelect, scoreInputs });
      return false;
    }

    const scores = {
      'english': scoreInputs[0].value || '0',
      'math': scoreInputs[1].value || '0',
      'japanese': scoreInputs[2].value || '0',
      'science': scoreInputs[3].value || '0',
      'social': scoreInputs[4].value || '0'
    };

    // フォームデータを準備
    const formData = new FormData();
    formData.append('action', 'save_score');
    formData.append('student_id', '1'); // 新規登録時は仮のID
    formData.append('test_date', dateInput.value);
    formData.append('test_type', typeSelect.value);
    formData.append('scores', JSON.stringify(scores));

    // リクエストでデータを送信
    fetch('student.sousa.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(data => {
      console.log('保存完了:', data);
    })
    .catch(error => {
      console.error('保存エラー:', error);
      alert('保存中にエラーが発生しました。');
    });

    return true;
  }

  // 成績データを削除する関数
  function deleteScoreData(tr) {
    if (!tr) {
      console.error('削除対象の要素が見つかりません');
      return;
    }

    const rowId = tr.getAttribute('data-row-id');
    const isExisting = tr.getAttribute('data-existing') === 'true';

    if (isExisting) {
      // 既存データの場合はサーバーに削除リクエストを送信
      const testId = rowId.replace('existing_', '');

      const formData = new FormData();
      formData.append('action', 'delete_score');
      formData.append('student_id', '1'); // 新規登録時は仮のID
      formData.append('test_id', testId);

      fetch('score.sousa.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        console.log('削除完了:', data);
        // ローカルデータからも削除
        const index = scoreData.findIndex(item => item.rowId === rowId);
        if (index !== -1) {
          scoreData.splice(index, 1);
          sortAndRedrawScoreTable();
        }
        // 削除
        try {
          const ids = JSON.parse(localStorage.getItem('deletedTestIds') || '[]');
          const numericId = parseInt(testId, 10);
          if (!ids.includes(numericId)) {
            ids.push(numericId);
            localStorage.setItem('deletedTestIds', JSON.stringify(ids));
          }
        } catch (_) {}
        alert('テスト情報を削除しました。');
      })
      .catch(error => {
        console.error('削除エラー:', error);
        alert('削除中にエラーが発生しました。');
      });
    } else {
      // 新規追加された行の場合は、ローカルデータから削除
      const index = scoreData.findIndex(item => item.rowId === rowId);
      if (index !== -1) {
        scoreData.splice(index, 1);
        sortAndRedrawScoreTable();
      }

      // DOMからも削除
      tr.remove();
      alert('テスト情報を削除しました。');
    }
  }

  // 成績テーブルを日付順にソートして再描画する関数
  function sortAndRedrawScoreTable() {
    // 日付の新しい順にソート
    scoreData.sort((a, b) => new Date(b.date) - new Date(a.date));

    // テーブル
    redrawAllScoreTable();
  }

  // 成績テーブル全体
  function redrawAllScoreTable() {
    scoreTableBody.innerHTML = '';

    scoreData.forEach(item => {
      if (!item.isSaved) return; // 未保存のデータは表示しない

      const tr = createScoreRow();
      if (!tr) { return; }
      tr.setAttribute('data-row-id', item.rowId);

      // 既存データの場合はマークを付ける
      if (item.isExisting) {
        tr.setAttribute('data-existing', 'true');
      }

      const dateInput = tr.querySelector('.score-date');
      const typeSelect = tr.querySelector('.score-type');
      const scoreInputs = tr.querySelectorAll('.score-input');

      if (dateInput) dateInput.value = item.date;
      if (typeSelect) typeSelect.value = item.type;

      item.scores.forEach((score, index) => {
        if (scoreInputs[index]) {
          scoreInputs[index].value = score;
        }
      });

      calcScore(tr);

      // 保存済みの場合は日付とテスト種別を無効化
      if (item.isSaved) {
        if (dateInput) dateInput.disabled = true;
        if (typeSelect) typeSelect.disabled = true;
      }

      scoreTableBody.appendChild(tr);

      // 新規追加された行のみにイベントリスナーを設定
      if (!item.isExisting) {
        attachEventListenersToNewRow(tr);
      }
    });
  }


  function redrawScoreTable() {
    const newRows = scoreTableBody.querySelectorAll('tr:not([data-existing="true"])');
    newRows.forEach(row => row.remove());
    scoreData.forEach(item => {
      if (!item.isSaved || item.isExisting) return; // 未保存または既存データはスキップ

      const tr = createScoreRow();
      if (!tr) { return; }
      tr.setAttribute('data-row-id', item.rowId);

      const dateInput = tr.querySelector('.score-date');
      const typeSelect = tr.querySelector('.score-type');
      const scoreInputs = tr.querySelectorAll('.score-input');

      if (dateInput) dateInput.value = item.date;
      if (typeSelect) typeSelect.value = item.type;

      item.scores.forEach((score, index) => {
        if (scoreInputs[index]) {
          scoreInputs[index].value = score;
        }
      });

      calcScore(tr);

      if (item.date && item.date.trim() !== '') {
        if (dateInput) dateInput.disabled = true;
      }

      scoreTableBody.appendChild(tr);

      // 新規追加された行にイベントリスナーを設定
      attachEventListenersToNewRow(tr);
    });
  }

  // 新規追加された行にイベントリスナーを設定
  function attachEventListenersToNewRow(tr) {
    if (!tr) {
      console.error('イベントリスナー設定対象の要素が見つかりません');
      return;
    }

    // テスト種別変更時 - 未受験を選んだら点数を0にする
    const typeSelect = tr.querySelector('.score-type');
    if (typeSelect) {
      typeSelect.addEventListener('change', function() {
        if (this.classList.contains('select-error')) {
          this.classList.remove('select-error');
        }
        if (this.value === '未受験') {
          tr.querySelectorAll('.score-input').forEach(input => {
            input.value = '0';
          });
          calcScore(tr);
        }
      });
    }

    // 削除ボタン - 確認してから削除
    const deleteBtn = tr.querySelector('.score-delete');
    if (deleteBtn && !deleteBtn.hasAttribute('data-listener-attached')) {
      deleteBtn.addEventListener('click', (e) => {
        e.preventDefault(); // フォームの送信を防ぐ
        if (confirm('本当に削除しますか？')) {
          deleteScoreData(tr);
        }
      });
      deleteBtn.setAttribute('data-listener-attached', 'true');
    }

    // 保存ボタン - チェックしてから保存
    const saveBtn = tr.querySelector('.score-save');
    if (saveBtn && !saveBtn.hasAttribute('data-listener-attached')) {
      saveBtn.addEventListener('click', (e) => {
        e.preventDefault(); // フォームの送信を防ぐ
        validateAndSaveScore(tr);
      });
      saveBtn.setAttribute('data-listener-attached', 'true');
    }
  }

  // 成績バリデーションエラーリセット
  function resetScoreValidationErrors(tr) {
    const errorElements = tr.querySelectorAll('.input-error, .select-error');
    errorElements.forEach(element => {
      element.classList.remove('input-error', 'select-error');
    });
  }


  // 8. イベント
  // ログアウト処理 - 確認してからログアウト
  document.getElementById('logout-logo').addEventListener('click', function() {
    if (confirm('ログアウトしますか？')) {
      document.getElementById('logout-form').submit();
    }
  });

  // 写真選択ボタン
  photoBtn.addEventListener('click', () => photoInput.click());
  photoInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      handlePhotoUpload(e.target.files[0]);
    }
  });

  // ログアウトボタン - 確認してからログアウト
  logoutBtn.addEventListener('click', () => {
    if (confirm('ログアウトしますか？')) {
      document.getElementById('logout-form').submit();
    }
  });
  // 検索
  searchBtn.addEventListener('click', searchStudents);
  searchInput.addEventListener('input', searchStudents);

  // 登録処理
  function submitForm(e) {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    // テスト情報チェック
    const rows = document.querySelectorAll('#score-table-body tr');
    let hasTest = false;
    for (let i = 0; i < rows.length; i++) {
      const tr = rows[i];
      const dateInput = tr.querySelector('.score-date');
      const typeSelect = tr.querySelector('.score-type');
      const scoreInputs = tr.querySelectorAll('.score-input');
      if (!dateInput || !typeSelect || scoreInputs.length === 0) continue;

      let hasScore = false;
      for (let j = 0; j < scoreInputs.length; j++) {
        if (scoreInputs[j].value && scoreInputs[j].value.trim() !== '') {
          hasScore = true;
          break;
        }
      }

      if (dateInput.value.trim() && typeSelect.value !== '未受験' && hasScore) {
        hasTest = true;
        break;
      }
    }

    if (!hasTest) {
      const ok = confirm('テスト情報が未入力です、生徒情報のみ保存しますか');
      if (!ok) {
        return;
      }
    }

    // フォームを実際に送信（データベースに保存される）
    studentForm.submit();
  }

  studentForm.addEventListener('submit', submitForm);
  const registerBtn = document.getElementById('register-btn');
  if (registerBtn) {
    registerBtn.addEventListener('click', submitForm);
  }

  studentTableBody.addEventListener('click', e => {
    if (e.target.classList.contains("delete")) {
      if (confirm("本当に削除しますか？\nこの生徒のすべての情報（基本情報、テスト成績、写真など）が完全に削除されます。")) {
        const row = e.target.closest("tr");
        const rowIndex = Array.from(studentTableBody.children).indexOf(row);
        const studentIndex = (currentPage - 1) * itemsPerPage + rowIndex;
        const student = filteredStudents[studentIndex];

        if (student) {
          // 生徒IDを取得
          const studentId = student[0];

          // 削除フォームに生徒IDを設定して送信
          document.getElementById('delete-student-id').value = studentId;
          document.getElementById('delete-student-form').submit();
        }
      }
    } else if (e.target.classList.contains("detail")) {
      const row = e.target.closest("tr");
      const rowIndex = Array.from(studentTableBody.children).indexOf(row);
      const studentIndex = (currentPage - 1) * itemsPerPage + rowIndex;
      const student = filteredStudents[studentIndex];
      if (student) {
        window.location.href = `student_detail.php?id=${student[0]}`;
      }
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

  // 入力時のエラー解除
  function setupInputValidation() {
    const inputFields = document.querySelectorAll('#student-register-form input, #student-register-form select');
    inputFields.forEach(field => {
      field.addEventListener('input', function() {
        if (this.classList.contains('input-error') || this.classList.contains('select-error')) {
          this.classList.remove('input-error', 'select-error');
        }
        // すべてのフィールドが入力済みの場合、エラーメッセージを非表示
        if (validateForm()) {
          validationError.classList.add('hidden');
        }
      });
    });
  }


  // 9. 初期化
  function init() {
    // PHPから生徒データを取得
    loadStudentsFromPHP();

    // URLパラメータからページ番号を取得
    const urlParams = new URLSearchParams(window.location.search);
    const pageParam = urlParams.get('page');
    if (pageParam) {
      const pageNum = parseInt(pageParam, 10);
      if (pageNum > 0 && pageNum <= totalPages()) {
        currentPage = pageNum;
      }
    } else {
      // ページパラメータがない場合、最後のページに移動（新規登録後の場合）
      const isNewRegistration = urlParams.get('new') === '1';
      if (isNewRegistration && totalPages() > 0) {
        currentPage = totalPages();
      }
    }

    renderTable(currentPage);
    renderPageButtons();
    setupInputValidation();

    // 初期行を3つ用意
    for (let i = 0; i < 3; i++) {
      const tr = createScoreRow();
      scoreTableBody.appendChild(tr);

      // 成績データに追加
      const rowId = tr.getAttribute('data-row-id');
      scoreData.push({
        rowId: rowId,
        date: '',
        type: '未受験',
        scores: ['', '', '', '', ''],
        isSaved: false
      });

      // 初期行にもイベントリスナーを設定
      attachEventListenersToNewRow(tr);
    }

  }
  init();
});
