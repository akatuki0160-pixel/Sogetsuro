<?php
/**
 * 共通フッター（</main> 〜 フッター・固定ボタン 〜 </html> まで）
 * 静的版の static/index.html の <!-- sg:footer --> と同じ内容です。
 *
 * @package SogetsuroPortal
 */

defined( 'ABSPATH' ) || exit;

$sg_site_name = sg_portal_option( 'site_name' );
$sg_privacy   = sg_portal_url( sg_portal_option( 'privacy_url' ) );
$sg_sns       = array_filter(
	array(
		'Instagram' => sg_portal_option( 'instagram' ),
		'Facebook'  => sg_portal_option( 'facebook' ),
	)
);
?>
	</main>

	<!-- フッター -->
	<footer class="sg-footer">
		<div class="sg-inner sg-footer__inner">
			<div class="sg-footer__brand">
				<svg class="sg-footer__mark" aria-hidden="true"><use href="#sg-emblem"></use></svg>
				<p class="sg-footer__name"><?php echo esc_html( $sg_site_name ); ?></p>
				<p class="sg-footer__sub"><?php echo esc_html( sg_portal_option( 'site_sub' ) ); ?></p>
			</div>
			<div class="sg-footer__info">
				<address class="sg-footer__address"><?php echo esc_html( sg_portal_option( 'address' ) ); ?></address>
				<p class="sg-footer__tel">TEL <a href="<?php echo esc_attr( sg_portal_tel_href() ); ?>"><?php echo esc_html( sg_portal_option( 'tel' ) ); ?></a></p>
				<?php if ( $sg_sns ) : ?>
				<ul class="sg-footer__sns">
					<?php foreach ( $sg_sns as $sg_label => $sg_url ) : ?>
					<li><a href="<?php echo esc_url( $sg_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sg_label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
			<nav class="sg-footer__nav" aria-label="フッターメニュー">
				<ul>
					<?php foreach ( sg_portal_nav_items() as $sg_item ) : ?>
						<?php if ( ! empty( $sg_item['footer'] ) ) : ?>
					<li><a href="<?php echo esc_url( $sg_item['url'] ); ?>"><?php echo esc_html( $sg_item['ja'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<div class="sg-inner sg-footer__bottom">
			<?php if ( $sg_privacy ) : ?>
			<a class="sg-footer__policy" href="<?php echo esc_url( $sg_privacy ); ?>">プライバシーポリシー</a>
			<?php endif; ?>
			<p class="sg-footer__copy"><small>&copy; <span data-sg-year><?php echo esc_html( gmdate( 'Y' ) ); ?></span> <?php echo esc_html( $sg_site_name ); ?></small></p>
		</div>
	</footer>

	<!-- ページトップ / スマホ用固定ボタン -->
	<a class="sg-pagetop" href="#sg-site" aria-label="ページの先頭へ戻る"><span aria-hidden="true"></span></a>
	<div class="sg-fixed-cta">
		<a class="sg-fixed-cta__btn" href="<?php echo esc_attr( sg_portal_tel_href() ); ?>">電話</a>
		<a class="sg-fixed-cta__btn" href="<?php echo esc_url( sg_portal_front_url( 'sg-access' ) ); ?>">アクセス</a>
		<a class="sg-fixed-cta__btn sg-fixed-cta__btn--primary" href="<?php echo esc_url( sg_portal_url( sg_portal_option( 'reserve_url' ) ) ); ?>">ご予約</a>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
