<?php
if (have_posts()) :

    while (have_posts()) : the_post();

        global $post;
        $post_id = $post->ID;
        $sjr = get_field('sjr', $post_id);
        $sjr_best_quartile = get_field('sjr_best_quartile', $post_id);
        $publisher = get_field('publisher', $post_id);
        $hIndex = get_field('h_index', $post_id);
        $country = get_field('country', $post_id);
        $coverage = get_field('coverage', $post_id);
        $issn = get_field('issn', $post_id);
        $categoryes = get_the_category($post_id);

        $flag = 101;
        foreach ($categoryes as $category) {
            $valuenumber = get_the_order_by_sjr($category->slug, $post->ID) * 100;

            if ($valuenumber < $flag) {
                $flag = $valuenumber;
            }
        }

        $top_rank = get_rank($flag);
        $color = '';
        if ($flag > 0 && $flag <= 25) {
            $color = '#28CC2D';
        }
        if ($flag > 25 && $flag <= 50) {
            $color = '#3581D8';
        }
        if ($flag > 50 && $flag <= 75) {
            $color = '#FFE135';
        }
        if ($flag > 75 && $flag <= 100) {
            $color = '#FFE135';
        }


        ?>


        <article class="journal_listing_article">

            <div class="full-content-1" style="background-color: <?php echo $color; ?>">
                <div class="journal_title">
                    <a href="<?php echo esc_url(get_post_permalink()); ?>">
                        <h3><?php echo get_the_title(); ?></h3>
                    </a>
                </div>
            </div>

            <div class="full-content-2">
                <div class="sjr-holder">
                    <h5>SJR:</h5>
                    <h6><?php echo $sjr ?> </h6>
                </div>

                <div class="hindex-holder">
                    <h5>H Index:</h5>
                    <h6><?php echo $hIndex ?> </h6>
                </div>
                <div class="toprank-holder">
                    <h5>Top-rank:</h5>
                    <h6><?php echo $top_rank ?> </h6>
                </div>

            </div>
        </article>

    <?php

    endwhile;


    ?>


<?php

else :

    ?>

    <p></p>

    <p class="nothingfound" style="text-align: center">Nothing found</p>

    <p></p>

<?php

endif;