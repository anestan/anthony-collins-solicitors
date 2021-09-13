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
							<p class="breadcrumbs fs-15 lh-25">Who we help / <?php single_term_title(); ?></p>
							<h1 class="fs-40 lh-30 fs-lg-45 lg-lh-55 font-weight-bold">
								<?php single_term_title(); ?>
							</h1>
						</div>
					</div>
				</div>
			</div>
			<div class="container border-top-yellow">
				<div class="row">
					<div class="col-12 col-md-2 py-6">
						<div class="tab-titles pl-4">
							<div class="list-group" id="list-tab" role="tablist">
								<a class="list-group-item list-group-item-action rounded-0 border-0 pl-5 fs-12 lh-17 active" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">Home</a>
								<a class="list-group-item list-group-item-action rounded-0 border-0 pl-5 fs-12 lh-17" id="list-profile-list" data-toggle="list" href="#list-profile" role="tab" aria-controls="profile">Profile</a>
								<a class="list-group-item list-group-item-action rounded-0 border-0 pl-5 fs-12 lh-17" id="list-messages-list" data-toggle="list" href="#list-messages" role="tab" aria-controls="messages">Messages</a>
								<a class="list-group-item list-group-item-action rounded-0 border-0 pl-5 fs-12 lh-17" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Settings</a>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-8 py-6 px-md-5 border-left border-right">
						<div class="tab-content" id="nav-tabContent">
							<div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
								<p class="fs-20 lh-32 font-weight-bold">We are a nationally recognised education law firm with legal experts who are trusted by schools, academies, education providers and dioceses across the country.</p>
								<p class="fs-17 lh-30">We believe education is of fundamental importance for human flourishing and the common good. Our clients tell us that the service they receive from us is unique. We believe this comes directly from our commitment to the purpose of our firm: 'to improve lives, communities and society'.</p>
								<p class="fs-17 lh-30">We believe education is of fundamental importance for human flourishing and the common good. Our clients tell us that the service they receive from us is unique. We believe this comes directly from our commitment to the purpose of our firm: 'to improve lives, communities and society'.</p>
							</div>
							<div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">2</div>
							<div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">3</div>
							<div class="tab-pane fade" id="list-settings" role="tabpanel" aria-labelledby="list-settings-list">4</div>
						</div>
					</div>
					<div class="col-12 col-md-2 py-6">
						<div class="row">
							<div class="col-12 pl-4">
								<button type="button" class="btn btn-primary btn-block rounded-0 fs-16 lh-18 py-3 px-4 ">Contact Us</button>
							</div>
							<div class="col-12 border-top border-bottom py-5 my-4 pl-4">
								<p class="font-weight-bold fs-12 lh-18 ls-16 text-secondary text-uppercase">Sector Lead</p>
								<img src="/wp-content/themes/acs/public/images/chris-wittington.jpg" alt="">
							</div>
							<div class="col-12"></div>
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
