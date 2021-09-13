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
		<article id="taxonomy-sector" class="page type-page status-publish hentry">
			<div class="entry-content">
				<div class="hero-section text-white" style="background-image:url(<?php the_field('bg'); ?>);">
					<div class="container">
						<div class="row">
							<div class="col pl-4">
								<a href="/" class="d-flex align-items-center"><i class="bi bi-arrow-left-short text-primary fs-24"></i><span class="text-white fs-12 lh-25">Back</span></a>
								<p class="breadcrumbs fs-15 lh-25">Who we help / Education</p>
								<h1 class="fs-40 lh-30 fs-lg-45 lg-lh-55 font-weight-bold">
									<?php single_term_title(); ?>
								</h1>
							</div>
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
