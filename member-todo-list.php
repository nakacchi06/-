<?php
/**
 * 会員用ToDoリスト
 *
 * ショートコード: [member_todo_list]
 * Code Snippets プラグインに貼り付けて使用。
 *
 * ■ 項目のカスタマイズ方法
 *   下の member_todo_get_items() 内の配列を編集してください。
 *   'キー名' => 'ラベル文字列'  の形式で追加・変更できます。
 */

// ============================================================
// チェックリスト項目の定義
// ============================================================
function member_todo_get_items() {
    return [
        'intro_post'   => '質問掲示板の「自己紹介」に投稿する。',
        'diagnosis'    => '「オススメ教材診断」を使用する。',
        'download'     => '教材を１つダウンロードする。',
        'zoom_reserve' => '「月１Zoom相談」の予約を入れる。',
    ];
}

// ============================================================
// ショートコード
// ============================================================
function member_todo_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>このコンテンツを表示するにはログインが必要です。</p>';
    }

    $user_id      = get_current_user_id();
    $checked      = get_user_meta( $user_id, 'member_todo_checked', true );
    $checked      = is_array( $checked ) ? $checked : [];
    $items        = member_todo_get_items();
    $total        = count( $items );
    $done         = count( array_intersect( array_keys( $items ), $checked ) );
    $percent      = $total > 0 ? round( $done / $total * 100 ) : 0;

    ob_start();
    ?>
    <div class="mtodo-wrap">
        <div class="mtodo-header">
            <h3 class="mtodo-title">入会直後にやること</h3>
            <span class="mtodo-count"><?php echo esc_html( $done ); ?> / <?php echo esc_html( $total ); ?> 完了</span>
        </div>

        <div class="mtodo-bar-bg">
            <div class="mtodo-bar-fill" style="width:<?php echo esc_attr( $percent ); ?>%"></div>
        </div>

        <ul class="mtodo-list">
            <?php foreach ( $items as $key => $label ) :
                $is_checked = in_array( $key, $checked, true );
            ?>
            <li class="mtodo-item<?php echo $is_checked ? ' mtodo-done' : ''; ?>">
                <label class="mtodo-label">
                    <input
                        type="checkbox"
                        class="mtodo-checkbox"
                        data-key="<?php echo esc_attr( $key ); ?>"
                        <?php checked( $is_checked ); ?>
                    >
                    <span class="mtodo-text"><?php echo esc_html( $label ); ?></span>
                </label>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php if ( $done === $total ) : ?>
        <p class="mtodo-complete-msg">全て完了しました！次のステップへ進みましょう。</p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'member_todo_list', 'member_todo_shortcode' );

// ============================================================
// スタイル & スクリプトの読み込み
// ============================================================
function member_todo_enqueue() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $css = "
    .mtodo-wrap {
        max-width: 560px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 24px 28px;
        margin: 24px 0;
        font-family: sans-serif;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .mtodo-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .mtodo-title {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
    }
    .mtodo-count {
        font-size: .85rem;
        color: #666;
        white-space: nowrap;
    }
    .mtodo-bar-bg {
        background: #eee;
        border-radius: 99px;
        height: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .mtodo-bar-fill {
        height: 100%;
        background: #4a90e2;
        border-radius: 99px;
        transition: width .4s ease;
    }
    .mtodo-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .mtodo-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 12px 0;
    }
    .mtodo-item:last-child {
        border-bottom: none;
    }
    .mtodo-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }
    .mtodo-checkbox {
        width: 18px;
        height: 18px;
        accent-color: #4a90e2;
        cursor: pointer;
        flex-shrink: 0;
    }
    .mtodo-text {
        font-size: .95rem;
        color: #333;
        line-height: 1.5;
        transition: color .2s, text-decoration .2s;
    }
    .mtodo-done .mtodo-text {
        color: #aaa;
        text-decoration: line-through;
    }
    .mtodo-complete-msg {
        margin: 16px 0 0;
        padding: 10px 14px;
        background: #eaf4ff;
        border-left: 4px solid #4a90e2;
        border-radius: 4px;
        color: #2c6fad;
        font-size: .9rem;
    }
    ";

    $js = "
    (function($){
        function updateProgress() {
            var total   = $('.mtodo-checkbox').length;
            var done    = $('.mtodo-checkbox:checked').length;
            var percent = total > 0 ? Math.round(done / total * 100) : 0;
            $('.mtodo-count').text(done + ' / ' + total + ' 完了');
            $('.mtodo-bar-fill').css('width', percent + '%');

            if (done === total) {
                if (!$('.mtodo-complete-msg').length) {
                    $('.mtodo-list').after('<p class=\"mtodo-complete-msg\">全て完了しました！次のステップへ進みましょう。</p>');
                }
            } else {
                $('.mtodo-complete-msg').remove();
            }
        }

        $(document).on('change', '.mtodo-checkbox', function() {
            var key     = $(this).data('key');
            var checked = $(this).is(':checked');
            var \$item  = $(this).closest('.mtodo-item');

            if (checked) {
                \$item.addClass('mtodo-done');
            } else {
                \$item.removeClass('mtodo-done');
            }
            updateProgress();

            $.post(memberTodoAjax.ajaxUrl, {
                action  : 'member_todo_save',
                nonce   : memberTodoAjax.nonce,
                key     : key,
                checked : checked ? 1 : 0
            });
        });
    })(jQuery);
    ";

    wp_enqueue_script( 'jquery' );
    wp_add_inline_style( 'wp-block-library', $css );   // 既存のスタイルシートに追記
    wp_add_inline_script( 'jquery-migrate', $js );

    wp_localize_script( 'jquery-migrate', 'memberTodoAjax', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'member_todo_nonce' ),
    ] );
}
add_action( 'wp_enqueue_scripts', 'member_todo_enqueue' );

// ============================================================
// AJAX: チェック状態の保存
// ============================================================
function member_todo_save() {
    check_ajax_referer( 'member_todo_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $user_id      = get_current_user_id();
    $key          = sanitize_key( wp_unslash( $_POST['key'] ?? '' ) );
    $is_checked   = ! empty( $_POST['checked'] ) && '1' === $_POST['checked'];
    $valid_keys   = array_keys( member_todo_get_items() );

    // 定義済みのキー以外は無視
    if ( ! in_array( $key, $valid_keys, true ) ) {
        wp_send_json_error( 'Invalid key' );
    }

    $saved = get_user_meta( $user_id, 'member_todo_checked', true );
    $saved = is_array( $saved ) ? $saved : [];

    if ( $is_checked ) {
        $saved[] = $key;
        $saved   = array_unique( $saved );
    } else {
        $saved = array_values( array_diff( $saved, [ $key ] ) );
    }

    update_user_meta( $user_id, 'member_todo_checked', $saved );
    wp_send_json_success( [ 'done' => count( $saved ) ] );
}
add_action( 'wp_ajax_member_todo_save', 'member_todo_save' );
