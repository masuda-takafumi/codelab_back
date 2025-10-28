<!--
ログイン画面

1. ログイン処理
2. HTML構造
3. スクリプト読み込み
-->
<?php
require_once '/work/app/common-p.php';

$error = '';

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // メールアドレスとパスワードの両方が空欄チェック
    if (empty($email) && empty($password)) {
        $error = 'メールアドレスおよびパスワードを入力してください';
    }
    // メールアドレスの空欄チェック
    elseif (empty($email)) {
        $error = 'メールアドレスを入力してください';
    }
    // パスワードの空欄チェック
    elseif (empty($password)) {
        $error = 'パスワードを入力してください';
    }
    // メールアドレス形式チェック
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレス形式で入力してください';
    }
    // パスワードの英数字チェック（記号が含まれていないか）
    elseif (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        $error = 'パスワードは英数字のみです';
    }
    else {
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                header("Location: student_list.php");
                exit;
            } else {
                $error = 'メールアドレスまたはパスワードが正しくありません。';
            }
        } catch (PDOException $e) {
            $error = 'システムエラーが発生しました';
        }
    }
}
?>






<!-- 2. HTML構造 -->
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>生徒管理システム - ログイン</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <header>
    <div class="logo-area">
      <img src="img/生徒管理ロゴ.png" alt="ロゴ" class="logo">
      <span class="title">生徒管理システム</span>
      <span class="subtitle">－Score Manager－</span>
    </div>
  </header>

  <main>
    <div class="container">
      <h2>認証</h2>
      <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="post">
        <label for="email">メールアドレス</label>
        <input type="email" name="email" id="email">
        <label for="password">パスワード</label>
        <input type="password" name="password" id="password">
        <button type="submit">ログイン</button>
      </form>
    </div>
  </main>

  <!-- フッター - コピーライト -->
  <footer>
    <span>Copyright &copy; Vuetech corp . All Right Reserved</span>
  </footer>
</body>
</html>