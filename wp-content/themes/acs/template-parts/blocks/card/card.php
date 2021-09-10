<?php

/**
 * Card Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'card-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'card bg-dark text-white border-0 mt-5';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

$link = get_field('link');
if( $link ): 
    $link_url = $link['url'];
    $link_title = $link['title'];
    $link_target = $link['target'] ? $link['target'] : '_self';
    ?>

    <div id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
        <img src="<?php the_field('image'); ?>" class="card-img rounded-lg" alt="<?php echo esc_html( $link_title ); ?>">
        <div class="card-img-overlay d-flex align-items-center justify-content-center">
            <p class="card-title fs-18 lh-18 fs-lg-24"><?php echo esc_html( $link_title ); ?></p>
            <a class="stretched-link" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"></a>
        </div>
        <style type="text/css">
            #<?php echo $id; ?> {
                /* Insert block styles here */
            }
        </style>
    </div>
<?php endif; ?>