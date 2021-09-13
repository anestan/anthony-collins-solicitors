<?php

/**
 * Hero Section Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'hero-section-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'hero-section pt-5';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

?>
<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <div class="container">
        <div class="row">
            <div class="col-11 mx-auto col-lg-12 text-white heading-area">
                <p class="col-md-7 col-xl-7 fs-15 lh-25"><?php the_field('subheading'); ?></p>
                <h1 class="col-md-10 col-xl-8 fs-lg-60 lh-lg-70"><?php the_field('heading'); ?></h1>
            </div>
            <div class="col-12">
                <?php if ( is_front_page() ): ?>
                    <div class="input-group mb-3 col-md-8 col-lg-6 col-xl-5 mt-5">
                        <input type="text" class="form-control px-3 py-4" placeholder="Search Sectors, Resources, News & more…" aria-label="Search Sectors, Resources, News & more…" aria-describedby="button-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="button-addon2">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <style type="text/css">
        #<?php echo $id; ?> {
            background-image: url(<?php the_field('bg'); ?>);
        }
    </style>
</div>