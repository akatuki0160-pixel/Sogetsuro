<?php
/**
 * 固定ページ用「SOGETSURO 共通レイアウト」
 *
 * ・wordpress/pages/ の HTML（カスタムHTMLブロック）を貼ったページ
 *   → 共通ヘッダー・フッターの間に、そのまま表示します。
 * ・文章だけのページ（プライバシーポリシーなど、ふつうのブロックで書いたページ）
 *   → 記事ページと同じ読みやすいレイアウトで表示します。見出しが2つ以上あれば目次も自動で作られます。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

sg_portal_part( 'header' );

while ( have_posts() ) :
	the_post();

	// 見出し画像（sg-hero / sg-page-hero）から始まる SOGETSURO の HTML は、そのまま表示
	if ( preg_match( '/class="sg-(?:page-)?hero\b/', get_post_field( 'post_content', get_the_ID(), 'raw' ) ) ) {
		the_content();
		continue;
	}

	sg_portal_breadcrumb(
		array(
			array( 'トップ', home_url( '/' ) ),
			array( get_the_title() ),
		),
		true
	);
	?>

	<article class="sg-article sg-article--page" id="post-<?php the_ID(); ?>">
		<header class="sg-inner sg-article__head">
			<h1 class="sg-article__title"><?php the_title(); ?></h1>
		</header>

		<div class="sg-article__container">
			<nav class="sg-toc" data-sg-toc aria-label="目次" hidden>
				<p class="sg-toc__title"><span>Contents</span>目次</p>
				<ol class="sg-toc__list"></ol>
			</nav>

			<div class="sg-article__body">
				<?php the_content(); ?>
			</div>
		</div>
	</article>

	<?php
endwhile;

sg_portal_part( 'footer' );
