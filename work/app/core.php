<?php
/*
共通処理ファイル

1. データベース接続
2. セッション管理
3. ログアウト処理
4. リダイレクト処理
5. ユーティリティ
6. UI生成
*/

session_start();

// 1. データベース接続

function getDatabaseConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $host = 'db';
            $db   = 'scoremanager';
            $user = 'smuser';
            $pass = 'smpass';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $pdo = new PDO($dsn, $user, $pass, $options);

            $pdo->query('SELECT 1');

        } catch (Exception $e) {
            throw new Exception('データベース接続に失敗しました');
        }
    }

    return $pdo;
}






// 2. セッション管理
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        redirectTo('login.php');
    }
}

// 3. ログアウト処理
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function checkLogoutRequest() {
    if (isset($_POST['logout'])) {
        logout();
    }
}

// 4. リダイレクト処理
function redirectTo($url) {
    header("Location: $url");
    exit;
}

// 5. ユーティリティ
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 6. UI生成
function generateSelectOptions($start, $end, $selected = '', $placeholder = '') {
    $html = '';
    if ($placeholder) {
        $html .= "<option value=\"\">$placeholder</option>";
    }
    for ($i = $start; $i <= $end; $i++) {
        $selectedAttr = ($selected == $i) ? ' selected' : '';
        $html .= "<option value=\"$i\"$selectedAttr>$i</option>";
    }
    return $html;
}

// クラス選択肢作成
function generateClassOptions($selected = '') {
    $classes = ['A', 'B', 'C', 'D', 'E', 'F'];
    $html = '<option value="">クラス</option>';
    foreach ($classes as $class) {
        $selectedAttr = ($selected === $class) ? ' selected' : '';
        $html .= "<option value=\"$class\"$selectedAttr>$class</option>";
    }
    return $html;
}

// 性別選択肢作成
function generateGenderOptions($selected = '') {
    $genders = [
        '1' => '男',
        '2' => '女'
    ];
    $html = '<option value="">性別</option>';
    foreach ($genders as $value => $label) {
        $selectedAttr = ($selected == $value) ? ' selected' : '';
        $html .= "<option value=\"$value\"$selectedAttr>$label</option>";
    }
    return $html;
}

// フッターHTML
function generateFooter() {
    return '<span class="copyright">Copyright &copy; Vuetech corp . All Right Reserved</span>';
}

// ヘッダーHTML
function generateHeader($title = '生徒管理システム', $subtitle = '－Score Manager－') {
    return '
    <div class="logo-area">
      <img src="img/生徒管理ロゴ.png" alt="ロゴ" class="logo">
      <span class="title">' . h($title) . '</span>
      <span class="subtitle">' . h($subtitle) . '</span>
    </div>
    <button class="logout-button" id="logout-btn" style="display:none;">ログアウト</button>
    <img src="img/ログアウト透過.png" alt="ログアウト" class="logout-logo" id="logout-logo" tabindex="0">';
}

// ログアウトフォーム
function generateLogoutForm() {
    return '
    <form id="logout-form" method="POST" style="display:none;">
      <input type="hidden" name="logout" value="1">
    </form>';
}
?>

