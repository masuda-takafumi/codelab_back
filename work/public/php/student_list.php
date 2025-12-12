<!--
生徒一覧画面

1. PHP処理
   1.1 共通関数読み込み
   1.2 ログイン確認
   1.3 ログアウト処理
   1.4 生徒データ取得
2. HTML構造
   2.1 ヘッダー
   2.2 タブナビゲーション
   2.3 生徒一覧タブ
   2.4 検索機能
   2.5 生徒テーブル
   2.6 新規登録タブ
   2.7 写真アップロード
   2.8 基本情報入力
   2.9 成績一覧テーブル
   2.10 テンプレート
   2.11 隠しフォーム
   2.12 フッター
3. スクリプト読み込み
-->
<?php
// 1. PHP処理 - ログイン確認と生徒データの準備
// 1.1 共通関数読み込み - データベース接続とか使う
require_once '/work/app/config.php';
require_once '/work/app/core.php';

// 1.2 ログイン確認 - ログインしてないといけない
if (!Utils::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}



// 1.3 ログアウト処理 - ログアウトボタンが押されたら処理
checkLogoutRequest();



// 1.4 生徒一覧データの取得 - 全生徒の情報を表示するため
$students = [];
try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT * FROM students ORDER BY class, class_no");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
}
?>



<!-- 2. HTML構造 - 生徒一覧画面の表示 -->
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>生徒管理システム - 生徒一覧</title>
  <link rel="stylesheet" href="../css/student_list.css">
</head>
<body>
  <!-- 2.1 ヘッダー - ロゴとタイトル -->
  <header>
    <?php echo generateHeader(); ?>
  </header>

  <main>
    <!-- 2.2 タブナビゲーション - 画面を切り替える -->
    <nav class="tabs">
      <button class="tab active" id="tab-list">生徒一覧</button>
      <button class="tab" id="tab-register">新規生徒登録</button>
    </nav>
    <!-- 2.3 生徒一覧タブ - 生徒の一覧を表示 -->
    <section id="tab-content-list" class="tab-content">
      <!-- 2.4 検索機能 - 生徒を探しやすくする -->
      <section class="search-box">
        <input type="text" id="search-name" placeholder="生徒名（漢字・かな）">
        <button id="search-btn">検索</button>
      </section>
      <!-- 2.5 生徒テーブル - 生徒の情報を表形式で表示 -->
      <table>
        <thead>
          <tr>
            <th>クラス</th>
            <th>クラス番号</th>
            <th>性別</th>
            <th>生年月日</th>
            <th>氏名</th>
            <th>かな</th>
            <th>詳細</th>
            <th>削除</th>
          </tr>
        </thead>
        <tbody id="student-table-body">
          <?php foreach ($students as $student): ?>
            <tr>
              <td><?php echo htmlspecialchars($student['class']); ?></td>
              <td><?php echo htmlspecialchars($student['class_no']); ?></td>
              <td><?php echo $student['gender'] == 1 ? '男' : '女'; ?></td>
              <td><?php echo htmlspecialchars($student['birth_date']); ?></td>
              <td><?php echo htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?></td>
              <td><?php echo htmlspecialchars($student['last_name_kana'] . ' ' . $student['first_name_kana']); ?></td>
              <td><a href="/php/student_detail.php?id=<?php echo $student['id']; ?>" class="detail-link">詳細</a></td>
              <td><button type="button" class="delete-btn" data-student-id="<?php echo $student['id']; ?>">削除</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>

        <!-- 2.10 テンプレート - 新しい生徒行を追加するためのテンプレート -->
        <template id="student-row-template">
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><button class="action detail">詳細</button></td>
            <td><button class="action delete">削除</button></td>
          </tr>
        </template>
      </table>
      <div class="pagination" id="pege-btn"></div>

      <!-- 2.11 生徒削除 -->
      <form id="delete-student-form" method="POST" action="student.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="delete_student">
        <input type="hidden" name="student_id" id="delete-student-id">
      </form>
    </section>

    <!-- 2.6 新規生徒登録タブ - 新しい生徒を登録する -->
    <section id="tab-content-register" class="hidden">
      <form id="student-register-form" method="POST" action="student.sousa.php" enctype="multipart/form-data">
        <fieldset class="register-area">
            <legend class="hidden">新規生徒登録フォーム</legend>
            <input type="hidden" name="action" value="register_student">
          <!-- 2.7 写真アップロード - 生徒の写真を設定 -->
          <section class="photo-upload">
            <img id="student-photo" src="../img/ダミー生徒画像.png" alt="写真" class="photo-preview">
            <input type="file" id="photo-input" accept="image/jpeg,image/jpg" class="hidden">
            <button type="button" id="photo-btn">写真を挿入</button>
            <div id="photo-error" class="photo-error hidden"></div>
          </section>
          <!-- 2.8 基本情報入力 - 名前やクラスなどの情報を入力 -->
          <section class="info-input">
            <div class="row">
              <label for="last-name">氏名(姓)</label>
              <input type="text" id="last-name" name="last_name" placeholder="氏名(姓)" required>
              <label for="first-name">氏名(名)</label>
              <input type="text" id="first-name" name="first_name" placeholder="氏名(名)" required>
            </div>
            <div class="row">
              <label for="last-name-kana">氏名(せい)</label>
              <input type="text" id="last-name-kana" name="last_name_kana" placeholder="氏名(せい)" required pattern="[ぁ-ん]+">
              <label for="first-name-kana">氏名(めい)</label>
              <input type="text" id="first-name-kana" name="first_name_kana" placeholder="氏名(めい)" required pattern="[ぁ-ん]+">
            </div>
            <div class="row">
              <label for="class-select">クラス</label>
              <select id="class-select" name="class" required>
                <?php echo generateClassOptions(); ?>
              </select>
              <label for="gender-select">性別</label>
              <select id="gender-select" name="gender" required>
                <?php echo generateGenderOptions(); ?>
              </select>
            </div>
            <div class="row">
              <label for="class-number">クラス番号</label>
              <select id="class-number" name="class_no" required>
                <?php echo generateSelectOptions(1, 31, '', 'クラス番号'); ?>
              </select>
            </div>
            <div class="row">
              <label for="birth-year">生年月日</label>
              <select id="birth-year" name="birth_year" required>
                <?php echo generateSelectOptions(1990, 2020, '', '年'); ?>
              </select>
              <span>年</span>
              <select id="birth-month" name="birth_month" required>
                <?php echo generateSelectOptions(1, 12, '', '月'); ?>
              </select>
              <span>月</span>
              <select id="birth-day" name="birth_day" required>
                <?php echo generateSelectOptions(1, 31, '', '日'); ?>
              </select>
              <span>日</span>
              <button type="submit" id="register-btn">登録</button>
            </div>
             <!-- バリデーションエラーメッセージ -->
        <div id="validation-error" class="validation-error hidden">
          未入力の項目があります
        </div>
          </section>
        </fieldset>
      </form>
      <!-- 2.9 成績一覧テーブル - テスト結果を表示 -->
      <section class="score-area">
        <h2>成績一覧</h2>
        <table id="score-table">
          <thead>
            <tr>
              <th>実施日</th>
              <th>種別</th>
              <th>国語</th>
              <th>数学</th>
              <th>英語</th>
              <th>理科</th>
              <th>社会</th>
              <th>平均</th>
              <th>合計</th>
              <th>保存</th>
              <th>削除</th>
            </tr>
          </thead>
          <tbody id="score-table-body">
            <!-- JSで行追加 -->
          </tbody>
        </table>
        <button type="button" id="add-score-btn">＋テスト情報を追加する</button>
      </section>
      <!-- 3. 戻るリンク -->
      <div class="back-list-wrapper">
        <a href="/php/student_list.php" class="back-list">←生徒一覧に戻る</a>
      </div>

      <!-- 2.11 隠しフォーム - 成績の保存と削除用 -->
      <form id="score-form" method="POST" action="student.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="save_score">
        <input type="hidden" name="student_id" id="score-student-id">
        <input type="hidden" name="test_date" id="score-test-date">
        <input type="hidden" name="test_type" id="score-test-type">
        <input type="hidden" name="scores" id="score-scores">
      </form>

      <form id="score-delete-form" method="POST" action="score.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="delete_score">
        <input type="hidden" name="student_id" id="score-delete-student-id">
        <input type="hidden" name="test_id" id="score-delete-test-id">
      </form>

      <!-- 2.10 テンプレート - 新しい成績行を追加するためのテンプレート -->
      <template id="score-row-template">
        <tr>
          <td><input type="date" class="score-date" style="width:120px;"></td>
          <td>
            <select class="score-type">
              <option value="未受験">未受験</option>
              <option value="期末試験">期末試験</option>
              <option value="中間試験">中間試験</option>
            </select>
          </td>
          <td><input type="number" class="score-input" min="0" max="100" style="width:60px;" placeholder="0"></td>
          <td><input type="number" class="score-input" min="0" max="100" style="width:60px;" placeholder="0"></td>
          <td><input type="number" class="score-input" min="0" max="100" style="width:60px;" placeholder="0"></td>
          <td><input type="number" class="score-input" min="0" max="100" style="width:60px;" placeholder="0"></td>
          <td><input type="number" class="score-input" min="0" max="100" style="width:60px;" placeholder="0"></td>
          <td><input type="text" class="score-avg" readonly style="width:60px;"></td>
          <td><input type="text" class="score-sum" readonly style="width:60px;"></td>
          <td><button type="button" class="score-save action">保存</button></td>
          <td><button type="button" class="score-delete action">削除</button></td>
        </tr>
      </template>
    </section>
  </main>

  <!-- 2.12 フッター - コピーライト -->
  <footer>
    <?php echo generateFooter(); ?>
  </footer>

  <?php echo generateLogoutForm(); ?>

  <!-- 3. スクリプト読み込み - フォーム操作とデータ管理 -->
  <script>
    window.studentsData = <?php echo json_encode($students); ?>;
  </script>
  <script src="../js/student_list.js?v=<?php echo time(); ?>"></script>
</body>
</html>
