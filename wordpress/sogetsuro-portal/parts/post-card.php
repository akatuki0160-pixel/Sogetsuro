<?php
/**
 * 記事カード（記事一覧・あわせて読みたい・[sogetsuro_journal] で共通）
 *
 * $args['post']    WP_Post
 * $args['heading'] 見出しタグ（h2 / h3 / h4）
 * $args['delay']   表示を遅らせる秒数
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

$sg_post = $args['post'];
if ( ! $sg_post ) {
	return;
}
$sg_cat   = sg_portal_primary_category( $sg_post );
$sg_tag   = $args['heading'];
$sg_delay = $args['delay'] ? ' style="--d:' . esc_attr( number_format( $args['delay'], 1 ) ) . 's"' : '';
?>
<article class="sg-post-card sg-reveal"<?php echo $sg_delay; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 上で esc_attr 済み. ?>>
	<a class="sg-post-card__link" href="<?php echo esc_url( get_permalink( $sg_post ) ); ?>">
		<figure class="sg-post-card__img"><?php echo sg_portal_card_image( $sg_post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress の画像タグ. ?></figure>
		<p class="sg-post-meta">
			<?php if ( $sg_cat ) : ?>
			<span class="sg-post-meta__cat"><?php echo esc_html( $sg_cat->name ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $sg_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d', $sg_post ) ); ?></time>
		</p>
		<<?php echo esc_html( $sg_tag ); ?> class="sg-post-card__title"><?php echo esc_html( get_the_title( $sg_post ) ); ?></<?php echo esc_html( $sg_tag ); ?>>
		<p class="sg-post-card__excerpt"><?php echo esc_html( sg_portal_excerpt( $sg_post ) ); ?></p>
	</a>
</article>
