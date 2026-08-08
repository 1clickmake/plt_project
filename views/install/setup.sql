SET FOREIGN_KEY_CHECKS = 0;

-- 사용자 정보 테이블
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '고유 식별 ID',
    `user_id` VARCHAR(255) NOT NULL UNIQUE COMMENT '사용자 아이디',
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '사용자 이름',
    `password` VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    `email` VARCHAR(100) NOT NULL UNIQUE COMMENT '이메일 주소',
    `role` ENUM('user', 'admin') DEFAULT 'user' COMMENT '사용자 권한',
    `point` INT(11) DEFAULT 0 COMMENT '포인트',
    `level` INT(11) DEFAULT 1 COMMENT '레벨',
    `country` VARCHAR(50) DEFAULT 'Unknown' COMMENT '접속 국가',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '가입 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '사용자 정보 테이블';

-- 사이트 정보 설정 테이블
DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
    `id` INT(11) PRIMARY KEY DEFAULT 1 COMMENT '고유 ID',
    `site_name` VARCHAR(100) DEFAULT 'Neuron AI PHP' COMMENT '사이트 명',
    `company_name` VARCHAR(100) DEFAULT '' COMMENT '회사 명',
    `company_owner` VARCHAR(50) DEFAULT '' COMMENT '대표자 명',
    `company_license_num` VARCHAR(50) DEFAULT '' COMMENT '사업자 등록번호',
    `company_tel` VARCHAR(50) DEFAULT '' COMMENT '회사 전화번호',
    `company_email` VARCHAR(100) DEFAULT '' COMMENT '회사 이메일',
    `company_address` VARCHAR(255) DEFAULT '' COMMENT '회사 주소',
    `company_info` TEXT COMMENT '회사 소개내용',
    `logo_type` ENUM('text', 'image') DEFAULT 'text' COMMENT '로고 타입',
    `logo_text` VARCHAR(100) DEFAULT '' COMMENT '로고 텍스트',
    `logo_image` VARCHAR(255) DEFAULT '' COMMENT '로고 이미지 경로',
    `template` VARCHAR(50) DEFAULT 'basic' COMMENT '사이트 템플릿',
    `join_point` INT(11) DEFAULT 0 COMMENT '가입시 지급 포인트',
    `join_level` INT(11) DEFAULT 1 COMMENT '가입시 부여 레벨',
    `allowed_ips` TEXT COMMENT '접속 허용 IP 목록',
    `blocked_ips` TEXT COMMENT '접속 차단 IP 목록',
    `faq_category` VARCHAR(255) DEFAULT '회원|포인트|게시판|기타' COMMENT 'FAQ 카테고리',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '사이트 정보 설정 테이블';

-- 독립 페이지 관리 테이블
DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '페이지 고유 ID',
    `title` VARCHAR(255) NOT NULL COMMENT '페이지 제목',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT '페이지 URL 슬러그',
    `content` LONGTEXT NOT NULL COMMENT '페이지 내용',
    `display_title` TINYINT(1) DEFAULT 1 COMMENT '제목 표시 여부',
    `use_card_style` TINYINT(1) DEFAULT 1 COMMENT '카드 스타일 사용 여부',
    `editor_mode` ENUM('visual', 'html') DEFAULT 'visual' COMMENT '에디터 모드',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
    INDEX `idx_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '독립 페이지 관리 테이블';

-- 기본 독립 페이지 생성
INSERT INTO `pages` (`title`, `slug`, `content`) VALUES 
('About Us', 'about-us', '<p>This is the About Us page.</p>'),
('Terms of Service', 'terms-of-service', '<p>This is the Terms of Service page.</p>'),
('Privacy Policy', 'privacy-policy', '<p>This is the Privacy Policy page.</p>');

-- 기본 설정값 삽입
INSERT IGNORE INTO `config` (`id`, `site_name`) VALUES (1, 'Neuron AI PHP');

-- 게시판 그룹 테이블
DROP TABLE IF EXISTS `board_groups`;
CREATE TABLE IF NOT EXISTS `board_groups` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '그룹 고유 ID',
    `name` VARCHAR(100) NOT NULL COMMENT '그룹 이름',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT '그룹 슬러그',
    `description` TEXT COMMENT '그룹 설명',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    INDEX `idx_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '게시판 그룹 테이블';

-- 게시판 설정 테이블
DROP TABLE IF EXISTS `boards`;
CREATE TABLE IF NOT EXISTS `boards` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '게시판 고유 ID',
    `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '소속 그룹 ID',
    `title` VARCHAR(100) NOT NULL COMMENT '게시판 제목',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT '게시판 슬러그',
    `description` TEXT COMMENT '게시판 설명',
    `skin` VARCHAR(50) DEFAULT 'basic' COMMENT '게시판 스킨',
    `max_replies` INT(11) DEFAULT 3 COMMENT '원글당 최대 답글 개수',
    `level_list` INT(11) DEFAULT 1 COMMENT '목록 접근 레벨',
    `level_view` INT(11) DEFAULT 1 COMMENT '상세보기 접근 레벨',
    `level_write` INT(11) DEFAULT 1 COMMENT '글쓰기 접근 레벨',
    `level_comment` INT(11) DEFAULT 1 COMMENT '댓글 작성 레벨',
    `point_write` INT(11) DEFAULT 0 COMMENT '글작성 포인트',
    `point_view` INT(11) DEFAULT 0 COMMENT '상세보기 포인트(차감시 음수)',
    `point_comment` INT(11) DEFAULT 0 COMMENT '댓글작성 포인트',
    `allow_comments` TINYINT(1) DEFAULT 1 COMMENT '댓글 허용 여부',
    `page_rows` INT(11) DEFAULT 20 COMMENT '페이지당 출력 행수',
    `page_buttons` INT(11) DEFAULT 5 COMMENT '페이징 버튼 수',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    INDEX `idx_group_id` (`group_id`),
    INDEX `idx_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '게시판 설정 테이블';

-- 기본 게시판 그룹 및 게시판 생성
INSERT INTO `board_groups` (`id`, `name`, `slug`, `description`) VALUES (1, 'community', 'community', 'community group');
INSERT INTO `boards` (`group_id`, `title`, `slug`, `description`, `skin`) VALUES 
(1, 'free board', 'free', 'free board', 'basic'),
(1, 'gallery board', 'gallery', 'gallery board', 'gallery'),
(1, 'blog', 'blog', 'blog', 'blog');

-- 게시글 테이블
DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '게시글 고유 ID',
    `group_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '소속 그룹 ID',
    `board_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '게시판 ID',
    `user_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '작성자 ID',
    `parent_id` INT(11) UNSIGNED DEFAULT NULL COMMENT '부모 글 ID',
    `title` VARCHAR(255) NOT NULL COMMENT '게시글 제목',
    `content` TEXT NOT NULL COMMENT '게시글 내용',
    `reply_depth` INT(11) DEFAULT 0 COMMENT '답글 깊이',
    `views` INT(11) DEFAULT 0 COMMENT '조회수',
    `editor_mode` ENUM('visual', 'html') DEFAULT 'visual' COMMENT '에디터 모드',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '작성 일시',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
    INDEX `idx_board_id` (`board_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '게시글 테이블';

-- 댓글 테이블
DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '댓글 고유 ID',
    `post_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '게시글 ID',
    `user_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '작성자 ID',
    `parent_comment_id` INT(11) UNSIGNED DEFAULT NULL COMMENT '부모 댓글 ID',
    `content` TEXT NOT NULL COMMENT '댓글 내용',
    `reply_depth` INT(11) DEFAULT 0 COMMENT '댓글 깊이',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '작성 일시',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
    INDEX `idx_post_id` (`post_id`),
    INDEX `idx_parent_comment_id` (`parent_comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '댓글 테이블';

-- 게시글 첨부 파일 테이블
DROP TABLE IF EXISTS `post_files`;
CREATE TABLE IF NOT EXISTS `post_files` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '파일 고유 ID',
    `post_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '게시글 ID',
    `filename` VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
    `original_name` VARCHAR(255) NOT NULL COMMENT '원본 파일명',
    `filepath` VARCHAR(255) NOT NULL COMMENT '파일 경로',
    `file_size` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '파일 크기',
    `file_type` VARCHAR(100) COMMENT '파일 타입',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '업로드 일시',
    INDEX `idx_post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '게시글 첨부 파일 테이블';

-- 파일 다운로드 기록 테이블
DROP TABLE IF EXISTS `file_downloads`;
CREATE TABLE IF NOT EXISTS `file_downloads` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '다운로드 기록 ID',
    `file_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '파일 ID',
    `user_id` VARCHAR(255) DEFAULT NULL COMMENT '다운로드한 사용자 ID',
    `ip_address` VARCHAR(45) NOT NULL COMMENT '다운로드 IP 주소',
    `download_count` INT(11) DEFAULT 1 COMMENT '다운로드 횟수',
    `last_downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '마지막 다운로드 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '파일 다운로드 기록 테이블';

-- 접속자 로그 테이블
DROP TABLE IF EXISTS `visitor_logs`;
CREATE TABLE IF NOT EXISTS `visitor_logs` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '로그 고유 ID',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP 주소',
    `country` VARCHAR(50) DEFAULT 'Unknown' COMMENT '국가 코드/이름',
    `user_agent` TEXT COMMENT '브라우저 에이전트',
    `referer` TEXT COMMENT '유입 경로',
    `visit_date` DATE NOT NULL COMMENT '접속 날짜',
    `visit_time` TIME NOT NULL COMMENT '접속 시간',
    `last_active_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '마지막 활성 시간',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '기록 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '접속자 로그 테이블';

CREATE INDEX idx_visit_date ON visitor_logs(visit_date);
CREATE INDEX idx_ip_date ON visitor_logs(ip_address, visit_date);

-- 포인트 이력 테이블
DROP TABLE IF EXISTS `point_log`;
CREATE TABLE IF NOT EXISTS `point_log` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '고유 ID',
    `user_id` VARCHAR(255) NOT NULL COMMENT '사용자 아이디',
    `point` INT(11) NOT NULL COMMENT '지급/차감 포인트',
    `rel_msg` VARCHAR(255) DEFAULT '' COMMENT '관련 사유/메시지',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '일시',
    INDEX `idx_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '포인트 이력 테이블';

-- 메일 발송 로그 테이블
DROP TABLE IF EXISTS `mail_logs`;
CREATE TABLE IF NOT EXISTS `mail_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '고유 ID',
    `target_info` VARCHAR(255) DEFAULT NULL COMMENT '수신 대상 정보',
    `sender_name` VARCHAR(100) DEFAULT NULL COMMENT '발송자 이름',
    `sender_phone` VARCHAR(50) DEFAULT NULL COMMENT '발송자 연락처',
    `sender_email` VARCHAR(255) DEFAULT NULL COMMENT '발송자 이메일',
    `recipient` LONGTEXT NOT NULL COMMENT '수신 이메일 목록',
    `subject` VARCHAR(255) NOT NULL COMMENT '메일 제목',
    `content` LONGTEXT NOT NULL COMMENT '메일 본문',
    `attachments` TEXT DEFAULT NULL COMMENT '첨부 파일 목록',
    `status` VARCHAR(20) NOT NULL DEFAULT 'success' COMMENT '발송 상태',
    `error_message` TEXT DEFAULT NULL COMMENT '에러 메시지',
    `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '발송 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '메일 발송 로그 테이블';

-- FAQ 테이블
DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '고유 ID',
    `category` VARCHAR(50) NOT NULL COMMENT '질문 카테고리',
    `question` VARCHAR(255) NOT NULL COMMENT '질문',
    `answer` TEXT NOT NULL COMMENT '답변',
    `display_order` INT(11) DEFAULT 0 COMMENT '출력 순서',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'FAQ 테이블';


--
-- 여기에서 부터 추가 테이블 참고
-- 테이블 구조 `ace_bank_inout`
--

CREATE TABLE `ace_bank_inout` (
  `num` int NOT NULL COMMENT 'no',
  `mb_id` varchar(255) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_num` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '계좌번호',
  `trand_no` int NOT NULL COMMENT '1 (입금) 2(출금) 숫자출력',
  `trand_text` varchar(100) NOT NULL COMMENT '입금 / 출금 한글출력',
  `dpscrbal_amt` int NOT NULL COMMENT '입출금 후 잔액',
  `fcurrtrd_amt` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '입금 출금 거래금액 78원 입금 했을 때 78.0000 로 출력됨',
  `trd_dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '거래일시간'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COMMENT='계좌 입출금 내역';

--
-- 테이블의 덤프 데이터 `ace_bank_inout`
--

INSERT INTO `ace_bank_inout` (`num`, `mb_id`, `bank_name`, `bank_num`, `trand_no`, `trand_text`, `dpscrbal_amt`, `fcurrtrd_amt`, `trd_dt`) VALUES
(80, '7777', 'LS증권', '20004804105', 1, '입금', 20000, '20000.0000', '2026-02-21 02:33:54');


-- --------------------------------------------------------

--
-- 테이블 구조 `ace_bank_mb`
--

CREATE TABLE `ace_bank_mb` (
  `num` int NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `fintech_use_num` varchar(255) NOT NULL COMMENT '핀테크 이용번호',
  `bank_name` varchar(20) NOT NULL DEFAULT '',
  `account_num_masked` varchar(255) NOT NULL DEFAULT '' COMMENT '계좌번호',
  `account_holder_name` varchar(255) NOT NULL DEFAULT '' COMMENT '예금주',
  `inquiry_agree_dtime` varchar(255) NOT NULL DEFAULT '' COMMENT '조회시간',
  `payer_num` varchar(255) NOT NULL DEFAULT '' COMMENT '납부자번호',
  `bank_type` varchar(20) NOT NULL DEFAULT '' COMMENT '출금통장 transfer 입금통장 inquiry',
  `bank_code_tran` varchar(20) NOT NULL COMMENT '은행코드',
  `balance_amt` int NOT NULL COMMENT '잔액',
  `product_name` varchar(255) NOT NULL COMMENT '계좌종류',
  `num_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '잔액조회 가능한 시간'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- 테이블의 덤프 데이터 `ace_bank_mb`
--

INSERT INTO `ace_bank_mb` (`num`, `mb_id`, `fintech_use_num`, `bank_name`, `account_num_masked`, `account_holder_name`, `inquiry_agree_dtime`, `payer_num`, `bank_type`, `bank_code_tran`, `balance_amt`, `product_name`, `num_datetime`, `next_datetime`) VALUES
(20, '7777', '', 'LS증권', '20004804105', '최진호', '20260226125520', '', 'transfer', '', 22728, 'LS증권', '2026-02-26 12:55:20', '2026-06-27 00:00:00');

-- --------------------------------------------------------

--
-- 테이블 구조 `ace_bank_sum`
--

CREATE TABLE `ace_bank_sum` (
  `num` int NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `day_in` int NOT NULL DEFAULT '0' COMMENT '금일매출입금',
  `day_out` int NOT NULL DEFAULT '0' COMMENT '금일출금',
  `day_acc` int NOT NULL DEFAULT '0' COMMENT '금일목표적립',
  `day_rev` int NOT NULL DEFAULT '0' COMMENT '금일잔고',
  `ab_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '날짜'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- 테이블의 덤프 데이터 `ace_bank_sum`
--

INSERT INTO `ace_bank_sum` (`num`, `mb_id`, `day_in`, `day_out`, `day_acc`, `day_rev`, `ab_date`) VALUES

(89, '7777', 22572, 0, 0, 22572, '2026-02-22'),
(90, '7777', 22575, 0, 280, 22295, '2026-02-26'),
(91, '7777', 22573, 0, 0, 22573, '2026-02-25'),
(92, '7777', 22577, 0, 280, 22297, '2026-03-03'),
(93, '7777', 22585, 0, 280, 22305, '2026-03-06'),
(94, '7777', 22604, 0, 30, 22574, '2026-03-20'),
(95, '7777', 22605, 0, 30, 22575, '2026-03-23'),
(96, '7777', 22611, 0, 50, 22561, '2026-03-25'),
(97, '7777', 22632, 0, 50, 22582, '2026-04-09'),
(98, '7777', 22634, 0, 50, 22584, '2026-04-13'),
(99, '7777', 22679, 0, 30, 22649, '2026-05-14'),
(100, '7777', 22696, 0, 30, 22666, '2026-06-03'),
(101, '7777', 22720, 0, 0, 22720, '2026-06-21'),
(102, '7777', 22725, 0, 30, 22695, '2026-06-24');

-- --------------------------------------------------------

--
-- 테이블 구조 `ace_bank_today`
--

CREATE TABLE `ace_bank_today` (
  `num` int NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '',
  `bank_type` varchar(255) NOT NULL DEFAULT '',
  `api_tran_id` varchar(255) NOT NULL COMMENT '거래고유번호',
  `api_tran_dtm` varchar(255) NOT NULL COMMENT '거래일시',
  `tran_date` varchar(255) NOT NULL DEFAULT '' COMMENT '거래일자 20160310',
  `tran_time` varchar(255) NOT NULL DEFAULT '' COMMENT '거래시간 113000',
  `inout_type` varchar(255) NOT NULL DEFAULT '' COMMENT '입출금구분\r\n("입금", "출금")',
  `print_content` varchar(255) NOT NULL DEFAULT '' COMMENT '통장인자내용',
  `tran_amt` int NOT NULL DEFAULT '0' COMMENT '거래금액',
  `after_balance_amt` int NOT NULL DEFAULT '0' COMMENT '거래 후 잔액',
  `tran_type` varchar(255) NOT NULL COMMENT '현금, 대체, 급여, 타행환, F/B출금 등',
  `fintech_use_num` varchar(255) NOT NULL COMMENT '핀테크이용번호',
  `cron_chk` tinyint NOT NULL COMMENT '크론체크',
  `cron_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '크론실행시간'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- 테이블의 덤프 데이터 `ace_bank_today`
--

INSERT INTO `ace_bank_today` (`num`, `mb_id`, `bank_type`, `api_tran_id`, `api_tran_dtm`, `tran_date`, `tran_time`, `inout_type`, `print_content`, `tran_amt`, `after_balance_amt`, `tran_type`, `fintech_use_num`, `cron_chk`, `cron_datetime`) VALUES
(1207, '7777', 'transfer', '', '20260622121020', '20260622', '010101', '입금', '매출', 22720, 22720, '매출', '', 1, '2026-06-22 00:10:17'),
(1209, '7777', 'transfer', '', '20260623121016', '20260623', '010101', '입금', '매출', 22724, 22724, '매출', '', 1, '2026-06-23 00:10:15'),
(1211, '7777', 'transfer', '', '20260624121018', '20260624', '010101', '입금', '매출', 22725, 22725, '매출', '', 1, '2026-06-24 00:10:16'),
(1213, '7777', 'transfer', '', '20260625121019', '20260625', '010101', '입금', '매출', 22727, 22727, '매출', '', 1, '2026-06-25 00:10:17'),
(1215, '7777', 'transfer', '', '20260626121019', '20260626', '010101', '입금', '매출', 22728, 22728, '매출', '', 1, '2026-06-26 00:10:16');

-- --------------------------------------------------------

--
-- 테이블 구조 `sp_plan`
--

CREATE TABLE `sp_plan` (
  `num` int NOT NULL,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `date_chk` tinyint NOT NULL COMMENT '날짜지정0 미지정1',
  `sp_category_en` varchar(20) NOT NULL COMMENT '메인카테고리 영문',
  `sp_category` varchar(255) NOT NULL DEFAULT '',
  `sp_subject` varchar(255) NOT NULL DEFAULT '',
  `sp_price` int NOT NULL DEFAULT '0' COMMENT '목표금액',
  `sp_price_sum` int NOT NULL COMMENT '매일적립합',
  `from_date` varchar(255) NOT NULL DEFAULT '',
  `to_date` varchar(255) NOT NULL DEFAULT '',
  `out_type` varchar(255) NOT NULL COMMENT '카드,계좌이체등',
  `sp_memo` text NOT NULL,
  `sp_order` int NOT NULL DEFAULT '0' COMMENT '리스트순서',
  `sp_day` int NOT NULL COMMENT '토일공휴일제외한 날짜수',
  `sp_day_arr` text NOT NULL COMMENT '휴일공유일제외 날짜등록',
  `sp_day_price` float NOT NULL DEFAULT '0' COMMENT '매일적립되는 금액',
  `sp_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '등록일',
  `sp_cron` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '크론실행시간',
  `sp_next_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '다음적립일',
  `sp_1` varchar(255) NOT NULL DEFAULT '',
  `sp_2` varchar(255) NOT NULL DEFAULT '',
  `sp_3` varchar(255) NOT NULL DEFAULT '',
  `sp_4` varchar(255) NOT NULL DEFAULT '',
  `sp_5` varchar(255) NOT NULL DEFAULT '',
  `sp_use` tinyint NOT NULL DEFAULT '0' COMMENT '적립중 0, 목표완료 1, 출금완료 2, 적립중지 3, 취소 4',
  `sp_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '입금/ 취소시간',
  `in_chk` int NOT NULL COMMENT '입금체크',
  `in_chk_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '입금시간'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- 테이블의 덤프 데이터 `sp_plan`
--

INSERT INTO `sp_plan` (`num`, `mb_id`, `date_chk`, `sp_category_en`, `sp_category`, `sp_subject`, `sp_price`, `sp_price_sum`, `from_date`, `to_date`, `out_type`, `sp_memo`, `sp_order`, `sp_day`, `sp_day_arr`, `sp_day_price`, `sp_datetime`, `sp_cron`, `sp_next_date`, `sp_1`, `sp_2`, `sp_3`, `sp_4`, `sp_5`, `sp_use`, `sp_end_date`, `in_chk`, `in_chk_date`) VALUES
(80, '7777', 1, '', '사업용도|임대관련|정액', '임대관련 ', 0, 2610, '2026-02-26', '2026-03-08', '', '', 1, 0, '20260226,20260227,20260302,20260303,20260304,20260305,20260306', 30, '2026-02-26 12:31:36', '2026-06-26 00:10:16', '2026-06-29', '사업용도', '임대관련', '정액', 'C', '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(81, '7777', 0, '', '개인용도|공과금|월단위', '공과금 ', 2000, 2000, '2026-02-26', '2026-03-10', '', '', 2, 8, '20260226,20260227,20260302,20260303,20260304,20260305,20260306,20260309,20260310', 250, '2026-02-26 12:32:23', '2026-03-09 00:10:06', '2026-03-10', '개인용도', '공과금', '월단위', 'A', '10', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(82, '7777', 0, '', '사업용도|임대관련|월단위', '임대료', 300, 300, '2026-03-24', '2026-04-20', '', '', 3, 20, '20260324,20260325,20260326,20260327,20260330,20260331,20260401,20260402,20260403,20260406,20260407,20260408,20260409,20260410,20260413,20260414,20260415,20260416,20260417,20260420', 20, '2026-03-23 07:14:13', '2026-04-13 00:10:08', '2026-04-14', '사업용도', '임대관련', '월단위', 'A', '20', 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 테이블 구조 `sp_plan_list`
--

CREATE TABLE `sp_plan_list` (
  `num` int NOT NULL,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `plan_num` int NOT NULL,
  `sp_category` varchar(255) NOT NULL DEFAULT '',
  `sp_subject` varchar(255) NOT NULL DEFAULT '',
  `sp_price` int NOT NULL COMMENT '금일입금액',
  `sp_plan_price` int NOT NULL COMMENT '일일목표적립금액',
  `sp_in_out_price` int NOT NULL COMMENT '금일적립금액',
  `sp_memo` varchar(255) NOT NULL,
  `sp_memo_type` varchar(20) NOT NULL COMMENT 'a정상 b잔액부족잔금적립 c잔액부족적립실패 d채우기적립 e비우기 차감',
  `sp_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '적립일',
  `sp_time2` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '적립일 상세시간',
  `sp_use` tinyint NOT NULL COMMENT '적립중 0, 목표완료 1, 출금완료 2, 적립중지 3, 취소 4',
  `sp_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '입금 / 취소시간'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- 테이블의 덤프 데이터 `sp_plan_list`
--

INSERT INTO `sp_plan_list` (`num`, `mb_id`, `plan_num`, `sp_category`, `sp_subject`, `sp_price`, `sp_plan_price`, `sp_in_out_price`, `sp_memo`, `sp_memo_type`, `sp_time`, `sp_time2`, `sp_use`, `sp_end_date`) VALUES

(1146, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 45150, 30, 30, '정상적립', 'A', '2026-02-26', '2026-02-26 13:19:11', 0, '0000-00-00 00:00:00'),
(1147, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 45150, 250, 250, '정상적립', 'A', '2026-02-26', '2026-02-26 13:19:11', 0, '0000-00-00 00:00:00'),
(1148, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22575, 30, 30, '정상적립', 'A', '2026-02-27', '2026-02-27 00:10:06', 0, '0000-00-00 00:00:00'),
(1149, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22575, 250, 250, '정상적립', 'A', '2026-02-27', '2026-02-27 00:10:06', 0, '0000-00-00 00:00:00'),
(1150, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22577, 30, 30, '정상적립', 'A', '2026-03-02', '2026-03-02 00:10:05', 0, '0000-00-00 00:00:00'),
(1151, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22577, 250, 250, '정상적립', 'A', '2026-03-02', '2026-03-02 00:10:05', 0, '0000-00-00 00:00:00'),
(1152, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22577, 30, 30, '정상적립', 'A', '2026-03-03', '2026-03-03 00:10:07', 0, '0000-00-00 00:00:00'),
(1153, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22577, 250, 250, '정상적립', 'A', '2026-03-03', '2026-03-03 00:10:07', 0, '0000-00-00 00:00:00'),
(1154, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22582, 30, 30, '정상적립', 'A', '2026-03-04', '2026-03-04 00:10:06', 0, '0000-00-00 00:00:00'),
(1155, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22582, 250, 250, '정상적립', 'A', '2026-03-04', '2026-03-04 00:10:06', 0, '0000-00-00 00:00:00'),
(1156, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22583, 30, 30, '정상적립', 'A', '2026-03-05', '2026-03-05 00:10:06', 0, '0000-00-00 00:00:00'),
(1157, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22583, 250, 250, '정상적립', 'A', '2026-03-05', '2026-03-05 00:10:06', 0, '0000-00-00 00:00:00'),
(1158, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22585, 30, 30, '정상적립', 'A', '2026-03-06', '2026-03-06 00:10:07', 0, '0000-00-00 00:00:00'),
(1159, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22585, 250, 250, '정상적립', 'A', '2026-03-06', '2026-03-06 00:10:07', 0, '0000-00-00 00:00:00'),
(1160, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22586, 30, 30, '정상적립', 'A', '2026-03-09', '2026-03-09 00:10:06', 0, '0000-00-00 00:00:00'),
(1161, '7777', 81, '개인용도|공과금|월단위', '공과금 ', 22586, 250, 250, '정상적립', 'A', '2026-03-09', '2026-03-09 00:10:06', 0, '0000-00-00 00:00:00'),
(1162, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22590, 30, 30, '정상적립', 'A', '2026-03-10', '2026-03-10 00:10:06', 0, '0000-00-00 00:00:00'),
(1163, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22592, 30, 30, '정상적립', 'A', '2026-03-11', '2026-03-11 00:10:06', 0, '0000-00-00 00:00:00'),
(1164, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22593, 30, 30, '정상적립', 'A', '2026-03-12', '2026-03-12 00:10:06', 0, '0000-00-00 00:00:00'),
(1165, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22595, 30, 30, '정상적립', 'A', '2026-03-13', '2026-03-13 00:10:06', 0, '0000-00-00 00:00:00'),
(1166, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22596, 30, 30, '정상적립', 'A', '2026-03-16', '2026-03-16 00:10:06', 0, '0000-00-00 00:00:00'),
(1167, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22600, 30, 30, '정상적립', 'A', '2026-03-17', '2026-03-17 00:10:07', 0, '0000-00-00 00:00:00'),
(1168, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22601, 30, 30, '정상적립', 'A', '2026-03-18', '2026-03-18 00:10:06', 0, '0000-00-00 00:00:00'),
(1169, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22602, 30, 30, '정상적립', 'A', '2026-03-19', '2026-03-19 00:10:06', 0, '0000-00-00 00:00:00'),
(1170, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22604, 30, 30, '정상적립', 'A', '2026-03-20', '2026-03-20 00:10:06', 0, '0000-00-00 00:00:00'),
(1171, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22605, 30, 30, '정상적립', 'A', '2026-03-23', '2026-03-23 00:10:07', 0, '0000-00-00 00:00:00'),
(1172, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22609, 30, 30, '정상적립', 'A', '2026-03-24', '2026-03-24 00:10:07', 0, '0000-00-00 00:00:00'),
(1173, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22609, 20, 20, '정상적립', 'A', '2026-03-24', '2026-03-24 00:10:07', 0, '0000-00-00 00:00:00'),
(1174, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22611, 30, 30, '정상적립', 'A', '2026-03-25', '2026-03-25 00:10:07', 0, '0000-00-00 00:00:00'),
(1175, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22611, 20, 20, '정상적립', 'A', '2026-03-25', '2026-03-25 00:10:07', 0, '0000-00-00 00:00:00'),
(1176, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22613, 30, 30, '정상적립', 'A', '2026-03-26', '2026-03-26 00:10:07', 0, '0000-00-00 00:00:00'),
(1177, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22613, 20, 20, '정상적립', 'A', '2026-03-26', '2026-03-26 00:10:07', 0, '0000-00-00 00:00:00'),
(1178, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22614, 30, 30, '정상적립', 'A', '2026-03-27', '2026-03-27 00:10:07', 0, '0000-00-00 00:00:00'),
(1179, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22614, 20, 20, '정상적립', 'A', '2026-03-27', '2026-03-27 00:10:07', 0, '0000-00-00 00:00:00'),
(1180, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22615, 30, 30, '정상적립', 'A', '2026-03-30', '2026-03-30 00:10:08', 0, '0000-00-00 00:00:00'),
(1181, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22615, 20, 20, '정상적립', 'A', '2026-03-30', '2026-03-30 00:10:08', 0, '0000-00-00 00:00:00'),
(1182, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22619, 30, 30, '정상적립', 'A', '2026-03-31', '2026-03-31 00:10:08', 0, '0000-00-00 00:00:00'),
(1183, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22619, 20, 20, '정상적립', 'A', '2026-03-31', '2026-03-31 00:10:08', 0, '0000-00-00 00:00:00'),
(1184, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22620, 30, 30, '정상적립', 'A', '2026-04-01', '2026-04-01 00:10:08', 0, '0000-00-00 00:00:00'),
(1185, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22620, 20, 20, '정상적립', 'A', '2026-04-01', '2026-04-01 00:10:08', 0, '0000-00-00 00:00:00'),
(1186, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22622, 30, 30, '정상적립', 'A', '2026-04-02', '2026-04-02 00:10:08', 0, '0000-00-00 00:00:00'),
(1187, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22622, 20, 20, '정상적립', 'A', '2026-04-02', '2026-04-02 00:10:08', 0, '0000-00-00 00:00:00'),
(1188, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22623, 30, 30, '정상적립', 'A', '2026-04-03', '2026-04-03 00:10:07', 0, '0000-00-00 00:00:00'),
(1189, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22623, 20, 20, '정상적립', 'A', '2026-04-03', '2026-04-03 00:10:07', 0, '0000-00-00 00:00:00'),
(1190, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22624, 30, 30, '정상적립', 'A', '2026-04-06', '2026-04-06 00:10:08', 0, '0000-00-00 00:00:00'),
(1191, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22624, 20, 20, '정상적립', 'A', '2026-04-06', '2026-04-06 00:10:08', 0, '0000-00-00 00:00:00'),
(1192, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22629, 30, 30, '정상적립', 'A', '2026-04-07', '2026-04-07 00:10:07', 0, '0000-00-00 00:00:00'),
(1193, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22629, 20, 20, '정상적립', 'A', '2026-04-07', '2026-04-07 00:10:07', 0, '0000-00-00 00:00:00'),
(1194, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22630, 30, 30, '정상적립', 'A', '2026-04-08', '2026-04-08 00:10:08', 0, '0000-00-00 00:00:00'),
(1195, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22630, 20, 20, '정상적립', 'A', '2026-04-08', '2026-04-08 00:10:08', 0, '0000-00-00 00:00:00'),
(1196, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22632, 30, 30, '정상적립', 'A', '2026-04-09', '2026-04-09 00:10:07', 0, '0000-00-00 00:00:00'),
(1197, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22632, 20, 20, '정상적립', 'A', '2026-04-09', '2026-04-09 00:10:07', 0, '0000-00-00 00:00:00'),
(1198, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22633, 30, 30, '정상적립', 'A', '2026-04-10', '2026-04-10 00:10:08', 0, '0000-00-00 00:00:00'),
(1199, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22633, 20, 20, '정상적립', 'A', '2026-04-10', '2026-04-10 00:10:08', 0, '0000-00-00 00:00:00'),
(1200, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22634, 30, 30, '정상적립', 'A', '2026-04-13', '2026-04-13 00:10:08', 0, '0000-00-00 00:00:00'),
(1201, '7777', 82, '사업용도|임대관련|월단위', '임대료', 22634, 20, 20, '정상적립', 'A', '2026-04-13', '2026-04-13 00:10:08', 0, '0000-00-00 00:00:00'),
(1202, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22638, 30, 30, '정상적립', 'A', '2026-04-14', '2026-04-14 00:10:07', 0, '0000-00-00 00:00:00'),
(1203, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22640, 30, 30, '정상적립', 'A', '2026-04-15', '2026-04-15 00:10:08', 0, '0000-00-00 00:00:00'),
(1204, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22641, 30, 30, '정상적립', 'A', '2026-04-16', '2026-04-16 00:10:08', 0, '0000-00-00 00:00:00'),
(1205, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22642, 30, 30, '정상적립', 'A', '2026-04-17', '2026-04-17 00:10:08', 0, '0000-00-00 00:00:00'),
(1206, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22644, 30, 30, '정상적립', 'A', '2026-04-20', '2026-04-20 00:10:07', 0, '0000-00-00 00:00:00'),
(1207, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22648, 30, 30, '정상적립', 'A', '2026-04-21', '2026-04-21 00:10:07', 0, '0000-00-00 00:00:00'),
(1208, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22650, 30, 30, '정상적립', 'A', '2026-04-22', '2026-04-22 00:10:10', 0, '0000-00-00 00:00:00'),
(1209, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22651, 30, 30, '정상적립', 'A', '2026-04-23', '2026-04-23 00:10:08', 0, '0000-00-00 00:00:00'),
(1210, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22652, 30, 30, '정상적립', 'A', '2026-04-24', '2026-04-24 00:10:07', 0, '0000-00-00 00:00:00'),
(1211, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22654, 30, 30, '정상적립', 'A', '2026-04-27', '2026-04-27 00:10:07', 0, '0000-00-00 00:00:00'),
(1212, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22658, 30, 30, '정상적립', 'A', '2026-04-28', '2026-04-28 00:10:08', 0, '0000-00-00 00:00:00'),
(1213, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22659, 30, 30, '정상적립', 'A', '2026-04-29', '2026-04-29 00:10:08', 0, '0000-00-00 00:00:00'),
(1214, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22660, 30, 30, '정상적립', 'A', '2026-04-30', '2026-04-30 00:10:08', 0, '0000-00-00 00:00:00'),
(1215, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22661, 30, 30, '정상적립', 'A', '2026-05-01', '2026-05-01 00:10:07', 0, '0000-00-00 00:00:00'),
(1216, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22661, 30, 30, '정상적립', 'A', '2026-05-04', '2026-05-04 00:10:07', 0, '0000-00-00 00:00:00'),
(1217, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22668, 30, 30, '정상적립', 'A', '2026-05-05', '2026-05-05 00:10:08', 0, '0000-00-00 00:00:00'),
(1218, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22668, 30, 30, '정상적립', 'A', '2026-05-06', '2026-05-06 00:10:07', 0, '0000-00-00 00:00:00'),
(1219, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22670, 30, 30, '정상적립', 'A', '2026-05-07', '2026-05-07 00:10:07', 0, '0000-00-00 00:00:00'),
(1220, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22672, 30, 30, '정상적립', 'A', '2026-05-08', '2026-05-08 00:10:08', 0, '0000-00-00 00:00:00'),
(1221, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22673, 30, 30, '정상적립', 'A', '2026-05-11', '2026-05-11 00:10:08', 0, '0000-00-00 00:00:00'),
(1222, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22677, 30, 30, '정상적립', 'A', '2026-05-12', '2026-05-12 00:10:08', 0, '0000-00-00 00:00:00'),
(1223, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22678, 30, 30, '정상적립', 'A', '2026-05-13', '2026-05-13 00:10:07', 0, '0000-00-00 00:00:00'),
(1224, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22679, 30, 30, '정상적립', 'A', '2026-05-14', '2026-05-14 00:10:08', 0, '0000-00-00 00:00:00'),
(1225, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22681, 30, 30, '정상적립', 'A', '2026-05-15', '2026-05-15 00:10:08', 0, '0000-00-00 00:00:00'),
(1226, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22682, 30, 30, '정상적립', 'A', '2026-05-18', '2026-05-18 00:10:09', 0, '0000-00-00 00:00:00'),
(1227, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22687, 30, 30, '정상적립', 'A', '2026-05-19', '2026-05-19 00:10:08', 0, '0000-00-00 00:00:00'),
(1228, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22688, 30, 30, '정상적립', 'A', '2026-05-20', '2026-05-20 00:10:08', 0, '0000-00-00 00:00:00'),
(1229, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22689, 30, 30, '정상적립', 'A', '2026-05-21', '2026-05-21 00:10:09', 0, '0000-00-00 00:00:00'),
(1230, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22691, 30, 30, '정상적립', 'A', '2026-05-22', '2026-05-22 00:10:09', 0, '0000-00-00 00:00:00'),
(1231, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22692, 30, 30, '정상적립', 'A', '2026-05-25', '2026-05-25 00:10:09', 0, '0000-00-00 00:00:00'),
(1232, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22692, 30, 30, '정상적립', 'A', '2026-05-26', '2026-05-26 00:10:08', 0, '0000-00-00 00:00:00'),
(1233, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22687, 30, 30, '정상적립', 'A', '2026-05-27', '2026-05-27 00:10:09', 0, '0000-00-00 00:00:00'),
(1234, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22688, 30, 30, '정상적립', 'A', '2026-05-28', '2026-05-28 00:10:09', 0, '0000-00-00 00:00:00'),
(1235, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22689, 30, 30, '정상적립', 'A', '2026-05-29', '2026-05-29 00:10:10', 0, '0000-00-00 00:00:00'),
(1236, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22691, 30, 30, '정상적립', 'A', '2026-06-01', '2026-06-01 00:10:09', 0, '0000-00-00 00:00:00'),
(1237, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22695, 30, 30, '정상적립', 'A', '2026-06-02', '2026-06-02 00:10:09', 0, '0000-00-00 00:00:00'),
(1238, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22696, 30, 30, '정상적립', 'A', '2026-06-03', '2026-06-03 00:10:11', 0, '0000-00-00 00:00:00'),
(1239, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22696, 30, 30, '정상적립', 'A', '2026-06-04', '2026-06-04 00:10:13', 0, '0000-00-00 00:00:00'),
(1240, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22699, 30, 30, '정상적립', 'A', '2026-06-05', '2026-06-05 00:10:11', 0, '0000-00-00 00:00:00'),
(1241, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22700, 30, 30, '정상적립', 'A', '2026-06-08', '2026-06-08 00:10:11', 0, '0000-00-00 00:00:00'),
(1242, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22704, 30, 30, '정상적립', 'A', '2026-06-09', '2026-06-09 00:10:12', 0, '0000-00-00 00:00:00'),
(1243, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22706, 30, 30, '정상적립', 'A', '2026-06-10', '2026-06-10 00:10:12', 0, '0000-00-00 00:00:00'),
(1244, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22707, 30, 30, '정상적립', 'A', '2026-06-11', '2026-06-11 00:10:17', 0, '0000-00-00 00:00:00'),
(1245, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22709, 30, 30, '정상적립', 'A', '2026-06-12', '2026-06-12 00:10:13', 0, '0000-00-00 00:00:00'),
(1246, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22710, 30, 30, '정상적립', 'A', '2026-06-15', '2026-06-15 00:10:14', 0, '0000-00-00 00:00:00'),
(1247, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22714, 30, 30, '정상적립', 'A', '2026-06-16', '2026-06-16 00:10:13', 0, '0000-00-00 00:00:00'),
(1248, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22716, 30, 30, '정상적립', 'A', '2026-06-17', '2026-06-17 00:10:14', 0, '0000-00-00 00:00:00'),
(1249, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22717, 30, 30, '정상적립', 'A', '2026-06-18', '2026-06-18 00:10:15', 0, '0000-00-00 00:00:00'),
(1250, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22718, 30, 30, '정상적립', 'A', '2026-06-19', '2026-06-19 00:10:16', 0, '0000-00-00 00:00:00'),
(1251, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22720, 30, 30, '정상적립', 'A', '2026-06-22', '2026-06-22 00:10:17', 0, '0000-00-00 00:00:00'),
(1252, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22724, 30, 30, '정상적립', 'A', '2026-06-23', '2026-06-23 00:10:15', 0, '0000-00-00 00:00:00'),
(1253, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22725, 30, 30, '정상적립', 'A', '2026-06-24', '2026-06-24 00:10:16', 0, '0000-00-00 00:00:00'),
(1254, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22727, 30, 30, '정상적립', 'A', '2026-06-25', '2026-06-25 00:10:17', 0, '0000-00-00 00:00:00'),
(1255, '7777', 80, '사업용도|임대관련|정액', '임대관련 ', 22728, 30, 30, '정상적립', 'A', '2026-06-26', '2026-06-26 00:10:16', 0, '0000-00-00 00:00:00');

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `ace_bank_inout`
--
ALTER TABLE `ace_bank_inout`
  ADD PRIMARY KEY (`num`);

--
-- 테이블의 인덱스 `ace_bank_mb`
--
ALTER TABLE `ace_bank_mb`
  ADD PRIMARY KEY (`num`);

--
-- 테이블의 인덱스 `ace_bank_sum`
--
ALTER TABLE `ace_bank_sum`
  ADD PRIMARY KEY (`num`),
  ADD KEY `mb_id` (`mb_id`),
  ADD KEY `ad_date` (`ab_date`) USING BTREE;

--
-- 테이블의 인덱스 `ace_bank_today`
--
ALTER TABLE `ace_bank_today`
  ADD PRIMARY KEY (`num`);

--
-- 테이블의 인덱스 `sp_plan`
--
ALTER TABLE `sp_plan`
  ADD PRIMARY KEY (`num`);

--
-- 테이블의 인덱스 `sp_plan_list`
--
ALTER TABLE `sp_plan_list`
  ADD PRIMARY KEY (`num`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `ace_bank_inout`
--
ALTER TABLE `ace_bank_inout`
  MODIFY `num` int NOT NULL AUTO_INCREMENT COMMENT 'no', AUTO_INCREMENT=81;

--
-- 테이블의 AUTO_INCREMENT `ace_bank_mb`
--
ALTER TABLE `ace_bank_mb`
  MODIFY `num` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 테이블의 AUTO_INCREMENT `ace_bank_sum`
--
ALTER TABLE `ace_bank_sum`
  MODIFY `num` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- 테이블의 AUTO_INCREMENT `ace_bank_today`
--
ALTER TABLE `ace_bank_today`
  MODIFY `num` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1216;

--
-- 테이블의 AUTO_INCREMENT `sp_plan`
--
ALTER TABLE `sp_plan`
  MODIFY `num` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- 테이블의 AUTO_INCREMENT `sp_plan_list`
--
ALTER TABLE `sp_plan_list`
  MODIFY `num` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1256;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;


