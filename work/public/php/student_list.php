<<<<<<< HEAD
﻿<?php
/*
 * 役割：生徒一覧と新規登録フォーム
 * 1) 共通読み込み/ログイン確認
 * 2) 入力取得（検索/ページ）
 * 3) 一覧取得（検索条件）
 * 4) 件数取得とページング
 * 5) 一覧URL生成
 * 6) HTML出力（タブ/一覧/登録）
 * 7) 削除フォーム
 * 8) スクリプト読み込み
 */

// 共通読み込み/ログイン確認
require_once '/work/app/config.php';
require_once '/work/app/core.php';

=======
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
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
if (!Utils::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

<<<<<<< HEAD
checkLogoutRequest();

// 入力取得（検索/ページ）
$students = [];
$search_query = trim($_GET['q'] ?? '');
$page_param = $_GET['page'] ?? '';
$new_param = $_GET['new'] ?? '';
$current_page = (ctype_digit((string)$page_param) && (int)$page_param > 0) ? (int)$page_param : 1;
$items_per_page = 10;
$total_students = 0;
$total_pages = 1;

// 生徒一覧取得/ページング
try {
    $pdo = getDatabaseConnection();
    $where_sql = '';
    $where_params = [];
    // GETの検索語を条件にする
    if ($search_query !== '') {
        $where_sql = "WHERE last_name LIKE ? OR first_name LIKE ? OR last_name_kana LIKE ? OR first_name_kana LIKE ?";
        $like_query = '%' . $search_query . '%';
        $where_params = [$like_query, $like_query, $like_query, $like_query];
    }

    // 件数取得とページング
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $where_sql");
    $count_stmt->execute($where_params);
    $total_students = (int)$count_stmt->fetchColumn();
    $total_pages = (int)ceil($total_students / $items_per_page);
    if ($total_pages < 1) {
        $total_pages = 1;
    }
    if ($new_param === '1' && $page_param === '') {
        $current_page = $total_pages;
    }
    if ($current_page > $total_pages) {
        $current_page = $total_pages;
    }

    // 一覧URL生成
    $page_buttons = buildPaginationButtons($current_page, $total_pages);

    // LIMIT/OFFSETでページング
    $offset = ($current_page - 1) * $items_per_page;
    $stmt = $pdo->prepare("SELECT * FROM students $where_sql ORDER BY class, class_no LIMIT ? OFFSET ?");
    foreach ($where_params as $index => $value) {
        $stmt->bindValue($index + 1, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(count($where_params) + 1, $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(count($where_params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
=======


// 1.3 ログアウト処理 - ログアウトボタンが押されたら処理
checkLogoutRequest();



// 1.4 生徒一覧データの取得 - 全生徒の情報を表示するため
$students = [];
try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT * FROM students ORDER BY class, class_no");
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $students = [];
}
?>

<<<<<<< HEAD
<!-- HTML出力 -->
=======


<!-- 2. HTML構造 - 生徒一覧画面の表示 -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>生徒管理システム - 生徒一覧</title>
  <link rel="stylesheet" href="../css/student_list.css">
</head>
<body>
<<<<<<< HEAD

  <!-- ヘッダー -->
=======
  <!-- 2.1 ヘッダー - ロゴとタイトル -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
  <header>
    <?php echo generateHeader(); ?>
  </header>

  <main>
<<<<<<< HEAD

    <!-- タブ -->
=======
    <!-- 2.2 タブナビゲーション - 画面を切り替える -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
    <nav class="tabs">
      <button class="tab active" id="tab-list">生徒一覧</button>
      <button class="tab" id="tab-register">新規生徒登録</button>
    </nav>
<<<<<<< HEAD

    <!-- 生徒一覧 -->
    <section id="tab-content-list" class="tab-content">

      <!-- 検索 -->
      <form class="search-box" method="get" action="student_list.php">
        <div class="search-input-wrapper">
        <input type="text" id="search-name" class="pill-input" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="生徒名（漢字・かな）">
          <button type="button" id="search-clear" class="search-clear" aria-label="検索をクリア">×</button>
        </div>
        <button id="search-btn" class="search-btn" type="submit">検索</button>
      </form>

      <!-- 生徒テーブル -->
      <table class="student-list-table student-table">
=======
    <!-- 2.3 生徒一覧タブ - 生徒の一覧を表示 -->
    <section id="tab-content-list" class="tab-content">
      <!-- 2.4 検索機能 - 生徒を探しやすくする -->
      <section class="search-box">
        <input type="text" id="search-name" placeholder="生徒名（漢字・かな）">
        <button id="search-btn">検索</button>
      </section>
      <!-- 2.5 生徒テーブル - 生徒の情報を表形式で表示 -->
      <table>
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
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
<<<<<<< HEAD
              <td>
                <?php
                  $detail_params = ['id' => $student['id']];
                  if ($search_query !== '') $detail_params['q'] = $search_query;
                  if ($current_page > 1) $detail_params['page'] = $current_page;
                  $detail_url = buildUrl('/php/student_detail.php', $detail_params);
                ?>
                <a href="<?php echo htmlspecialchars($detail_url); ?>" class="detail-link pill-button">詳細</a>
              </td>
              <td>
                <button type="button" class="delete-btn pill-button" data-student-id="<?php echo $student['id']; ?>">削除</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="pagination" id="pege-btn">
        <?php if ($total_students > $items_per_page): ?>
          <form method="get" action="student_list.php">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
            <?php foreach ($page_buttons as $button): ?>
              <button <?php echo $button['attrs']; ?>><?php echo htmlspecialchars($button['label']); ?></button>
            <?php endforeach; ?>
          </form>
        <?php endif; ?>
      </div>

      <!-- 削除フォーム -->
=======
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
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
      <form id="delete-student-form" method="POST" action="student.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="delete_student">
        <input type="hidden" name="student_id" id="delete-student-id">
      </form>
    </section>

<<<<<<< HEAD
    <!-- 新規登録 -->
=======
    <!-- 2.6 新規生徒登録タブ - 新しい生徒を登録する -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
    <section id="tab-content-register" class="hidden">
      <form id="student-register-form" method="POST" action="student.sousa.php" enctype="multipart/form-data">
        <fieldset class="register-area">
            <legend class="hidden">新規生徒登録フォーム</legend>
            <input type="hidden" name="action" value="register_student">
<<<<<<< HEAD

          <!-- 写真アップロード -->
          <section class="photo-upload">
            <img id="student-photo" src="../img/ダミー生徒画像.png" alt="写真" class="photo-preview">
            <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/jpg" class="hidden">
            <button type="button" id="photo-btn">写真を挿入</button>
            <div id="photo-error" class="photo-error hidden"></div>
          </section>

          <!-- 基本情報入力 -->
          <section class="info-input">
            <div class="row">
              <label for="last-name">氏名(姓)</label>
              <input type="text" id="last-name" class="pill-input" name="last_name" placeholder="氏名(姓)" required>
              <label for="first-name">氏名(名)</label>
              <input type="text" id="first-name" class="pill-input" name="first_name" placeholder="氏名(名)" required>
            </div>
            <div class="row">
              <label for="last-name-kana">氏名(せい)</label>
              <input type="text" id="last-name-kana" class="pill-input" name="last_name_kana" placeholder="氏名(せい)" required pattern="[ぁ-ん]+">
              <label for="first-name-kana">氏名(めい)</label>
              <input type="text" id="first-name-kana" class="pill-input" name="first_name_kana" placeholder="氏名(めい)" required pattern="[ぁ-ん]+">
            </div>
            <div class="row">
              <label for="class-select">クラス</label>
              <select id="class-select" class="pill-input" name="class" required>
                <?php echo generateClassOptions(); ?>
              </select>
              <label for="gender-select">性別</label>
              <select id="gender-select" class="pill-input" name="gender" required>
=======
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
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
                <?php echo generateGenderOptions(); ?>
              </select>
            </div>
            <div class="row">
              <label for="class-number">クラス番号</label>
<<<<<<< HEAD
              <select id="class-number" class="pill-input" name="class_no" required>
=======
              <select id="class-number" name="class_no" required>
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
                <?php echo generateSelectOptions(1, 31, '', 'クラス番号'); ?>
              </select>
            </div>
            <div class="row">
              <label for="birth-year">生年月日</label>
<<<<<<< HEAD
              <select id="birth-year" class="pill-input" name="birth_year" required>
                <?php echo generateSelectOptions(1990, 2020, '', '年'); ?>
              </select>
              <span>年</span>
              <select id="birth-month" class="pill-input" name="birth_month" required>
                <?php echo generateSelectOptions(1, 12, '', '月'); ?>
              </select>
              <span>月</span>
              <select id="birth-day" class="pill-input" name="birth_day" required>
=======
              <select id="birth-year" name="birth_year" required>
                <?php echo generateSelectOptions(1990, 2020, '', '年'); ?>
              </select>
              <span>年</span>
              <select id="birth-month" name="birth_month" required>
                <?php echo generateSelectOptions(1, 12, '', '月'); ?>
              </select>
              <span>月</span>
              <select id="birth-day" name="birth_day" required>
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
                <?php echo generateSelectOptions(1, 31, '', '日'); ?>
              </select>
              <span>日</span>
              <button type="submit" id="register-btn">登録</button>
            </div>
<<<<<<< HEAD
=======
             <!-- バリデーションエラーメッセージ -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
        <div id="validation-error" class="validation-error hidden">
          未入力の項目があります
        </div>
          </section>
        </fieldset>
      </form>
<<<<<<< HEAD
=======
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
      </section>
      <!-- 3. 戻るリンク -->
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
      <div class="back-list-wrapper">
        <a href="/php/student_list.php" class="back-list">←生徒一覧に戻る</a>
      </div>

<<<<<<< HEAD
    </section>
  </main>

  <!-- フッター -->
=======
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
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
  <footer>
    <?php echo generateFooter(); ?>
  </footer>

  <?php echo generateLogoutForm(); ?>

<<<<<<< HEAD
  <!-- スクリプト読み込み -->
  <script src="../js/student_list.js?v=<?php echo time(); ?>"></script>
</body>
</html>


=======
  <!-- 3. スクリプト読み込み - フォーム操作とデータ管理 -->
  <script>
    window.studentsData = <?php echo json_encode($students); ?>;
  </script>
  <script src="../js/student_list.js?v=<?php echo time(); ?>"></script>
</body>
</html>
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
