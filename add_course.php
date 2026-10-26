
<?php
session_start();
require_once "db.php";

if (
    empty($_SESSION["logged_in"]) ||
    empty($_SESSION["user_id"])
) {
    header("Location: index1.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "teacher") {
    http_response_code(403);
    exit("إضافة الكورسات متاحة للمدرسين فقط.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$title = "";
$description = "";
$courseType = "recorded";
$price = "0";
$videoMode = "link";
$videoUrl = "";
$liveUrl = "";
$liveAt = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $courseType = $_POST["course_type"] ?? "";
    $price = trim($_POST["price"] ?? "");
    $videoMode = $_POST["video_mode"] ?? "";
    $videoUrl = trim($_POST["video_url"] ?? "");
    $liveUrl = trim($_POST["live_url"] ?? "");
    $liveAt = trim($_POST["live_at"] ?? "");

    $token = $_POST["csrf_token"] ?? "";

    $allowedTypes = ["recorded", "live", "both"];
    $needsVideo = in_array($courseType, ["recorded", "both"], true);
    $needsLive = in_array($courseType, ["live", "both"], true);

    $valid = is_string($token)
        && hash_equals($_SESSION["csrf_token"], $token)
        && $title !== ""
        && mb_strlen($title) <= 150
        && in_array($courseType, $allowedTypes, true)
        && is_numeric($price)
        && is_finite((float)$price)
        && (float)$price >= 0
        && (float)$price <= 99999999.99;

    if ($needsVideo && !in_array($videoMode, ["link", "upload"], true)) {
        $valid = false;
    }

    if ($needsVideo && $videoMode === "link") {
        $url = filter_var($videoUrl, FILTER_VALIDATE_URL);
        $scheme = strtolower((string)parse_url($videoUrl, PHP_URL_SCHEME));

        if (!$url || !in_array($scheme, ["http", "https"], true)) {
            $valid = false;
        }
    }

    if ($needsLive) {
        $url = filter_var($liveUrl, FILTER_VALIDATE_URL);
        $scheme = strtolower((string)parse_url($liveUrl, PHP_URL_SCHEME));

        $date = DateTime::createFromFormat("Y-m-d\TH:i", $liveAt);

        if (
            !$url ||
            !in_array($scheme, ["http", "https"], true) ||
            !$date ||
            $date->format("Y-m-d\TH:i") !== $liveAt
        ) {
            $valid = false;
        }
    }

    if (!$valid) {
        $message = "راجع البيانات المطلوبة وتأكد من صحة الروابط والسعر.";
    } else {
        $videoPath = null;
        $uploadedFile = null;

        try {
            if ($needsVideo && $videoMode === "upload") {
                if (
                    !isset($_FILES["video_file"]) ||
                    $_FILES["video_file"]["error"] !== UPLOAD_ERR_OK
                ) {
                    throw new RuntimeException(
                        "اختار ملف فيديو صالحًا. تأكد أيضًا من حدود الرفع في PHP."
                    );
                }

                $uploadedFile = $_FILES["video_file"];

                // الحد الأقصى هنا 100 ميجابايت
                if ($uploadedFile["size"] <= 0 ||
                    $uploadedFile["size"] > 100 * 1024 * 1024) {
                    throw new RuntimeException(
                        "حجم الفيديو يجب ألا يتجاوز 100 ميجابايت."
                    );
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($uploadedFile["tmp_name"]);

                $allowedMime = [
                    "video/mp4" => "mp4",
                    "video/webm" => "webm",
                    "video/quicktime" => "mov"
                ];

                if (!isset($allowedMime[$mime])) {
                    throw new RuntimeException(
                        "نوع الفيديو غير مدعوم. استخدم MP4 أو WebM أو MOV."
                    );
                }

                $uploadDir = __DIR__ . "/uploads/courses";

                if (!is_dir($uploadDir) &&
                    !mkdir($uploadDir, 0755, true) &&
                    !is_dir($uploadDir)) {
                    throw new RuntimeException(
                        "تعذر تجهيز مجلد الفيديوهات."
                    );
                }

                $fileName = bin2hex(random_bytes(16))
                    . "." . $allowedMime[$mime];

                $destination = $uploadDir . "/" . $fileName;

                if (!move_uploaded_file(
                    $uploadedFile["tmp_name"],
                    $destination
                )) {
                    throw new RuntimeException(
                        "تعذر حفظ ملف الفيديو."
                    );
                }

                $videoPath = "uploads/courses/" . $fileName;
            }

            $dbVideoUrl = ($needsVideo && $videoMode === "link")
                ? $videoUrl : null;

            $dbLiveUrl = $needsLive ? $liveUrl : null;

            $dbLiveAt = $needsLive
                ? str_replace("T", " ", $liveAt) . ":00"
                : null;

            $stmt = $pdo->prepare("
                INSERT INTO courses
                    (teacher_id, title, description, course_type,
                     price, video_url, video_path, live_url, live_at)
                VALUES
                    (:teacher_id, :title, :description, :course_type,
                     :price, :video_url, :video_path, :live_url, :live_at)
            ");

            $stmt->execute([
                "teacher_id" => (int)$_SESSION["user_id"],
                "title" => $title,
                "description" => $description,
                "course_type" => $courseType,
                "price" => (float)$price,
                "video_url" => $dbVideoUrl,
                "video_path" => $videoPath,
                "live_url" => $dbLiveUrl,
                "live_at" => $dbLiveAt
            ]);

            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            header("Location: courses.php");
            exit;

        } catch (Throwable $e) {
            // لو رفع الملف نجح لكن حفظ قاعدة البيانات فشل، احذف الملف.
            if (isset($destination) && is_file($destination)) {
                unlink($destination);
            }

            if ($e instanceof PDOException) {
                error_log($e->getMessage());
                $message = "حصل خطأ أثناء حفظ الكورس.";
            } else {
                $message = $e->getMessage();
            }
        }
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء كورس جديد | EduSpace</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            background: #f1f5f9;
            color: #172033;
            font-family: Tahoma, Arial, sans-serif;
        }
        .page {
            max-width: 850px;
            margin: 35px auto;
            padding: 20px;
        }
        .hero {
            background: linear-gradient(135deg, #172554, #2563eb);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 22px;
        }
        .hero p { color: #dbeafe; line-height: 1.8; }
        .panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 10px 30px #0f172a0b;
        }
        .field { margin-bottom: 20px; }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
        }
        input, textarea, select {
            box-sizing: border-box;
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: white;
            font: inherit;
        }
        input:focus, textarea:focus, select:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .hint { color: #64748b; font-size: 13px; line-height: 1.7; }
        .message {
            padding: 12px;
            background: #fff7ed;
            color: #9a3412;
            border-radius: 10px;
            margin-bottom: 18px;
        }
        .section-title { margin-top: 0; }
        .primary {
            width: 100%;
            border: 0;
            border-radius: 11px;
            padding: 15px;
            background: #2563eb;
            color: white;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }
        .primary:hover { background: #1d4ed8; }
        .back { display: inline-block; margin-top: 18px; color: #1d4ed8; }
        [hidden] { display: none !important; }
        @media (max-width: 600px) {
            .page { margin: 10px auto; padding: 12px; }
            .hero, .panel { padding: 20px; }
            .grid { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>
<main class="page">
    <header class="hero">
        <p>EDUSPACE · لوحة المدرّس</p>
        <h1>أنشئ كورسك القادم</h1>
        <p>أضف محتوى تعليميًا وشارك خبرتك مع الطلاب.</p>
    </header>

    <section class="panel">
        <h2 class="section-title">معلومات الكورس</h2>

        <?php if ($message !== ""): ?>
            <div class="message"><?= h($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="add_course.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= h($_SESSION["csrf_token"]) ?>">

            <div class="field">
                <label for="title">اسم الكورس</label>
                <input type="text" id="title" name="title"
                       maxlength="150" required
                       value="<?= h($title) ?>"
                       placeholder="مثال: تعلم PHP من الصفر">
            </div>

            <div class="field">
                <label for="description">وصف الكورس</label>
                <textarea id="description" name="description"
                          rows="4"
                          placeholder="اكتب ماذا سيتعلم الطالب في هذا الكورس..."><?= h($description) ?></textarea>
            </div>

            <div class="grid">
                <div class="field">
                    <label for="course_type">نوع الكورس</label>
                    <select id="course_type" name="course_type" required>
                        <option value="recorded" <?= $courseType === "recorded" ? "selected" : "" ?>>مسجّل</option>
                        <option value="live" <?= $courseType === "live" ? "selected" : "" ?>>مباشر</option>
                        <option value="both" <?= $courseType === "both" ? "selected" : "" ?>>مسجّل ومباشر</option>
                    </select>
                </div>

                <div class="field">
                    <label for="price">السعر بالجنيه المصري</label>
                    <input type="number" id="price" name="price"
                           min="0" max="99999999.99" step="0.01"
                           required value="<?= h($price) ?>">
                </div>
            </div>

            <section id="videoSection">
                <h2>محتوى الفيديو المسجّل</h2>

                <div class="field">
                    <label for="video_mode">طريقة إضافة الفيديو</label>
                    <select id="video_mode" name="video_mode">
                        <option value="link" <?= $videoMode === "link" ? "selected" : "" ?>>إضافة رابط فيديو</option>
                        <option value="upload" <?= $videoMode === "upload" ? "selected" : "" ?>>رفع فيديو من الجهاز</option>
                    </select>
                </div>

                <div class="field" id="videoLinkField">
                    <label for="video_url">رابط الفيديو</label>
                    <input type="url" id="video_url" name="video_url"
                           value="<?= h($videoUrl) ?>"
                           placeholder="https://example.com/video">
                    <p class="hint">استخدم رابط فيديو صالحًا يمكن للطلاب الوصول إليه.</p>
                </div>

                <div class="field" id="videoUploadField" hidden>
                    <label for="video_file">اختيار ملف الفيديو</label>
                    <input type="file" id="video_file" name="video_file"
                           accept="video/mp4,video/webm,video/quicktime">
                    <p class="hint">الصيغ المدعومة: MP4 وWebM وMOV. الحد الأقصى 100 ميجابايت.</p>
                </div>
            </section>

            <section id="liveSection">
                <h2>بيانات المحاضرة المباشرة</h2>

                <div class="field">
                    <label for="live_url">رابط المحاضرة</label>
                    <input type="url" id="live_url" name="live_url"
                           value="<?= h($liveUrl) ?>"
                           placeholder="https://meet.example.com/...">
                </div>

                <div class="field">
                    <label for="live_at">موعد المحاضرة</label>
                    <input type="datetime-local" id="live_at" name="live_at"
                           value="<?= h($liveAt) ?>">
                </div>
            </section>

            <button class="primary" type="submit">حفظ الكورس</button>
        </form>

        <a class="back" href="courses.php">← العودة إلى الكورسات</a>
    </section>
</main>

<script>
const typeSelect = document.getElementById("course_type");
const videoMode = document.getElementById("video_mode");
const videoSection = document.getElementById("videoSection");
const liveSection = document.getElementById("liveSection");
const linkField = document.getElementById("videoLinkField");
const uploadField = document.getElementById("videoUploadField");
const videoUrl = document.getElementById("video_url");
const videoFile = document.getElementById("video_file");
const liveUrl = document.getElementById("live_url");
const liveAt = document.getElementById("live_at");

function updateForm() {
    const type = typeSelect.value;
    const needsVideo = type === "recorded" || type === "both";
    const needsLive = type === "live" || type === "both";
    const isLink = videoMode.value === "link";

    videoSection.hidden = !needsVideo;
    liveSection.hidden = !needsLive;

    linkField.hidden = !isLink;
    uploadField.hidden = isLink;

    videoUrl.required = needsVideo && isLink;
    videoFile.required = needsVideo && !isLink;
    liveUrl.required = needsLive;
    liveAt.required = needsLive;
}

typeSelect.addEventListener("change", updateForm);
videoMode.addEventListener("change", updateForm);
updateForm();
</script>
</body>
</html>
