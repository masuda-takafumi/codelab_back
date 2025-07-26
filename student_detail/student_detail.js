/**
 * 生徒管理システム - 生徒詳細画面 JS
 * 目次:
 *   1. DOM要素取得
 *   2. プルダウン生成
 *   3. 成績テーブル操作
 *   4. イベントリスナー
 *   5. 初期化
 */

document.addEventListener("DOMContentLoaded", function () {
  // 1. DOM要素取得
  const logoutBtn = document.getElementById("logout-btn");
  const scoreTableBody = document.getElementById('score-table-body');
  const addScoreBtn = document.getElementById('add-score-btn');

  // 2. プルダウン生成
  function fillSelectOptions() {
    const createOptions = (id, start, end, placeholder) => {
      const select = document.getElementById(id);
      if (select) {
        let optionsHtml = `<option value=\"\">${placeholder}</option>`;
        for (let i = start; i <= end; i++) {
          optionsHtml += `<option value=\"${i}\">${i}</option>`;
        }
        select.innerHTML = optionsHtml;
      }
    };
    createOptions('class-number', 1, 30, 'クラス番号');
    createOptions('birth-year', 1990, 2020, '年');
    createOptions('birth-month', 1, 12, '月');
    createOptions('birth-day', 1, 31, '日');
  }

  // 3. 成績テーブル操作
  const testTypes = ['期末試験', '中間試験', 'その他'];
  function createScoreRow() {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="date" class="score-date" style="width:120px;"></td>
      <td>
        <select class="score-type">${testTypes.map(type => `<option value=\"${type}\">${type}</option>`).join('')}</select>
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
    tr.querySelector('.score-save').addEventListener('click', () => alert('保存しました（仮）'));
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

  // 4. イベントリスナー
  logoutBtn.addEventListener('click', () => { window.location.href = '../login/login.html'; });
  addScoreBtn.addEventListener('click', () => scoreTableBody.appendChild(createScoreRow()));
  document.getElementById('logout-logo').addEventListener('click', function() {
    window.location.href = '../login/login.html';
  });

  // 5. 初期化
  function init() {
    fillSelectOptions();
  }
  init();
});
