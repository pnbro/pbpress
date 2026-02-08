# PBPress

**MCP Model 기반 PHP 프레임워크** — 빠르고 확장 가능한 웹사이트 & 관리자 시스템 제작을 위한 경량 풀스택 프레임워크

> **현재 버전**: 7.7.0

---

## ✨ Features

- **MCP Architecture** — Module(데이터·비즈니스 로직), Controller(AJAX·라우팅), Presenter(뷰 렌더링) 3계층 구조
- **Hook System** — WordPress 스타일 Action/Filter 기반의 확장 구조로 코어 수정 없이 기능 추가
- **Data Object (PBDB_DO)** — PHP 코드로 테이블 스키마를 선언하면 자동 생성·마이그레이션되며, 내장 CRUD 메서드 제공
- **Query Builder** — `PBDB_select_statement`로 JOIN, 서브쿼리, 동적 조건을 안전한 파라미터 바인딩과 함께 사용
- **Admin System** — 훅 기반 메뉴 등록, `pb_edit_form_modal`을 통한 선언적 CRUD UI, 권한 관리 내장
- **EasyList / EasyTable** — 페이지네이션, AJAX 기반 목록·테이블 컴포넌트 (PHP + JS 연동)
- **Theme System** — `themes/` 디렉토리에서 테마 단위로 프론트엔드 완전 분리, 라이브 전환 지원
- **Plugin System** — `plugins/` 디렉토리에서 플러그인 활성화/비활성화, 훅 기반 확장
- **Page Builder** — 비주얼 페이지 에디터 (XML 기반)
- **Menu Editor** — 계층형 드래그&드롭 메뉴 관리
- **다국어 지원** — `__()` 함수 기반 다국어 시스템
- **보안** — CSRF 토큰, AES-256 암호화, 세션 관리, 로그인 보안 정책 내장

---

## 📋 Requirements

| 구성요소 | 최소 버전 | 권장 |
|----------|----------|------|
| **PHP** | 7.0+ | 8.0+ |
| **MySQL** | 5.x+ (InnoDB) | 8.0+ |
| **DB 드라이버** | mysql / mysqli / pdo 중 택 1 | pdo |
| **Node.js** | 14+ (프론트엔드 빌드용) | 18+ |
| **Apache** | mod_rewrite 활성화 필요 | - |

---

## 🚀 Installation

### 1. 프로젝트 배치

```bash
# 방법 A: Git Clone
git clone https://github.com/pnbro/pbpress.git your-project
cd your-project

# 방법 B: Release ZIP 다운로드 (프로덕션 빌드 포함)
# https://github.com/pnbro/pbpress/releases
```

### 2. 프론트엔드 빌드 (Git Clone의 경우)

```bash
npm install
npx grunt dist     # 개발 소스 → 프로덕션 빌드
```

### 3. 설정 파일 생성

```bash
cp pb-config.php.sample pb-config.php
```

`pb-config.php`를 환경에 맞게 편집:

```php
define('PB_DB_CONNECTION_TYPE', 'pdo');   // mysql, mysqli, pdo
define('PB_DB_HOST', 'localhost');
define('PB_DB_PORT', '3306');
define('PB_DB_USERNAME', 'your_user');
define('PB_DB_USERPASS', 'your_pass');
define('PB_DB_NAME', 'your_database');
define('PB_DB_CHARSET', 'utf8mb4');

define("PB_CRYPT_PASSWORD", "change-this-to-random-string");

// 개발 모드 (lib/dev/ 직접 참조, 에러 표시)
// define('PB_DEV', true);
```

### 4. 브라우저 접속

```
http://your-domain/
```

설치 화면이 표시되면 안내에 따라 초기 설정을 완료합니다.

### 5. 관리자 접속

```
http://your-domain/admin
```

---

## 📁 Project Structure

```
pbpress/
├── index.php              # 프론트 엔트리포인트 (rewrite 라우팅)
├── defined.php            # 상수 정의 (경로, 버전, DEV/DIST 분기)
├── pb-config.php          # 환경 설정 (DB, 암호화, 세션) — gitignore 대상
├── Gruntfile.js           # 빌드 파이프라인 설정
│
├── admin/                 # 관리자 시스템
│   ├── index.php          #   관리자 엔트리포인트
│   ├── aside.php          #   사이드 메뉴
│   └── _ajax_*.php        #   관리자 AJAX 핸들러들
│
├── includes/              # 핵심 백엔드 모듈
│   ├── includes.php       #   전체 모듈 로더
│   ├── initialize.php     #   부트 시퀀스
│   ├── common/            #   프레임워크 코어 (50+ 파일)
│   │   ├── hook.php       #     Action/Filter 훅 시스템
│   │   ├── database.php   #     PBDB 쿼리 엔진
│   │   ├── database-do.php#     PBDB_DO ORM
│   │   ├── database-select-statement.php  # 쿼리 빌더
│   │   ├── rewrite.php    #     URL 라우팅
│   │   ├── ajax.php       #     AJAX 디스패처
│   │   ├── adminpage.php  #     관리자 페이지 등록/라우팅
│   │   ├── easylist.php   #     페이지네이션 리스트
│   │   ├── easytable.php  #     AJAX 테이블
│   │   └── ...            #     session, crypt, theme, plugin, mail 등
│   │
│   ├── authority/         #   권한 관리
│   ├── user/              #   사용자 모듈
│   ├── page/              #   페이지 관리
│   ├── post/              #   게시물 관리
│   ├── menu/              #   메뉴 관리 (계층구조)
│   └── page-builder/      #   페이지 빌더
│
├── lib/                   # 프론트엔드 자산
│   ├── dev/               #   개발 소스
│   │   ├── js/            #     JS (jQuery, 페이지별 스크립트)
│   │   ├── less/          #     LESS 소스 (Bootstrap 기반)
│   │   └── concat-lib/    #     JS 모듈 번들링 소스
│   │       └── src/pb/modules/  # 핵심 JS 프레임워크 모듈
│   └── dist/              #   프로덕션 빌드 (minified)
│
├── themes/                # 테마 디렉토리
├── plugins/               # 플러그인 디렉토리
└── uploads/               # 업로드 파일 저장소
```

---

## 🏗️ Architecture — MCP Model

PBPress는 독자적인 **MCP(Module-Controller-Presenter)** 아키텍처 패턴을 사용합니다:

```
[Browser]  ──Request──▶  [Controller]  ──Call──▶  [Module]  ──Query──▶  [Database]
                          AJAX Handler             DO / Statement        PBDB
                          Rewrite                  Helper Functions
                              │                         │
                              ▼                         ▼
[Browser]  ◀──Response──  [Presenter]  ◀──Data──  pb_ajax_success()
                          Views (PHP)               JSON Response
                          Theme Templates
```

| 레이어 | 역할 | 위치 |
|--------|------|------|
| **Module** | 데이터 모델 + 비즈니스 로직 | `includes/{module}/{module}.php` |
| **Controller** | 요청 처리, AJAX, 라우팅, 권한 | `{module}-adminpage.php` |
| **Presenter** | 뷰 렌더링, 폼, 테이블 | `views/*.php`, `themes/` |

---

## 🔧 Build System (Grunt)

| 명령어 | 설명 |
|--------|------|
| `npx grunt build-js` | JS concat (모듈 번들링) |
| `npx grunt build-css` | LESS → CSS 컴파일 |
| `npx grunt build` | clean + build-js + build-css |
| `npx grunt dist` | build + uglify + cssmin + copy (프로덕션) |
| `npx grunt watch` | 파일 변경 감시 자동 빌드 |

### DEV vs DIST 모드

- **DEV** (`PB_DEV = true`): `lib/dev/` 직접 참조, 매 요청마다 캐시 버스팅
- **DIST** (기본): `lib/dist/` (minified) 참조 — `grunt dist` 실행 필요

---

## 🎨 Theme Development

`themes/` 디렉토리에 테마 폴더를 생성하고 아래 파일을 구성합니다:

```
themes/my-theme/
├── theme-info.json      # 테마 메타정보
├── functions.php        # 훅 등록, 모듈 로드
├── index.php            # 홈페이지 템플릿
├── header.php           # pb_head() 호출 포함
└── footer.php           # pb_foot() 호출 포함
```

**theme-info.json 예시:**
```json
{
  "name": "My Theme",
  "desc": "커스텀 테마 설명",
  "author": "Author Name",
  "version": "1.0.0"
}
```

> 관리자 페이지 → 사이트설정에서 테마를 전환할 수 있습니다. 샘플 테마는 `themes/sample/`을 참조하세요.

---

## 🔌 Plugin Development

`plugins/` 디렉토리에 플러그인 폴더를 생성합니다:

```
plugins/my-plugin/
├── plugin-info.json     # 플러그인 메타정보
└── index.php            # 진입점 (훅 등록)
```

**plugin-info.json 예시:**
```json
{
  "name": "My Plugin",
  "desc": "플러그인 설명",
  "author": "Author Name",
  "version": "1.0.0"
}
```

> 관리자 페이지에서 플러그인을 활성화/비활성화할 수 있습니다.

---

## 📖 Quick Examples

### 관리자 페이지 등록

```php
pb_hook_add_filter('pb_adminpage_list', function($results_){
    $results_['my-feature'] = array(
        'name' => '나의 기능',
        'type' => 'menu',
        'directory' => 'common',
        'page' => __DIR__ . '/views/edit.php',
        'authority_task' => 'manage_my_feature',
    );
    return $results_;
});
```

### AJAX 핸들러 등록

```php
pb_add_ajax('my-action', function(){
    $id_ = _POST('id', null, PB_PARAM_INT);
    // 비즈니스 로직 ...
    pb_ajax_success(array("result" => "ok"));
});
```

### JS에서 AJAX 호출

```javascript
PB.post("my-action", { id: 123 }, function(result_, json_){
    if(!result_ || !json_.success) return;
    // 성공 처리
}, true);
```

> 상세한 개발 가이드라인은 `.antigravity/mcp_guide.md`를 참조하세요.

---

## 📦 Included Open Sources

### Frontend
| 라이브러리 | URL |
|-----------|-----|
| jQuery 2.x / 4.x | https://jquery.com |
| Bootstrap 3.x | https://getbootstrap.com |
| Moment.js | https://momentjs.com |
| jsencrypt | https://github.com/travist/jsencrypt |
| jquery.blockUI | http://malsup.com/jquery/block |
| jquery.cookie | https://github.com/carhartl/jquery-cookie |
| bootstrap-datetimepicker | https://github.com/Eonasdan/bootstrap-datetimepicker |
| bootstrap-select | https://developer.snapappointments.com/bootstrap-select |
| bootstrap-colorpicker | https://farbelous.io/bootstrap-colorpicker/v2/ |
| SortableJS | https://sortablejs.github.io/Sortable/ |
| CodeMirror | https://codemirror.net |
| Summernote | https://summernote.org |
| Trumbowyg | https://alex-d.github.io/Trumbowyg/ |

### Backend
| 라이브러리 | URL |
|-----------|-----|
| PHPMailer | https://github.com/PHPMailer/PHPMailer |

### Font
| 폰트 | URL |
|------|-----|
| NotoSansKR Webfont | https://github.com/theeluwin/NotoSansKR-Hestia |

---

## 📄 License

개인 또는 업체가 PBPress를 사용한 테마 및 플러그인을 개발하여 상업적으로 납품이 가능하나, PBPress 자체를 판매 및 수익 활동에 사용하는 것은 금지합니다. 기타 라이센스 규약은 MIT 라이센스를 따릅니다.
