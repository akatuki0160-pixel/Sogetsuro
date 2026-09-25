<?php
/**
 * ショートコード
 *
 *   [sogetsuro_news count="3" category="" exclude=""]     新しい記事の一覧（日付・カテゴリー・タイトル）
 *   [sogetsuro_journal count="3" category="" exclude=""]  新しい記事のカード（写真つき）
 *   [sogetsuro_beds24 roomid=""]                          Beds24 の予約画面
 *
 *   category … このカテゴリー（スラッグ）の記事だけを表示（子カテゴリーも含む）
 *   exclude  … このカテゴリー（スラッグ）の記事を除いて表示。「news,media」のようにカンマ区切りで複数指定できます
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

/**
 * 記事を取得する条件
 *
 * @param array $atts ショートコードの属性.
 * @return array
 */
function sg_portal_query_args( $atts ) {
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, min( 24, (int) $atts['count'] ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( '' !== $atts['category'] ) {
		$args['category_name'] = sanitize_title( $atts['category'] );
	}
	if ( '' !== $atts['exclude'] ) {
		$exclude = array();
		foreach ( explode( ',', $atts['exclude'] ) as $slug ) {
			$term = get_category_by_slug( sanitize_title( $slug ) );
			if ( ! $term ) {
				continue;
			}
			$exclude[] = (int) $term->term_id;
			$children  = get_term_children( $term->term_id, 'category' );
			if ( ! is_wp_error( $children ) ) {
				$exclude = array_merge( $exclude, array_map( 'intval', $children ) );
			}
		}
		if ( $exclude ) {
			$args['category__not_in'] = $exclude;
		}
	}
	return $args;
}

add_shortcode(
	'sogetsuro_news',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'count' => 3, 'category' => '', 'exclude' => '' ), $atts, 'sogetsuro_news' );
		$query = new WP_Query( sg_portal_query_args( $atts ) );
		ob_start();
		echo '<ul class="sg-news__list sg-reveal" style="--d:.15s">';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$category = sg_portal_primary_category( get_post() );
				printf(
					'<li class="sg-news__item"><a href="%1$s"><time class="sg-news__date" datetime="%2$s">%3$s</time>%4$s<span class="sg-news__title">%5$s</span></a></li>',
					esc_url( get_permalink() ),
					esc_attr( get_the_date( 'Y-m-d' ) ),
					esc_html( get_the_date( 'Y.m.d' ) ),
					$category ? '<span class="sg-news__cat">' . esc_html( $category->name ) . '</span>' : '<span class="sg-news__cat">お知らせ</span>',
					esc_html( get_the_title() )
				);
			}
			wp_reset_postdata();
		} else {
			echo '<li class="sg-news__item"><p class="sg-empty">お知らせはまだありません。</p></li>';
		}
		echo '</ul>';
		return ob_get_clean();
	}
);

add_shortcode(
	'sogetsuro_journal',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'count' => 3, 'category' => '', 'exclude' => '' ), $atts, 'sogetsuro_journal' );
		$query = new WP_Query( sg_portal_query_args( $atts ) );
		if ( ! $query->have_posts() ) {
			return '<p class="sg-empty">記事はまだありません。</p>';
		}
		ob_start();
		echo '<div class="sg-post-grid">';
		$i = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			sg_portal_post_card( get_post(), 'h3', ( $i % 3 ) * 0.1 );
			$i++;
		}
		wp_reset_postdata();
		echo '</div>';
		return ob_get_clean();
	}
);

add_shortcode(
	'sogetsuro_beds24',
	function ( $atts ) {
		$atts   = shortcode_atts(
			array(
				'propid' => sg_portal_option( 'beds24_propid' ),
				'roomid' => '',
				'lang'   => sg_portal_option( 'beds24_lang' ),
			),
			$atts,
			'sogetsuro_beds24'
		);
		$propid = preg_replace( '/[^0-9]/', '', (string) $atts['propid'] );
		if ( '' === $propid ) {
			$message = 'オンライン予約は準備中です。ご予約はお電話でお問い合わせください。';
			if ( current_user_can( 'manage_options' ) ) {
				$message .= '（管理者の方へ：「設定 → SOGETSURO」で Beds24 の物件IDを入力すると、ここに予約画面が表示されます）';
			}
			return '<div class="sg-booking"><p class="sg-booking__loading">' . esc_html( $message ) . '</p></div>';
		}

		$params = array( 'propid' => $propid );
		$roomid = preg_replace( '/[^0-9]/', '', (string) $atts['roomid'] );
		if ( '' !== $roomid ) {
			$params['roomid'] = $roomid;
		}
		$lang = sanitize_key( $atts['lang'] );
		if ( '' !== $lang ) {
			$params['lang'] = $lang;
		}
		$base  = 'https://beds24.com/booking2.php';
		$frame = add_query_arg( array_merge( $params, array( 'referer' => 'iFrame' ) ), $base );
		$open  = add_query_arg( $params, $base );

		return '<div class="sg-booking">'
			. '<p class="sg-booking__loading">予約画面を読み込んでいます…</p>'
			. '<iframe class="sg-booking__frame" src="' . esc_url( $frame ) . '" title="オンライン予約（Beds24）" loading="lazy" data-sg-beds24></iframe>'
			. '</div>'
			. '<p class="sg-booking__fallback">予約画面が表示されない場合は、<a href="' . esc_url( $open ) . '" target="_blank" rel="noopener">こちら（別ウィンドウ）</a>からご予約ください。</p>';
	}
);
