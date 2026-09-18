SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ai_config`;
CREATE TABLE `ai_config` (
  `id` int(11) NOT NULL DEFAULT 1,
  `openai_key` varchar(255) DEFAULT '',
  `claude_key` varchar(255) DEFAULT '',
  `gemini_key` varchar(255) DEFAULT '',
  `groq_key` varchar(255) DEFAULT '',
  `meta_key` varchar(255) DEFAULT NULL,
  `default_model` varchar(50) DEFAULT 'gpt-4o',
  `use_chatbot` tinyint(1) DEFAULT 0,
  `chatbot_limit_guest` int(11) DEFAULT 5,
  `chatbot_limit_member` int(11) DEFAULT 20,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='AI Configuration Table';

DROP TABLE IF EXISTS `board_groups`;
CREATE TABLE `board_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '그룹 고유 ID',
  `name` varchar(100) NOT NULL COMMENT '그룹 이름',
  `slug` varchar(100) NOT NULL COMMENT '그룹 슬러그',
  `description` text DEFAULT NULL COMMENT '그룹 설명',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='게시판 그룹 테이블';

INSERT INTO `board_groups` (`id`, `name`, `slug`, `description`, `created_at`) VALUES ('1', 'community', 'community', 'community group', '2026-08-07 15:31:48');

DROP TABLE IF EXISTS `boards`;
CREATE TABLE `boards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시판 고유 ID',
  `group_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '소속 그룹 ID',
  `title` varchar(100) NOT NULL COMMENT '게시판 제목',
  `slug` varchar(100) NOT NULL COMMENT '게시판 슬러그',
  `description` text DEFAULT NULL COMMENT '게시판 설명',
  `skin` varchar(50) DEFAULT 'basic' COMMENT '게시판 스킨',
  `max_replies` int(11) DEFAULT 3 COMMENT '원글당 최대 답글 개수',
  `level_list` int(11) DEFAULT 1 COMMENT '목록 접근 레벨',
  `level_view` int(11) DEFAULT 1 COMMENT '상세보기 접근 레벨',
  `level_write` int(11) DEFAULT 1 COMMENT '글쓰기 접근 레벨',
  `level_comment` int(11) DEFAULT 1 COMMENT '댓글 작성 레벨',
  `point_write` int(11) DEFAULT 0 COMMENT '글작성 포인트',
  `point_view` int(11) DEFAULT 0 COMMENT '상세보기 포인트(차감시 음수)',
  `point_comment` int(11) DEFAULT 0 COMMENT '댓글작성 포인트',
  `allow_comments` tinyint(1) DEFAULT 1 COMMENT '댓글 허용 여부',
  `page_rows` int(11) DEFAULT 20 COMMENT '페이지당 출력 행수',
  `page_buttons` int(11) DEFAULT 5 COMMENT '페이징 버튼 수',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='게시판 설정 테이블';

INSERT INTO `boards` (`id`, `group_id`, `title`, `slug`, `description`, `skin`, `max_replies`, `level_list`, `level_view`, `level_write`, `level_comment`, `point_write`, `point_view`, `point_comment`, `allow_comments`, `page_rows`, `page_buttons`, `created_at`) VALUES ('1', '1', 'free board', 'free', 'free board', 'basic', '3', '1', '1', '1', '1', '0', '0', '0', '1', '20', '5', '2026-08-07 15:31:48');
INSERT INTO `boards` (`id`, `group_id`, `title`, `slug`, `description`, `skin`, `max_replies`, `level_list`, `level_view`, `level_write`, `level_comment`, `point_write`, `point_view`, `point_comment`, `allow_comments`, `page_rows`, `page_buttons`, `created_at`) VALUES ('2', '1', 'gallery board', 'gallery', 'gallery board', 'gallery', '3', '1', '1', '1', '1', '0', '0', '0', '1', '20', '5', '2026-08-07 15:31:48');
INSERT INTO `boards` (`id`, `group_id`, `title`, `slug`, `description`, `skin`, `max_replies`, `level_list`, `level_view`, `level_write`, `level_comment`, `point_write`, `point_view`, `point_comment`, `allow_comments`, `page_rows`, `page_buttons`, `created_at`) VALUES ('3', '1', 'blog', 'blog', 'blog', 'blog', '3', '1', '1', '1', '1', '0', '0', '0', '1', '20', '5', '2026-08-07 15:31:48');

DROP TABLE IF EXISTS `bom_part_prices`;
CREATE TABLE `bom_part_prices` (
  `price_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `cost_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '원가',
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '기준 판매가',
  `margin_rate` decimal(5,2) DEFAULT 15.00 COMMENT '마진율(%)',
  `effective_date` date DEFAULT NULL COMMENT '단가 적용 시작일',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`price_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `bom_parts`;
CREATE TABLE `bom_parts` (
  `part_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL,
  `part_code` varchar(50) NOT NULL COMMENT '부품 식별 코드',
  `part_name` varchar(100) NOT NULL COMMENT '부품명',
  `part_category` varchar(50) DEFAULT NULL COMMENT '부품 종류 (예: FRAME, BEAM, BRACING)',
  `spec_height` int(11) DEFAULT 0 COMMENT '높이(mm)',
  `spec_length` int(11) DEFAULT 0 COMMENT '길이(mm)',
  `spec_depth` int(11) DEFAULT 0 COMMENT '깊이(mm)',
  `spec_thickness` varchar(20) DEFAULT NULL COMMENT '두께(예: 1.5t, 100바)',
  `load_capacity` int(11) DEFAULT 0 COMMENT '허용 하중(kg)',
  `unit` varchar(10) DEFAULT 'EA' COMMENT '단위',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`part_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `bom_rules`;
CREATE TABLE `bom_rules` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL,
  `parent_type` varchar(50) NOT NULL COMMENT '대상 랙 타입 (예: RACK_STANDALONE)',
  `child_part_id` int(11) NOT NULL COMMENT '소요 부품 ID',
  `calc_formula_type` varchar(50) NOT NULL COMMENT '계산 타입 (예: FIXED, PER_LEVEL, BY_HEIGHT)',
  `base_qty` int(11) DEFAULT 1 COMMENT '기본 수량',
  `multiplier` decimal(8,2) DEFAULT 1.00 COMMENT '계산 승수',
  `condition_json` text DEFAULT NULL COMMENT '구조화된 조건식 JSON',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chatbot_logs`;
CREATE TABLE `chatbot_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '댓글 고유 ID',
  `post_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 ID',
  `user_id` varchar(255) NOT NULL DEFAULT '' COMMENT '작성자 ID',
  `parent_comment_id` int(11) unsigned DEFAULT NULL COMMENT '부모 댓글 ID',
  `content` text NOT NULL COMMENT '댓글 내용',
  `reply_depth` int(11) DEFAULT 0 COMMENT '댓글 깊이',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_parent_comment_id` (`parent_comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='댓글 테이블';

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL DEFAULT 1 COMMENT '고유 ID',
  `site_name` varchar(100) DEFAULT 'Neuron AI PHP' COMMENT '사이트 명',
  `company_name` varchar(100) DEFAULT '' COMMENT '회사 명',
  `company_owner` varchar(50) DEFAULT '' COMMENT '대표자 명',
  `company_license_num` varchar(50) DEFAULT '' COMMENT '사업자 등록번호',
  `company_tel` varchar(50) DEFAULT '' COMMENT '회사 전화번호',
  `company_email` varchar(100) DEFAULT '' COMMENT '회사 이메일',
  `company_address` varchar(255) DEFAULT '' COMMENT '회사 주소',
  `company_info` text DEFAULT NULL COMMENT '회사 소개내용',
  `logo_type` enum('text','image') DEFAULT 'text' COMMENT '로고 타입',
  `logo_text` varchar(100) DEFAULT '' COMMENT '로고 텍스트',
  `logo_image` varchar(255) DEFAULT '' COMMENT '로고 이미지 경로',
  `template` varchar(50) DEFAULT 'basic' COMMENT '사이트 템플릿',
  `join_point` int(11) DEFAULT 0 COMMENT '가입시 지급 포인트',
  `join_level` int(11) DEFAULT 1 COMMENT '가입시 부여 레벨',
  `mall_commission` int(11) DEFAULT 10,
  `mall_name` varchar(255) DEFAULT 'Open Market',
  `allowed_ips` text DEFAULT NULL COMMENT '접속 허용 IP 목록',
  `blocked_ips` text DEFAULT NULL COMMENT '접속 차단 IP 목록',
  `faq_category` varchar(255) DEFAULT '회원|포인트|게시판|기타' COMMENT 'FAQ 카테고리',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='사이트 정보 설정 테이블';

INSERT INTO `config` (`id`, `site_name`) VALUES (1, 'Neuron AI PHP');

DROP TABLE IF EXISTS `employee_logs`;
CREATE TABLE `employee_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `excel_mappings`;
CREATE TABLE `excel_mappings` (
  `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL,
  `sheet_type` varchar(50) NOT NULL COMMENT '시트 유형 (예: PARTS, PRICES)',
  `source_column_index` varchar(10) NOT NULL COMMENT '엑셀 열 번호 (예: 0, 1, 2 또는 A, B, C)',
  `target_field_name` varchar(50) NOT NULL COMMENT 'DB 컬럼명',
  `header_name` varchar(100) DEFAULT NULL COMMENT '엑셀 헤더명',
  PRIMARY KEY (`mapping_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `excel_upload_history`;
CREATE TABLE `excel_upload_history` (
  `upload_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_status` varchar(50) DEFAULT 'SUCCESS',
  `parsed_rows_count` int(11) DEFAULT 0,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `faq`;
CREATE TABLE `faq` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '고유 ID',
  `category` varchar(50) NOT NULL COMMENT '질문 카테고리',
  `question` varchar(255) NOT NULL COMMENT '질문',
  `answer` text NOT NULL COMMENT '답변',
  `display_order` int(11) DEFAULT 0 COMMENT '출력 순서',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='FAQ 테이블';

DROP TABLE IF EXISTS `file_downloads`;
CREATE TABLE `file_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '다운로드 기록 ID',
  `file_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '파일 ID',
  `user_id` varchar(255) DEFAULT NULL COMMENT '다운로드한 사용자 ID',
  `ip_address` varchar(45) NOT NULL COMMENT '다운로드 IP 주소',
  `download_count` int(11) DEFAULT 1 COMMENT '다운로드 횟수',
  `last_downloaded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '마지막 다운로드 일시',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='파일 다운로드 기록 테이블';

DROP TABLE IF EXISTS `mail_logs`;
CREATE TABLE `mail_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유 ID',
  `target_info` varchar(255) DEFAULT NULL COMMENT '수신 대상 정보',
  `sender_name` varchar(100) DEFAULT NULL COMMENT '발송자 이름',
  `sender_phone` varchar(50) DEFAULT NULL COMMENT '발송자 연락처',
  `sender_email` varchar(255) DEFAULT NULL COMMENT '발송자 이메일',
  `recipient` longtext NOT NULL COMMENT '수신 이메일 목록',
  `subject` varchar(255) NOT NULL COMMENT '메일 제목',
  `content` longtext NOT NULL COMMENT '메일 본문',
  `attachments` text DEFAULT NULL COMMENT '첨부 파일 목록',
  `status` varchar(20) NOT NULL DEFAULT 'success' COMMENT '발송 상태',
  `error_message` text DEFAULT NULL COMMENT '에러 메시지',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '발송 일시',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='메일 발송 로그 테이블';

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '페이지 고유 ID',
  `title` varchar(255) NOT NULL COMMENT '페이지 제목',
  `slug` varchar(100) NOT NULL COMMENT '페이지 URL 슬러그',
  `content` longtext NOT NULL COMMENT '페이지 내용',
  `display_title` tinyint(1) DEFAULT 1 COMMENT '제목 표시 여부',
  `use_card_style` tinyint(1) DEFAULT 1 COMMENT '카드 스타일 사용 여부',
  `editor_mode` enum('visual','html') DEFAULT 'visual' COMMENT '에디터 모드',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='독립 페이지 관리 테이블';

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `display_title`, `use_card_style`, `editor_mode`, `created_at`, `updated_at`) VALUES ('1', 'About Us', 'about-us', '<p>This is the About Us page.</p>', '1', '1', 'visual', '2026-08-07 15:31:48', '2026-08-07 15:31:48');
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `display_title`, `use_card_style`, `editor_mode`, `created_at`, `updated_at`) VALUES ('2', 'Terms of Service', 'terms-of-service', '<p>This is the Terms of Service page.</p>', '1', '1', 'visual', '2026-08-07 15:31:48', '2026-08-07 15:31:48');
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `display_title`, `use_card_style`, `editor_mode`, `created_at`, `updated_at`) VALUES ('3', 'Privacy Policy', 'privacy-policy', '<p>This is the Privacy Policy page.</p>', '1', '1', 'visual', '2026-08-07 15:31:48', '2026-08-07 15:31:48');

DROP TABLE IF EXISTS `point_log`;
CREATE TABLE `point_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '고유 ID',
  `user_id` varchar(255) NOT NULL COMMENT '사용자 아이디',
  `point` int(11) NOT NULL COMMENT '지급/차감 포인트',
  `rel_msg` varchar(255) DEFAULT '' COMMENT '관련 사유/메시지',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '일시',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='포인트 이력 테이블';

DROP TABLE IF EXISTS `post_files`;
CREATE TABLE `post_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '파일 고유 ID',
  `post_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '게시글 ID',
  `filename` varchar(255) NOT NULL COMMENT '저장된 파일명',
  `original_name` varchar(255) NOT NULL COMMENT '원본 파일명',
  `filepath` varchar(255) NOT NULL COMMENT '파일 경로',
  `file_size` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '파일 크기',
  `file_type` varchar(100) DEFAULT NULL COMMENT '파일 타입',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '업로드 일시',
  PRIMARY KEY (`id`),
  KEY `idx_post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='게시글 첨부 파일 테이블';

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '게시글 고유 ID',
  `group_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '소속 그룹 ID',
  `board_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '게시판 ID',
  `user_id` varchar(255) NOT NULL DEFAULT '' COMMENT '작성자 ID',
  `parent_id` int(11) unsigned DEFAULT NULL COMMENT '부모 글 ID',
  `title` varchar(255) NOT NULL COMMENT '게시글 제목',
  `content` text NOT NULL COMMENT '게시글 내용',
  `reply_depth` int(11) DEFAULT 0 COMMENT '답글 깊이',
  `views` int(11) DEFAULT 0 COMMENT '조회수',
  `editor_mode` enum('visual','html') DEFAULT 'visual' COMMENT '에디터 모드',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`),
  KEY `idx_board_id` (`board_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='게시글 테이블';

DROP TABLE IF EXISTS `quote_items`;
CREATE TABLE `quote_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `item_type` varchar(50) DEFAULT '',
  `spec` varchar(100) DEFAULT '',
  `config` varchar(100) DEFAULT '',
  `qty` int(11) DEFAULT 0,
  `unit_price` decimal(15,2) DEFAULT 0.00,
  `total_price` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_quote_id` (`quote_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `quote_requests`;
CREATE TABLE `quote_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL COMMENT '공급사 회원 고유 ID',
  `active_employee_id` int(11) DEFAULT NULL COMMENT '현재 접속 중인 작업자 ID',
  `active_employee_at` datetime DEFAULT NULL COMMENT '마지막 작업자 생존 신고(접속) 시간',
  `source_mode` varchar(50) DEFAULT 'expert' COMMENT '요청 경로 (expert, easy, board)',
  `title` varchar(255) DEFAULT NULL COMMENT '게시판 문의 제목',
  `pricing_rule_id` int(11) DEFAULT NULL,
  `company` varchar(255) NOT NULL COMMENT '요청 회사명',
  `name` varchar(255) NOT NULL COMMENT '담당자 이름',
  `phone` varchar(50) NOT NULL COMMENT '연락처',
  `address` varchar(255) NOT NULL COMMENT '현장 주소',
  `canvas_data` longtext DEFAULT NULL COMMENT '랙 및 도면 정보 JSON',
  `image_path` varchar(255) DEFAULT NULL COMMENT '고해상도 캔버스 이미지 경로',
  `extra_files` text DEFAULT NULL COMMENT '고객 업로드 첨부파일 경로 (JSON)',
  `summary` longtext DEFAULT NULL COMMENT 'AI 시공 요약 리포트',
  `edge_lengths` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `admin_price` decimal(15,2) DEFAULT 0.00,
  `admin_margin` decimal(5,2) DEFAULT 0.00,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '요청 일시',
  `pallet_w` int(11) DEFAULT NULL,
  `pallet_d` int(11) DEFAULT NULL,
  `pallet_h` int(11) DEFAULT NULL,
  `pallet_weight` int(11) DEFAULT NULL,
  `fork_direction` varchar(50) DEFAULT NULL,
  `forklift_type` varchar(100) DEFAULT NULL,
  `forklift_lift_height` int(11) DEFAULT NULL,
  `forklift_ast` int(11) DEFAULT NULL,
  `rack_levels` int(11) DEFAULT NULL,
  `beam_thickness` int(11) DEFAULT 125,
  `rack_height` varchar(50) DEFAULT NULL,
  `rack_spec` varchar(100) DEFAULT NULL,
  `rack_type` varchar(100) DEFAULT NULL,
  `rack_indep` int(11) DEFAULT NULL,
  `rack_conn` int(11) DEFAULT NULL,
  `rack_small_conn` int(11) DEFAULT NULL,
  `rack_bypass` int(11) DEFAULT NULL,
  `rack_bypass_type` varchar(100) DEFAULT NULL,
  `rack_holders` int(11) DEFAULT NULL,
  `rack_pallets` int(11) DEFAULT NULL,
  `condition_type` varchar(50) DEFAULT NULL,
  `self_install` tinyint(1) DEFAULT 0,
  `email` varchar(255) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `is_mailed` tinyint(1) DEFAULT 0 COMMENT '메일 발송 여부',
  `mailed_at` datetime DEFAULT NULL COMMENT '메일 발송 일시',
  `admin_quote_details` longtext DEFAULT NULL COMMENT '견적서 수동입력 상세 데이터 JSON',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='캔버스 견적 요청 내역';

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '고유 식별 ID',
  `user_id` varchar(255) NOT NULL COMMENT '사용자 아이디',
  `username` varchar(50) NOT NULL COMMENT '사용자 이름',
  `password` varchar(255) NOT NULL COMMENT '암호화된 비밀번호',
  `email` varchar(100) NOT NULL COMMENT '이메일 주소',
  `role` enum('user','admin') DEFAULT 'user' COMMENT '사용자 권한',
  `point` int(11) DEFAULT 0 COMMENT '포인트',
  `level` int(11) DEFAULT 1 COMMENT '레벨',
  `country` varchar(50) DEFAULT 'Unknown' COMMENT '접속 국가',
  `addon_quotes_balance` int(11) DEFAULT 0 COMMENT '추가 결제된 견적 발송 건수',
  `plan` enum('free','starter','pro') DEFAULT 'free' COMMENT '구독 플랜 (free/starter/pro)',
  `plan_expires_at` datetime DEFAULT NULL COMMENT '구독 만료일 (NULL이면 무기한 또는 프리)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '가입 일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='사용자 정보 테이블';

DROP TABLE IF EXISTS `vendor_employees`;
CREATE TABLE `vendor_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT '',
  `phone` varchar(100) DEFAULT '',
  `color_code` varchar(20) DEFAULT '#ffffff',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vendor_pricing_rules`;
CREATE TABLE `vendor_pricing_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL DEFAULT 1,
  `applied_month` varchar(50) NOT NULL,
  `source_file` varchar(255) DEFAULT NULL,
  `pricing_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pricing_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `vendor_settings`;
CREATE TABLE `vendor_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `price_excel_path` varchar(255) DEFAULT NULL,
  `prices_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prices_data`)),
  `url_slug` varchar(255) DEFAULT NULL,
  `contact_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `headquarters_address` varchar(255) DEFAULT NULL,
  `fax_number` varchar(100) DEFAULT NULL,
  `manager_name` varchar(100) DEFAULT NULL,
  `manager_email` varchar(255) DEFAULT NULL,
  `factory_address` varchar(255) DEFAULT NULL,
  `factory_contact` varchar(100) DEFAULT NULL,
  `bank_account` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  UNIQUE KEY `idx_url_slug` (`url_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `visitor_logs`;
CREATE TABLE `visitor_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '로그 고유 ID',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP 주소',
  `country` varchar(50) DEFAULT 'Unknown' COMMENT '국가 코드/이름',
  `user_agent` text DEFAULT NULL COMMENT '브라우저 에이전트',
  `referer` text DEFAULT NULL COMMENT '유입 경로',
  `visit_date` date NOT NULL COMMENT '접속 날짜',
  `visit_time` time NOT NULL COMMENT '접속 시간',
  `last_active_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '마지막 활성 시간',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '기록 일시',
  PRIMARY KEY (`id`),
  KEY `idx_visit_date` (`visit_date`),
  KEY `idx_ip_date` (`ip_address`,`visit_date`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='접속자 로그 테이블';

DROP TABLE IF EXISTS `payment_subscriptions`;
CREATE TABLE `payment_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `billing_key` varchar(255) NOT NULL COMMENT '부트페이 빌링키',
  `plan_type` varchar(50) NOT NULL COMMENT '구독 플랜 (예: starter_1m)',
  `status` varchar(50) NOT NULL DEFAULT 'active' COMMENT '상태 (active, canceled, failed)',
  `next_payment_date` date NOT NULL COMMENT '다음 자동 결제일',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='정기구독(빌링키) 관리 테이블';

DROP TABLE IF EXISTS `payment_logs`;
CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0 COMMENT '결제 금액',
  `pay_type` enum('addon','subscribe') DEFAULT 'addon' COMMENT '결제 유형 (addon=건당, subscribe=정기구독)',
  `plan_type` varchar(50) DEFAULT NULL COMMENT '구독 플랜명 (subscribe일 때: starter_1m, pro_6m 등)',
  `receipt_url` varchar(255) DEFAULT NULL COMMENT '부트페이 영수증 URL',
  `status` varchar(50) NOT NULL DEFAULT 'success' COMMENT '결제 상태 (success, failed 등)',
  `error_msg` text DEFAULT NULL COMMENT '실패 시 에러 메시지',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_pay_type` (`pay_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='결제 기록 테이블 (건당/구독 통합)';

DROP TABLE IF EXISTS `vendor_page_visits`;
CREATE TABLE `vendor_page_visits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '접속 로그 고유 ID',
  `vendor_user_id` varchar(255) NOT NULL COMMENT '공급사 아이디',
  `ip_address` varchar(45) NOT NULL COMMENT '접속자 IP',
  `visited_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '접속 일시',
  PRIMARY KEY (`id`),
  KEY `idx_vendor_user_id` (`vendor_user_id`),
  KEY `idx_visited_at` (`visited_at`),
  KEY `idx_vendor_date` (`vendor_user_id`, `visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공급사 견적폼 접속 통계';

DROP TABLE IF EXISTS `vendor_inquiries`;
CREATE TABLE `vendor_inquiries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '문의 고유 ID',
  `vendor_user_id` varchar(255) NOT NULL COMMENT '공급사 아이디',
  `company` varchar(255) DEFAULT NULL COMMENT '회사명',
  `name` varchar(255) NOT NULL COMMENT '담당자명',
  `phone` varchar(50) NOT NULL COMMENT '연락처',
  `email` varchar(100) DEFAULT NULL COMMENT '이메일',
  `address` varchar(255) DEFAULT NULL COMMENT '현장 주소',
  `title` varchar(255) NOT NULL COMMENT '문의 제목',
  `content` text NOT NULL COMMENT '문의 내용',
  `status` enum('pending','answered','closed') DEFAULT 'pending' COMMENT '답변 상태',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시',
  PRIMARY KEY (`id`),
  KEY `idx_vendor_user_id` (`vendor_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공급사 전용 게시판 문의 테이블';

DROP TABLE IF EXISTS `service_reviews`;
CREATE TABLE `service_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(255) DEFAULT NULL COMMENT '회사명',
  `name` varchar(255) NOT NULL COMMENT '담당자명',
  `rating` int(1) NOT NULL DEFAULT 5 COMMENT '별점(1~5)',
  `comment` text DEFAULT NULL COMMENT '후기 내용',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '작성 일시',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 서비스 리뷰 테이블';

DROP TABLE IF EXISTS `website_portfolios`;
CREATE TABLE `website_portfolios` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '프로젝트/사이트명',
  `url` varchar(500) NOT NULL COMMENT '사이트 URL',
  `category` varchar(50) DEFAULT '회사홈페이지' COMMENT '카테고리',
  `description` text DEFAULT NULL COMMENT '설명',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '등록일시',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='제작 웹사이트 포트폴리오 테이블';

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_user_id` int(11) NOT NULL COMMENT '소유자(벤더) ID',
  `name` varchar(50) NOT NULL,
  `factory_name` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'none' COMMENT 'excel, manual, none',
  `color` varchar(20) DEFAULT '#94a3b8',
  `excel_file` varchar(255) DEFAULT NULL,
  `pricing_password` varchar(255) DEFAULT NULL COMMENT '단가표 보안 2차 비밀번호 (해시)',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공급사 관리 테이블';

DROP TABLE IF EXISTS `vendor_prices_manual`;
CREATE TABLE `vendor_prices_manual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `unit_price` varchar(255) DEFAULT NULL COMMENT 'AES-256 암호화 수동단가',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_supplier_item` (`supplier_id`,`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='수동 완제품 단가 테이블';

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 단가표 유출 추적(열람) 로그 테이블 (B2B 보안 감사 무기)
-- ※ 재설치/패치 시 데이터 유실 방지를 위해 DROP TABLE 금지 (CREATE TABLE IF NOT EXISTS)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_access_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `audit_token` varchar(64) DEFAULT NULL COMMENT 'HMAC-SHA256 감사 검증 토큰',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  KEY `idx_ip_created` (`ip_address`, `created_at`),
  KEY `idx_audit_token` (`audit_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='단가표 유출 추적(열람) 로그';
