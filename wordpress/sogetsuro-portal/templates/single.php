<?php
/**
 * 記事ページ（投稿）
 * レイアウトは固定。投稿画面で入力した「タイトル・カテゴリー・アイキャッチ・本文」が入ります。
 * 目次は本文の大見出し（H2）・小見出し（H3）から自動で作られます。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

sg_portal_part( 'header' );

while ( have_posts() ) :
	the_post();
	$sg_cat    = sg_portal_primary_category( get_post() );
	$sg_crumbs = array(
		array( 'トップ', home_url( '/' ) ),
		array( '町のよみもの', sg_portal_journal_url() ),
	);
	if ( $sg_cat ) {
		$sg_crumbs[] = array( $sg_cat->name, get_category_link( $sg_cat ) );
	}
	$sg_crumbs[] = array( get_the_title() );
	sg_portal_breadcrumb( $sg_crumbs, true );
	?>

	<article class="sg-article<?php echo has_post_thumbnail() ? '' : ' sg-article--noimage'; ?>" id="post-<?php the_ID(); ?>">
		<header class="sg-inner sg-article__head">
			<p class="sg-post-meta">
				<?php if ( $sg_cat ) : ?>
				<a class="sg-post-meta__cat" href="<?php echo esc_url( get_category_link( $sg_cat ) ); ?>"><?php echo esc_html( $sg_cat->name ); ?></a>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
			</p>
			<h1 class="sg-article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
		<figure class="sg-article__eyecatch">
			<?php
			the_post_thumbnail(
				'full',
				array(
					'fetchpriority' => 'high',
					'loading'       => false,
					'decoding'      => 'async',
					'sizes'         => '(min-width: 1312px) 1200px, 100vw',
				)
			);
			?>
		</figure>
		<?php endif; ?>

		<div class="sg-article__container">
			<nav class="sg-toc" data-sg-toc aria-label="目次" hidden>
				<p class="sg-toc__title"><span>Contents</span>目次</p>
				<ol class="sg-toc__list"></ol>
			</nav>

			<div class="sg-article__body">
				<?php the_content(); ?>
			</div>

			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="sg-pagination" aria-label="記事のページ"><div class="nav-links">',
					'after'  => '</div></nav>',
				)
			);
			?>

			<div class="sg-share">
				<p class="sg-share__label">Share</p>
				<ul class="sg-share__list">
					<?php foreach ( sg_portal_share_links() as $sg_key => $sg_share ) : ?>
					<li><a href="<?php echo esc_url( $sg_share[1] ); ?>" data-sg-share="<?php echo esc_attr( $sg_key ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sg_share[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php sg_portal_post_nav(); ?>
		</div>
	</article>

	<?php
	sg_portal_related_posts( get_the_ID(), $sg_cat );
endwhile;

sg_portal_part( 'cta' );
sg_portal_part( 'footer' );
