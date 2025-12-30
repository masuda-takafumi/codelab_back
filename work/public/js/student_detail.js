/*
生徒詳細画面の機能

1. 要素取得
2. データ管理
3. 成績行作成・管理
4. 点数計算
5. 入力チェック
6. データ保存
7. データ削除（物理削除）
8. 成績テーブル再描画
9. イベントリスナー設定
10. 写真アップロード
11. フォーム処理
12. 初期化
*/

document.addEventListener("DOMContentLoaded", function () {
  /* ページのHTML要素がすべて読み込まれたタイミングで
各種ボタンやフォームなどの要素を取得し、
後続の処理（イベント設定・入力チェック等）に備える */

  // 1. 要素取得
  const logoutBtn = document.getElementById("logout-btn");
  const scoreTableBody = document.getElementById('score-table-body');
  const photoBtn = document.getElementById('photo-btn');
  const photoInput = document.getElementById('photo-input');
  const studentPhoto = document.getElementById('student-photo');
  const photoError = document.getElementById('photo-error');
  const studentForm = document.getElementById('student-register-form');
  const validationError = document.getElementById('validation-error');

  // 2. データ管理
  let scoreData = [];
  /* 成績データを格納するための配列を初期化。
  この配列に科目名・点数などを追加していき、
  画面への表示や送信時に利用する */


  // 既存の成績データをローカル配列に追加
  const existingScores = window.existingScores || []; /* サーバー側などから埋め込まれた既存の成績データを取得。window.existingScores が存在しない場合は空配列をセットしてエラーを防ぐ */
  existingScores.forEach(score => {  /* 既存の成績データを1件ずつ取り出して処理する */
    scoreData.push({
      rowId: 'existing_' + score.test_id,
      testId: score.test_id,
      date: score.test_date,
      type: score.test_type,
      scores: [score.english ?? '0', score.math ?? '0', score.japanese ?? '0', score.science ?? '0', score.social ?? '0'],
      isSaved: true,
      isExisting: true
    });
  });


  // ページ読み込み時に既存の成績データを日付順で表示
  if (scoreData.length > 0) {
    scoreData.sort((a, b) => new Date(b.date) - new Date(a.date)); // 降順（新しい日付から古い日付）
    redrawAllScoreTable();
  }


  // 3. 成績行作成・管理
  function createScoreRow() {
    const template = document.getElementById('score-row-template');
    const tr = template.content.cloneNode(true);
    const rowId = Date.now() + Math.random();
    const trElement = tr.querySelector('tr');
    trElement.setAttribute('data-row-id', rowId);


// 点数入力時 - 入力したら自動で合計・平均を計算
    const scoreInputs = trElement.querySelectorAll('.score-input');
    if (!scoreInputs.length) return trElement;

    scoreInputs.forEach(input => {
      input.addEventListener('input', () => {
        calcScore(trElement);
        input.classList.remove('input-error');
      });
    });

    // 実施日入力時 - エラー表示を消す
    const dateInput = trElement.querySelector('.score-date');
    if (dateInput) {
      dateInput.addEventListener('input', function() {
        this.classList.remove('input-error');
      });
    }

    // イベントリスナーを設定
    attachEventListenersToNewRow(trElement);

    return trElement;
  }


  // 4. 点数計算
  function calcScore(tr) {
    const inputs = Array.from(tr.querySelectorAll('.score-input'));
    const nums = inputs.map(i => parseInt(i.value, 10)).filter(n => !isNaN(n));
    const sum = nums.reduce((a, b) => a + b, 0);
    const avg = nums.length ? (sum / nums.length).toFixed(1) : '';
    tr.querySelector('.score-sum').value = nums.length ? sum : '';
    tr.querySelector('.score-avg').value = nums.length ? avg : '';
  }


  // 5. 入力チェック
  function validateAndSaveScore(tr) {
    resetScoreValidationErrors(tr);

    const dateInput = tr.querySelector('.score-date');
    const typeSelect = tr.querySelector('.score-type');
    const scoreInputs = tr.querySelectorAll('.score-input');



    // 要素が見つからない場合はエラー
    if (!dateInput || !typeSelect || scoreInputs.length === 0) {
      console.error('必要な要素が見つかりません');
      alert('エラー: 必要な要素が見つかりません');
      return;
    }

    let isValid = true;

    // 実施日が入力されているかチェック - 必須項目の確認
    if (!dateInput.value.trim()) {
      dateInput.classList.add('input-error');
      isValid = false;
    }

    // 種別が「未受験」の場合、種別プルダウンを赤く表示
    if (typeSelect.value === '未受験') {
      typeSelect.classList.add('select-error');
      isValid = false;
    }

    // 生徒情報の必須項目をチェック - 全体の整合性確認
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

      sortAndRedrawScoreTable();
      dateInput.disabled = true;
      typeSelect.disabled = true;

      if (isUpdate) {
        alert('テスト情報を更新しました。');
      } else {
        alert('テスト情報を保存しました。');
      }
    } else {
      // 入力不足アラート
      alert('未入力の項目があります。入力内容を確認してください。');
    }
  }

  // 生徒情報の必須項目をチェック
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

    return isValid;
  }


  // 6. データ保存
  function saveScoreData(tr) {
    const rowId = tr.getAttribute('data-row-id');
    const testId = tr.getAttribute('data-test-id');
    const dateInput = tr.querySelector('.score-date');
    const typeSelect = tr.querySelector('.score-type');
    const scoreInputs = tr.querySelectorAll('.score-input');

    const urlParams = new URLSearchParams(window.location.search);
    const studentId = urlParams.get('id');

    // test_idが必須（既存のテストのみ更新可能）
    if (!testId) {
      alert('既存のテストのみ更新できます。');
      return false;
    }

    // 現在の生徒情報を取得
    const currentStudentData = {
      class: document.getElementById('class-select').value,
      class_no: document.getElementById('class-number').value,
      last_name: document.getElementById('last-name').value,
      first_name: document.getElementById('first-name').value,
      last_name_kana: document.getElementById('last-name-kana').value,
      first_name_kana: document.getElementById('first-name-kana').value,
      gender: document.getElementById('gender-select').value,
      birth_year: document.getElementById('birth-year').value,
      birth_month: document.getElementById('birth-month').value,
      birth_day: document.getElementById('birth-day').value
    };

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
    formData.append('student_id', studentId);
    formData.append('test_id', testId);
    formData.append('test_date', dateInput.value);
    formData.append('test_type', typeSelect.value);
    formData.append('scores', JSON.stringify(scores));

    // 現在の生徒情報を追加
    Object.keys(currentStudentData).forEach(key => {
      formData.append(key, currentStudentData[key]);
    });

    //データを送信
    fetch('student.sousa.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(data => {

    })
    .catch(error => {
      console.error('保存エラー:', error);
      alert('保存中にエラーが発生しました。');
    });

    return true;
  }

  // 7. データ削除（物理削除）
  function deleteScoreData(tr) {
    const rowId = tr.getAttribute('data-row-id');
    const isExisting = tr.getAttribute('data-existing') === 'true';

    if (isExisting) {
      // 既存データの場合はサーバーに物理削除リクエストを送信
      const urlParams = new URLSearchParams(window.location.search);
      const studentId = urlParams.get('id');
      // test_id を取得
      const testId = rowId.replace('existing_', '');

      // 削除前にテスト情報を取得（削除後も0点で表示するため）
      const dateInput = tr.querySelector('.score-date');
      const typeSelect = tr.querySelector('.score-type');
      const testDate = dateInput ? dateInput.value : '';
      const testType = typeSelect ? typeSelect.value : '';

      const formData = new FormData();
      formData.append('action', 'delete_score');
      formData.append('student_id', studentId);
      formData.append('test_id', testId);

      fetch('student.sousa.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // 削除後、ページをリロードしてデータベースの状態を反映（0点表示になる）
        alert('成績を削除しました。全科目0点で表示されます。');
        window.location.reload();
      })
      .catch(error => {
        console.error('削除エラー:', error);
        alert('削除中にエラーが発生しました。');
      });
    } else {
      // 新規行削除
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

  // 成績テーブルを日付順にソートして再描画
  function sortAndRedrawScoreTable() {
    scoreData.sort((a, b) => new Date(b.date) - new Date(a.date)); // 降順（新しい日付から古い日付）
    redrawAllScoreTable();
    // 再描画後に既存行へイベントを再付与
    attachEventListenersToExistingRows();
  }

  // 成績テーブル全体を再描画（既存データも含む）
  function redrawAllScoreTable() {
    scoreTableBody.innerHTML = '';

    scoreData.forEach(item => {
      if (!item.isSaved) return; // 未保存のデータは表示しない

      const tr = createScoreRow();
      tr.setAttribute('data-row-id', item.rowId);

      // 既存データの場合はマークを付ける
      if (item.isExisting) {
        tr.setAttribute('data-existing', 'true');
        if (item.testId) {
          tr.setAttribute('data-test-id', item.testId);
        }
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
    
    // 既存行（削除されたテストも含む）にイベントリスナーを設定
    attachEventListenersToExistingRows();
  }

  // 成績テーブルを再描画（新規追加分のみ）
  function redrawScoreTable() {
    // 新規追加された行のみを再描画（既存の成績行は保持）
    const newRows = scoreTableBody.querySelectorAll('tr:not([data-existing="true"])');
    newRows.forEach(row => row.remove());

    // 新規行再描画
    scoreData.forEach(item => {
      if (!item.isSaved || item.isExisting) return;

      const tr = createScoreRow();
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

  // エラー表示をリセット
  function resetScoreValidationErrors(tr) {
    const errorElements = tr.querySelectorAll('.input-error', '.select-error');
    errorElements.forEach(element => {
      element.classList.remove('input-error', 'select-error');
    });
  }

  // 新規追加された行にイベントリスナーを設定
  function attachEventListenersToNewRow(tr) {
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
        if (confirm('成績を削除します。全科目0点となりますがよろしいですか？')) {
          deleteScoreData(tr);
        }
      });
      deleteBtn.setAttribute('data-listener-attached', 'true');
    }

    // 保存ボタン - チェックしてから保存
    const saveBtn = tr.querySelector('.score-save');
    if (saveBtn) {
      saveBtn.addEventListener('click', (e) => {
        e.preventDefault(); // フォームの送信を防ぐ
        validateAndSaveScore(tr);
      });
    }
  }

  // 既存の成績行にイベントリスナーを設定
  function attachEventListenersToExistingRows() {
    const existingRows = document.querySelectorAll('#score-table-body tr[data-existing="true"]');


    existingRows.forEach((tr, index) => {


      // テスト種別変更時の処理（未受験で赤色表示）
      const typeSelect = tr.querySelector('.score-type');
      if (typeSelect && !typeSelect.hasAttribute('data-listener-attached')) {
        typeSelect.addEventListener('change', function() {
          if (this.classList.contains('select-error')) {
            this.classList.remove('select-error');
          }
          if (this.value === '未受験') {
            this.classList.add('select-error');
            tr.querySelectorAll('.score-input').forEach(input => {
              input.value = '0';
            });
            calcScore(tr);
          }
        });
        typeSelect.setAttribute('data-listener-attached', 'true');
      }

      // 点数入力時の処理
      tr.querySelectorAll('.score-input').forEach(input => {
        if (!input.hasAttribute('data-listener-attached')) {
          input.addEventListener('input', () => {
            calcScore(tr);
            if (input.classList.contains('input-error')) {
              input.classList.remove('input-error');
            }
          });
          input.setAttribute('data-listener-attached', 'true');
        }
      });

      // 保存ボタン
      const saveBtn = tr.querySelector('.score-save');
      if (saveBtn && !saveBtn.hasAttribute('data-listener-attached')) {
        saveBtn.addEventListener('click', (e) => {
          e.preventDefault();
          validateAndSaveScore(tr);
        });
        saveBtn.setAttribute('data-listener-attached', 'true');
      }

      // 削除ボタン
      const deleteBtn = tr.querySelector('.score-delete');
      if (deleteBtn && !deleteBtn.hasAttribute('data-listener-attached')) {
        deleteBtn.addEventListener('click', (e) => {
          e.preventDefault();
          if (confirm('成績を削除します。全科目0点となりますがよろしいですか？')) {
            deleteScoreData(tr);
          }
        });
        deleteBtn.setAttribute('data-listener-attached', 'true');
      }
    });
  }

  // ログアウトボタンの処理
  logoutBtn.addEventListener('click', () => { window.location.href = 'logout.php'; });

  // 既存の行にイベントリスナーを設定
  attachEventListenersToExistingRows();

  // 既存の行で「未受験」が選択されている場合は赤色表示
  function highlightUnselectedTests() {
    const existingRows = document.querySelectorAll('#score-table-body tr[data-existing="true"]');
    existingRows.forEach(tr => {
      const typeSelect = tr.querySelector('.score-type');
      if (typeSelect && typeSelect.value === '未受験') {
        typeSelect.classList.add('select-error');
      }
    });
  }

  // 初期状態で「未受験」を赤色表示
  highlightUnselectedTests();


  // 8. 写真アップロード
  photoBtn.addEventListener('click', () => {
    photoInput.click();
  });

  // ファイル選択時の処理
  photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      handlePhotoUpload(file);
    }
  });

  // 写真アップロード処理
  function handlePhotoUpload(file) {
    hidePhotoError();

    // ファイルサイズチェック（3MB制限）
    if (file.size > 3 * 1024 * 1024) {
      showPhotoError('ファイルサイズは3MB以下にしてください。');
      return;
    }

    // ファイル形式チェック（JPEGのみ許可）
    if (!file.type.match('image/jpeg') && !file.type.match('image/jpg')) {
      showPhotoError('JPEG形式のファイルを選択してください。');
      return;
    }

    // 画像を表示
    const reader = new FileReader();
    reader.onload = function(e) {
      studentPhoto.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  // 写真エラーメッセージ表示
  function showPhotoError(message) {
    photoError.textContent = message;
    photoError.classList.remove('hidden');
  }

  // 写真エラーメッセージ非表示
  function hidePhotoError() {
    photoError.classList.add('hidden');
  }


  // 9. フォーム処理
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

    resetValidationErrors();

    requiredFields.forEach(field => {
      const element = document.getElementById(field.id);
      if (!element.value.trim()) {
        isValid = false;
        showFieldError(element, field.type);
      }
    });

    if (!isValid) {
      validationError.classList.remove('hidden');
    } else {
      validationError.classList.add('hidden');
    }

    return isValid;
  }

  // エラー表示 - 入力欄を赤くする
  function showFieldError(element, type) {
    if (type === 'input') {
      element.classList.add('input-error');
    } else if (type === 'select') {
      element.classList.add('select-error');
    }
  }

  // エラーリセット - エラー表示を消す
  function resetValidationErrors() {
    const errorElements = document.querySelectorAll('.input-error, .select-error');
    errorElements.forEach(element => {
      element.classList.remove('input-error', 'select-error');
    });
    validationError.classList.add('hidden');
  }

  // フォーム送信 - チェックOKなら完了画面へ
  studentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (validateForm()) {
      // 登録ボタン押下時、テスト情報が1件も入力されていない場合の確認
      const rows = document.querySelectorAll('#score-table-body tr');
      let hasMeaningfulTest = false;
      rows.forEach(tr => {
        const dateInput = tr.querySelector('.score-date');
        const typeSelect = tr.querySelector('.score-type');
        const scoreInputs = tr.querySelectorAll('.score-input');
        if (!dateInput || !typeSelect || scoreInputs.length === 0) return;
        const anyScoreFilled = Array.from(scoreInputs).some(input => input.value && input.value.trim() !== '');
        if (dateInput.value.trim() && typeSelect.value !== '未受験' && anyScoreFilled) {
          hasMeaningfulTest = true;
        }
      });
      if (!hasMeaningfulTest) {
        const proceed = confirm('テスト情報が未入力です、生徒情報のみ保存しますか');
        if (!proceed) {
          return;
        }
      }
      // バリデーションが通った場合の処理
      // complete.phpに遷移
      window.location.href = 'complete.php';
    }
  });

  // ログアウト処理 - 確認してからログアウト
  document.getElementById('logout-logo').addEventListener('click', function() {
    if (confirm('ログアウトしますか？')) {
      document.getElementById('logout-form').submit();
    }
  });

  //入力したらエラーを消す
  function setupInputValidation() {
    const inputFields = document.querySelectorAll('#student-register-form input, #student-register-form select');
    inputFields.forEach(field => {
      field.addEventListener('input', function() {
        // 入力時にエラー表示をリセット
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


  // 10. 初期化
  function init() {
    setupInputValidation();     // リアルタイムバリデーションを設定
  }
  init();
});