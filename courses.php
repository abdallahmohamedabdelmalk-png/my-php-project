

<?php
session_start();
require_once "db.php";

// إنشاء رمز حماية للحذف
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query("
    SELECT id, title, description, course_type, price,
           video_url, video_path, live_url, live_at
    FROM courses
    ORDER BY id DESC
");

$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الكورسات التعليمية</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="courses.css">
</head>
<body class="courses-page">

<header class="courses-header">
    <div>
        <h1>منصة التعليم</h1>
        <p>اتعلم مهارات جديدة وابدأ رحلتك</p>
    </div>
    <a class="add-course-btn" href="add_course.php">+ إضافة كورس</a>
</header>

<main class="courses-container">
    <h2>اكتشف الكورسات</h2>
    <p class="section-description">اختار الكورس المناسب ليك وابدأ التعلم.</p>

    <?php if (empty($courses)): ?>
        <p class="empty-courses">لا توجد كورسات متاحة حاليًا.</p>
    <?php else: ?>
        <div class="courses-grid">

        <?php foreach ($courses as $course): ?>
            <article class="course-card">

                <div class="course-cover">
                    <span>
                        <?= $course["course_type"] === "live"
                            ? "بث مباشر"
                            : ($course["course_type"] === "both"
                                ? "مسجل ومباشر"
                                : "كورس مسجل") ?>
                    </span>
                    <div class="course-cover-icon">▶</div>
                </div>

                <div class="course-content">
                    <h3><?= h($course["title"]) ?></h3>

                    <p class="course-description">
                        <?= nl2br(h($course["description"] ?? "")) ?>
                    </p>

                    <div class="course-info">
                        <span>نوع الكورس</span>
                        <strong>
                            <?= $course["course_type"] === "live"
                                ? "مباشر"
                                : ($course["course_type"] === "both"
                                    ? "مسجل ومباشر"
                                    : "مسجل") ?>
                        </strong>
                    </div>

                    <div class="course-price">
                        <?= number_format((float)$course["price"], 2) ?>
                        <span>جنيه</span>
                    </div>

                   
<?php if (
    in_array($course["course_type"], ["recorded", "both"], true)
    && (!empty($course["video_url"]) || !empty($course["video_path"]))
): ?>
    <?php
    $video = !empty($course["video_url"])
        ? $course["video_url"]
        : $course["video_path"];
    ?>

    <a class="watch-btn"
       href="<?= h($video) ?>"
       target="_blank"
       rel="noopener noreferrer">
        ▶ شاهد الكورس
    </a>

<?php elseif (
    $course["course_type"] === "live"
    || $course["course_type"] === "both"
): ?>
    <?php if (!empty($course["live_url"])): ?>
        <a class="watch-btn"
           href="<?= h($course["live_url"]) ?>"
           target="_blank"
           rel="noopener noreferrer">
            دخول البث المباشر
        </a>
    <?php else: ?>
        <p class="video-unavailable">رابط البث لم يُضف بعد.</p>
    <?php endif; ?>

<?php else: ?>
    <p class="video-unavailable">الفيديو لم يُضف بعد.</p>
<?php endif; ?>


<?php if (($_SESSION["role"] ?? "") === "admin"): ?>
    <form action="delete_course.php" method="POST"
          onsubmit="return confirm('هل أنت متأكد من حذف هذا الكورس؟');">

        <input type="hidden" name="course_id"
               value="<?= (int)$course['id'] ?>">

        <input type="hidden" name="csrf_token"
               value="<?= h($_SESSION['csrf_token']) ?>">

        <button type="submit" class="delete-btn">
            🗑️ حذف الكورس
        </button>
    </form>
<?php endif; ?>
</div>
</article>
<?php endforeach; ?>

</div>
<?php endif; ?>

</main>

</body>
</html>
