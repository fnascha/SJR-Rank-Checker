<?php
/**     Add Styles      */
function dani_style()
{
    wp_enqueue_style('imokcss', get_stylesheet_directory_uri() . '/scss/custom.css', array());
    wp_enqueue_script('imokjs', get_stylesheet_directory_uri() . '/js/imok.js', array(), '1.2.105', true);
    wp_localize_script('imokjs', 'rank_checker', ['admin_url' => admin_url('admin-ajax.php')]);
//    wp_enqueue_script( 'jspPDF', get_stylesheet_directory_uri() . '/jsPDF-master/src/jspdf.js', array(), '1.5.3', true );

    //JS for jsPDF
    wp_enqueue_script('jspPDF', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), '2.5.1', true);

    //PDF html2 print
    wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js', array(), '1.3.2', true);
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
        wp_send_json_error('Too long ISSN, maximum numbers: 60', 404);
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
                    $issn_without_space = str_replace(' ', '', $issn);

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
                    $data_succes[] = array(
                        "title" => $response_title,
                        "max_rank" => $response_max_rank,
                        "ranking" => $data_min,
                        "post_id" => $post->ID

                    );
                }
            } else {
                $issn_without_space = str_replace(' ', '', $issn);
                $data_failed[] = array(
                    "failed_issn" => $issn_without_space
                );
            }
        }

        if ($data_succes || $data_failed) {
            $data[] = array(
                "succes_data" => $data_succes,
                "failed_issn" => $data_failed
            );
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
    //TODO::I can create it with new Map

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


//Function to create new Journal Shortcode
function popup_form_shortcode_add()
{
    ob_start(); ?>

    <!-- Popup Form -->
    <div id="journal_list_add_form_popup" style="display: none;">
        <button id="close_popup_add">x</button>
        <form id="journal_list_add_form">

            <h1>Add to our database</h1>
            <div class="input_field_holder">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title"><br><br>

                <label for="categories">Categories:</label>
                <label for="categories">Categories:</label>
                <?php
                $categories = get_categories(); // Retrieve all post categories
                if ($categories) {
                    echo '<select id="categories" name="categories[]" multiple >';
                    foreach ($categories as $category) {
                        echo '<option value="' . $category->slug . '">' . $category->name . '</option>';
                    }
                    echo '</select>';
                }
                ?><br><br>

                <label for="rank">Rank:</label>
                <input type="text" id="rank" name="rank"><br><br>

                <label for="type">Type:</label>
                <input type="text" id="type" name="type"> <br><br>

                <label for="sjr">SJR:</label>
                <input type="text" id="sjr" name="sjr"><br><br>

                <label for="sjr_best_quartile">SJR bestquartile:</label>
                <input type="text" id="sjr_best_quartile" name="sjr_best_quartile"><br><br>

                <label for="h_index">H Index:</label>
                <input type="text" id="h_index" name="h_index"><br><br>

                <label for="total_docs_2021">Total Docs 2021:</label>
                <input type="text" id="total_docs_2021" name="total_docs_2021"><br><br>

                <label for="total_docs_3_years">Total Docs 3 Years:</label>
                <input type="text" id="total_docs_3_years" name="total_docs_3_years"><br><br>

                <label for="total_refs">Total Refs:</label>
                <input type="text" id="total_refs" name="total_refs"><br><br>

                <label for="country">Country:</label>
                <input type="text" id="country" name="country"><br><br>

                <label for="region">Region:</label>
                <input type="text" id="region" name="region"><br><br>

                <label for="publisher">Publisher:</label>
                <input type="text" id="publisher" name="publisher"><br><br>

                <label for="coverage">Coverage:</label>
                <input type="text" id="coverage" name="coverage"><br><br>

                <input type="submit" value="Create Post">
            </div>
        </form>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('popup_form_add_journal', 'popup_form_shortcode_add');

function popup_form_shortcode_edit()
{
    ob_start(); ?>
    <div id="journal_list_edit_form_popup" style="display: none;">
        <button id="close_popup_edit">x</button>
        <form id="journal_list_edit_form">
            <h1>Edit our database</h1>
            <div>
                <label for="titleInput">Title of Article:</label>
                <input type="text" id="titleInput" name="titleInput">
            </div>
            <div>
                <label for="editorsInput">Number of Editors:</label>
                <input type="number" id="editorsInput" name="editorsInput">
            </div>
            <div>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('popup_form_edit_journal', 'popup_form_shortcode_edit');


/** the input field values importing to the new post */
add_action('wp_ajax_create_journal_post', 'create_journal_post');
add_action('wp_ajax_nopriv_create_journal_post', 'create_journal_post');
function create_journal_post()
{
    // Retrieve form data from AJAX request
    $title = $_POST['title'];
    $categories = $_POST['categories'];
    $rank = $_POST['rank'];
    $type = $_POST['type'];
    $issn = $_POST['issn'];
    $sjr = $_POST['sjr'];
    $sjr_best_quartile = $_POST['sjr_best_quartile'];
    $h_index = $_POST['h_index'];
    $total_docs_2021 = $_POST['total_docs_2021'];
    $total_docs_3_years = $_POST['total_docs_3_years'];
    $total_refs = $_POST['total_refs'];
    $country = $_POST['country'];
    $region = $_POST['region'];
    $publisher = $_POST['publisher'];
    $coverage = $_POST['coverage'];

    foreach ($categories as $slug) {
        $category = get_category_by_slug($slug);
        if ($category) {
            $category_ids[] = $category->term_id;
        }
    }

    // Create new post
    $post_data = array(
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_category' => $category_ids
    );
    $post_id = wp_insert_post($post_data);

    // Update ACF fields
    update_field('rank', $rank, $post_id);
    update_field('type', $type, $post_id);
    update_field('issn', $issn, $post_id);
    update_field('sjt', $sjr, $post_id);
    update_field('sjr_best_quartile', $sjr_best_quartile, $post_id);
    update_field('h_index', $h_index, $post_id);
    update_field('total_docs_2021', $total_docs_2021, $post_id);
    update_field('total_docs_3_years', $total_docs_3_years, $post_id);
    update_field('total_refs', $total_refs, $post_id);
    update_field('country', $country, $post_id);
    update_field('region', $region, $post_id);
    update_field('publisher', $publisher, $post_id);
    update_field('coverage', $coverage, $post_id);

    // Return the newly created post ID
    echo $post_id;

    // Always remember to exit
    wp_die();
}

// Handle AJAX request to save ACF fields
add_action('wp_ajax_save_journal_fields', 'save_journal_fields');
add_action('wp_ajax_nopriv_save_journal_fields', 'save_journal_fields');
function save_journal_fields()
{
    // Verify nonce or perform any necessary security checks

    // Retrieve the data from the AJAX request
    $post_id = $_POST['post_id'];
    $writers_number = $_POST['writers_number'];
    $article_title = $_POST['article_title'];

    // Update ACF fields for the post
    update_field('number_of_editors', $writers_number, $post_id);
    update_field('title_of_article', $article_title, $post_id);

    // Return a response if needed
    wp_send_json_success('ACF fields updated successfully.');
}

// Handle AJAX request to get current ACF field values
add_action('wp_ajax_get_journal_fields', 'get_journal_fields');
add_action('wp_ajax_nopriv_get_journal_fields', 'get_journal_fields');
function get_journal_fields()
{
    // Verify nonce or perform any necessary security checks
    // Retrieve the data from the AJAX request
    $post_id = $_POST['post_id'];

    // Get the current ACF field values for the post
    $writers_number = get_field('number_of_editors', $post_id);
    $article_title = get_field('title_of_article', $post_id);

    // Return the ACF field values as the response

    wp_send_json_success(array(
        'writers_number' => $writers_number,
        'article_title' => $article_title
    ));
}
