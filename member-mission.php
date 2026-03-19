<?php
/**
 * 会員ミッションシステム
 *
 * ショートコード: [member_mission]
 * Code Snippets プラグインに貼り付けて使用。
 *
 * ステージ構成:
 *   Stage 1 (入会直後ミッション①)  : 1 項目
 *   Stage 2 (入会直後ミッション②)  : 2 項目
 *   Stage 3 (入会直後ミッション③)  : 3 項目
 *   Stage 4 (マンスリーミッション)  : 4 項目 / 毎月リセット
 */

// ============================================================
// ステージ定義
// ============================================================
function member_mission_get_stages() {
    return [
        1 => [
            'label'   => '入会直後ミッション①',
            'monthly' => false,
            'items'   => [
                'intro_post' => '質問掲示板の「自己紹介」に投稿する。',
            ],
        ],
        2 => [
            'label'   => '入会直後ミッション②',
            'monthly' => false,
            'items'   => [
                'diagnosis' => 'オススメ教材診断を使用する。',
                'download'  => '教材を１つダウンロードする。',
            ],
        ],
        3 => [
            'label'   => '入会直後ミッション③',
            'monthly' => false,
            'items'   => [
                'zoom_reserve' => '月１Zoom相談の予約を入れる。',
                'club_visit'   => 'クラブ活動を覗いてみる。',
                'weekend_talk' => '週末しゃべるばに参加する。',
            ],
        ],
        4 => [
            'label'   => 'マンスリーミッション',
            'monthly' => true,
            'items'   => [
                'zoom_monthly'  => '月１Zoom相談の予約を入れる。',
                'live_check'    => 'LIVE授業の内容をチェックする。',
                'material_try'  => '教材を１つ試してみる。',
                'weekend_talk2' => '週末しゃべるばに１回参加する。',
            ],
        ],
    ];
}

// ============================================================
// 月次リセット（マンスリーミッション）
// ============================================================
function member_mission_maybe_reset_monthly( $user_id ) {
    $stages = member_mission_get_stages();

    foreach ( $stages as $num => $stage ) {
        if ( empty( $stage['monthly'] ) ) {
            continue;
        }

        $current_ym  = gmdate( 'Y-m' );
        $meta_key_ym = 'member_mission_month_' . $num;
        $stored_ym   = get_user_meta( $user_id, $meta_key_ym, true );

        if ( $stored_ym !== $current_ym ) {
            update_user_meta( $user_id, 'member_mission_checked_' . $num, [] );
            update_user_meta( $user_id, $meta_key_ym, $current_ym );
        }
    }
}

// ============================================================
// ショートコード
// ============================================================
function member_mission_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . esc_html( 'このコンテンツを表示するにはログインが必要です。' ) . '</p>';
    }

    $user_id = get_current_user_id();
    member_mission_maybe_reset_monthly( $user_id );

    $stages       = member_mission_get_stages();
    $total_stages = count( $stages );
    $current      = (int) get_user_meta( $user_id, 'member_mission_stage', true );
    if ( $current < 1 || $current > $total_stages ) {
        $current = 1;
    }

    ob_start();
    ?>
    <div class="mmission-wrap" id="mmission-wrap"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'member_mission_nonce' ) ); ?>"
         data-ajaxurl="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>"
         data-total="<?php echo esc_attr( $total_stages ); ?>"
         data-current="<?php echo esc_attr( $current ); ?>">

        <?php foreach ( $stages as $num => $stage ) :
            $checked_meta = get_user_meta( $user_id, 'member_mission_checked_' . $num, true );
            $checked_list = is_array( $checked_meta ) ? $checked_meta : [];
            $items        = $stage['items'];
            $total_items  = count( $items );
            $done_items   = count( array_intersect( array_keys( $items ), $checked_list ) );
            $percent      = $total_items > 0 ? round( $done_items / $total_items * 100 ) : 0;
            $is_visible   = ( $num === $current );
        ?>
        <div class="mmission-stage<?php echo $is_visible ? ' mmission-stage--active' : ''; ?>"
             id="mmission-stage-<?php echo esc_attr( $num ); ?>"
             data-stage="<?php echo esc_attr( $num ); ?>"
             style="<?php echo $is_visible ? '' : 'display:none;opacity:0;'; ?>">

            <div class="mmission-header">
                <h3 class="mmission-title"><?php echo esc_html( $stage['label'] ); ?></h3>
                <span class="mmission-count" data-stage="<?php echo esc_attr( $num ); ?>">
                    <?php echo esc_html( $done_items ); ?> / <?php echo esc_html( $total_items ); ?> 完了
                </span>
            </div>

            <div class="mmission-bar-bg">
                <div class="mmission-bar-fill"
                     data-stage="<?php echo esc_attr( $num ); ?>"
                     style="width:<?php echo esc_attr( $percent ); ?>%"></div>
            </div>

            <ul class="mmission-list">
                <?php foreach ( $items as $key => $label ) :
                    $is_checked = in_array( $key, $checked_list, true );
                ?>
                <li class="mmission-item<?php echo $is_checked ? ' mmission-done' : ''; ?>"
                    data-stage="<?php echo esc_attr( $num ); ?>">
                    <label class="mmission-label">
                        <span class="mmission-cb<?php echo $is_checked ? ' mmission-cb--checked' : ''; ?>"
                              data-key="<?php echo esc_attr( $key ); ?>"
                              data-stage="<?php echo esc_attr( $num ); ?>"
                              role="checkbox"
                              tabindex="0"
                              aria-checked="<?php echo $is_checked ? 'true' : 'false'; ?>">
                            <svg class="mmission-checkmark" viewBox="0 0 12 10" aria-hidden="true">
                                <polyline points="1,5 4.5,9 11,1"/>
                            </svg>
                        </span>
                        <span class="mmission-text"><?php echo esc_html( $label ); ?></span>
                    </label>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="mmission-footer" data-stage="<?php echo esc_attr( $num ); ?>">
                <?php if ( $num > 1 ) : ?>
                <button class="mmission-back-btn" data-stage="<?php echo esc_attr( $num ); ?>" aria-label="前のミッションへ戻る">&#9664; 戻る</button>
                <?php endif; ?>
                <?php if ( $num < $total_stages ) : ?>
                <button class="mmission-next-btn"
                        data-stage="<?php echo esc_attr( $num ); ?>"
                        style="display:none;opacity:0;">
                    次のミッションへ &#9654;
                </button>
                <?php else : ?>
                <p class="mmission-complete-msg" style="display:none;opacity:0;">
                    全ミッション完了！おめでとうございます&#127881;
                </p>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>

        <div class="mmission-confetti-overlay" id="mmission-confetti" aria-hidden="true"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'member_mission', 'member_mission_shortcode' );

// ============================================================
// スタイル & スクリプトの読み込み
// ============================================================
function member_mission_enqueue() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    // ---- CSS ----
    $css = <<<'CSS'
/* ===== wrapper ===== */
.mmission-wrap {
    position: relative;
    max-width: 580px;
    margin: 24px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* ===== stage card ===== */
.mmission-stage {
    background: #fff;
    border-radius: 14px;
    padding: 28px 32px 24px;
    box-shadow: 0 4px 18px rgba(0,0,0,.09);
    position: relative;
}

/* ===== back button ===== */
.mmission-back-btn {
    background: none;
    border: 1px solid #dde3ee;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: .78rem;
    color: #888;
    cursor: pointer;
    transition: background .18s, color .18s;
    line-height: 1.6;
    margin-right: auto;
    flex-shrink: 0;
}
.mmission-back-btn:hover {
    background: #f0f4fb;
    color: #4a90e2;
    border-color: #4a90e2;
}

/* ===== header ===== */
.mmission-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.mmission-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #222;
}
.mmission-count {
    font-size: .82rem;
    color: #777;
    white-space: nowrap;
}

/* ===== progress bar ===== */
.mmission-bar-bg {
    background: #edf1f7;
    border-radius: 99px;
    height: 7px;
    margin-bottom: 22px;
    overflow: hidden;
}
.mmission-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #4a90e2, #6ab0f5);
    border-radius: 99px;
    transition: width .45s cubic-bezier(.4,0,.2,1);
}

/* ===== list ===== */
.mmission-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.mmission-item {
    border-bottom: 1px solid #f2f4f8;
    padding: 13px 0;
}
.mmission-item:last-child {
    border-bottom: none;
}
.mmission-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}

/* ===== custom circular checkbox ===== */
.mmission-cb {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #c5cde0;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .22s, border-color .22s, transform .22s;
    cursor: pointer;
    outline-offset: 3px;
}
.mmission-cb:focus-visible {
    outline: 2px solid #4a90e2;
}
.mmission-cb--checked {
    background: #4a90e2;
    border-color: #4a90e2;
    transform: scale(1.15);
    animation: mmission-cb-pop .28s ease;
}
@keyframes mmission-cb-pop {
    0%   { transform: scale(1); }
    55%  { transform: scale(1.28); }
    100% { transform: scale(1.15); }
}

/* checkmark SVG */
.mmission-checkmark {
    width: 11px;
    height: 9px;
    fill: none;
    stroke: #fff;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    opacity: 0;
    transition: opacity .15s .08s;
}
.mmission-cb--checked .mmission-checkmark {
    opacity: 1;
}

/* ===== item text ===== */
.mmission-text {
    font-size: .93rem;
    color: #333;
    line-height: 1.55;
    transition: color .2s;
}
.mmission-done .mmission-text {
    color: #b0b8c8;
    text-decoration: line-through;
    text-decoration-color: #c8d0df;
}

/* ===== footer ===== */
.mmission-footer {
    margin-top: 20px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
}

/* ===== next button ===== */
.mmission-next-btn {
    background: #4a90e2;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: .92rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(74,144,226,.35);
    transition: background .18s, box-shadow .18s, transform .12s;
    animation: mmission-btn-pop .35s cubic-bezier(.18,1.4,.4,1) both;
}
.mmission-next-btn:hover {
    background: #3a7bd0;
    box-shadow: 0 5px 14px rgba(74,144,226,.45);
    transform: translateY(-1px);
}
.mmission-next-btn:active {
    transform: translateY(0);
}
@keyframes mmission-btn-pop {
    0%   { transform: scale(.6); opacity: 0; }
    100% { transform: scale(1);  opacity: 1; }
}

/* ===== completion message ===== */
.mmission-complete-msg {
    margin: 0;
    padding: 10px 16px;
    background: #eaf3ff;
    border-left: 4px solid #4a90e2;
    border-radius: 6px;
    color: #2c6fad;
    font-size: .9rem;
    font-weight: 600;
    animation: mmission-btn-pop .35s cubic-bezier(.18,1.4,.4,1) both;
}

/* ===== confetti overlay ===== */
.mmission-confetti-overlay {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 99999;
    overflow: hidden;
}
.mmission-particle {
    position: absolute;
    top: -14px;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    opacity: 1;
    animation: mmission-drop var(--dur, 2.4s) var(--delay, 0s) ease-in forwards;
}
@keyframes mmission-drop {
    0%   { transform: translateY(0)      rotate(0deg)   scaleX(1);   opacity: 1; }
    70%  { opacity: 1; }
    100% { transform: translateY(105vh)  rotate(720deg) scaleX(.6);  opacity: 0; }
}
CSS;

    wp_register_style( 'member-mission-inline', false, [], null );
    wp_enqueue_style( 'member-mission-inline' );
    wp_add_inline_style( 'member-mission-inline', $css );

    // ---- JS ----
    $js = <<<'JS'
(function ($) {
    'use strict';

    var $wrap      = $('#mmission-wrap');
    if (!$wrap.length) { return; }

    var ajaxUrl    = $wrap.data('ajaxurl');
    var nonce      = $wrap.data('nonce');
    var total      = parseInt($wrap.data('total'), 10);
    var current    = parseInt($wrap.data('current'), 10);
    var celebrating = false;

    /* ── helpers ─────────────────────────────────────── */

    function stageEl(num) {
        return $('#mmission-stage-' + num);
    }

    function checkedInStage(num) {
        return stageEl(num).find('.mmission-cb--checked').length;
    }

    function totalInStage(num) {
        return stageEl(num).find('.mmission-cb').length;
    }

    /* ── refreshFooter ────────────────────────────────── */
    function refreshFooter(stageNum) {
        var done    = checkedInStage(stageNum);
        var tot     = totalInStage(stageNum);
        var pct     = tot > 0 ? Math.round(done / tot * 100) : 0;
        var allDone = (done === tot);

        // count label
        $wrap.find('.mmission-count[data-stage="' + stageNum + '"]')
             .text(done + ' / ' + tot + ' 完了');

        // progress bar
        $wrap.find('.mmission-bar-fill[data-stage="' + stageNum + '"]')
             .css('width', pct + '%');

        var $footer = $wrap.find('.mmission-footer[data-stage="' + stageNum + '"]');
        var $btn    = $footer.find('.mmission-next-btn');
        var $msg    = $footer.find('.mmission-complete-msg');

        if (allDone) {
            if ($btn.length) {
                if ($btn.css('display') === 'none') {
                    $btn.css({ display: 'inline-block', opacity: 0 })
                        .animate({ opacity: 1 }, 260);
                }
            } else if ($msg.length) {
                if ($msg.css('display') === 'none') {
                    $msg.css({ display: 'block', opacity: 0 })
                        .animate({ opacity: 1 }, 260);
                }
            }
        } else {
            $btn.animate({ opacity: 0 }, 160, function () { $(this).hide(); });
            $msg.animate({ opacity: 0 }, 160, function () { $(this).hide(); });
        }
    }

    /* ── celebrate ────────────────────────────────────── */
    function celebrate() {
        if (celebrating) { return; }
        celebrating = true;

        var $overlay = $('#mmission-confetti');
        $overlay.empty();

        var colors = [
            '#4a90e2','#e24a7a','#f5a623','#7ed321','#9b59b6',
            '#e74c3c','#1abc9c','#f39c12','#3498db','#2ecc71'
        ];

        for (var i = 0; i < 70; i++) {
            var color   = colors[Math.floor(Math.random() * colors.length)];
            var left    = Math.random() * 100;
            var dur     = (1.8 + Math.random() * 1.4).toFixed(2) + 's';
            var delay   = (Math.random() * 0.9).toFixed(2) + 's';
            var size    = (7 + Math.random() * 8).toFixed(1) + 'px';
            var shape   = Math.random() > 0.5 ? '50%' : '2px';

            $('<div class="mmission-particle"></div>').css({
                left             : left + '%',
                background       : color,
                width            : size,
                height           : size,
                borderRadius     : shape,
                '--dur'          : dur,
                '--delay'        : delay,
                animationDuration  : dur,
                animationDelay     : delay
            }).appendTo($overlay);
        }

        setTimeout(function () {
            $overlay.fadeOut(600, function () {
                $overlay.empty().show();
                celebrating = false;
            });
        }, 2800);
    }

    /* ── goToStage ────────────────────────────────────── */
    function goToStage(num, dir) {
        if (num < 1 || num > total) { return; }

        var $old = stageEl(current);
        var $new = stageEl(num);

        // save stage via AJAX
        $.post(ajaxUrl, {
            action : 'member_mission_set_stage',
            nonce  : nonce,
            stage  : num
        });

        $old.animate({ opacity: 0 }, 220, function () {
            $old.hide().removeClass('mmission-stage--active');
            $new.css({ opacity: 0, display: 'block' })
                .addClass('mmission-stage--active')
                .animate({ opacity: 1 }, 280);
            current = num;
            refreshFooter(current);
        });
    }

    /* ── checkbox toggle ─────────────────────────────── */
    $(document).on('click keydown', '.mmission-cb', function (e) {
        if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) { return; }
        e.preventDefault();

        var $cb      = $(this);
        var key      = $cb.data('key');
        var stageNum = parseInt($cb.data('stage'), 10);
        var isNowOn  = !$cb.hasClass('mmission-cb--checked');

        // DOM更新前に「全完了だったか」を記録
        var tot        = totalInStage(stageNum);
        var wasAllDone = (checkedInStage(stageNum) === tot);

        $cb.toggleClass('mmission-cb--checked', isNowOn)
           .attr('aria-checked', isNowOn ? 'true' : 'false');

        $cb.closest('.mmission-item').toggleClass('mmission-done', isNowOn);

        refreshFooter(stageNum);

        // DOM更新後に「全完了になったか」を判定 → 紙吹雪
        var allDone = (checkedInStage(stageNum) === tot);
        if (allDone && !wasAllDone) {
            setTimeout(celebrate, 120);
        }

        // persist via AJAX
        $.post(ajaxUrl, {
            action  : 'member_mission_save',
            nonce   : nonce,
            stage   : stageNum,
            key     : key,
            checked : isNowOn ? 1 : 0
        });
    });

    /* ── next button ─────────────────────────────────── */
    $(document).on('click', '.mmission-next-btn', function () {
        var fromStage = parseInt($(this).data('stage'), 10);
        goToStage(fromStage + 1, 'forward');
    });

    /* ── back button ─────────────────────────────────── */
    $(document).on('click', '.mmission-back-btn', function () {
        var fromStage = parseInt($(this).data('stage'), 10);
        goToStage(fromStage - 1, 'back');
    });

    /* ── init ────────────────────────────────────────── */
    $(function () {
        refreshFooter(current);
    });

})(jQuery);
JS;

    wp_register_script( 'member-mission-inline', false, [ 'jquery' ], null, true );
    wp_enqueue_script( 'member-mission-inline' );
    wp_add_inline_script( 'member-mission-inline', $js );
}
add_action( 'wp_enqueue_scripts', 'member_mission_enqueue' );

// ============================================================
// AJAX: チェック状態の保存
// ============================================================
function member_mission_save_ajax() {
    check_ajax_referer( 'member_mission_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $user_id     = get_current_user_id();
    $stage_num   = (int) sanitize_key( wp_unslash( $_POST['stage'] ?? '0' ) );
    $key         = sanitize_key( wp_unslash( $_POST['key'] ?? '' ) );
    $is_checked  = isset( $_POST['checked'] ) && '1' === $_POST['checked'];

    $stages = member_mission_get_stages();

    if ( ! isset( $stages[ $stage_num ] ) ) {
        wp_send_json_error( 'Invalid stage' );
    }

    $valid_keys = array_keys( $stages[ $stage_num ]['items'] );

    if ( ! in_array( $key, $valid_keys, true ) ) {
        wp_send_json_error( 'Invalid key' );
    }

    $meta_key = 'member_mission_checked_' . $stage_num;
    $saved    = get_user_meta( $user_id, $meta_key, true );
    $saved    = is_array( $saved ) ? $saved : [];

    if ( $is_checked ) {
        $saved[] = $key;
        $saved   = array_values( array_unique( $saved ) );
    } else {
        $saved = array_values( array_diff( $saved, [ $key ] ) );
    }

    update_user_meta( $user_id, $meta_key, $saved );
    wp_send_json_success( [ 'done' => count( $saved ) ] );
}
add_action( 'wp_ajax_member_mission_save', 'member_mission_save_ajax' );

// ============================================================
// AJAX: 現在のステージを保存
// ============================================================
function member_mission_set_stage_ajax() {
    check_ajax_referer( 'member_mission_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $user_id   = get_current_user_id();
    $stages    = member_mission_get_stages();
    $stage_num = (int) sanitize_key( wp_unslash( $_POST['stage'] ?? '0' ) );

    if ( ! isset( $stages[ $stage_num ] ) ) {
        wp_send_json_error( 'Invalid stage' );
    }

    update_user_meta( $user_id, 'member_mission_stage', $stage_num );
    wp_send_json_success( [ 'stage' => $stage_num ] );
}
add_action( 'wp_ajax_member_mission_set_stage', 'member_mission_set_stage_ajax' );
