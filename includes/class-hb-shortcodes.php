<?php

/**
 * Class HB_Shortcodes
 */
class HB_Shortcodes{

    /**
     * Initial
     */
    static function init(){
        add_shortcode( 'hotel_booking', array( __CLASS__, 'hotel_booking' ) );
        add_shortcode( 'hotel_booking_slider', array( __CLASS__, 'hotel_booking_slider' ) );
        add_shortcode( 'hotel_booking_best_reviews', array( __CLASS__, 'hotel_booking_best_reviews' ) );
        add_shortcode( 'hotel_booking_lastest_reviews', array( __CLASS__, 'hotel_booking_lastest_reviews' ) );
    }

    /**
     * Shortcode to display the search form
     *
     * @param $atts
     * @return string
     */
    static function hotel_booking( $atts ){
        if( ! class_exists( 'HB_Room' ) ){
            TP_Hotel_Booking::instance()->_include( 'includes/class-hb-room.php' );
        }
        $start_date     = hb_get_request( 'check_in_date' );
        $end_date       = hb_get_request( 'check_out_date' );
        $adults         = hb_get_request( 'adults' );
        $max_child      = hb_get_request( 'max_child' );

        $atts = wp_parse_args(
            $atts,
            array(
                'check_in_date'     => $start_date,
                'check_out_date'    => $end_date,
                'adults'            => hb_get_request( 'adults' ),
                'max_child'         => hb_get_request( 'max_child' ),
                'search_page'       => null
            )
        );
        $page = hb_get_request( 'hotel-booking' );
        $template = 'search-room.php';
        $template_args = array();

        // find the url for form action
        $search_permalink = '';
        if( $search_page = $atts['search_page'] ){
            if( is_numeric( $search_page ) ){
                $search_permalink = get_the_permalink( $search_page );
            }else{
                $search_permalink = $search_page;
            }
        }else{
            global $post;
            if( $post && ( $post_id = get_the_ID() ) && is_page( $post_id ) ){
                $search_permalink = get_the_permalink( $post_id );
            }
        }
        $template_args['search_page'] = $search_permalink;
        /**
        *  Add argument use in shortcode display
        */
        $template_args['atts']         = $atts;

        /**
         * Display the template based on current step
         */
        switch( $page ){
            case 'results':
                $template = 'results.php';
                $template_args['results']   = hb_search_rooms(
                    array(
                        'check_in_date'     => $start_date,
                        'check_out_date'    => $end_date,
                        'adults'            => $adults,
                        'max_child'         => $max_child
                    )
                );
                break;
            case 'cart':
                $template = 'cart.php';
                break;
            case 'checkout':
                if( is_user_logged_in() ){
                    global $current_user;
                    get_currentuserinfo();

                    $template_args['customer'] = hb_get_customer( $current_user->user_email );

                }else{
                    $template_args['customer'] = hb_create_empty_post();
                    $template_args['customer']->data = array(
                        'title'             => '',
                        'first_name'        => '',
                        'last_name'         => '',
                        'address'           => '',
                        'city'              => '',
                        'state'             => '',
                        'postal_code'       => '',
                        'country'           => '',
                        'phone'             => '',
                        'fax'               => ''
                    );
                }
                $template = 'checkout.php';
                break;
            case 'payment':
                if( is_user_logged_in() ){
                    global $current_user;
                    get_currentuserinfo();

                    $template_args['customer'] = hb_get_customer( $current_user->user_email );

                }else{
                    $template_args['customer'] = hb_create_empty_post();
                    $template_args['customer']->data = array(
                        'title'             => '',
                        'first_name'        => '',
                        'last_name'         => '',
                        'address'           => '',
                        'city'              => '',
                        'state'             => '',
                        'postal_code'       => '',
                        'country'           => '',
                        'phone'             => '',
                        'fax'               => ''
                    );
                }
                $template = 'payment.php';
                break;
            case 'confirm':
                $template = 'confirm.php';
                break;
            case 'complete':
                $template = 'message.php';
                break;
        }
        ob_start();
        do_action( 'hb_wrapper_start' );
        hb_get_template( $template, $template_args );
        do_action( 'hb_wrapper_end' );
        $output = ob_get_clean();
        return $output;
    }

    static function hotel_booking_slider($atts, $content = null)
    {
        $number_rooms = isset($atts['rooms']) ? (int)$atts['rooms'] : 10;
        $size = isset($atts['image_size']) ? $atts['image_size'] : 'thumbnail';
        $items = isset($atts['number']) ? (int)$atts['number'] : 4;
        // $posts = get_terms( 'hb_room_type', array('hide_empty' => 0)); gallery of room_type taxonmy change to gallery of room post_type

        $args = array(
                'post_type'         => 'hb_room',
                'posts_per_page'    => $number_rooms,
                'orderby'           => 'date',
                'order'             => 'DESC',
                // 'meta_key'          => '_hb_gallery'
            );
        $the_query = new WP_Query( $args );

        $currentcy = hb_get_currency_symbol();
        $sliderId = 'hotel_booking_slider_'.uniqid();
        $upload_dir = wp_upload_dir();
        $upload_base_dir = $upload_dir['basedir'];
        $upload_base_url = $upload_dir['baseurl'];
        if( $the_query->have_posts() ):
    ?>
            <div id="<?php echo $sliderId ?>" class="hb_room_carousel_container tp-hotel-booking">
                <?php if( isset($atts['title']) && $atts['title'] ): ?>
                    <h3><?php echo $atts['title'] ?></h3>
                <?php endif; ?>
                <!--navigation-->
                <?php if( !isset($atts['navigation']) || $atts['navigation'] ): ?>
                    <div class="navigation">
                        <div class="prev"><span class="pe-7s-angle-left"></span></div>
                        <div class="next"><span class="pe-7s-angle-right"></span></div>
                    </div>
                <?php endif; ?>
                <!--pagination-->
                <?php if( !isset($atts['pagination']) || $atts['pagination'] ): ?>
                    <div class="pagination"></div>
                <?php endif; ?>
                <!--text_link-->
                <?php if( isset($atts['text_link']) && $atts['text_link'] !== '' ): ?>
                    <div class="text_link"><a href="<?php echo get_post_type_archive_link('hb_room'); ?>"><?php echo $atts['text_link']; ?></a></div>
                <?php endif; ?>
                <div class="hb_room_carousel">
                    <?php  while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                        <?php $galleries = get_post_meta( get_the_ID(), '_hb_gallery', true ); ?>
                        <?php
                            $prices = array();
                            if( function_exists('hb_get_price_plan_room') )
                            {
                                $prices = hb_get_price_plan_room(get_the_ID());
                                if( $prices )
                                    sort($prices);
                            }
                            $currency = get_option( 'tp_hotel_booking_currency' );
                            $title = get_the_title();
                        ?>
                            <div class="item">
                                <div class="media">
                                    <a href="<?php echo get_the_permalink(get_the_ID()); ?>" class="media-image" title="<?php echo esc_attr($title); ?>">
                                    <?php
                                        global $hb_room;
                                        $hb_room->getImage( 'catalog' );
                                    ?>
                                    </a>
                                </div>
                                <div class="title">
                                    <h4>
                                        <a href="<?php echo get_the_permalink(get_the_ID()); ?>" class="media-image"><?php echo $title; ?></a>
                                    </h4>
                                </div>
                                <?php if( (!isset($atts['price']) || $atts['price'] !== '*') && $prices ): ?>
                                    <div class="price">
                                        <span>
                                            <?php
                                                $current = current($prices);
                                                $end = end($prices);
                                                if( $current !== $end && $atts['price'] === 'min_to_max' )
                                                {
                                                    echo $currentcy.$current . ' - ' . $end;
                                                }
                                                else
                                                {
                                                    echo $currentcy.$current;
                                                }
                                            ?>
                                        </span>
                                        <span class="unit"><?php  _e( 'Night', 'tp-hotel-booking' ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <!--rating-->
                                <?php if( !isset($atts['rating']) || $atts['rating'] ): ?>
                                    <?php hb_get_template( 'loop/rating.php' ) ?>
                                <?php endif; ?>
                            </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
            <script type="text/javascript">
                (function($){
                    "use strict";
                    $(document).ready(function(){
                        $('#<?php echo $sliderId ?> .hb_room_carousel').carouFredSel({
                            responsive: true,
                            items: {
                                height: 'auto',
                                visible: {
                                    min: <?php echo $items ?>,
                                    max: <?php echo $items ?>
                                }
                            },
                            width: 'auto',
                            prev: {
                                button: '#<?php echo $sliderId; ?> .navigation .prev'
                            },
                            next: {
                                button: '#<?php echo $sliderId; ?> .navigation .next'
                            },
                            pagination: '#<?php echo $sliderId; ?> > .pagination',
                            mousewheel: false,
                            auto: false,
                            pauseOnHover: true,
                            onCreate: function()
                            {

                            },
                            swipe: {
                                onTouch: true,
                                onMouse: true
                            },
                            scroll : {
                                items           : 1,
                                easing          : "swing",
                                duration        : 700,
                                pauseOnHover    : true
                            }
                        });
                    });
                })(jQuery);
            </script>
    <?php
        endif;
    }

    static function hotel_booking_best_reviews( $atts )
    {
        $number = isset( $atts['number'] ) ? $atts['number'] : 5;
        $args = array(
                'post_type'     => 'hb_room',
                'meta_key'      => 'arveger_rating',
                'limit'         => $number,
                'order'         => 'DESC',
                'orderby'       => array( 'meta_value_num' => 'DESC' )
            );
        $query = new WP_Query( $args );

        if( $query->have_posts() ): ?>

            <div id="hotel_booking_best_reviews-<?php echo uniqid(); ?>" class="hotel_booking_best_reviews tp-hotel-booking">
                <?php if( isset($atts['title']) && $atts['title'] ): ?>
                    <h3><?php echo $atts['title'] ?></h3>
                <?php endif; ?>
                <?php hotel_booking_room_loop_start(); ?>

                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>

                        <?php hb_get_template_part( 'content', 'room' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php hotel_booking_room_loop_end(); ?>

            </div>

        <?php endif;
    }

    static function hotel_booking_lastest_reviews( $atts )
    {
        $number = isset( $atts['number'] ) ? $atts['number'] : 5;
        $args = array(
                'post_type'     => 'hb_room',
                'meta_key'      => 'arveger_rating_last_modify',
                'limit'         => $number,
                'order'         => 'DESC',
                'orderby'       => array( 'meta_value_num' => 'DESC' )
            );
        $query = new WP_Query( $args );

        if( $query->have_posts() ): ?>

            <div id="hotel_booking_lastest_reviews-<?php echo uniqid(); ?>" class="hotel_booking_lastest_reviews tp-hotel-booking">
                <?php if( isset($atts['title']) && $atts['title'] ): ?>
                    <h3><?php echo $atts['title'] ?></h3>
                <?php endif; ?>
                <?php hotel_booking_room_loop_start(); ?>

                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>

                        <?php hb_get_template_part( 'content', 'room' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php hotel_booking_room_loop_end(); ?>

            </div>

        <?php endif;
    }
}

// Init
HB_Shortcodes::init();