<?php
/**
 * Template Name: SOGETSURO（ヘッダー・フッターなし）
 * Template Post Type: page
 *
 * テーマのヘッダー・フッター・サイドバーを出さず、本文（カスタムHTMLブロック）だけを
 * 画面いっぱいに表示する固定ページ用テンプレートです。
 *
 * 使い方
 *   1. このファイルを、使用中のテーマ（できれば子テーマ）のフォルダ直下にアップロード
 *      例）/wp-content/themes/お使いの子テーマ/template-sogetsuro.php
 *   2. 固定ページの編集画面 右側「ページ」→「テンプレート」で
 *      「SOGETSURO（ヘッダー・フッターなし）」を選んで更新
 *
 * ※ 親テーマに直接置くと、テーマ更新時に消えることがあります。子テーマに置いてください。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'sg-body' ); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
}

while ( have_posts() ) {
	the_post();
	the_content();
}

wp_footer();
?>
</body>
</html>
