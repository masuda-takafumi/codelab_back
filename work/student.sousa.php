<?php
/*
生徒操作処理

1. 生徒登録
2. 生徒更新
3. 生徒削除
4. 成績保存
5. 成績削除
*/

require_once 'common-p.php';
requireAuth();

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
            // 4. 成績保存
            $student_id = $_POST['student_id'] ?? '';
            $test_date = $_POST['test_date'] ?? '';
            $test_type = $_POST['test_type'] ?? '';
            $scores = json_decode($_POST['scores'] ?? '[]', true);

            // 種別コード
            $test_cd = ($test_type === '期末試験') ? 1 : 2;

            // テスト作成
            $stmt = $pdo->prepare("INSERT INTO tests (test_date, test_cd) VALUES (?, ?)");
            $stmt->execute([$test_date, $test_cd]);
            $test_id = $pdo->lastInsertId();

            // 各科目の点数
            $subjects = [
                'english' => 1,
                'math' => 2,
                'japanese' => 3,
                'science' => 4,
                'social' => 5
            ];
            foreach ($scores as $subject => $score) {
                if (isset($subjects[$subject]) && $score !== '') {
                    $stmt = $pdo->prepare("INSERT INTO scores (student_id, test_id, subject_id, score) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$student_id, $test_id, $subjects[$subject], $score]);
                }
            }

            header("Location: student_detail.php?id=" . $student_id);
            exit;

        case 'delete_score':
            // 5. 成績削除
            $student_id = $_POST['student_id'] ?? '';
            $test_id = $_POST['test_id'] ?? '';

            // 成績データ
            $stmt = $pdo->prepare("DELETE FROM scores WHERE student_id = ? AND test_id = ?");
            $stmt->execute([$student_id, $test_id]);

            // テストそのものも削除
            $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
            $stmt->execute([$test_id]);

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