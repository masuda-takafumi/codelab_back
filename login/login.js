/**
 * 生徒管理システム - ログイン画面 JS
 * 目次:
 *   1. DOM要素取得
 *   2. イベントリスナー
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. DOM要素取得
  const form = document.getElementById('loginForm');

  // 2. イベントリスナー
  form.addEventListener('submit', (e) => {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    if (!email || !password) {
      e.preventDefault();
      alert('メールアドレスとパスワードを入力してください。');
    }
  });
});