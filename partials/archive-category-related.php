<?php
	// category pages show a list of related terms
	if ( defined('SHOW_CATEGORY_RELATED_TOPICS') && SHOW_CATEGORY_RELATED_TOPICS ) {
		if ( is_category() && largo_get_related_topics_for_category( get_queried_object() ) != '<ul></ul>' ) { ?>
			<div class="related-topics">
				<strong><?php _e('Related Topics:', 'largo'); ?> </strong>
				<?php echo largo_get_related_topics_for_category( get_queried_object() ); ?>
			</div>
	<?php
		}
	}
