# CMAKE v1 (AI_PHP Board System) 🚀

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-Open_Source-success)](LICENSE)
[![Security](https://img.shields.io/badge/Security-OWASP_Compliant-success)](SECURITY.md)

**CMAKE v1**은 누구나 다운로드하여 자유롭게 사용할 수 있는 오픈소스 PHP 게시판 시스템입니다. 현대적인 MVC 아키텍처와 AI 통합 기능을 갖추고 있으며, 강력한 보안과 확장성을 제공합니다. (단, 번들된 플러그인의 개별 배포는 제한됩니다.)

---

## 📢 공지: 누구나 사용 가능한 오픈소스
이 프로젝트는 **CMAKE v1**이라는 이름으로 공개되었으며, 개인이나 기업 누구나 무료로 다운로드하여 수정 및 사용이 가능합니다. 

> ⚠️ **주의: 플러그인 배포 제한**
> - 본체(Core) 시스템은 MIT 라이선스로 자유롭게 이용 가능하나, 기본 포함된 **플러그인(plugins/ 디렉토리 내 항목)의 무단 전재, 유료 판매 및 별도 배포는 금지**되어 있습니다. 사용 시 이 점 유의해 주시기 바랍니다.

---

## ✨ 주요 특징

### 🎨 **사용자 경험**
- ✅ **자동 설치 시스템**: 웹 UI를 통한 원클릭 설치
- ✅ **프리미엄 Glassmorphism UI**: 다크 모드 기반 현대적 디자인
- ✅ **반응형 디자인**: 모바일/태블릿/데스크톱 완벽 대응
- ✅ **다중 게시판 스킨**: Basic, Gallery, Blog 스킨 지원
- ✅ **독립 페이지 관리**: 제목 표시 조절 및 카드 스타일 적용 옵션 제공

### 🔒 **보안 (PRODUCTION READY)**
- ✅ **CSRF 보호**: 모든 POST 폼에 토큰 적용
- ✅ **XSS 방지**: 헬퍼 함수를 통한 안전한 출력
- ✅ **SQL Injection 방지**: PDO Prepared Statement 사용
- ✅ **파일 업로드 보안**: .htaccess로 스크립트 실행 차단
- ✅ **Rate Limiting**: 로그인 브루트 포스 공격 방지
- ✅ **세션 보안**: httponly, secure, samesite 플래그
- ✅ **IP 접근 제어**: 화이트리스트/블랙리스트
- 📊 **OWASP Top 10 대응**: 8/10 항목 완료

### 🔧 **관리 기능**
- ✅ **사이트 설정**: 로고, 템플릿, 회원가입 설정
- ✅ **회원 관리**: CRUD, 포인트 수동 조정
- ✅ **게시판 관리**: 스킨, 권한, 페이징 커스터마이징
- ✅ **페이지 관리**: 가시성 및 스타일 개별 설정 가능
- ✅ **접속자 통계**: Chart.js 기반 시각화
- ✅ **IP 접근 제어**: 실시간 차단/허용
- ✅ **템플릿 빌더**: 클릭 한 번으로 새 템플릿 생성

---

## 🧩 플러그인 시스템

이 프로젝트는 강력한 플러그인 아키텍처를 지원합니다. 플러그인은 `plugins/` 디렉토리에 위치하며, 독립적으로 동작할 수 있습니다.

### 🤖 Chatbot Plugin (Standalone)
**위치:** `plugins/chatbot/`
Neuron AI 기반의 챗봇 플러그인입니다. 사이트의 콘텐츠(게시글, 페이지, FAQ)를 학습하여 사용자 질문에 자동으로 답변합니다.

### 🧠 AI Manager Plugin
**위치:** `plugins/ai-manager/`
사이트 전반에 걸친 AI 기능을 관리하며, 관리자 페이지에서의 AI 글쓰기 보조 등을 제공합니다.

---

## 🛠️ 기술 스택

| Category | Technology |
|----------|-----------|
| **Backend** | PHP 8.x |
| **Database** | MySQL / MariaDB (PDO) |
| **Frontend** | Bootstrap 5.3.3, jQuery 4.0, Font Awesome 6.0 |
| **Routing** | FastRoute (nikic/fast-route) |
| **Template** | Quill.js (Rich Text Editor), Chart.js |
| **Security** | CSRF Tokens, Bcrypt, PDO, .htaccess |

---

## 🚀 빠른 시작

### 1️⃣ **시스템 요구사항**
- PHP 8.0 이상
- MySQL 5.7 이상
- Composer
- Apache 또는 Nginx (mod_rewrite 필요)

### 2️⃣ **설치**

```bash
# 저장소 클론
git clone https://github.com/1clickmake/NeuronAIPHP.git
cd NeuronAIPHP

# Composer 의존성 설치
composer install

# 환경 변수 설정
cp .env.example .env
# .env 파일을 열어 데이터베이스 정보 입력
```

### 3️⃣ **자동 설치**
브라우저에서 접속 시 자동으로 설치 화면이 나타납니다. 안내에 따라 DB 정보를 입력하면 설치가 완료됩니다.

---

## 📂 프로젝트 구조

```
CMAKE_v1/
├── app/
│   ├── Controllers/      # MVC 컨트롤러
│   ├── Core/             # 핵심 클래스 (Database, CSRF)
│   └── Services/         # 비즈니스 로직
├── config/
│   └── config.php        # 전역 설정
├── lib/
│   └── common.lib.php    # 공통 함수 라이브러리
├── plugins/              # 확장 플러그인
├── public/               # 웹 루트 (index.php, css, js 등)
├── views/                # 화면 템플릿
├── .env.example          # 환경 변수 템플릿
├── composer.json
├── LICENSE               # 라이선스 파일
├── SECURITY.md           # 보안 가이드
└── views/install/setup.sql # DB 스키마
```

---

## 📊 프로젝트 현황

- **버전**: CMAKE v1.0
- **개발 상태**: Production Ready ✅
- **라이선스**: MIT (누구나 무료 사용 가능)
- **마지막 업데이트**: 2026-02-19

---

<div align="center">

**⭐ 이 프로젝트가 마음에 드셨다면 Star를 눌러주세요! ⭐**

Made with ❤️ by [@1clickmake](https://github.com/1clickmake)

</div>
