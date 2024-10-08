<?php
/**
 * Archive elements.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'generate_archive_title' ) ) {
	add_action( 'generate_archive_title', 'generate_archive_title' );
	/**
	 * Build the archive title
	 *
	 * @since 1.3.24
	 */
	function generate_archive_title() {
		if ( ! function_exists( 'the_archive_title' ) ) {
			return;
		}
		?>
		<header <?php generate_do_attr( 'page-header' ); ?>>
			<?php
			/**
			 * generate_before_archive_title hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_archive_title' );
			?>

			<h1 class="page-title">
				<?php the_archive_title(); ?>
			</h1>

			<?php
			/**
			 * generate_after_archive_title hook.
			 *
			 * @since 0.1
			 *
			 * @hooked generate_do_archive_description - 10
			 */
			do_action( 'generate_after_archive_title' );
			?>
		</header>
		<?php
	}
}

if ( ! function_exists( 'generate_filter_the_archive_title' ) ) {
	add_filter( 'get_the_archive_title', 'generate_filter_the_archive_title' );
	/**
	 * Alter the_archive_title() function to match our original archive title function
	 *
	 * @since 1.3.45
	 *
	 * @param string $title The archive title.
	 * @return string The altered archive title
	 */
	function generate_filter_the_archive_title( $title ) {
		if ( is_category() ) {
			$title = single_cat_title( 'Alle Beiträge zum Urlaubsziel ', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( 'Alle Beiträge zum Thema ', false );
		} elseif ( is_author() ) {
			/*
			 * Queue the first post, that way we know
			 * what author we're dealing with (if that is the case).
			 */
			the_post();

			$title = sprintf(
				'%1$s<span class="vcard">%2$s</span>',
				get_avatar( get_the_author_meta( 'ID' ), 50 ),
				get_the_author()
			);

			/*
			 * Since we called the_post() above, we need to
			 * rewind the loop back to the beginning that way
			 * we can run the loop properly, in full.
			 */
			rewind_posts();
		}

		return $title;

	}
}

add_action( 'generate_after_archive_title', 'generate_do_archive_description' );
/**
 * Output the archive description.
 *
 * @since 2.3
 */
function generate_do_archive_description() {
	$term_description = get_the_archive_description();

	if ( ! empty( $term_description ) ) {
		if ( is_author() ) {
			printf( '<div class="author-info">%s</div>', $term_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			printf( '<div class="taxonomy-description">%s</div>', $term_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * generate_after_archive_description hook.
	 *
	 * @since 0.1
	 */
	do_action( 'generate_after_archive_description' );
}

add_action( 'generate_before_loop', 'generate_do_search_results_title' );
/**
 * Add the search results title to the search results page.
 *
 * @since 3.1.0
 * @param string $template The template we're targeting.
 */
function generate_do_search_results_title( $template ) {
	if ( 'search' === $template ) {
		// phpcs:ignore -- No escaping needed.
		echo apply_filters(
			'generate_search_title_output',
			sprintf(
				'<header %s><h1 class="page-title">%s</h1></header>',
				generate_get_attr( 'page-header' ),
				sprintf(
					/* translators: 1: Search query name */
					__( 'Search Results for: %s', 'generatepress' ),
					'<span>' . get_search_query() . '</span>'
				)
			)
		);
	}
}

add_action( 'generate_after_header', 'eb_featured_archive_content' );
/**
 * Add the featured category content to the category page page.
 *
 */
function eb_featured_archive_content() {
	// Retrieve related page by tag slug
	$query_var = get_query_var( 'cat' );

	if ( $query_var ) {
		$category = get_category($query_var );
		$page = get_page_by_path( $category->slug );

		// in case there is a content page for the current tag render it in the top area
		if ( $page && $page->post_status == 'publish') {
			echo '<div class="site grid-container featured-category-content-area">';
			echo '<h2 class="entry-title">' . apply_filters('the_title', $page->post_title) . '</h2>';
			echo apply_filters('the_content', $page->post_content); 
			echo '</div>';
		}

	} else {
		$query_var = get_query_var('tag');
		
		if ( $query_var ) {
			$page = get_page_by_path( "/tag/".$query_var );
	
			// in case there is a content page for the current tag render it in the top area
			if ( $page && $page->post_status == 'publish') {
				echo '<div class="site grid-container featured-category-content-area">';
				echo '<h2 class="entry-title">' . apply_filters('the_title', $page->post_title) . '</h2>';
				echo apply_filters('the_content', $page->post_content); 
				echo '</div>';
			}
		}
	}
}

/**
 * Set page size to 18.
 *
 */
add_action( 'pre_get_posts', 'eb_set_pagesize' );
function eb_set_pagesize( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

	$query->set( 'posts_per_page', 18 );
	return;
}


/**
 * Filter the except length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function eb_excerpt_length( $length ) {
	return 18;
}
add_filter( 'excerpt_length', 'eb_excerpt_length' );
