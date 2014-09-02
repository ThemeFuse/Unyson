<?php

/**
 * Template Name: Payment Page
 *
 * @var FW_Extension_Payment $payment
 */

get_header();

$payment = fw()->extensions->get('payment');

?>
<div id="main-content" class="main-content">

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<article id="post-<?php the_ID() ?>" <?php post_class() ?>>

				<header class="entry-header">
					<?php
						// Page thumbnail and title.
						fw_theme_post_thumbnail();
						the_title('<h1 class="entry-title">', '</h1>');
					?>
					<?php
					if (function_exists('fw_ext_breadcrumbs_render') && is_page()) {
						echo fw_ext_breadcrumbs_render();
					}
					?>
				</header>

				<div class="entry-content">
					<?php $payment->render_html() ?>
				</div>

			</article>
		</div>
	</div>

</div>
<?php

get_sidebar();
get_footer();
