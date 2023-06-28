<?php
if (have_posts()) :
    while (have_posts()): the_post();

        echo '<div class="single-search">';
        echo '<div class="title-holder">';
        echo '<h2>' . get_the_title() . '</h2>';
        echo '</div>';

        if (have_rows('searched_journals')):

            echo '<ul class="searches-holder">';
            // Loop through the rows of 'searched_journals'
            while (have_rows('searched_journals')): the_row();
                $max_rank = get_sub_field('max_rank');
                $journal_post_id = get_sub_field('the_journal');
                $date_added = get_sub_field('date_of_addition');
                $journal_post_title = get_the_title($journal_post_id);

                echo '<li> <span> <a href="' . get_permalink($journal_post_id) . '">' . $journal_post_title . '</a></span> <span>' . ' Max Rank: ' . $max_rank . '</span><span>' . '  Date Added: ' . $date_added . '</span> </li>';
            endwhile;
            echo '</ul>';
            echo '</div>';

        endif;
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