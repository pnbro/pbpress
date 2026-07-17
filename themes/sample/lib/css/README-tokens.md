# Sample Theme 디자인시스템 카탈로그 (devplan002 part001)

이후 Wave 1 part(002~009)는 아래 목록의 클래스/변수/JS API만 **조합**해서 사용한다.
신규 클래스가 꼭 필요하면 `components.css`/`base.css`에 재사용 가능한 형태로 추가하고,
`theme.css`는 자신의 파트 섹션 주석(`/* ---------- part00N ... ---------- */`) 아래에만 append한다.

## 로드 순서 (header.php / header-other.php)
```
tokens.css → base.css → components.css → theme.css → pb_head()
```
footer.php: `ui.js` → `pb_foot()`. 코어가 주입하는 jQuery/PBVAR/all-main.js(`pb_head()`)와
`pb_foot()` 훅은 절대 수정하지 말 것.

## 브레이크포인트 규약 (CSS 변수 불가, 주석 규약)
```
sm : 640px
md : 768px
lg : 1024px
xl : 1280px
```

## 디자인 토큰 (tokens.css, `:root` CSS 변수)
- 색상(뉴트럴): `--c-white --c-bg --c-surface --c-text --c-text-muted --c-border --c-border-strong`
- 색상(포인트/상태): `--c-primary --c-primary-hover --c-primary-soft --c-danger --c-danger-hover --c-danger-soft --c-success --c-success-hover --c-success-soft --c-warning --c-warning-hover --c-warning-soft --c-info --c-info-soft`
- 보조: `--c-overlay --c-shimmer --c-text-inverse`
- 타이포: `--font-sans --fs-xs --fs-sm --fs-md --fs-lg --fs-xl --fs-2xl --lh-tight --lh-normal --lh-loose --fw-regular --fw-medium --fw-semibold --fw-bold`
- 간격(4px 스케일): `--sp-1 ~ --sp-10`, 컨테이너 `--container-max`
- 라운드: `--radius-sm --radius-md --radius-lg --radius-full`
- 그림자: `--shadow-sm --shadow-md --shadow-lg`
- 트랜지션: `--transition-fast --transition-base`
- z-index: `--z-header --z-drawer --z-modal --z-toast`

**규칙**: 모든 색/간격/타이포/라운드/그림자는 위 변수만 참조. 하드코딩 hex/rgb 금지(신규 토큰이 필요하면 tokens.css에 추가 후 사용).

## base.css — 레이아웃 & 유틸
- 컨테이너: `.container`
- 그리드(CSS Grid 12칼럼): `.grid`, `.col-1`~`.col-12`, `.col-auto`, 반응형 `.col-sm-*` `.col-md-*` `.col-lg-*` `.col-xl-*`
- 플렉스: `.flex` `.inline-flex` `.flex-col` `.flex-wrap` `.items-{start|center|end}` `.justify-{start|center|between|end}` `.gap-1~6`
- 표시 유틸: `.hidden` `.sr-only` `.only-mobile`(md 미만만 표시) `.only-desktop`(md 이상만 표시)
- 여백 유틸: `.m-0 .p-0`, `.mt-1~10` `.mb-1~10` `.ml-1~4` `.mr-1~4` `.mx-auto` `.my-1|2|4|6` `.pt-1~4` `.pb-1~4` `.p-1~6`
- 텍스트 유틸: `.text-center|left|right` `.text-muted` `.text-sm` `.text-lg` `.fw-medium|semibold|bold`
- 레이아웃 셸: `.pb-header` `.pb-header__inner` `.pb-header__brand` `.pb-header__toggle`(+`__toggle-bar`) `.pb-header__nav`(=`.drawer`와 조합해 모바일 오프캔버스/데스크탑 인라인 겸용) `.pb-main` `.pb-footer`

## components.css — 공통 컴포넌트
- 버튼: `.btn` `.btn-primary` `.btn-ghost` `.btn-danger` `.btn-link` `.btn-sm` `.btn-lg` `.btn-block` `.is-disabled`
- 폼: `.field` `.label`(+`.label-required`) `.input` `.select` `.textarea` `.field-hint` `.field-error` `.is-error` `.form-check`
- 카드: `.card` `.card-header` `.card-body` `.card-footer`
- 뱃지/칩: `.badge`(+`-primary|-success|-danger|-warning`) `.chip` `.chip-remove`
- 알림: `.alert`(+`-success|-danger|-warning|-info`)
- 테이블: `.table-responsive` `.table` `.table-striped` `.table-hover`
- 페이지네이션: `.pagination`(li `.active` `.disabled`)
- 브레드크럼: `.breadcrumb`
- 아바타/유저카드: `.avatar`(+`-sm|-lg`) `.user-card`(+`-name` `-meta`)
- 모달: `.modal` `.modal-backdrop` `.modal-dialog`(+`-sm|-lg`) `.modal-header` `.modal-title` `.modal-body` `.modal-footer` `.modal-close` (열림 상태 `.is-open`)
- 드로어: `.drawer`(+`.drawer-left`) `.drawer__header` `.drawer__body` `.drawer-backdrop` (열림 상태 `.is-open`)
- 탭: `.tabs` `.tab`(`.is-active`) `.tab-panel`(`.is-active`)
- 토스트: `.toast-region` `.toast`(+`-success|-danger|-warning|-info`) (`.is-visible`)
- 스켈레톤: `.skeleton` `.skeleton-text` `.skeleton-avatar`
- 드롭다운: `.dropdown` `.dropdown-menu`(+`-right`) `.dropdown-item` (열림 상태 `.is-open`)

## ui.js — `window.SampleUI` (바닐라 JS, jQuery 미사용)
- `SampleUI.toast(msg, type, duration)` — type: `default|success|danger|warning|info`. 선언적 트리거: `[data-toast="메시지"] [data-toast-type="success"]`
- `SampleUI.modal.open(id) / .close(id) / .closeAll()` — 트리거: `[data-modal-open="modalId"]`, 닫기: `[data-modal-close]`(모달 내부), backdrop 클릭·Esc로도 닫힘
- `SampleUI.drawer.open(id) / .close(id) / .toggle(id) / .closeAll()` — 트리거: `[data-drawer-toggle="drawerId"]`, 닫기: `[data-drawer-close]`
- `SampleUI.tabs.activate(group, target)` — 트리거: `[data-tab="paneId"]`, 패널: `[data-tab-panel="paneId"]`, 그룹 루트(선택): `[data-tabs-root]`
- `SampleUI.dropdown.toggle(id) / .closeAll()` — 트리거: `[data-dropdown-toggle="dropdownId"]`
- `SampleUI.formValidate(form)` — `form[data-validate]` 자동 바인딩. 입력 속성: `data-required` `data-email` `data-min` `data-max` `data-pattern` `data-match="#selector"`(+ 각 `-message` 커스텀 메시지)
- `SampleUI.infiniteScroll({el, action, limit, params, listEl, renderItem})` — 코어 `PB.post` 래핑, `IntersectionObserver`로 하단 도달 시 자동 로드. 선언적: `[data-infinite-scroll data-action="ajax-action" data-limit="20" data-list-target="#selector"]`

모든 바인딩은 `DOMContentLoaded`에서 자동 스캔되며, jQuery/`$`는 사용하지 않는다.
코어 `PB.post` / `PB.crypt` / `PBVAR` 등은 `pb_head()`가 주입하며 그대로 유지된다.

## 주의사항 / 다음 part 참고
- 메뉴 walker(`includes/menu-render.php`, `PBMenuWalker_sample_mainmenu`)는 아직 `navbar-nav`/`nav-item`/`nav-link`/`active` 클래스를 출력한다. part001은 이를 건드리지 않고 `theme.css` 하단에 임시 호환 스타일만 추가했다(`/* 메뉴 walker 임시 호환 셈 */` 섹션). **part003이 nav를 전면 교체할 때 이 임시 섹션도 함께 정리(제거)할 것.**
- `.pb-header__nav`는 `.drawer` 클래스를 겸용한다: 모바일(< md768)에서는 오프캔버스 드로어로, 데스크탑(≥ md768)에서는 `base.css`의 미디어쿼리로 인라인 가로 배치로 전환된다. part003에서 nav 마크업을 바꿀 때도 이 겸용 패턴을 유지하거나, 새 패턴으로 바꾸면 이 README를 갱신할 것.
- `lib/css/bootstrap.min.css`, `lib/js/bootstrap.bundle.min.js` 파일은 삭제되지 않았다(참조만 제거). part010 통합검수 후 삭제 예정이므로 다른 part에서 임의로 삭제하지 말 것.
- 아이콘은 인라인 SVG 또는 경량 SVG 스프라이트만 사용(외부 아이콘 폰트/CDN 금지).
