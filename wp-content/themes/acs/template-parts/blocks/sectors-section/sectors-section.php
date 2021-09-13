<?php

/**
 * Sectors Section Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'sectors-section-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'sectors-section container py-5';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

?>
<div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
    <div class="row">
        <div class="col">
            <h2 class="text-uppercase fs-12 lh-18 fs-lg-16 text-center text-secondary">Sectors we help</h2>
        </div>
    </div>
    <style type="text/css">
        #<?php echo $id; ?> {
            /* background-image: url(<?php the_field('bg'); ?>); */
        }
    </style>
</div>