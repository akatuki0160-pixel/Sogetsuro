<?php
/**
 * 記事の投稿画面（初心者の方でも書きやすくするための設定）
 *
 * ・「投稿 → 新規追加」を開くと、リード文・大見出し・本文・写真・小見出し の枠が最初から入っています。
 *   灰色の案内文をクリックして、文章を入れていくだけで記事ができあがります。
 * ・投稿画面の右側に「記事の書き方」の案内を表示します。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * 新しい記事を開いたときに、最初から入っているブロック
 */
add_filter(
	'register_post_type_args',
	function ( $args, $post_type ) {
		if ( 'post' !== $post_type || ! sg_portal_option( 'use_on_posts' ) ) {
			return $args;
		}
		$args['template'] = array(
			array( 'core/paragraph', array( 'placeholder' => 'リード文：記事のはじめに表示される導入の文章です（2〜3行が目安）。' ) ),
			array(
				'core/heading',
				array(
					'level'       => 2,
					'placeholder' => '大見出し（例：朝の町は、音から目を覚ます）',
				),
			),
			array( 'core/paragraph', array( 'placeholder' => '本文を書きます。Enter キーを押すと、次の段落になります。' ) ),
			array( 'core/image', array() ),
			array(
				'core/heading',
				array(
					'level'       => 3,
					'placeholder' => '小見出し（例：おすすめは、川沿いの小道）',
				),
			),
			array( 'core/paragraph', array( 'placeholder' => '本文' ) ),
			array(
				'core/heading',
				array(
					'level'       => 2,
					'placeholder' => '大見出し',
				),
			),
			array( 'core/paragraph', array( 'placeholder' => '本文' ) ),
		);
		return $args;
	},
	10,
	2
);

/**
 * 投稿画面の右側に「記事の書き方」を表示
 */
add_action(
	'add_meta_boxes_post',
	function () {
		if ( ! sg_portal_option( 'use_on_posts' ) ) {
			return;
		}
		add_meta_box( 'sogetsuro-portal-help', '記事の書き方', 'sg_portal_render_help', 'post', 'side', 'high' );
	}
);

/**
 * 「記事の書き方」の中身
 */
function sg_portal_render_help() {
	?>
	<ol style="margin:0 0 0 1.4em;line-height:1.8">
		<li><strong>タイトル</strong>：いちばん上に記事の題名を入れます。</li>
		<li><strong>大見出し</strong>：「見出し」ブロックの <strong>H2</strong>。</li>
		<li><strong>小見出し</strong>：「見出し」ブロックの <strong>H3</strong>。</li>
		<li><strong>本文</strong>：「段落」ブロック。Enter で次の段落になります。</li>
		<li><strong>写真</strong>：「画像」ブロック。説明文（キャプション）も入れられます。</li>
		<li>右側の「投稿」タブで<strong>カテゴリー</strong>と<strong>アイキャッチ画像</strong>を選びます。</li>
	</ol>
	<p style="margin-top:1em">目次・日付・デザインは自動で整います。文字の色や大きさは変えなくて大丈夫です。</p>
	<?php
}
