<?php
/*
Template Name: Archives
*/
get_header(); ?>

<div id="primary" class="site-content">
    <div id="content" role="main">

        <div id="header-title">
            <div class="title">
                <h1>Recent Searches</h1>
            </div>
        </div>

        <div class="divider"></div>

        <div id="facets-holder">

        </div>

        <div id="searches-holder-archive">
            <?php echo do_shortcode('[facetwp template="searches_listing"]'); ?>

        </div>

    </div><!-- #content -->
</div><!-- #primary -->
