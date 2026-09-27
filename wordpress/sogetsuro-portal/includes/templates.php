<?php
/**
 * テンプレートの切り替え・CSS/JS の読み込み
 *
 * ・固定ページ：テンプレート「SOGETSURO 共通レイアウト」を選ぶと、共通ヘッダー・フッターで表示
 * ・投稿（記事）：記事ページ・記事一覧（投稿ページ／カテゴリー／タグ／日付／作成者）をこのデザインで表示
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * 表示中のページがどのテンプレートか（'page' / 'single' / 'archive' / ''）
 *
 * @param string|null $set 設定する値.
 * @return string
 */
function sg_portal_context( $set = null ) {
	static $context = '';
	if ( null !== $set ) {
		$context = $set;
	}
	return $context;
}

/**
 * 固定ページの「テンプレート」に「SOGETSURO 共通レイアウト」を追加
 */
add_filter(
	'theme_page_templates',
	function ( $templates ) {
		$templates[ SG_PORTAL_PAGE_TEMPLATE ] = 'SOGETSURO 共通レイアウト';
		return $templates;
	}
);

/**
 * 表示するテンプレートを決める
 */
add_filter(
	'template_include',
	function ( $template ) {
		$context = '';
		if ( is_page() && SG_PORTAL_PAGE_TEMPLATE === get_page_template_slug( get_queried_object_id() ) ) {
			$context = 'page';
		} elseif ( sg_portal_option( 'use_on_posts' ) ) {
			if ( is_singular( 'post' ) ) {
				$context = 'single';
			} elseif ( is_home() || is_category() || is_tag() || is_date() || is_author() ) {
				$context = 'archive';
			}
		}
		if ( ! $context ) {
			return $template;
		}
		sg_portal_context( $context );
		return SG_PORTAL_DIR . 'templates/' . $context . '.php';
	},
	99
);

/**
 * フォント・CSS・JS の読み込み
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! sg_portal_context() ) {
			return;
		}
		$ver = function ( $file ) {
			$path = SG_PORTAL_DIR . $file;
			return SG_PORTAL_VERSION . ( file_exists( $path ) ? '.' . filemtime( $path ) : '' );
		};
		wp_enqueue_style( 'sogetsuro-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Shippori+Mincho:wght@400;500;600&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts.
		wp_enqueue_style( 'sogetsuro-settings', SG_PORTAL_URL . 'assets/css/settings.css', array( 'sogetsuro-fonts' ), $ver( 'assets/css/settings.css' ) );
		wp_enqueue_style( 'sogetsuro-style', SG_PORTAL_URL . 'assets/css/style.css', array( 'sogetsuro-settings' ), $ver( 'assets/css/style.css' ) );
		$colors = sg_portal_color_css();
		if ( $colors ) {
			wp_add_inline_style( 'sogetsuro-style', $colors );
		}
		wp_enqueue_script( 'sogetsuro-main', SG_PORTAL_URL . 'assets/js/main.js', array(), $ver( 'assets/js/main.js' ), true );
	},
	20
);

/**
 * テーマの CSS・JS を外す（SOGETSURO のページだけ。設定画面でオフにできます）
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! sg_portal_context() || ! sg_portal_option( 'isolate_theme' ) ) {
			return;
		}
		$bases = array();
		foreach ( array_unique( array( get_template_directory_uri(), get_stylesheet_directory_uri() ) ) as $uri ) {
			$bases[] = $uri;
			$bases[] = wp_make_link_relative( $uri );
		}
		foreach ( array( wp_styles(), wp_scripts() ) as $deps ) {
			foreach ( (array) $deps->queue as $handle ) {
				$src = isset( $deps->registered[ $handle ] ) ? (string) $deps->registered[ $handle ]->src : '';
				foreach ( $bases as $base ) {
					if ( '' !== $src && '' !== $base && 0 === strpos( $src, $base ) ) {
						$deps->dequeue( $handle );
						break;
					}
				}
			}
		}
	},
	9999
);

/**
 * テーマが画像に付ける style 属性を外す（SOGETSURO のページだけ。上と同じ設定でオフにできます）
 * 例：Twenty Twenty-One は、アイキャッチなどの画像に幅・高さの style を付けるため、写真の縦横比が崩れます。
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr ) {
		if ( sg_portal_context() && sg_portal_option( 'isolate_theme' ) ) {
			unset( $attr['style'] );
		}
		return $attr;
	},
	99
);

/**
 * JS が動くことを最初に知らせる（ちらつき防止）
 */
add_action(
	'wp_head',
	function () {
		if ( ! sg_portal_context() ) {
			return;
		}
		wp_print_inline_script_tag( "(function(d){d.documentElement.classList.add('sg-js');try{if(sessionStorage.getItem('sg-visited')){d.documentElement.classList.add('sg-visited');}}catch(e){}})(document);" );
	},
	1
);

/**
 * body に目印のクラスを付ける
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( sg_portal_context() ) {
			$classes[] = 'sg-body';
		}
		return $classes;
	}
);

/**
 * Contact Form 7：SOGETSURO のページでは自動整形（<p>・<br> の自動挿入）をしない
 */
add_filter(
	'wpcf7_autop_or_not',
	function ( $autop ) {
		return sg_portal_context() ? false : $autop;
	}
);

/**
 * 「/」から始まる URL に、WordPress を置いたフォルダを補う（SOGETSURO のページだけ）
 *
 * 貼り付けた HTML は、画像やリンクを「/wp-content/…」「/reserve/」のようにドメイン直下からの形で書いています。
 * WordPress をフォルダの中（例：https://example.com/site/）に置いた場合は、表示するときに自動で
 *   /wp-content/uploads/sogetsuro/hero-01.jpg → /site/wp-content/uploads/sogetsuro/hero-01.jpg
 *   /reserve/                                 → /site/reserve/
 * のように補います。ドメイン直下に置いた場合は何も変えません。
 * HTML は書き換えずに、ステージング（フォルダの中）と本番（ドメイン直下）の両方で同じように表示できます。
 *
 * @param string $html 本文の HTML.
 * @return string
 */
function sg_portal_fix_root_paths( $html ) {
	$home    = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$content = (string) wp_parse_url( content_url( '/' ), PHP_URL_PATH );
	$home    = '' === $home ? '/' : trailingslashit( $home );
	$content = '' === $content ? '/wp-content/' : trailingslashit( $content );
	if ( '/' === $home && '/wp-content/' === $content ) {
		return $html;
	}

	// $path は「/」から始まる URL（「//」から始まるものは対象外）
	$fix = function ( $path ) use ( $home, $content ) {
		if ( 0 === strpos( $path, '/wp-content/' ) ) {
			return $content . substr( $path, strlen( '/wp-content/' ) );
		}
		if ( '/' !== $home && ( 0 === strpos( $path, $home ) || untrailingslashit( $home ) === $path ) ) {
			return $path; // すでにフォルダが付いている
		}
		return $home . ltrim( $path, '/' );
	};

	// src・href・action・poster
	$html = preg_replace_callback(
		'#(\s(?:src|href|action|poster)\s*=\s*)(["\'])(/(?!/)[^"\']*)\2#i',
		function ( $m ) use ( $fix ) {
			return $m[1] . $m[2] . $fix( $m[3] ) . $m[2];
		},
		$html
	);

	// srcset（「URL 幅」をカンマで区切ったもの）
	$html = preg_replace_callback(
		'#(\ssrcset\s*=\s*)(["\'])([^"\']*)\2#i',
		function ( $m ) use ( $fix ) {
			$items = array();
			foreach ( explode( ',', $m[3] ) as $item ) {
				$item    = trim( $item );
				$items[] = preg_match( '#^/(?!/)#', $item )
					? preg_replace_callback(
						'#^\S+#',
						function ( $url ) use ( $fix ) {
							return $fix( $url[0] );
						},
						$item
					)
					: $item;
			}
			return $m[1] . $m[2] . implode( ', ', $items ) . $m[2];
		},
		$html
	);

	// style の中の url(/…)
	return preg_replace_callback(
		'#url\(\s*(["\']?)(/(?!/)[^"\')\s]*)\1\s*\)#i',
		function ( $m ) use ( $fix ) {
			return 'url(' . $m[1] . $fix( $m[2] ) . $m[1] . ')';
		},
		$html
	);
}

add_filter(
	'the_content',
	function ( $content ) {
		return sg_portal_context() ? sg_portal_fix_root_paths( $content ) : $content;
	},
	20 // ショートコード（11）のあとに実行（Contact Form 7 のフォームの中のリンクも対象にする）
);
