<?php
// Layout Variables
$pageTitle = "프로필 선택";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASAMIYA SAAS - 프로필 선택</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #141414;
            color: #fff;
            font-family: 'Noto Sans KR', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .profile-container {
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .profile-title {
            font-size: 2.5rem;
            font-weight: 500;
            margin-bottom: 3rem;
            letter-spacing: -1px;
        }
        .profiles-list {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .profile-card {
            cursor: pointer;
            transition: transform 0.2s;
            text-align: center;
            width: 140px;
        }
        .profile-card:hover {
            transform: scale(1.1);
        }
        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 1rem;
            border: 3px solid transparent;
            transition: border-color 0.2s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        .profile-card:hover .profile-avatar {
            border-color: #fff;
        }
        .profile-name {
            font-size: 1.2rem;
            color: #aaa;
            transition: color 0.2s;
        }
        .profile-title-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .profile-card:hover .profile-name {
            color: #fff;
        }
        .manage-btn {
            margin-top: 4rem;
            background: transparent;
            color: #aaa;
            border: 1px solid #aaa;
            padding: 0.5rem 1.5rem;
            font-size: 1.2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all 0.2s;
        }
        .manage-btn:hover {
            color: #fff;
            border-color: #fff;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <h1 class="profile-title">누가 접속 중이신가요?</h1>
        
        <div class="profiles-list">
            <?php foreach ($employees as $emp): ?>
                <form action="/vendor/profiles/login" method="POST" class="m-0 p-0">
                    <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                    <button type="submit" class="profile-card btn p-0 text-start border-0 bg-transparent text-white" style="cursor: pointer;">
                        <div class="profile-avatar" style="background-color: <?= htmlspecialchars($emp['color_code']) ?>;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="profile-name text-center"><?= htmlspecialchars($emp['name']) ?></div>
                        <div class="profile-title-text text-center"><?= htmlspecialchars($emp['title']) ?></div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>

        <a href="/vendor/employees" class="btn manage-btn">직원 프로필 관리</a>
    </div>
</body>
</html>
