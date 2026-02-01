<?php
/**
 * 面包屑导航组件
 * 
 * @package SeoPress_AI
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 输出面包屑导航
 * 
 * @param array $args 参数
 */
function seopress_ai_breadcrumb( $args = array() ) {
    $defaults = array(
        'home_text'  => '首页',
        'separator'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>',
        'show_home'  => true,
        'show_current' => true,
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    // 不在首页显示
    if ( is_front_page() ) {
        return;
    }
    
    $items = array();
    
    // 首页
    if ( $args['show_home'] ) {
        $items[] = array(
            'url'   => home_url( '/' ),
            'title' => $args['home_text'],
            'icon'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        );
    }
    
    // 文章页
    if ( is_single() ) {
        // 分类
        $categories = get_the_category();
        if ( ! empty( $categories ) ) {
            // 获取最深层的分类
            $category = $categories[0];
            $ancestors = get_ancestors( $category->term_id, 'category' );
            $ancestors = array_reverse( $ancestors );
            
            // 添加祖先分类
            foreach ( $ancestors as $ancestor_id ) {
                $ancestor = get_category( $ancestor_id );
                if ( $ancestor ) {
                    $items[] = array(
                        'url'   => get_category_link( $ancestor_id ),
                        'title' => $ancestor->name,
                    );
                }
            }
            
            // 当前分类
            $items[] = array(
                'url'   => get_category_link( $category->term_id ),
                'title' => $category->name,
            );
        }
        
        // 当前文章
        if ( $args['show_current'] ) {
            $items[] = array(
                'url'   => '',
                'title' => get_the_title(),
            );
        }
    }
    
    // 分类归档
    elseif ( is_category() ) {
        $category = get_queried_object();
        $ancestors = get_ancestors( $category->term_id, 'category' );
        $ancestors = array_reverse( $ancestors );
        
        foreach ( $ancestors as $ancestor_id ) {
            $ancestor = get_category( $ancestor_id );
            if ( $ancestor ) {
                $items[] = array(
                    'url'   => get_category_link( $ancestor_id ),
                    'title' => $ancestor->name,
                );
            }
        }
        
        if ( $args['show_current'] ) {
            $items[] = array(
                'url'   => '',
                'title' => $category->name,
            );
        }
    }
    
    // 标签归档
    elseif ( is_tag() ) {
        $items[] = array(
            'url'   => '',
            'title' => sprintf( '标签: %s', single_tag_title( '', false ) ),
        );
    }
    
    // 作者归档
    elseif ( is_author() ) {
        $items[] = array(
            'url'   => '',
            'title' => sprintf( '作者: %s', get_the_author() ),
        );
    }
    
    // 日期归档
    elseif ( is_date() ) {
        if ( is_year() ) {
            $items[] = array(
                'url'   => '',
                'title' => get_the_date( 'Y年' ),
            );
        } elseif ( is_month() ) {
            $items[] = array(
                'url'   => get_year_link( get_the_date( 'Y' ) ),
                'title' => get_the_date( 'Y年' ),
            );
            $items[] = array(
                'url'   => '',
                'title' => get_the_date( 'n月' ),
            );
        } elseif ( is_day() ) {
            $items[] = array(
                'url'   => get_year_link( get_the_date( 'Y' ) ),
                'title' => get_the_date( 'Y年' ),
            );
            $items[] = array(
                'url'   => get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) ),
                'title' => get_the_date( 'n月' ),
            );
            $items[] = array(
                'url'   => '',
                'title' => get_the_date( 'j日' ),
            );
        }
    }
    
    // 搜索结果
    elseif ( is_search() ) {
        $items[] = array(
            'url'   => '',
            'title' => sprintf( '搜索: %s', get_search_query() ),
        );
    }
    
    // 404页面
    elseif ( is_404() ) {
        $items[] = array(
            'url'   => '',
            'title' => '页面未找到',
        );
    }
    
    // 页面
    elseif ( is_page() ) {
        global $post;
        
        // 父级页面
        if ( $post->post_parent ) {
            $ancestors = get_ancestors( $post->ID, 'page' );
            $ancestors = array_reverse( $ancestors );
            
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = array(
                    'url'   => get_permalink( $ancestor_id ),
                    'title' => get_the_title( $ancestor_id ),
                );
            }
        }
        
        if ( $args['show_current'] ) {
            $items[] = array(
                'url'   => '',
                'title' => get_the_title(),
            );
        }
    }
    
    // 输出
    if ( empty( $items ) || count( $items ) < 2 ) {
        return;
    }
    
    $output = '<nav class="breadcrumb" aria-label="面包屑导航">';
    $output .= '<ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';
    
    $position = 0;
    $last_index = count( $items ) - 1;
    
    foreach ( $items as $index => $item ) {
        $position++;
        $is_last = ( $index === $last_index );
        
        $output .= '<li class="breadcrumb-item' . ( $is_last ? ' current' : '' ) . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        
        if ( ! $is_last && ! empty( $item['url'] ) ) {
            $output .= '<a href="' . esc_url( $item['url'] ) . '" itemprop="item">';
            if ( isset( $item['icon'] ) ) {
                $output .= $item['icon'];
            }
            $output .= '<span itemprop="name">' . esc_html( $item['title'] ) . '</span>';
            $output .= '</a>';
        } else {
            $output .= '<span itemprop="name">' . esc_html( $item['title'] ) . '</span>';
        }
        
        $output .= '<meta itemprop="position" content="' . $position . '">';
        $output .= '</li>';
        
        if ( ! $is_last ) {
            $output .= '<li class="breadcrumb-separator" aria-hidden="true">' . $args['separator'] . '</li>';
        }
    }
    
    $output .= '</ol>';
    $output .= '</nav>';
    
    echo $output;
}
