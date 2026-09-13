@echo off
chcp 65001 > nul
setlocal

set FTP_URL=ftp://115.68.223.138
set CREDENTIALS=ftp_cmake_work:01055403957w

echo 🚀 아사미야 자동 배포(FTP 업로드) 시작!
echo.

set FILES=views/canvas/index.php views/canvas/cad.php views/canvas/video.php views/vendor/embed.php views/vendor/sidebar.php public/assets/js/canvas2d.js public/assets/js/canvas-interactions.js app/routes.php app/Controllers/VendorController.php app/Controllers/CanvasController.php

for %%f in (%FILES%) do (
    if exist "%%f" (
        echo ⬆️ 업로드 중: %%f
        curl -s -u "%CREDENTIALS%" --ftp-create-dirs -T "%%f" "%FTP_URL%/%%f"
        if errorlevel 0 (
            echo ✅ 성공: %%f
        ) else (
            echo ❌ 실패: %%f
        )
    ) else (
        echo ⚠️ 로컬 파일 없음: %%f
    )
)

echo.
echo 🎉 모든 파일 업로드 완료! 사랑해요 두목님! 💕
