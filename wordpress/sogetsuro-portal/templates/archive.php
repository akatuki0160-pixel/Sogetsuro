<?php
/**
 * 記事一覧（投稿ページ・カテゴリー・タグ・日付・作成者）
 *
 * ・見出し画像：「投稿ページ」に指定した固定ページのアイキャッチ（なければ設定画面の画像）
 * ・見出しの説明文：「投稿ページ」の抜粋／カテゴリーの説明
 * ・1ページ目の最新記事は大きく表示（Pick up）
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

sg_portal_part( 'header' );

$sg_posts_page = (int) get_option( 'page_for_posts' );
$sg_en         = 'Journal';
$sg_title      = $sg_posts_page ? get_the_title( $sg_posts_page ) : '町のよみもの';
$sg_lead       = '宿のまわりの町のこと、季節のこと。<br>スタッフが歩いて集めた小さな話をお届けします。';
$sg_hero       = sg_portal_url( sg_portal_option( 'journal_hero' ) );

if ( $sg_posts_page ) {
	$sg_excerpt = get_post_field( 'post_excerpt', $sg_posts_page );
	if ( $sg_excerpt ) {
		$sg_lead = esc_html( $sg_excerpt );
	}
	if ( has_post_thumbnail( $sg_posts_page ) ) {
		$sg_hero = get_the_post_thumbnail_url( $sg_posts_page, 'full' );
	}
}

$sg_crumbs = array( array( 'トップ', home_url( '/' ) ) );
if ( is_home() ) {
	$sg_crumbs[] = array( $sg_title );
} else {
	$sg_crumbs[] = array( $sg_title, sg_portal_journal_url() );
	if ( is_category() || is_tag() ) {
		$sg_en    = is_category() ? 'Category' : 'Tag';
		$sg_title = single_term_title( '', false );
		$sg_desc  = wp_strip_all_tags( term_description() );
		$sg_lead  = $sg_desc ? esc_html( $sg_desc ) : '';
	} elseif ( is_author() ) {
		$sg_en    = 'Writer';
		$sg_title = get_the_author_meta( 'display_name', get_queried_object_id() );
		$sg_lead  = '';
	} else {
		$sg_en    = 'Archive';
		$sg_title = wp_strip_all_tags( get_the_archive_title() );
		$sg_lead  = '';
	}
	$sg_crumbs[] = array( $sg_title );
}
?>

<section class="sg-page-hero">
	<figure class="sg-page-hero__bg" data-sg-parallax="0.12">
		<img src="<?php echo esc_url( $sg_hero ); ?>" alt="" width="2400" height="1200" fetchpriority="high" decoding="async">
	</figure>
	<div class="sg-page-hero__inner sg-reveal">
		<p class="sg-page-hero__en"><?php echo esc_html( $sg_en ); ?></p>
		<h1 class="sg-page-hero__title"><?php echo esc_html( $sg_title ); ?></h1>
		<?php if ( $sg_lead ) : ?>
		<p class="sg-page-hero__lead"><?php echo wp_kses( $sg_lead, array( 'br' => array() ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php sg_portal_breadcrumb( $sg_crumbs ); ?>

<section class="sg-section sg-journal-list">
	<div class="sg-inner">
		<?php sg_portal_category_tabs(); ?>

		<?php if ( have_posts() ) : ?>

			<?php
			// 1ページ目の最新記事は大きく表示
			if ( is_home() && ! is_paged() ) :
				the_post();
				$sg_cat = sg_portal_primary_category( get_post() );
				?>
			<article class="sg-feature sg-reveal">
				<a class="sg-feature__link" href="<?php the_permalink(); ?>">
					<figure class="sg-feature__img"><?php echo sg_portal_card_image( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress の画像タグ. ?></figure>
					<div class="sg-feature__body">
						<p class="sg-feature__label">Pick up</p>
						<p class="sg-post-meta">
							<?php if ( $sg_cat ) : ?>
							<span class="sg-post-meta__cat"><?php echo esc_html( $sg_cat->name ); ?></span>
							<?php endif; ?>
							<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
						</p>
						<h2 class="sg-feature__title"><?php echo esc_html( get_the_title() ); ?></h2>
						<p class="sg-feature__excerpt"><?php echo esc_html( sg_portal_excerpt( get_post(), 120 ) ); ?></p>
						<span class="sg-link-arrow">続きを読む</span>
					</div>
				</a>
			</article>
			<?php endif; ?>

			<div class="sg-post-grid">
				<?php
				$sg_i = 0;
				while ( have_posts() ) {
					the_post();
					sg_portal_post_card( get_post(), 'h2', ( $sg_i % 3 ) * 0.1 );
					$sg_i++;
				}
				?>
			</div>

			<?php sg_portal_pagination(); ?>

		<?php else : ?>
			<p class="sg-empty">まだ記事がありません。</p>
		<?php endif; ?>
	</div>
</section>

<?php
sg_portal_part( 'cta' );
sg_portal_part( 'footer' );
