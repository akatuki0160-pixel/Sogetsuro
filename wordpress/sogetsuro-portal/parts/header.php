<?php
/**
 * 共通ヘッダー（<html> の開始〜ヘッダー・メニュー〜 <main> の開始まで）
 * 静的版の static/index.html の <!-- sg:header --> と同じ内容です。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

$sg_is_front  = is_front_page();
$sg_site_name = sg_portal_option( 'site_name' );
$sg_site_sub  = sg_portal_option( 'site_sub' );
$sg_reserve   = sg_portal_url( sg_portal_option( 'reserve_url' ) );
$sg_nav       = sg_portal_nav_items();
$sg_sns       = array_filter(
	array(
		'Instagram' => sg_portal_option( 'instagram' ),
		'Facebook'  => sg_portal_option( 'facebook' ),
	)
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
<title><?php echo esc_html( wp_get_document_title() ); ?></title>
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="sg-site<?php echo $sg_is_front ? ' sg-site--intro' : ''; ?>" id="sg-site">

	<?php if ( $sg_is_front && sg_portal_option( 'show_loader' ) ) : ?>
	<!-- ローディング（トップページのみ） -->
	<div class="sg-loader" aria-hidden="true">
		<div class="sg-loader__inner">
			<svg class="sg-loader__mark"><use href="#sg-emblem"></use></svg>
			<span class="sg-loader__name"><?php echo esc_html( $sg_site_name ); ?></span>
		</div>
	</div>
	<?php endif; ?>

	<!-- ロゴマーク（円窓） -->
	<svg class="sg-sprite" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
		<symbol id="sg-emblem" viewBox="0 0 48 48">
			<circle cx="24" cy="24" r="22.5" fill="none" stroke="currentColor" stroke-width="1"/>
			<path fill="none" stroke="currentColor" stroke-width="1" d="M16 2.97V45.03M24 1.5V46.5M32 2.97V45.03M2.62 31H45.38"/>
		</symbol>
	</svg>

	<!-- ヘッダー -->
	<header class="sg-header">
		<div class="sg-header__inner">
			<a class="sg-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $sg_site_name ); ?> トップページへ">
				<svg class="sg-header__mark" aria-hidden="true"><use href="#sg-emblem"></use></svg>
				<span class="sg-header__name"><?php echo esc_html( $sg_site_name ); ?></span>
				<?php if ( $sg_site_sub ) : ?>
				<span class="sg-header__sub"><?php echo esc_html( $sg_site_sub ); ?></span>
				<?php endif; ?>
			</a>
			<nav class="sg-gnav" aria-label="メインメニュー">
				<ul class="sg-gnav__list">
					<?php foreach ( $sg_nav as $sg_item ) : ?>
						<?php if ( ! empty( $sg_item['gnav'] ) ) : ?>
					<li><a href="<?php echo esc_url( $sg_item['url'] ); ?>"><?php echo esc_html( $sg_item['ja'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>
			<a class="sg-header__reserve" href="<?php echo esc_url( $sg_reserve ); ?>"><span class="sg-header__reserve-en">Reserve</span><span class="sg-header__reserve-ja">ご予約</span></a>
			<button class="sg-menu-btn" type="button" aria-expanded="false" aria-controls="sg-drawer" aria-label="メニューを開く">
				<span class="sg-menu-btn__lines" aria-hidden="true"><span></span><span></span></span>
				<span class="sg-menu-btn__text" aria-hidden="true">Menu</span>
			</button>
		</div>
	</header>

	<!-- メニュー（ハンバーガーで開くパネル） -->
	<div class="sg-drawer" id="sg-drawer" inert>
		<div class="sg-drawer__visual" aria-hidden="true">
			<img src="<?php echo esc_url( sg_portal_url( sg_portal_option( 'menu_image' ) ) ); ?>" alt="" width="1200" height="1600" loading="lazy" decoding="async">
		</div>
		<div class="sg-drawer__body">
			<nav class="sg-drawer__nav" aria-label="サイトメニュー">
				<ul class="sg-drawer__list">
					<?php foreach ( $sg_nav as $sg_item ) : ?>
					<li><a href="<?php echo esc_url( $sg_item['url'] ); ?>"><span class="sg-drawer__en"><?php echo esc_html( $sg_item['en'] ); ?></span><span class="sg-drawer__ja"><?php echo esc_html( $sg_item['ja'] ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="sg-drawer__info">
				<a class="sg-btn sg-btn--light" href="<?php echo esc_url( $sg_reserve ); ?>">空室検索・ご予約</a>
				<p class="sg-drawer__tel">TEL <a href="<?php echo esc_attr( sg_portal_tel_href() ); ?>"><?php echo esc_html( sg_portal_option( 'tel' ) ); ?></a></p>
				<?php if ( $sg_sns ) : ?>
				<ul class="sg-drawer__sns">
					<?php foreach ( $sg_sns as $sg_label => $sg_url ) : ?>
					<li><a href="<?php echo esc_url( $sg_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sg_label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<main class="sg-main" id="sg-main">
