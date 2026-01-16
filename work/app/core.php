<<<<<<< HEAD
﻿<?php
/*
 * 役割：共通関数（DB/認証/UI）
 * 1) DB接続
 * 2) 認証/セッション
 * 3) ログアウト
 * 4) リダイレクト
 * 5) 文字列エスケープ
 * 6) UI生成
 * 7) URL生成
 * 8) ページング補助
 */

session_start();

// DB接続
=======
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

>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
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

<<<<<<< HEAD
// 認証/セッション
=======





// 2. セッション管理
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
<<<<<<< HEAD
        redirectTo('/login.php');
    }
}

// ログアウト
function logout() {
    session_destroy();
    header('Location: /login.php');
=======
        redirectTo('/php/login.php');
    }
}


// 3. ログアウト処理
function logout() {
    session_destroy();
    header('Location: /php/login.php');
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
    exit;
}

function checkLogoutRequest() {
    if (isset($_POST['logout'])) {
        logout();
    }
}

<<<<<<< HEAD
// リダイレクト
=======
// 4. リダイレクト処理
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function redirectTo($url) {
    header("Location: $url");
    exit;
}

<<<<<<< HEAD
// 文字列エスケープ
=======
// 5. ユーティリティ
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

<<<<<<< HEAD
// UI生成
=======
// 6. UI生成
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
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

<<<<<<< HEAD
=======
// クラス選択肢作成
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function generateClassOptions($selected = '') {
    $classes = ['A', 'B', 'C', 'D', 'E', 'F'];
    $html = '<option value="">クラス</option>';
    foreach ($classes as $class) {
        $selectedAttr = ($selected === $class) ? ' selected' : '';
        $html .= "<option value=\"$class\"$selectedAttr>$class</option>";
    }
    return $html;
}

<<<<<<< HEAD
=======
// 性別選択肢作成
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
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

<<<<<<< HEAD
=======
// フッターHTML
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function generateFooter() {
    return '<span class="copyright">Copyright &copy; Vuetech corp . All Right Reserved</span>';
}

<<<<<<< HEAD
=======
// ヘッダーHTML
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function generateHeader($title = '生徒管理システム', $subtitle = '－Score Manager－') {
    return '
    <div class="logo-area">
      <img src="../img/生徒管理ロゴ.png" alt="ロゴ" class="logo">
      <span class="title">' . h($title) . '</span>
      <span class="subtitle">' . h($subtitle) . '</span>
    </div>
    <button class="logout-button" id="logout-btn" style="display:none;">ログアウト</button>
    <img src="../img/ログアウト透過.png" alt="ログアウト" class="logout-logo" id="logout-logo" tabindex="0">';
}

<<<<<<< HEAD
=======
// ログアウトフォーム
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
function generateLogoutForm() {
    return '
    <form id="logout-form" method="POST" style="display:none;">
      <input type="hidden" name="logout" value="1">
    </form>';
}
<<<<<<< HEAD

// URL生成
function buildUrl($path, $params = []) {
    if (!$params) {
        return $path;
    }
    return $path . '?' . http_build_query($params);
}

// ページング補助
function getCircledNumber($number) {
    $circled = ['①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩','⑪','⑫','⑬','⑭','⑮','⑯','⑰','⑱','⑲','⑳'];
    if ($number >= 1 && $number <= 20) {
        return $circled[$number - 1];
    }
    return (string)$number;
}

function buildPaginationButtons($current_page, $total_pages) {
    if ($total_pages <= 1) {
        return [];
    }
    $page_numbers = [];
    if ($total_pages <= 5) {
        $page_numbers = range(1, $total_pages);
    } else {
        if ($current_page > 2) {
            $page_numbers[] = '...';
        }
        $start_page = max(1, $current_page - 1);
        $end_page = min($total_pages, $current_page + 1);
        for ($i = $start_page; $i <= $end_page; $i++) {
            $page_numbers[] = $i;
        }
        if ($current_page < $total_pages - 1) {
            $page_numbers[] = '...';
        }
    }

    $buttons = [];
    $buttons[] = [
        'label' => '最初へ',
        'attrs' => 'type="submit" name="page" value="1"' . ($current_page === 1 ? ' disabled' : '')
    ];

    foreach ($page_numbers as $page_item) {
        if (is_int($page_item)) {
            $attrs = 'type="submit" name="page" value="' . $page_item . '"';
            if ($page_item === $current_page) {
                $attrs .= ' class="active-page"';
            }
            $buttons[] = [
                'label' => getCircledNumber($page_item),
                'attrs' => $attrs
            ];
        } else {
            $buttons[] = [
                'label' => $page_item,
                'attrs' => 'type="button" disabled'
            ];
        }
    }

    $buttons[] = [
        'label' => '最後へ',
        'attrs' => 'type="submit" name="page" value="' . $total_pages . '"' . ($current_page === $total_pages ? ' disabled' : '')
    ];

    return $buttons;
}
?>


=======
?>
>>>>>>> 5a6520016f86592e24c27614155e8eb66e15913a
