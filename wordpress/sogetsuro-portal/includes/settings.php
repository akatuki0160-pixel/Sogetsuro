<?php
/**
 * 設定画面（管理画面「設定 → SOGETSURO」）
 *
 * 電話番号・住所・SNS・各ページのURL・Beds24 の物件ID・配色などを、
 * コードを触らずに変更できるようにしています。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * 設定項目の一覧（表示順・種類・初期値）
 *
 * @return array
 */
function sg_portal_fields() {
	return array(
		'basic'    => array(
			'title'  => '基本情報',
			'fields' => array(
				'site_name' => array( 'label' => '施設名', 'type' => 'text', 'default' => '湊月楼', 'help' => 'ロゴ画像の代わりの文字や、コピーライトなどに使われます。' ),
				'site_sub'  => array( 'label' => 'サブタイトル', 'type' => 'text', 'default' => '佐渡町家一棟貸しの宿' ),
				'tel'       => array( 'label' => '電話番号', 'type' => 'text', 'default' => '0259-27-2550', 'help' => 'ハイフン付きで入力してください。タップで発信できるリンクは自動で作られます。' ),
				'tel_hours' => array( 'label' => '電話の受付時間', 'type' => 'text', 'default' => '', 'help' => '今は表示に使っていません（控えとしてご利用ください）。' ),
				'address'   => array( 'label' => '住所', 'type' => 'text', 'default' => '〒952-0014 新潟県佐渡市両津湊39' ),
				'operator'  => array( 'label' => '運営会社', 'type' => 'text', 'default' => '株式会社アジアンドキュメンタリーズ', 'help' => 'フッターに「運営：〇〇」と表示されます。空欄にすると表示されません。' ),
				'instagram' => array( 'label' => 'Instagram のURL', 'type' => 'url', 'default' => 'https://www.instagram.com/sogetsuro_sado', 'help' => '空欄にすると表示されません。' ),
				'facebook'  => array( 'label' => 'Facebook のURL', 'type' => 'url', 'default' => 'https://www.facebook.com/sougetsurou.sado', 'help' => '空欄にすると表示されません。' ),
				'line'      => array( 'label' => 'LINE のURL', 'type' => 'url', 'default' => 'https://lin.ee/ufmJAFa', 'help' => '空欄にすると表示されません。' ),
				'whatsapp'  => array( 'label' => 'WhatsApp のURL', 'type' => 'url', 'default' => 'https://wa.me/message/SHPZVZGGMMDHG1', 'help' => '空欄にすると表示されません。' ),
			),
		),
		'links'    => array(
			'title'  => 'ページのURL',
			'fields' => array(
				'reserve_url' => array( 'label' => 'ご予約ページ', 'type' => 'url', 'default' => '/reserve/' ),
				'contact_url' => array( 'label' => 'お問い合わせページ', 'type' => 'url', 'default' => '/contact/' ),
				'privacy_url' => array( 'label' => 'プライバシーポリシー', 'type' => 'url', 'default' => '/privacy-policy/', 'help' => '空欄にするとフッターに表示されません。' ),
			),
		),
		'beds24'   => array(
			'title'  => '予約システム（Beds24）',
			'fields' => array(
				'beds24_propid' => array( 'label' => '物件ID（propid）', 'type' => 'text', 'default' => '', 'help' => 'Beds24 の物件ID（数字）。ご予約ページの [sogetsuro_beds24] に使われます。' ),
				'beds24_lang'   => array( 'label' => '予約画面の言語', 'type' => 'text', 'default' => 'ja', 'help' => '通常は ja（日本語）のままで大丈夫です。' ),
			),
		),
		'images'   => array(
			'title'  => '画像',
			'fields' => array(
				'logo_image'   => array( 'label' => 'ロゴ画像（白）', 'type' => 'url', 'default' => '/wp-content/uploads/sogetsuro/logo.png', 'help' => 'ヘッダー・フッター・ローディングに表示されます。空欄にすると、施設名の文字で表示します。' ),
				'logo_mark'    => array( 'label' => 'ロゴマーク（白）', 'type' => 'url', 'default' => '/wp-content/uploads/sogetsuro/logo-mark.png', 'help' => '写真のない記事カードなどに表示されます。' ),
				'journal_hero' => array( 'label' => '記事一覧の見出し画像', 'type' => 'url', 'default' => '/wp-content/uploads/sogetsuro/page-journal.jpg', 'help' => '「投稿ページ」に指定した固定ページにアイキャッチ画像を設定すると、そちらが優先されます。' ),
				'cta_image'    => array( 'label' => '記事ページ下の予約バナーの画像', 'type' => 'url', 'default' => '/wp-content/uploads/sogetsuro/reserve.jpg' ),
				'menu_image'   => array( 'label' => 'メニューを開いたときの画像', 'type' => 'url', 'default' => '/wp-content/uploads/sogetsuro/menu.jpg' ),
			),
		),
		'colors'   => array(
			'title'  => '配色',
			'fields' => array(
				'color_bg'     => array( 'label' => '背景', 'type' => 'color', 'default' => '#030925', 'var' => '--sg-c-bg' ),
				'color_bg_sub' => array( 'label' => '背景（サブ）', 'type' => 'color', 'default' => '#0a1433', 'var' => '--sg-c-bg-sub' ),
				'color_text'   => array( 'label' => '文字', 'type' => 'color', 'default' => '#efece6', 'var' => '--sg-c-text' ),
				'color_accent' => array( 'label' => 'アクセント（予約ボタン・番号）', 'type' => 'color', 'default' => '#cfa85a', 'var' => '--sg-c-accent' ),
				'color_dark'   => array( 'label' => '暗い背景（メニュー・フッター）', 'type' => 'color', 'default' => '#01040f', 'var' => '--sg-c-dark' ),
			),
		),
		'behavior' => array(
			'title'  => '表示の設定',
			'fields' => array(
				'use_on_posts'  => array( 'label' => '記事（投稿）をこのデザインで表示する', 'type' => 'checkbox', 'default' => 1 ),
				'isolate_theme' => array( 'label' => 'SOGETSURO のページではテーマの CSS・JS を読み込まない（表示崩れを防ぎます）', 'type' => 'checkbox', 'default' => 1 ),
				'show_loader'   => array( 'label' => 'トップページでローディング画面を表示する', 'type' => 'checkbox', 'default' => 1 ),
			),
		),
	);
}

/**
 * 初期値
 *
 * @return array
 */
function sg_portal_defaults() {
	$defaults = array();
	foreach ( sg_portal_fields() as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			$defaults[ $key ] = $field['default'];
		}
	}
	return $defaults;
}

/**
 * 設定値を取り出す
 *
 * @param string $key 設定のキー.
 * @return mixed
 */
function sg_portal_option( $key ) {
	$options = wp_parse_args( (array) get_option( SG_PORTAL_OPTION, array() ), sg_portal_defaults() );
	return isset( $options[ $key ] ) ? $options[ $key ] : '';
}

/**
 * 保存前のチェック
 *
 * @param mixed $input 送信された値.
 * @return array
 */
function sg_portal_sanitize( $input ) {
	$input = (array) $input;
	$clean = array();
	foreach ( sg_portal_fields() as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			$value = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
			switch ( $field['type'] ) {
				case 'checkbox':
					$clean[ $key ] = empty( $value ) ? 0 : 1;
					break;
				case 'url':
					$clean[ $key ] = esc_url_raw( trim( $value ) );
					break;
				case 'color':
					$color         = sanitize_hex_color( $value );
					$clean[ $key ] = $color ? strtolower( $color ) : $field['default'];
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $value );
			}
		}
	}
	$clean['beds24_propid'] = preg_replace( '/[^0-9]/', '', $clean['beds24_propid'] );
	$clean['beds24_lang']   = sanitize_key( $clean['beds24_lang'] );
	return $clean;
}

add_action(
	'admin_init',
	function () {
		register_setting(
			'sogetsuro_portal',
			SG_PORTAL_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'sg_portal_sanitize',
				'default'           => array(),
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page( 'SOGETSURO の設定', 'SOGETSURO', 'manage_options', 'sogetsuro-portal', 'sg_portal_render_settings' );
	}
);

/**
 * 設定画面の表示
 */
function sg_portal_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>SOGETSURO の設定</h1>
		<p>ヘッダー・フッター・記事ページ・予約画面などに表示される情報です。変更したら「変更を保存」を押してください。</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'sogetsuro_portal' ); ?>
			<?php foreach ( sg_portal_fields() as $group ) : ?>
				<h2 class="title"><?php echo esc_html( $group['title'] ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $group['fields'] as $key => $field ) :
						$value = sg_portal_option( $key );
						$name  = SG_PORTAL_OPTION . '[' . $key . ']';
						$id    = 'sg-portal-' . $key;
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( 'checkbox' === $field['type'] ) : ?>
									<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0">
									<label><input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( 1, (int) $value ); ?>> 有効にする</label>
								<?php elseif ( 'color' === $field['type'] ) : ?>
									<input type="color" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ? $value : $field['default'] ); ?>">
									<span class="description">初期値 <?php echo esc_html( $field['default'] ); ?></span>
								<?php else : ?>
									<input type="text" class="regular-text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
								<?php endif; ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>

		<h2 class="title">ショートコード（カスタムHTMLブロックの中でも使えます）</h2>
		<table class="widefat striped" style="max-width:900px">
			<tbody>
				<tr><td><code>[sogetsuro_news count="3" category="news"]</code></td><td>お知らせの一覧（日付・カテゴリー・タイトル）。<code>category</code> に指定したカテゴリー（スラッグ）の記事だけを表示します。外すと、すべての記事から新しい順に表示します。</td></tr>
				<tr><td><code>[sogetsuro_journal count="3" exclude="news"]</code></td><td>記事のカード一覧（写真つき）。<code>exclude</code> に指定したカテゴリーの記事は表示しません。<code>category</code> も指定できます。</td></tr>
				<tr><td><code>[sogetsuro_beds24]</code></td><td>Beds24 の予約画面。<code>roomid="部屋ID"</code> を付けると、その部屋だけを表示します。</td></tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * 設定画面で変更された色を、CSS変数として出力する
 *
 * @return string
 */
function sg_portal_color_css() {
	$rules = array();
	foreach ( sg_portal_fields()['colors']['fields'] as $key => $field ) {
		$value = sg_portal_option( $key );
		if ( $value && strtolower( $value ) !== $field['default'] ) {
			$rules[] = $field['var'] . ':' . $value;
		}
	}
	return $rules ? '.sg-site{' . implode( ';', $rules ) . '}' : '';
}

/**
 * 以前の版（HTML・CSS・JS を貼り付ける方式）が残っているときのお知らせ
 * 古い CSS が「追加CSS」に残っていると、新しいデザインの上に古い指定がかかって表示が崩れます。
 */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins', 'edit-page', 'settings_page_sogetsuro-portal' ), true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$messages = array();
		if ( substr_count( wp_get_custom_css(), '.sg-site' ) >= 50 ) {
			$messages[] = '「追加CSS」に以前の SOGETSURO の CSS（02-custom.css）が残っています。新しいデザインの上に古い指定がかかり、表示が崩れます。「外観 → カスタマイズ → 追加CSS」（ブロックテーマの場合は「外観 → エディター → スタイル → 追加CSS」）から削除してください。';
		}
		$old_pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'template-sogetsuro.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'         => 'ids',
				'posts_per_page' => 10,
				'no_found_rows'  => true,
			)
		);
		if ( $old_pages ) {
			$titles     = array_map( 'get_the_title', $old_pages );
			$messages[] = '以前のテンプレート「SOGETSURO（ヘッダー・フッターなし）」を使っている固定ページがあります（' . implode( '、', $titles ) . '）。テンプレートを「SOGETSURO 共通レイアウト」に変更し、本文を新しい HTML（wordpress/pages/）に貼り替えてください。';
		}
		if ( ! $messages ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>SOGETSURO：以前の版の設定が残っています</strong></p><ul style="list-style:disc;margin-left:2em">';
		foreach ( $messages as $message ) {
			echo '<li>' . esc_html( $message ) . '</li>';
		}
		echo '</ul></div>';
	}
);

/**
 * 更新したときの引き継ぎ
 * 以前の版の初期値のまま保存されている項目（設定画面で「変更を保存」を押すと、触っていない項目も
 * その時点の初期値で保存されます）を、新しい初期値に置き換えます。自分で入力した値はそのままです。
 */
add_action(
	'init',
	function () {
		if ( get_option( 'sogetsuro_portal_version' ) === SG_PORTAL_VERSION ) {
			return;
		}
		$saved = get_option( SG_PORTAL_OPTION );
		if ( is_array( $saved ) && $saved ) {
			$old_defaults = array(
				'site_name'    => 'SOGETSURO',
				'site_sub'     => '古民家の宿',
				'tel'          => '0000-00-0000',
				'tel_hours'    => '9:00〜18:00（水曜定休）',
				'address'      => '〒000-0000 〇〇県〇〇市〇〇町0-00',
				'instagram'    => '',
				'facebook'     => '',
				'color_bg'     => '#f4f1ea',
				'color_bg_sub' => '#ebe5da',
				'color_text'   => '#2b2724',
				'color_accent' => '#8b3d2c',
				'color_dark'   => '#211e1b',
			);
			$defaults = sg_portal_defaults();
			$options  = wp_parse_args( $saved, $defaults ); // 新しく増えた項目は初期値で
			foreach ( $old_defaults as $key => $value ) {
				if ( isset( $options[ $key ] ) && (string) $options[ $key ] === $value ) {
					$options[ $key ] = $defaults[ $key ];
				}
			}
			update_option( SG_PORTAL_OPTION, $options );
		}
		update_option( 'sogetsuro_portal_version', SG_PORTAL_VERSION );
	}
);
