<?php
/**     Add Styles      */
function dani_style()
{
    wp_enqueue_style('imokcss', get_stylesheet_directory_uri() . '/scss/custom.css', array());
    wp_enqueue_script('imokjs', get_stylesheet_directory_uri() . '/js/imok.js', array(), '1.0.0', true);
    wp_localize_script('imokjs', 'rank_checker', ['admin_url' => admin_url('admin-ajax.php')]);
}

add_action('wp_enqueue_scripts', 'dani_style', 99);

//Page Slug Body Class
function add_slug_body_class($classes)
{
    global $post;

    if (isset($post)) {
        $classes[] = $post->post_type . '-' . $post->post_name;
    }
    return $classes;
}

add_filter('body_class', 'add_slug_body_class');

//prevent clickjacking
function block_frames()
{
    header('X-FRAME-OPTIONS: SAMEORIGIN');
}

add_action('send_headers', 'block_frames', 10);

//disable wordpress version signature
function tn_disable_wp_version()
{
    return '';
}

add_filter('the_generator', 'tn_disable_wp_version');


// function creates the input field shortcode

// register shortcode
add_shortcode('home_input', 'home_input_shortcode');

// function creates the input field shortcode for HOME NAME CHECKER
function home_input_shortcode()
{
    $atts = '<div class="form-container">';
    $atts .= '<form method="post" action="">';
    $atts .= '<input id="input-field-data-holder" type="text">';
    $atts .= '</form>';
    $atts .= '<button type="button"  name="submit" class="search-on-pressed" >Check</button>';
    $atts .= '</div>';
    $atts .= '<div id="response_holder">';
    $atts .= '</div>';
    return $atts;
}

add_shortcode('issn_input', 'issn_input_shortcode');

// function creates the input field shortcode for ISSN CHECKER
function issn_input_shortcode()
{
    $atts = '<div class="form-container-issn">';
    $atts .= '<form method="post" action="">';
    $atts .= '<label for="input-field-author">Author Name: </label>';
    $atts .= '<input id="input-field-author" type="text">';
    $atts .= '<label for="input-field-data-holder-issn">You can check at least one, up to 6 Journals, separate with -> ; For example: 12345678; 98765432; 12344321 </label>';
    $atts .= '<input id="input-field-data-holder-issn" pattern="^[0-9,]*$" type="text">';
    $atts .= '</form>';
    $atts .= '<button type="button"  name="submit" class="search-on-pressed-issn" >Check</button>';
    $atts .= '</div>';
    $atts .= '<div id="response_holder_issn">';
    $atts .= '</div>';
    return $atts;
}

//Hook For Ajax Search Connected to php below
add_action('wp_ajax_nopriv_get_journal_data_fun', 'check_the_journal');
add_action('wp_ajax_get_journal_data_fun', 'check_the_journal');

//Title Search
function check_the_journal()
{
    //Data From Input field
    $searched_journal = $_POST['title_input'];


    if (strlen($searched_journal) <= 3) {
        wp_send_json_error('Too short title, minimum characters: 4', 404);
    } else {

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            's' => $searched_journal
        );

        $journals = new WP_Query($args);
        if ($journals->have_posts()) {
            wp_send_json($journals->get_posts());
        } else {
            wp_send_json_error('No Search Reults Found', 404);
        }
    }
}

//Hook For Ajax Search by ISSN Connected to php below
add_action('wp_ajax_nopriv_get_issn_data_fun', 'check_multiple_issn');
add_action('wp_ajax_get_issn_data_fun', 'check_multiple_issn');

function check_multiple_issn()
{
    $searched_issns = $_POST['issn_input'];

    if (strlen($searched_issns) < 4) {
        wp_send_json_error('Too short ISSN, minimum numbers: 4', 404);
    } elseif (strlen($searched_issns) > 60) {
        wp_send_json_error('Too long ISSN, maximum numbers: 60, maximum ISSNs 4', 404);
    } else {
        $issn_holder = explode(";", $searched_issns);
        foreach ($issn_holder as $issn) {
            $issn_without_space = str_replace(' ', '', $issn);
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,

                'meta_query' => array(
                    array(
                        'key' => 'issn',
                        'value' => $issn_without_space,
                        'compare' => 'LIKE'
                    )
                )

            );
            $journal_found = new WP_Query($args);

            if ($journal_found->have_posts()) {
                foreach ($journal_found->get_posts() as $post) {
                    $categoryes = get_the_category($post->ID);
                    $response_title = get_the_title($post->ID);
                    $data_min = [];

                    $flag = 101;
                    foreach ($categoryes as $category) {
                        $response_cat = $category->name;
                        $response_cat_rank = get_rank(get_the_order_by_sjr($category->slug, $post->ID) * 100);
                        $data_min[] = array(
                            "response_cat" => $response_cat,
                            "response_cat_rank" => $response_cat_rank
                        );

                        $valuenumber = get_the_order_by_sjr($category->slug, $post->ID) * 100;

                        if ($valuenumber < $flag) {
                            $flag = $valuenumber;
                        }
                    }
                    $response_max_rank = get_rank($flag);
                    $data[] = array(
                        "title" => $response_title,
                        "max_rank" => $response_max_rank,
                        "ranking" => $data_min
                    );
                }
            }

        }

        if ($data) {
            echo json_encode($data);
            die;
        } else {
            wp_send_json_error('No Search Reults Found', 404);
        }

    }
}

//Single Page Sort nad display
function get_the_order_by_sjr($cat_input, $post_ID_input)
{

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'category_name' => $cat_input,
        'meta_query' => [
            'orderby_query' => array(
                'key' => 'sjr',
            )
        ],
        'orderby' => array(
            'orderby_query' => 'DESC',
        ),
    );

    $query = new WP_Query($args);
    $posts_found = $query->found_posts;

    if ($query->have_posts()) {
        $counter = 1;
        foreach ($query->get_posts() as $post) {
            //echo '<pre>' . var_dump($post) . '</pre>';

            if ($post->ID === $post_ID_input) {
                //echo $posts_found;
                return $counter / $posts_found;
            } else {
                $counter++;
            }
            //echo '<strong>' . $query->found_posts . '</strong>';
        }

    } else {
        return 'We didnt get any of other in this Category';
    }

}

function get_rank($value)
{
    if ($value >= 0 && $value <= 10) {
        return 'D1';
    }
    if ($value >= 10 && $value <= 25) {
        return 'Q1';
    }
    if ($value >= 25 && $value <= 50) {
        return 'Q2';
    }
    if ($value >= 50 && $value <= 75) {
        return 'Q3';
    }
    if ($value >= 75 && $value <= 100) {
        return 'Q4';
    }
}
