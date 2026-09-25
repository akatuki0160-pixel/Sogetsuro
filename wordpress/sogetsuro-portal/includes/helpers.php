<?php
/**
 * テンプレートで使う関数
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * テンプレートパーツを読み込む（parts/○○.php）
 *
 * @param string $name パーツ名.
 * @param array  $args パーツに渡す値.
 */
function sg_portal_part( $name, $args = array() ) {
	$file = SG_PORTAL_DIR . 'parts/' . sanitize_file_name( $name ) . '.php';
	if ( file_exists( $file ) ) {
		include $file;
	}
}

/**
 * 設定画面のURL（「/reserve/」のようなサイト内の書き方も可）を完全なURLにする
 *
 * @param string $value URL またはサイト内のパス.
 * @return string
 */
function sg_portal_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( preg_match( '#^([a-z][a-z0-9+.-]*:|//|\#)#i', $value ) ) {
		return $value;
	}
	if ( 0 === strpos( $value, '/wp-content/' ) ) {
		return content_url( substr( $value, strlen( '/wp-content' ) ) );
	}
	return home_url( '/' . ltrim( $value, '/' ) );
}

/**
 * トップページ内の位置へのURL（例：#sg-access）
 *
 * @param string $hash ページ内リンクのID.
 * @return string
 */
function sg_portal_front_url( $hash = '' ) {
	return home_url( '/' ) . ( $hash ? '#' . $hash : '' );
}

/**
 * 記事一覧（「設定 → 表示設定」の投稿ページ）のURL
 *
 * @return string
 */
function sg_portal_journal_url() {
	$page_id = (int) get_option( 'page_for_posts' );
	return $page_id ? get_permalink( $page_id ) : home_url( '/journal/' );
}

/**
 * 電話番号のリンク（tel:）
 *
 * @return string
 */
function sg_portal_tel_href() {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', sg_portal_option( 'tel' ) );
}

/**
 * メニューの項目（ヘッダー・メニュー・フッターで共通）
 * gnav: PCのヘッダーに表示 / footer: フッターに表示
 *
 * @return array
 */
function sg_portal_nav_items() {
	$items = array(
		array( 'en' => 'Top', 'ja' => 'トップ', 'url' => home_url( '/' ), 'gnav' => false, 'footer' => false ),
		array( 'en' => 'Concept', 'ja' => 'はじめに', 'url' => sg_portal_front_url( 'sg-concept' ), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Stay', 'ja' => '泊まる', 'url' => sg_portal_front_url( 'sg-stay' ), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Dining', 'ja' => '食べる', 'url' => sg_portal_front_url( 'sg-dining' ), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Experience', 'ja' => '体験', 'url' => sg_portal_front_url( 'sg-experience' ), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Town', 'ja' => '町を歩く', 'url' => sg_portal_front_url( 'sg-town' ), 'gnav' => false, 'footer' => true ),
		array( 'en' => 'Journal', 'ja' => 'よみもの', 'url' => sg_portal_journal_url(), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Access', 'ja' => 'アクセス', 'url' => sg_portal_front_url( 'sg-access' ), 'gnav' => true, 'footer' => true ),
		array( 'en' => 'Reservation', 'ja' => 'ご予約', 'url' => sg_portal_url( sg_portal_option( 'reserve_url' ) ), 'gnav' => false, 'footer' => true ),
		array( 'en' => 'Contact', 'ja' => 'お問い合わせ', 'url' => sg_portal_url( sg_portal_option( 'contact_url' ) ), 'gnav' => false, 'footer' => true ),
	);
	/**
	 * メニューの項目を変更するためのフィルター
	 *
	 * @param array $items メニューの項目.
	 */
	return apply_filters( 'sogetsuro_portal_nav_items', $items );
}

/**
 * 記事の抜粋（日本語でも指定の文字数で切る）
 *
 * @param WP_Post|int|null $post   投稿.
 * @param int              $length 文字数.
 * @return string
 */
function sg_portal_excerpt( $post = null, $length = 90 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	if ( has_excerpt( $post ) ) {
		$text = $post->post_excerpt;
	} else {
		// 抜粋が空のときは、本文の最初の段落（リード文）を使う
		$text = $post->post_content;
		if ( preg_match( '#<p[^>]*>(.*?)</p>#s', $text, $match ) ) {
			$first = trim( wp_strip_all_tags( $match[1] ) );
			if ( '' !== $first ) {
				$text = $first;
			}
		}
	}
	$text = wp_strip_all_tags( strip_shortcodes( $text ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length ) . '…';
	}
	return $text;
}

/**
 * 記事の最初のカテゴリー
 *
 * @param WP_Post|int|null $post 投稿.
 * @return WP_Term|null
 */
function sg_portal_primary_category( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$categories = get_the_category( $post->ID );
	return $categories ? $categories[0] : null;
}

/**
 * 記事カードの画像（アイキャッチがないときは空の枠）
 *
 * @param WP_Post $post 投稿.
 * @return string
 */
function sg_portal_card_image( $post ) {
	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail(
			$post,
			'large',
			array(
				'alt'      => '',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(min-width: 1024px) 400px, (min-width: 768px) 50vw, 100vw',
			)
		);
	}
	return '<span class="sg-noimage" aria-hidden="true"><svg><use href="#sg-emblem"></use></svg></span>';
}

/**
 * 記事カード
 *
 * @param WP_Post|int|null $post    投稿.
 * @param string           $heading 見出しのタグ（h2 / h3）.
 * @param float            $delay   表示を遅らせる秒数.
 */
function sg_portal_post_card( $post = null, $heading = 'h3', $delay = 0 ) {
	sg_portal_part(
		'post-card',
		array(
			'post'    => get_post( $post ),
			'heading' => in_array( $heading, array( 'h2', 'h3', 'h4' ), true ) ? $heading : 'h3',
			'delay'   => (float) $delay,
		)
	);
}

/**
 * パンくずリスト
 *
 * @param array $items [ [ 表示名, URL ], ..., [ 表示名 ] ].
 * @param bool  $top   見出し画像がないページ（ヘッダーの下に置く）.
 */
function sg_portal_breadcrumb( $items, $top = false ) {
	echo '<nav class="sg-breadcrumb' . ( $top ? ' sg-breadcrumb--top' : '' ) . '" aria-label="現在地"><ol class="sg-inner">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last || empty( $item[1] ) ) {
			echo '<li><span aria-current="page">' . esc_html( $item[0] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $item[1] ) . '">' . esc_html( $item[0] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * カテゴリーのタブ（記事一覧の上）
 */
function sg_portal_category_tabs() {
	$categories = get_categories( array( 'hide_empty' => true ) );
	if ( count( $categories ) < 2 ) {
		return;
	}
	$current = is_category() ? get_queried_object_id() : 0;
	echo '<ul class="sg-tabs" aria-label="カテゴリー">';
	echo '<li><a' . ( $current ? '' : ' class="is-current" aria-current="page"' ) . ' href="' . esc_url( sg_portal_journal_url() ) . '">すべて</a></li>';
	foreach ( $categories as $category ) {
		$is_current = ( (int) $category->term_id === (int) $current );
		echo '<li><a' . ( $is_current ? ' class="is-current" aria-current="page"' : '' ) . ' href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * ページ送り
 */
function sg_portal_pagination() {
	global $wp_query;
	if ( empty( $wp_query->max_num_pages ) || $wp_query->max_num_pages < 2 ) {
		return;
	}
	$links = paginate_links(
		array(
			'total'     => $wp_query->max_num_pages,
			'current'   => max( 1, (int) get_query_var( 'paged' ) ),
			'mid_size'  => 1,
			'prev_text' => '前へ',
			'next_text' => '次へ',
		)
	);
	if ( $links ) {
		echo '<nav class="sg-pagination" aria-label="ページ送り"><div class="nav-links">' . $links . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() の出力.
	}
}

/**
 * SNSシェアのURL
 *
 * @return array
 */
function sg_portal_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	return array(
		'x'        => array( 'X', 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'line'     => array( 'LINE', 'https://social-plugins.line.me/lineit/share?url=' . $url ),
	);
}

/**
 * 前後の記事
 */
function sg_portal_post_nav() {
	$prev = get_previous_post();
	$next = get_next_post();
	if ( ! $prev && ! $next ) {
		return;
	}
	echo '<nav class="sg-postnav" aria-label="前後の記事">';
	foreach ( array( 'prev' => array( $prev, 'Prev' ), 'next' => array( $next, 'Next' ) ) as $key => $data ) {
		list( $item, $label ) = $data;
		if ( $item ) {
			printf(
				'<a class="sg-postnav__%1$s" href="%2$s"><span class="sg-postnav__label">%3$s</span><span class="sg-postnav__title">%4$s</span></a>',
				esc_attr( $key ),
				esc_url( get_permalink( $item ) ),
				esc_html( $label ),
				esc_html( get_the_title( $item ) )
			);
		} else {
			echo '<span class="sg-postnav__' . esc_attr( $key ) . '"></span>';
		}
	}
	echo '</nav>';
}

/**
 * あわせて読みたい（同じカテゴリーの新しい記事。3件に足りないときは、ほかの新しい記事で埋める）
 *
 * @param int          $post_id  表示中の記事.
 * @param WP_Term|null $category カテゴリー.
 */
function sg_portal_related_posts( $post_id, $category ) {
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post_id ),
		'fields'         => 'ids',
	);
	$ids = $category ? get_posts( array_merge( $args, array( 'cat' => $category->term_id ) ) ) : array();

	// 同じカテゴリーの記事が3件に足りないときは、新しい記事で埋める
	if ( count( $ids ) < 3 ) {
		$args['posts_per_page'] = 3 - count( $ids );
		$args['post__not_in']   = array_merge( array( $post_id ), $ids );
		$ids                    = array_merge( $ids, get_posts( $args ) );
	}
	if ( ! $ids ) {
		return;
	}
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => count( $ids ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	?>
	<section class="sg-section sg-section--tight sg-section--sub sg-related">
		<div class="sg-inner">
			<div class="sg-section-head">
				<div class="sg-heading sg-heading--s sg-reveal">
					<p class="sg-heading__en">Related</p>
					<h2 class="sg-heading__ja">あわせて読みたい</h2>
				</div>
			</div>
			<div class="sg-post-grid">
				<?php
				$i = 0;
				while ( $query->have_posts() ) {
					$query->the_post();
					sg_portal_post_card( get_post(), 'h3', $i * 0.1 );
					$i++;
				}
				wp_reset_postdata();
				?>
			</div>
			<div class="sg-related__more">
				<a class="sg-btn" href="<?php echo esc_url( sg_portal_journal_url() ); ?>">記事の一覧を見る</a>
			</div>
		</div>
	</section>
	<?php
}
