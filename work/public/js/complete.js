/*1. ログアウト機能*/

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById('logout-logo').addEventListener('click', function() {
    if (confirm('ログアウトしますか？')) {
      document.getElementById('logout-form').submit();
    }
  });
});


/*１行解説*/

/* ページのHTML要素がすべて読み込まれたタイミングで、
中の処理（ログアウト機能）を実行できるようにする*/

/* id="logout-logo" をクリックしたときに
処理を実行するためのクリックイベントを設定 */

/* ブラウザの確認ダイアログを表示。
「OK」が押された場合だけ true になり、次の処理へ進む */

/* id="logout-form" のフォームを送信。
サーバー側のログアウト処理が実行される */

/*※見返し用
ログアウトロゴ（画像）をクリックすると「ログアウトしますか」と聞かれる。
「はい」をクリックするとログアウトし、「キャンセル」をクリックするとログアウトしない。
「はい」をクリックすると処理を行う。
「キャンセル」ならば何もせずダイアログを閉じる*/