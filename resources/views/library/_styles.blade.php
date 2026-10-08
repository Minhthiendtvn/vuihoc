{{-- library/_styles.blade.php — CSS riêng của các trang thư viện (nằm trong view để không chạm file chung) --}}
<style>
[x-cloak] { display: none !important; }
/* ---------- Hero trang chủ ---------- */
.lib-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #a855f7 100%);
    border-radius: 24px;
    color: #fff;
    padding: 56px 32px;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 32px rgba(79, 70, 229, 0.3);
    margin-bottom: 32px;
}
.lib-hero::before,
.lib-hero::after {
    content: "⭐";
    position: absolute;
    font-size: 3rem;
    opacity: 0.25;
}
.lib-hero::before { top: 16px; left: 24px; transform: rotate(-15deg); }
.lib-hero::after { content: "🎮"; bottom: 12px; right: 28px; }
.lib-hero h1 { font-size: 2.6rem; margin: 0 0 8px; font-weight: 900; }
.lib-hero .lib-slogan { font-size: 1.2rem; opacity: 0.95; margin: 0 0 24px; }
.lib-hero-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.lib-hero .vh-btn-primary { background: #fff; color: #4f46e5; }
.lib-hero .vh-btn-outline {
    background: transparent; color: #fff; border: 2px solid #fff;
    display: inline-block; padding: 12px 24px; font-weight: 700;
    border-radius: 999px; text-decoration: none; cursor: pointer; font-size: 1rem;
}

/* ---------- Section ---------- */
.lib-section { margin-bottom: 36px; }
.lib-section-title { font-size: 1.5rem; font-weight: 800; margin: 0 0 4px; }
.lib-section-sub { color: var(--ink-soft); margin: 0 0 16px; }

/* ---------- Card môn học ---------- */
.lib-subject-card {
    display: block; text-decoration: none; color: var(--ink);
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--subject-color, var(--brand)) 16%, #ffffff),
        #ffffff 72%);
    border-radius: var(--radius);
    box-shadow: var(--shadow); overflow: hidden;
    transition: transform 0.12s ease, box-shadow 0.15s ease;
    border-top: 6px solid var(--subject-color, var(--brand));
}
.lib-subject-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(79,70,229,0.2); }
.lib-subject-body { padding: 20px; }
.lib-subject-icon {
    width: 58px; height: 58px; border-radius: 18px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.9rem;
    background: color-mix(in srgb, var(--subject-color, var(--brand)) 20%, #ffffff);
    box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--subject-color, var(--brand)) 40%, #ffffff);
}
.lib-subject-name { font-size: 1.15rem; font-weight: 800; margin: 10px 0 4px; }
.lib-subject-desc { color: var(--ink-soft); font-size: 0.92rem; margin: 0 0 8px; }
.lib-subject-meta { font-size: 0.85rem; font-weight: 700; color: var(--subject-color, var(--brand)); }

/* ---------- Card chủ đề / bài học ---------- */
.lib-card {
    background: color-mix(in srgb, var(--subject-color, var(--brand)) 7%, #ffffff);
    border-radius: var(--radius);
    box-shadow: var(--shadow); padding: 20px;
    display: flex; flex-direction: column; gap: 8px;
    border-left: 6px solid var(--subject-color, var(--brand));
}
.lib-card-title { font-size: 1.1rem; font-weight: 800; margin: 0; }
.lib-card-title a { color: var(--ink); text-decoration: none; }
.lib-card-title a:hover { color: var(--brand); }
.lib-card-desc { color: var(--ink-soft); font-size: 0.92rem; margin: 0; flex: 1; }
.lib-card-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; font-size: 0.85rem; }
.lib-card-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }

.lib-badge {
    display: inline-block; padding: 4px 12px; border-radius: 999px;
    font-size: 0.8rem; font-weight: 700; background: #eef2ff; color: var(--brand);
}
.lib-badge-diff-de { background: #dcfce7; color: #166534; }
.lib-badge-diff-trung_binh { background: #fef3c7; color: #92400e; }
.lib-badge-diff-kho { background: #fee2e2; color: #991b1b; }
.lib-badge-type {
    background: color-mix(in srgb, var(--subject-color, var(--brand)) 14%, #ffffff);
    color: color-mix(in srgb, var(--subject-color, var(--brand)) 72%, #000);
}

/* ---------- Nút yêu thích ---------- */
.lib-fav-btn {
    border: 2px solid #e2e8f0; background: #fff; border-radius: 999px;
    padding: 8px 16px; font-size: 1rem; cursor: pointer; font-weight: 700; color: var(--ink-soft);
}
.lib-fav-btn:hover { border-color: #f472b6; color: #db2777; }
.lib-fav-btn.is-fav { border-color: #f472b6; color: #db2777; background: #fdf2f8; }

/* ---------- Breadcrumb ---------- */
.lib-breadcrumb { font-size: 0.92rem; color: var(--ink-soft); margin-bottom: 16px; }
.lib-breadcrumb a { color: var(--brand); text-decoration: none; font-weight: 600; }
.lib-breadcrumb a:hover { text-decoration: underline; }

/* ---------- Modal hướng dẫn ---------- */
.lib-modal-backdrop {
    position: fixed; inset: 0; background: rgba(30, 41, 59, 0.55);
    display: flex; align-items: center; justify-content: center; z-index: 100; padding: 16px;
}
.lib-modal {
    background: #fff; border-radius: 20px; padding: 32px; max-width: 560px; width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
.lib-step { display: flex; gap: 16px; margin-bottom: 20px; align-items: flex-start; }
.lib-step-num {
    flex: none; width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), #7c3aed); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; font-weight: 900;
}
.lib-step h3 { margin: 0 0 4px; font-size: 1.05rem; }
.lib-step p { margin: 0; color: var(--ink-soft); font-size: 0.95rem; }

/* ---------- Bộ lọc thư viện ---------- */
.lib-filters {
    background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow);
    padding: 20px; margin-bottom: 24px;
}
.lib-filter-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
.lib-filter-row .vh-field { margin-bottom: 0; flex: 1; min-width: 160px; }
.lib-filter-row .vh-field.search { flex: 2; min-width: 220px; }

/* ---------- Thẻ kiểu chơi ---------- */
.lib-gametype-grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
.lib-gametype-card {
    background: var(--card); border: 2px solid #e2e8f0; border-radius: var(--radius);
    padding: 16px; text-align: center;
}
.lib-gametype-card .lib-gt-name { font-weight: 800; font-size: 1.05rem; margin: 0 0 4px; }
.lib-gametype-card .lib-gt-count { color: var(--ink-soft); font-size: 0.9rem; margin: 0 0 12px; }
.lib-gametype-card.disabled { opacity: 0.5; }

/* ---------- Dropdown chọn kiểu chơi ---------- */
.lib-play-dropdown { position: relative; display: inline-block; }
.lib-play-menu {
    position: absolute; z-index: 60; background: #fff; border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18); padding: 8px; min-width: 220px;
}
.lib-play-menu form { margin: 0; }
.lib-play-menu button {
    display: block; width: 100%; text-align: left; background: none; border: none;
    padding: 10px 12px; border-radius: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 600;
}
.lib-play-menu button:hover { background: #eef2ff; color: var(--brand); }
.lib-play-menu button:disabled { opacity: 0.45; cursor: not-allowed; }

@media (max-width: 640px) {
    .lib-hero { padding: 36px 20px; }
    .lib-hero h1 { font-size: 1.9rem; }
}

/* ---------- Khối Tóm tắt bài học (trang chi tiết bài học) ---------- */
.vh-summary {
    margin-top: 24px;
    border-radius: var(--radius, 16px);
    border: 1px solid color-mix(in srgb, var(--subject-color, #4f46e5) 32%, #ffffff);
    background: color-mix(in srgb, var(--subject-color, #4f46e5) 8%, #ffffff);
    overflow: hidden;
}
.vh-summary-toggle {
    width: 100%;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px 18px;
    background: none; border: none; cursor: pointer;
    font-size: 1.1rem; font-weight: 800; color: var(--ink);
}
.vh-summary-toggle:hover {
    background: color-mix(in srgb, var(--subject-color, #4f46e5) 10%, transparent);
}
.vh-summary-chevron {
    display: inline-block;
    transition: transform 0.2s ease;
    color: color-mix(in srgb, var(--subject-color, #4f46e5) 70%, #000000);
}
.vh-summary-chevron.open { transform: rotate(180deg); }
.vh-summary-body { padding: 2px 18px 18px; }
.vh-summary-body ul { margin: 8px 0; padding-left: 22px; }
.vh-summary-body li { margin: 6px 0; line-height: 1.6; }
.vh-summary-body p { margin: 8px 0; line-height: 1.65; }
.vh-summary-body p:first-child, .vh-summary-body ul:first-child { margin-top: 8px; }
</style>
