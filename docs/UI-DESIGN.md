# TalentStage â€” Há»‡ thá»‘ng thiáº¿t káº¿ UI

Nguá»“n gá»‘c: artifact mockup `claude.ai/code/artifact/b4e21537-e823-414b-a2fa-bb9b026085fe` (báº£n Public Sans) â€” láº¥y **mÃ u sáº¯c, typography, khoáº£ng cÃ¡ch, thÃ nh pháº§n**. Pháº§n khung trang (sidebar, header, footer, tiÃªu Ä‘á» trang) Ä‘Ã£ Ä‘Æ°á»£c dá»±ng láº¡i theo hÆ°á»›ng **website ngÆ°á»i dÃ¹ng tháº­t**: khÃ´ng cÃ²n nhÃ³m Ä‘Ã¡nh sá»‘ 01â€“05, tag FR, nhÃ£n song ngá»¯ hay chÃº thÃ­ch "[ thumbnail ]" cá»§a báº£n mockup.

MÃ£ nguá»“n: tokens/components trong **`public/css/talentstage.css`**; tÆ°Æ¡ng tÃ¡c nhá» trong **`public/js/talentstage.js`** (drawer, menu tÃ i khoáº£n, flash, panel má»Ÿ/Ä‘Ã³ng, dropzone); icon SVG inline qua component **`<x-icon name="â€¦" />`** (`resources/views/components/icon.blade.php`, nÃ©t Lucide); fonts tá»± host trong `public/fonts/` (`public/css/fonts.css`).

## 1. NgÃ´n ngá»¯ thá»‹ giÃ¡c

| Yáº¿u tá»‘ | GiÃ¡ trá»‹ |
|---|---|
| Ná»n / bá» máº·t | `#fbfaf8` (giáº¥y áº¥m) / `#eae9e9` |
| Má»±c chá»¯ | `#201f1d` |
| Accent | `#b68235` (vÃ ng Ä‘á»“ng) â€” ramp 100â€“900; badge Ä‘áº¿m dÃ¹ng `accent-700` ná»n + chá»¯ tráº¯ng |
| MÃ u thá»ƒ loáº¡i | `--c-music #6b4bb8` Â· `--c-dance #c2410c` Â· `--c-visual #1d6fa5` Â· `--c-acting #a83252` Â· `--c-food #b07417` Â· `--c-sport #2f7d5e` â€” gÃ¡n qua `Category::colorVar()` â†’ `style="--cat: var(--c-â€¦)"` |
| Tráº¡ng thÃ¡i duyá»‡t | duyá»‡t = `--c-sport` Â· chá» = `--c-food` Â· tá»« chá»‘i = `--c-acting` â€” `.tag-status[data-status]` |
| Chá»¯ | **Public Sans** (variable) cho toÃ n bá»™; heading 600, wordmark 700; body 15px/1.55; `-webkit-font-smoothing: antialiased` |
| Cá»¡ chá»¯ | tiÃªu Ä‘á» trang 30px (mobile 26) Â· h2 28â€“32 Â· h3 20 Â· nav 13.5 Â· meta 11.5 Â· kicker 10px tracked uppercase (chá»‰ dÃ¹ng cho nhÃ£n tháº», khÃ´ng dÃ¹ng lÃ m mÃ£ FR) |
| Spacing | 4.6 / 9.2 / 13.8 / 18.4 / 27.6 / 36.8px (`--space-1â€¦8`) |
| Bo gÃ³c / bÃ³ng | 2 / 4 / 7px; Ã´ tÃ¬m kiáº¿m & chip: pill 999px; bÃ³ng `--shadow-sm/md/lg` chá»‰ khi hover / menu / dialog |
| Placeholder áº£nh | **khÃ´ng** dÃ¹ng chá»¯ chÃº thÃ­ch: thumbnail thiáº¿u â†’ `.thumb-ph` (vÃ²ng trÃ²n tint thá»ƒ loáº¡i + icon play); Ã´ nhá» `.hatch-mid` â†’ gradient dá»‹u + play má»; hero/cover â†’ `.ph-art` (gradient + icon mic má»); avatar thiáº¿u â†’ chá»¯ cÃ¡i Ä‘áº§u trÃªn ná»n `accent-100` |
| NgÃ´n ngá»¯ | ToÃ n bá»™ nhÃ£n UI **tiáº¿ng Viá»‡t**; `APP_LOCALE=vi` + `lang/vi/{validation,auth,pagination}.php` nÃªn thÃ´ng bÃ¡o lá»—i form cÅ©ng tiáº¿ng Viá»‡t (rule thiáº¿u tá»± rÆ¡i vá» tiáº¿ng Anh cá»§a framework); thá»i gian tÆ°Æ¡ng Ä‘á»‘i tiáº¿ng Viá»‡t (`Carbon::setLocale('vi')`); tÃªn vai trÃ² Creator/Mentor/Admin giá»¯ nguyÃªn nhÆ° thuáº­t ngá»¯ sáº£n pháº©m |

## 2. Khung trang (`layouts/app.blade.php`)

- **Sidebar 250px** (sticky, drawer trÃªn mobile): logo mark vuÃ´ng bo gÃ³c gradient vÃ ng + wordmark **TalentStage** + tagline "SÃ¢n kháº¥u tÃ i nÄƒng trá»±c tuyáº¿n"; nav dáº¡ng **icon + nhÃ£n**, chia nhÃ³m nhá»:
  - *(khÃ´ng nhÃ£n)*: Trang chá»§ Â· TÃ¬m kiáº¿m Â· Báº£ng tin (Ä‘Äƒng nháº­p) Â· Cuá»™c thi
  - **Cá»™ng Ä‘á»“ng**: NhÃ³m Â· Tin nháº¯n (creator/mentor, badge chÆ°a Ä‘á»c) Â· ThÃ´ng bÃ¡o (badge chÆ°a Ä‘á»c)
  - **KÃªnh cá»§a tÃ´i** (creator): ÄÄƒng tiáº¿t má»¥c Â· Tiáº¿t má»¥c cá»§a tÃ´i Â· Há»“ sÆ¡ cá»§a tÃ´i â€” user khÃ¡c: **TÃ i khoáº£n**: Há»“ sÆ¡ cá»§a tÃ´i
  - **Quáº£n trá»‹** (admin): Tá»•ng quan Â· Kiá»ƒm duyá»‡t (badge sá»‘ video chá») Â· NgÆ°á»i dÃ¹ng Â· Danh má»¥c Â· Cuá»™c thi
  - Má»¥c active: ná»n `accent-100`, viá»n trÃ¡i vÃ ng, chá»¯ 600, icon vÃ ng. ChÃ¢n sidebar: tháº» user (avatar Â· tÃªn Â· vai trÃ²) + nÃºt icon ÄÄƒng xuáº¥t; khÃ¡ch: ÄÄƒng nháº­p / Táº¡o tÃ i khoáº£n.
- **Header** sticky (ná»n má» blur): nÃºt â˜° (mobile), Ã´ tÃ¬m kiáº¿m pill cÃ³ icon, 4 chip thá»ƒ loáº¡i `.cat-chip`, bÃªn pháº£i: nÃºt **ÄÄƒng tiáº¿t má»¥c** (creator), chuÃ´ng thÃ´ng bÃ¡o `.icon-btn` + `.badge-dot`, **menu tÃ i khoáº£n** `details.menu > .ts-user` (Há»“ sÆ¡, Sá»­a há»“ sÆ¡, Tiáº¿t má»¥c cá»§a tÃ´i, Tin nháº¯n, Khu quáº£n trá»‹, ÄÄƒng xuáº¥t); khÃ¡ch: ÄÄƒng nháº­p / ÄÄƒng kÃ½.
- **TiÃªu Ä‘á» trang**: `screen-kicker` = breadcrumb (`.crumbs`, dÃ¹ng block section Ä‘á»ƒ chá»©a link) â†’ `screen-title` (30px/600) â†’ `screen-sub` (mÃ´ táº£ 13.5px). Trang xem video vÃ  há»“ sÆ¡ **khÃ´ng** dÃ¹ng khá»‘i nÃ y â€” tiÃªu Ä‘á» video / tÃªn ngÆ°á»i dÃ¹ng chÃ­nh lÃ  h1 trong ná»™i dung.
- **Footer**: logo nhá» + tagline Â· liÃªn káº¿t KhÃ¡m phÃ¡ / Cuá»™c thi / NhÃ³m / SÆ¡ Ä‘á»“ trang Â· Â© nÄƒm.

## 3. Chuyá»ƒn Ä‘á»™ng (motion)

Token chung: `--ease-out` (cubic-bezier .22 1 .36 1), `--ease-spring` (náº£y nháº¹), `--dur-1â€¦4` = 120 / 200 / 320 / 520ms.

- **VÃ o trang**: khá»‘i con cá»§a `.ts-content` fade-rise 10px so le 60ms; tháº» trong `.grid-4/.grid-2` so le thÃªm 40ms (`animation-fill-mode: backwards` Ä‘á»ƒ hover váº«n hoáº¡t Ä‘á»™ng).
- **Chuyá»ƒn trang** (MPA View Transitions): sidebar/header giá»¯ nguyÃªn, ná»™i dung cross-fade.
- **Hover/press**: nÃºt, tháº» (`.video-card` nháº¥c âˆ’2px + áº£nh zoom + play-icon phÃ³ng), nav (icon Ä‘á»•i mÃ u, chá»¯ trÆ°á»£t 2px), chip, logo mark xoay nháº¹, `.icon-btn`; **focus** ring vÃ ng 3px; **tab gáº¡ch chÃ¢n** trÆ°á»£t; **sao** xem trÆ°á»›c khi rÃª; **menu tÃ i khoáº£n** scale-in; **panel** `.reveal`; **dropzone** Ä‘á»•i mÃ u khi kÃ©o tá»‡p vÃ o; **flash** Ä‘Ã³ng má»; **drawer** + mÃ n che.
- TÃ´n trá»ng `prefers-reduced-motion`.

## 4. Component chÃ­nh (class trong talentstage.css)

- NÃºt: `.btn` + `-primary` / `-secondary` / `-ghost`, size `-sm` `-xs`; nÃºt icon `.icon-btn`; badge `.badge` / `.badge-dot`
- Form: `.field` + `.label-up`, `.input`, `.is-invalid` + `.err-msg`, `.input-wrap` + `.input-check` (tích xanh / ✕ đỏ, dùng cho cặp mật khẩu `[data-password-pair]` — kiểm tra khớp ngay khi gõ, chặn submit), `.field-hint`, `.seg`, `.radio`, `.dropzone` (+ `-ico/-title/-hint/-name`, input `.visually-hidden`)
- Tháº»: `.card` (`-kicker/-title/-body/-meta`), `.tag` (`-accent/-outline/-neutral/-muted/-status`), `.cat-chip` (`.active`)
- Báº£ng `.table`; há»™p thoáº¡i `.dialog*`; flash `.flash` / `.flash-error`; panel `.reveal` + `.reveal-inner`; menu `.menu` / `.menu-panel` / `.menu-item` / `.menu-head` / `.menu-sep`; breadcrumb `.crumbs` (+ `.sep`)
- Äáº·c thÃ¹ app: `.video-card` (+`.video-thumb`, `.thumb-ph`, `.thumb-badge` Ã¢m thanh/thá»i lÆ°á»£ng â€” partial `partials/thumb`, `.video-card-cat`), `.attach-card/.progress/.steps`, `.noti-ico`, `.hero-plate/.hero-box/.ph-art/.ph-art-ico`, `.player/.player-empty`, `.rank-row`+`.rank-num`, `.phase-strip`+`.phase.current`, `.bubble.me/.them`, `.stat`, `.line-tabs`, `.auth-tabs`, `.stars/.star[data-on]`, `.avatar` (`-lg/-xl`), `.kicker`, `.meta`, `.muted-i`, `.grid-4/.grid-2`

## 5. Trang chÃ­nh

| Trang | URL | Ghi chÃº |
|---|---|---|
| Trang chá»§ | `/` | Hero (`.ph-art` khi chÆ°a cÃ³ áº£nh) + "Äang thá»‹nh hÃ nh" + lÆ°á»›i video 4 cá»™t + cuá»™c thi Ä‘ang diá»…n ra |
| TÃ¬m kiáº¿m | `/explore` | Ã” tÃ¬m + sáº¯p xáº¿p + dÃ£y `.cat-chip` lá»c thá»ƒ loáº¡i (giá»¯ `q`/`sort`) |
| Xem video | `/videos/{id}` | `.player` (video) hoáº·c `.player-audio` (báº£n thu Ã¢m: cover mÃ u thá»ƒ loáº¡i + `<audio>`), h1 tiÃªu Ä‘á», tÃ¡c giáº£ + Theo dÃµi, ThÃ­ch, lÆ°á»£t xem, thá»i lÆ°á»£ng, cháº¥m sao, chip thá»ƒ loáº¡i, bÃ¬nh luáº­n + tráº£ lá»i `.reveal`; cá»™t pháº£i: nháº­n xÃ©t mentor, quáº£n lÃ½, Xem tiáº¿p |
| Há»“ sÆ¡ | `/users/{id}`, `/profile/edit` | h1 tÃªn + tag vai trÃ² + nÆ¡i á»Ÿ + thÃ nh tÃ­ch + 4 `.stat`; hÃ nh Ä‘á»™ng cá»™t pháº£i; lÆ°á»›i video |
| ÄÄƒng tiáº¿t má»¥c | `/videos/create`, `/my-videos`, `/videos/{id}/edit` | `.dropzone` kÃ©o tháº£ (video **hoáº·c Ã¢m thanh**) â†’ `.attach-card` kiá»ƒu "Your work" (xem trÆ°á»›c Â· tÃªn Â· loáº¡i/dung lÆ°á»£ng/thá»i lÆ°á»£ng Â· nÃºt gá»¡); gá»­i báº±ng XHR: `.progress` + tráº¡ng thÃ¡i, lá»—i theo trÆ°á»ng `[data-error-for]`, thÃ nh cÃ´ng â†’ chuyá»ƒn tá»›i danh sÃ¡ch kÃ¨m flash; panel `.steps` "tiáº¿t má»¥c Ä‘i Ä‘Ã¢u sau khi gá»­i"; báº£ng tráº¡ng thÃ¡i duyá»‡t `.tag-status` |
| Báº£ng tin / ThÃ´ng bÃ¡o | `/feed`, `/notifications` | Card hoáº¡t Ä‘á»™ng; gá»£i Ã½ theo dÃµi |
| NhÃ³m | `/groups`, `/groups/{id}`, `/groups/create` | Danh sÃ¡ch trÃ¡i + báº£ng tháº£o luáº­n pháº£i |
| Tin nháº¯n | `/messages`, `/messages/{user}` | Threads + khung chat `.bubble` |
| Cuá»™c thi | `/contests`, `/contests/{id}` | Breadcrumb, `.phase-strip`, báº£ng xáº¿p háº¡ng, lÆ°á»›i bÃ i dá»± thi + BÃ¬nh chá»n |
| ÄÄƒng nháº­p / ÄÄƒng kÃ½ | `/login`, `/register` | Panel giá»›i thiá»‡u `auth/_cover` + form; tab ÄÄƒng nháº­p / ÄÄƒng kÃ½ |
| Quáº£n trá»‹ | `/admin/*` | Breadcrumb "Quáº£n trá»‹ / â€¦"; kiá»ƒm duyá»‡t: tab gáº¡ch chÃ¢n, hÃ ng Ä‘á»£i, panel tá»« chá»‘i `.reveal` |
| SÆ¡ Ä‘á»“ trang | `/sitemap` | Trang yÃªu cáº§u cá»§a Ä‘á» bÃ i â€” váº«n liá»‡t kÃª nhÃ³m chá»©c nÄƒng kÃ¨m mÃ£ FR |

## 6. Quy táº¯c khi dá»±ng mÃ n hÃ¬nh má»›i

1. `@extends('layouts.app')`; khai bÃ¡o `screen-title` + `screen-sub` (tiáº¿ng Viá»‡t, mÃ´ táº£ ngáº¯n cho ngÆ°á»i dÃ¹ng); trang con dÃ¹ng `@section('screen-kicker')â€¦@endsection` lÃ m breadcrumb vá»›i link vá» trang cha.
2. **KhÃ´ng** Ä‘Æ°a mÃ£ yÃªu cáº§u (FR1â€¦), nhÃ£n song ngá»¯ "VN Â· EN" hay ghi chÃº thiáº¿t káº¿ vÃ o giao diá»‡n â€” chá»‰ tiáº¿ng Viá»‡t tá»± nhiÃªn (trá»« tÃªn vai trÃ²).
3. NÃºt hÃ nh Ä‘á»™ng chÃ­nh kÃ¨m icon `<x-icon>` 14â€“15px; nav/menu icon 16â€“18px.
4. áº¢nh chÆ°a cÃ³ â†’ `.thumb-ph` / `.hatch-mid` / `.ph-art`, khÃ´ng Ä‘á»ƒ Ã´ trá»‘ng tráº¯ng, khÃ´ng dÃ¹ng chá»¯ chÃº thÃ­ch.
5. Sá»‘ liá»‡u `.num`/`.meta` (tabular); xáº¿p háº¡ng `.rank-num`; tráº¡ng thÃ¡i `.tag-status[data-status]`; thá»ƒ loáº¡i kÃ¨m mÃ u (`--cat`, `.cat-chip`).
6. áº¨n/hiá»‡n cÃ³ chuyá»ƒn Ä‘á»™ng: `.reveal` + `tsToggle()`; hover/focus má»›i pháº£i kÃ¨m `transition` dÃ¹ng token `--dur-*`/`--ease-*`.
7. KhÃ´ng thÃªm thÆ° viá»‡n CSS/JS ngoÃ i â€” toÃ n bá»™ lÃ  `talentstage.css` + `talentstage.js` vanilla.
