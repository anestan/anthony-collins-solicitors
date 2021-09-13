<?php
/**
 * The template for displaying the taxonomy Sector.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package acs
 */

get_header();
?>
	<main id="primary" class="site-main">
		<article id="taxonomy-sector">
			<div class="entry-content">
				<div class="hero-section" style="background-image: url(<?php the_field('bg'); ?>);">
					<div class="container pb-5 pt-6 pt-xl-9 pb-xl-8">
						<div class="row">
							<div class="col-11 mx-auto col-lg-12 text-white heading-area">
								<p class="col-md-7 col-xl-7 fs-15 lh-25"><?php the_field('subheading'); ?></p>
								<h1 class="col-md-10 col-xl-8 fs-lg-60 lh-lg-70"><?php the_field('heading'); ?></h1>
							</div>
						</div>
					</div>
				</div>
				<div class="container">
					<div class="row">
						<div class="col">
							<p>Column 1</p>
						</div>
						<div class="col">
							<?php the_field('test'); ?>
						</div>
						<div class="col">
							<p>Column 3</p>
						</div>
					</div>
				</div>
			</div>
		</article>
	</main>
	<!-- #main -->

<?php
// get_sidebar();
get_footer();
