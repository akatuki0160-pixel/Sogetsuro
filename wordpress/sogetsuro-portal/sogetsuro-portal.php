<?php
/**
 * Plugin Name:       SOGETSURO Portal
 * Description:       古民家ポータルサイト「SOGETSURO」のデザイン一式。全ページ共通のヘッダー・フッター、記事（投稿）の一覧・詳細テンプレート、設定画面、ショートコード（お知らせ・記事・Beds24予約画面）を追加します。
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       sogetsuro-portal
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

define( 'SG_PORTAL_VERSION', '1.0.0' );
define( 'SG_PORTAL_FILE', __FILE__ );
define( 'SG_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'SG_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'SG_PORTAL_OPTION', 'sogetsuro_portal' );
define( 'SG_PORTAL_PAGE_TEMPLATE', 'sogetsuro-page.php' );

require_once SG_PORTAL_DIR . 'includes/settings.php';
require_once SG_PORTAL_DIR . 'includes/helpers.php';
require_once SG_PORTAL_DIR . 'includes/templates.php';
require_once SG_PORTAL_DIR . 'includes/shortcodes.php';
require_once SG_PORTAL_DIR . 'includes/editor.php';

/**
 * プラグイン一覧に「設定」リンクを追加
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=sogetsuro-portal' ) ) . '">設定</a>' );
		return $links;
	}
);
