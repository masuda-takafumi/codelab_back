<?php
/*
生徒操作処理

1. PHP処理
   1.1 共通ファイル読み込み
   1.2 ログイン確認
   1.3 アクション取得
   1.4 データベース接続
2. 処理分岐
   2.1 生徒登録（register_student）
   2.2 生徒更新（update_student）
   2.3 生徒削除（delete_student）
   2.4 成績保存（save_score：既存テストの更新のみ）
   2.5 成績削除（delete_score：物理削除、scoresのみ削除）
*/

// 1. PHP処理
// 1.1 共通ファイル読み込み
require_once '/work/app/config.php';
require_once '/work/app/core.php';

// 1.2 ログイン確認
if (!Utils::isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    $pdo = getDatabaseConnection();
    switch ($action) {
        case 'register_student':
            // 1. 生徒登録
            $last_name = $_POST['last_name'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name_kana = $_POST['last_name_kana'] ?? '';
            $first_name_kana = $_POST['first_name_kana'] ?? '';
            $class = $_POST['class'] ?? '';
            $class_no = $_POST['class_no'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birth_year = $_POST['birth_year'] ?? '';
            $birth_month = $_POST['birth_month'] ?? '';
            $birth_day = $_POST['birth_day'] ?? '';

            $birth_date = $birth_year . '-' . $birth_month . '-' . $birth_day;

            $stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, last_name_kana, first_name_kana, class, class_no, gender, birth_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$last_name, $first_name, $last_name_kana, $first_name_kana, $class, $class_no, $gender, $birth_date]);

            header("Location: complete.php");
            exit;

        case 'update_student':
            // 2. 生徒更新
            $student_id = $_POST['student_id'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name_kana = $_POST['last_name_kana'] ?? '';
            $first_name_kana = $_POST['first_name_kana'] ?? '';
            $class = $_POST['class'] ?? '';
            $class_no = $_POST['class_no'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $birth_year = $_POST['birth_year'] ?? '';
            $birth_month = $_POST['birth_month'] ?? '';
            $birth_day = $_POST['birth_day'] ?? '';

            $birth_date = $birth_year . '-' . $birth_month . '-' . $birth_day;

            $stmt = $pdo->prepare("UPDATE students SET last_name = ?, first_name = ?, last_name_kana = ?, first_name_kana = ?, class = ?, class_no = ?, gender = ?, birth_date = ? WHERE id = ?");
            $stmt->execute([$last_name, $first_name, $last_name_kana, $first_name_kana, $class, $class_no, $gender, $birth_date, $student_id]);

            header("Location: student_detail.php?id=" . $student_id);
            exit;

        case 'delete_student':
            // 3. 生徒削除
            $student_id = $_POST['student_id'] ?? '';

            // 成績もまとめて削除
            $stmt = $pdo->prepare("DELETE FROM scores WHERE student_id = ?");
            $stmt->execute([$student_id]);

            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$student_id]);

            header("Location: student_list.php");
            exit;

        case 'save_score':
            // 4. 成績保存（既存テストの更新のみ）
            $student_id = $_POST['student_id'] ?? '';
            $test_id = $_POST['test_id'] ?? '';
            $test_date = $_POST['test_date'] ?? '';
            $test_type = $_POST['test_type'] ?? '';
            $scores = json_decode($_POST['scores'] ?? '[]', true);

            // test_idが必須（既存のテストのみ更新可能）
            if (!$test_id) {
                header("Location: student_detail.php?id=" . $student_id . "&error=test_id_required");
                exit;
            }

            // 既存のテストの成績を削除
            $stmt = $pdo->prepare("DELETE FROM scores WHERE student_id = ? AND test_id = ?");
            $stmt->execute([$student_id, $test_id]);

            // 各科目の点数を保存（0点も含む）
            $subjects = [
                'english' => 1,
                'math' => 2,
                'japanese' => 3,
                'science' => 4,
                'social' => 5
            ];
            foreach ($scores as $subject => $score) {
                if (isset($subjects[$subject])) {
                    $score_value = ($score !== '' && $score !== null) ? $score : '0';
                    $stmt = $pdo->prepare("INSERT INTO scores (student_id, test_id, subject_id, score) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$student_id, $test_id, $subjects[$subject], $score_value]);
                }
            }

            header("Location: student_detail.php?id=" . $student_id);
            exit;

        case 'delete_score':
            // 5. 成績削除（物理削除：scoresのみ削除、testsは削除しない）
            $student_id = $_POST['student_id'] ?? '';
            $test_id = $_POST['test_id'] ?? '';

            // 成績データのみ物理削除（testsは全生徒共通なので削除しない）
            $stmt = $pdo->prepare("DELETE FROM scores WHERE student_id = ? AND test_id = ?");
            $stmt->execute([$student_id, $test_id]);

            header("Location: student_detail.php?id=" . $student_id);
            exit;

        default:
            header("Location: student_list.php");
            exit;
    }
} catch (Exception $e) {
    header("Location: student_list.php?error=1");
    exit;
}
?>
