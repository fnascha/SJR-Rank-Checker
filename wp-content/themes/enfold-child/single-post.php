<?php

if (have_posts()) :
    while (have_posts()) : the_post();

        /**
         * Getting the post fields
         */
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

        get_header();
        ?>

        <div id="main_container" class="main_container">
            <div class="journal_main_info">
                <div class="journal-title">
                    <h1><?php echo the_title(); ?></h1>
                </div>
                <div class="journal-publisher">
                    <h2>Publisher: <?php echo $publisher; ?></h2>
                </div>
            </div>
            <div class="data_container">

                <div class="first_row">
                    <div class="h_index">
                        <h5>H-INDEX:</h5>
                        <h3><?php echo $hIndex; ?></h3>
                    </div>

                    <div class="subject-category">
                        <h5>SUBJECT AREA AND CATEGORY:</h5>
                        <ul><?php foreach ($categoryes as $category) {
                                //var_dump($category);
                                $valueinnumber = get_the_order_by_sjr($category->slug, $post_id) * 100;
                                $rank_name = get_rank($valueinnumber);
                                ?>
                                <li> <?php echo 'Your rank ' . '<strong>' . $rank_name . '</strong>' . ' in ' . '<strong>' . $category->name . '</strong>' . ' category.'; ?></li>
                                <?php
                            }
                            ?></ul>
                    </div>

                    <div class="country">
                        <div class="country-real">
                            <h5>Country:</h5>
                            <h3><?php echo $country; ?></h3>
                        </div>
                        <div class="country-fake">
                            <h5>Personal Best:</h5>
                            <h3><?php $flag = 101;
                                foreach ($categoryes as $category) {
                                    $valuenumber = get_the_order_by_sjr($category->slug, $post_id) * 100;

                                    if ($valuenumber <= $flag) {
                                        $flag = $valuenumber;
                                    }
                                    ?>
                                    <?php
                                }
                                echo get_rank($flag);
                                ?></h3>
                        </div>


                    </div>
                </div>

            </div>
        </div>


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


