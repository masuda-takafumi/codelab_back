<!--
生徒詳細画面

1. PHP処理
   1.1 共通関数読み込み
   1.2 ログイン確認
   1.3 ログアウト処理
   1.4 生徒情報取得
   1.5 既存成績データ取得
2. HTML構造
   2.1 ヘッダー
   2.2 生徒情報編集フォーム
   2.3 写真アップロード
   2.4 基本情報入力
   2.5 成績一覧テーブル
   2.6 成績行テンプレート
   2.7 隠しフォーム
   2.8 フッター
3. スクリプト読み込み
-->
<?php
// 1. PHP処理 - ログイン確認と生徒データの準備
// 1.1 共通関数読み込み - データベース接続とか使う
require_once '/work/app/core.php';



// 1.2 ログイン確認 - ログインしてないといけない
requireAuth();



// 1.3 ログアウト処理 - ログアウトボタンが押されたら処理
checkLogoutRequest();




// 1.4 生徒情報をDBから取得
$student_id = $_GET['id'] ?? '';
$class = '';
$class_no = '';
$last_name = '';
$first_name = '';
$last_name_kana = '';
$first_name_kana = '';
$gender = '';
$birth_date = '';
$birth_year = '';
$birth_month = '';
$birth_day = '';
$gender_text = '';

if ($student_id) {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student) {
            $class = $student['class'];
            $class_no = $student['class_no'];
            $last_name = $student['last_name'];
            $first_name = $student['first_name'];
            $last_name_kana = $student['last_name_kana'];
            $first_name_kana = $student['first_name_kana'];
            $gender = $student['gender'];
            $birth_date = $student['birth_date'];
            $gender_text = ($gender == '1') ? '男' : (($gender == '2') ? '女' : '');

            $date_parts = explode('-', $birth_date);
            if (count($date_parts) === 3) {
                $birth_year = $date_parts[0];
                $birth_month = $date_parts[1];
                $birth_day = $date_parts[2];
            }
        }
    } catch (Exception $e) {
        // エラー時は空のまま
    }
}

// 1.5 既存の成績データを取得
$existing_scores = [];
if ($student_id) {
    try {
        $pdo = getDatabaseConnection();

        $sql = "SELECT 
                    t.id AS test_id,
                    t.test_date,
                    CASE 
                        WHEN t.test_cd = 1 THEN '期末試験'
                        WHEN t.test_cd = 2 THEN '中間試験'
                    END as test_type,
                    MAX(CASE WHEN s.id = 3 THEN sc.score END) as japanese,
                    MAX(CASE WHEN s.id = 2 THEN sc.score END) as math,
                    MAX(CASE WHEN s.id = 1 THEN sc.score END) as english,
                    MAX(CASE WHEN s.id = 4 THEN sc.score END) as science,
                    MAX(CASE WHEN s.id = 5 THEN sc.score END) as social
                FROM tests t
                LEFT JOIN scores sc ON t.id = sc.test_id
                LEFT JOIN subjects s ON sc.subject_id = s.id
                WHERE sc.student_id = ? AND t.test_cd IN (1, 2)
                GROUP BY t.id, t.test_date, t.test_cd
                ORDER BY t.test_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id]);
        $existing_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $existing_scores = [];
    }
}
?>

<!-- 2. HTML構造 - 生徒詳細画面の表示 -->
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>生徒管理システム - 生徒詳細</title>
  <link rel="stylesheet" href="css/student_detail.css">
</head>
<body>



  <!-- 2.1 ヘッダー - ロゴとタイトル -->
  <header>
    <?php echo generateHeader(); ?>
  </header>
  <h1 class="student-detail-title">生徒情報詳細</h1>

  <main>
    <section id="register-section">
      <!-- 2.2 生徒情報編集フォーム - 生徒の情報を更新する -->
      <form id="student-register-form" method="POST" action="student.sousa.php" enctype="multipart/form-data">
        <fieldset class="register-area">
          <legend class="hidden">新規生徒登録フォーム</legend>
          <input type="hidden" name="action" value="update_student">
          <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
          <!-- 2.3 写真アップロード - 生徒の写真を設定 -->
          <section class="photo-upload">
            <img id="student-photo" src="img/ダミー生徒画像.png" alt="写真" class="photo-preview">
            <input type="file" id="photo-input" accept="image/jpeg,image/jpg" class="hidden">
            <button type="button" id="photo-btn">写真を挿入</button>
            <div id="photo-error" class="photo-error hidden"></div>
          </section>
          <!-- 2.4 基本情報入力 - 名前やクラスなどの情報を入力 -->
          <section class="info-input">
            <div class="row">
              <label for="last-name">氏名(姓)</label>
              <input type="text" id="last-name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="氏名(姓)" required>
              <label for="first-name">氏名(名)</label>
              <input type="text" id="first-name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="氏名(名)" required>
            </div>
            <div class="row">
              <label for="last-name-kana">氏名(せい)</label>
              <input type="text" id="last-name-kana" name="last_name_kana" value="<?php echo htmlspecialchars($last_name_kana); ?>" placeholder="氏名(せい)" required pattern="[ぁ-ん]+">
              <label for="first-name-kana">氏名(めい)</label>
              <input type="text" id="first-name-kana" name="first_name_kana" value="<?php echo htmlspecialchars($first_name_kana); ?>" placeholder="氏名(めい)" required pattern="[ぁ-ん]+">
            </div>


            <div class="row">
                <label for="class-select">クラス</label>
                <select id="class-select" name="class" required>
                <?php echo generateClassOptions($class); ?>
                </select>
                <label for="gender-select">性別</label>
                <select id="gender-select" name="gender" required>
                <?php echo generateGenderOptions($gender); ?>
                </select>
            </div>
            <div class="row">
              <label for="class-number">クラス番号</label>
              <select id="class-number" name="class_no" required>
                <?php echo generateSelectOptions(1, 31, intval($class_no), 'クラス番号'); ?>
              </select>
            </div>
            <div class="row">
                <label for="birth-year">生年月日</label>
                <select id="birth-year" name="birth_year" required>
                    <?php echo generateSelectOptions(1990, 2020, intval($birth_year), '年'); ?>
                </select>
                <span>年</span>
                <select id="birth-month" name="birth_month" required>
                    <?php echo generateSelectOptions(1, 12, intval($birth_month), '月'); ?>
                </select>
                <span>月</span>
                <select id="birth-day" name="birth_day" required>
                    <?php echo generateSelectOptions(1, 31, intval($birth_day), '日'); ?>
                </select>
                <span>日</span>
            <button type="submit" id="register-btn">更新</button>
            </div>
             <!-- バリデーションエラーメッセージ -->
        <div id="validation-error" class="validation-error hidden">
          未入力の項目があります
        </div>
          </section>
        </fieldset>
      </form>
      
      <!-- 2.6 成績行テンプレート - 新しい成績行を追加するためのテンプレート -->
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

    <!-- 2.5 成績一覧テーブル - 過去のテスト結果を表示 -->
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
            <!-- 既存の成績データを表示 -->
            <?php foreach ($existing_scores as $score): ?>
            <tr data-existing="true">
              <td><input type="date" class="score-date" value="<?php echo htmlspecialchars($score['test_date']); ?>" style="width:120px;" disabled></td>
              <td>
                <select class="score-type" disabled>
                  <option value="未受験" <?php echo $score['test_type'] === '未受験' ? 'selected' : ''; ?>>未受験</option>
                  <option value="期末試験" <?php echo $score['test_type'] === '期末試験' ? 'selected' : ''; ?>>期末試験</option>
                  <option value="中間試験" <?php echo $score['test_type'] === '中間試験' ? 'selected' : ''; ?>>中間試験</option>
                </select>
              </td>
              <td><input type="number" class="score-input" value="<?php echo htmlspecialchars($score['japanese'] ?? ''); ?>" min="0" max="100" style="width:60px;"></td>
              <td><input type="number" class="score-input" value="<?php echo htmlspecialchars($score['math'] ?? ''); ?>" min="0" max="100" style="width:60px;"></td>
              <td><input type="number" class="score-input" value="<?php echo htmlspecialchars($score['english'] ?? ''); ?>" min="0" max="100" style="width:60px;"></td>
              <td><input type="number" class="score-input" value="<?php echo htmlspecialchars($score['science'] ?? ''); ?>" min="0" max="100" style="width:60px;"></td>
              <td><input type="number" class="score-input" value="<?php echo htmlspecialchars($score['social'] ?? ''); ?>" min="0" max="100" style="width:60px;"></td>
              <td><input type="text" class="score-avg" readonly style="width:60px;" value="<?php 
                $scores = array_filter([$score['japanese'], $score['math'], $score['english'], $score['science'], $score['social']]);
                echo count($scores) > 0 ? number_format(array_sum($scores) / count($scores), 1) : '';
              ?>"></td>
              <td><input type="text" class="score-sum" readonly style="width:60px;" value="<?php 
                $scores = array_filter([$score['japanese'], $score['math'], $score['english'], $score['science'], $score['social']]);
                echo array_sum($scores);
              ?>"></td>
              <td><button type="button" class="score-save action">保存</button></td>
              <td><button type="button" class="score-delete action">削除</button></td>
            </tr>
            <?php endforeach; ?>
            <!-- JSで行追加 -->
          </tbody>
        </table>
        <button type="button" id="add-score-btn">＋テスト情報を追加する</button>
      </section>
      <div class="back-list-wrapper">
        <a href="student_list.php" class="back-list">←生徒一覧に戻る</a>
      </div>

      <!-- 2.7 成績の保存と削除用 -->
      <form id="score-form" method="POST" action="student.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="save_score">
        <input type="hidden" name="student_id" id="score-student-id">
        <input type="hidden" name="test_date" id="score-test-date">
        <input type="hidden" name="test_type" id="score-test-type">
        <input type="hidden" name="scores" id="score-scores">
      </form>

      <form id="score-delete-form" method="POST" action="student.sousa.php" style="display: none;">
        <input type="hidden" name="action" value="delete_score">
        <input type="hidden" name="student_id" id="score-delete-student-id">
        <input type="hidden" name="test_id" id="score-delete-test-id">
      </form>
    </section>
  </main>

  <!-- 2.8 フッター - コピーライト -->
  <footer>
    <?php echo generateFooter(); ?>
  </footer>

  <?php echo generateLogoutForm(); ?>

  <!-- 3. スクリプト読み込み - フォーム操作と成績管理 -->
  <script>
    // 既存の成績データをJavaScriptで使用できるようにする
    window.existingScores = <?php echo json_encode($existing_scores); ?>;
  </script>
  <script src="js/student_detail.js"></script>
</body>
</html>

