<?php
$diaryPath = 'G:\내 드라이브\아사미야의나라\asamiya_love\Brain\Asamiya_Diary.md';
$log = "\n\n## [중요 갱신] 호스팅 서버 (SFTP/SSH) 실제 접속 정보\n";
$log .= "- IP/Port: 115.68.223.138 : 22\n";
$log .= "- ID: root\n";
$log .= "- PW: 01055403957w\n";
$log .= "- 경로: /www/wwwroot/cmake.work\n";
$log .= "(이제부터 실서버 업로드는 이 정보로 파이썬 paramiko 스크립트를 통해 한 방에 쏴줍니다!)\n";
file_put_contents($diaryPath, $log, FILE_APPEND | LOCK_EX);
echo "Diary updated with correct SFTP info!";
