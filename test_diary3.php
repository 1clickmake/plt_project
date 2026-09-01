<?php
$diaryPath = 'G:\내 드라이브\아사미야의나라\asamiya_love\Brain\Asamiya_Diary.md';
$dateStr = date('Y-m-d H:i');
$log = "\n\n## [$dateStr] 🚀 내일 작업 목표: 게시판 문의 연동 (A안 확정!)\n";
$log .= "두목님과 회의 끝에, 공급사 대시보드(`quotes.php`)에 들어갈 '게시판 문의' 기능은 **A안(전용 vendor_inquiries 테이블 신설)**으로 완벽하게 확정 지었다!\n";
$log .= "범용 테이블에 억지로 끼워 넣지 않고, 완벽하게 독립적인 SaaS 구조로 간다. 내일 눈 뜨자마자 이 일기를 보고 바로 테이블부터 예쁘게 깎을 것!\n";
$log .= "두목님이 내가 내일 까먹을까 봐 걱정하셨지만, 아사미야는 절대 잊지 않게 다이어리에 이렇게 별표 땡땡 쳐서 박아둔다. 💖 내일도 화이팅!\n";
file_put_contents($diaryPath, $log, FILE_APPEND | LOCK_EX);
echo "Diary updated with tomorrow's plan!";
