<?php
/**
 * 予約バナー（記事一覧・記事ページの下）
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sg-reserve sg-reserve--band">
	<figure class="sg-reserve__bg" data-sg-parallax="0.2">
		<img src="<?php echo esc_url( sg_portal_url( sg_portal_option( 'cta_image' ) ) ); ?>" alt="" width="2400" height="1400" loading="lazy" decoding="async">
	</figure>
	<div class="sg-reserve__inner sg-reveal">
		<p class="sg-reserve__en">Stay with us</p>
		<p class="sg-reserve__title">町の暮らしに、泊まってみませんか。</p>
		<div class="sg-reserve__btns">
			<a class="sg-btn sg-btn--fill" href="<?php echo esc_url( sg_portal_url( sg_portal_option( 'reserve_url' ) ) ); ?>">空室検索・ご予約</a>
			<a class="sg-btn sg-btn--light" href="<?php echo esc_url( sg_portal_front_url( 'sg-stay' ) ); ?>">客室を見る</a>
		</div>
	</div>
</section>
