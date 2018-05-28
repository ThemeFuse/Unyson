<?php

list( $display_version ) = explode( '-', get_bloginfo( 'version' ) );
$url_install_plugin = is_multisite() ? network_admin_url( 'plugin-install.php?s=brizy&tab=search&type=term' ) : admin_url( 'plugin-install.php?s=brizy&tab=search&type=term' );
?>
	<style>
		.about-wrap .feature-section .fw-brz {
			text-align: center;
			margin-top: 30px;
		}
		.fw-brz__btn-install {
			color: #fff;
			font-size: 15px;
			line-height: 1;
			background-color: #d62c64;
			box-shadow: 0px 2px 0px 0px #981e46;
			padding: 11px 27px 12px;
			border: 1px solid #d62c64;
			border-bottom: 0;
			border-radius: 3px;
			text-shadow: none;
			height: auto;
			text-decoration: none;
			transition: all 200ms linear;
		}
		.fw-brz__btn-install:hover {
			background-color: #141923;
			color: #fff;
			border-color: #141923;
			box-shadow: 0px 2px 0px 0px #141923;
		}
		.fw-btn-install-border {
			color: #d62c64;
			font-size: 18px;
			font-weight: bold;
			border: 3px solid #d62c64;
			text-decoration: none;
			padding: 11px 27px 12px;
			margin-top: 45px;
			display: inline-block;
			transition: all 200ms linear;
		}
		.fw-btn-install-border:hover {
			background-color: #141923;
			color: #fff;
			border-color: #141923;
		}
		.section-item .fw-brz-title-feature {
			font-size: 18px;
		}
		.section-item .inline-svg img {
			width: 300px;
			height: 200px;
		}
	</style>
	<div class="wrap about-wrap full-width-layout" style="margin:0 auto;">
		<div class="feature-section one-col">
			<div class="col" style="margin-top: 0;">
				<h2>Try Brizy: <b>An effortless way</b> to create WordPress pages visually! &#x1F389</h2>
				<p style="text-align: center;font-size: 16px;"><?php _e( 'No designer or coding skills required.' ); ?></p>
				<p class="fw-brz">
					<a class="fw-brz__btn-install" href="<?php echo $url_install_plugin; ?>">
						<?php _e( 'Install Now | for FREE' ); ?>
					</a>
				</p>
			</div>
		</div>

		<div class="inline-svg full-width">
			<iframe width="1200" height="600" src="https://www.youtube.com/embed/KUv-NqDR-8s?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
		</div>

		<div class="floating-header-section" style="margin-bottom: 60px;">
			<div class="section-header">
				<h2><?php _e( 'Create & Edit Everything Visually' ); ?></h2>
			</div>

			<div class="section-content">
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-1.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Choose from a lot of elements to create your own design' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-2.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Start from over 150 pre-made blocks to create your page' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-3.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'The interface shows only what’s needed for the task at hand' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-4.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Know where your elements will drop when you drag them' ); ?></h3>
				</div>
				<div class="section-item">
					<a class="fw-btn-install-border" href="<?php echo $url_install_plugin; ?>">
						<?php _e( 'Install Now & Start Creating' ); ?>
					</a>
				</div>
			</div>
		</div>


		<div class="floating-header-section" style="margin-bottom: 50px;">
			<div class="section-header">
				<h2>
					<?php _e( 'Fast & Easy' ); ?><br>
					<?php _e( 'Using Only' ); ?><br>
					<?php _e( 'Drag & Drop' ); ?>
				</h2>
			</div>

			<div class="section-content">
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-5.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Brizy has the smartest text editor you’ll ever work with' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-6.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Try different fonts & colors across your pages in seconds' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-7.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Changes in mobile view will be applied only on mobile devices' ); ?></h3>
				</div>
				<div class="section-item">
					<div class="inline-svg">
						<img src="https://ps.w.org/brizy/assets/screenshot-8.gif?rev=1863674" alt="">
					</div>
					<h3 class="fw-brz-title-feature"><?php _e( 'Over 4000 icons separated in 27 categories are included' ); ?></h3>
				</div>
				<div class="section-item">
					<a class="fw-btn-install-border" href="<?php echo $url_install_plugin; ?>">
						<?php _e( 'Install Now & Start Creating' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="feature-section">
			<h2>
				<?php echo esc_html_e( 'Thanks for giving Brizy a Try!' ); ?>
			</h2>
			<p style="font-size: 14px; text-align:center;">
				Brizy is developed by the team behind the <a href="https://wordpress.org/plugins/unyson/" target="_blank">Unyson open source framework</a> for WordPress. The content created with the back end visual page builder from Unyson can’t be edited with Brizy. You can however use Brizy to create new pages. Consider joining our <a href="https://www.facebook.com/brizy.io/" target="_blank">Facebook community</a> where our members help us shape the development of Brizy.
			</p>
		</div>

		<hr />
	</div>
<?php
return;
