<?php

namespace SolrPlugin;


class SolrPostFields {
	
	/**
	 * SolrPostFields constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct(Plugin $plugin ) {
		add_filter(Plugin::FILTER_SOLR_INDEX_FIELDS_POST, array($this, 'add_post_fields'),10,3);
	}
	
	/**
	 * Sets the fields from a WP_Post object to a Solarium Document, which will be
	 * uploaded to Solr.
	 *
	 * @param \Solarium\QueryType\Update\Query\Document\DocumentInterface $document
	 * @param \WP_Post $post
	 * @param \Solarium\QueryType\Update\Query\Query $update
	 * @return \Solarium\QueryType\Update\Query\Document\Document
	 */
	
	public function add_post_fields(\Solarium\QueryType\Update\Query\Document\Document $document, \WP_Post $post, \Solarium\QueryType\Update\Query\Query $update) {
		
		/**
		 * document field prefixes
		 * t._ for text (with filters and tokenizer or so?)
		 * s._ for string (no tokenizer or so?)
		 * d._ for date
		 * b._ for boolean
		 * .s_ for single value
		 * .m_ for multivalue (array)
		 */
		$document->ts_title = $update->getHelper()->filterControlCharacters($post->post_title);
		$document->item_id = $post->ID;
		$document->ds_published = date('Y-m-d\TH:i:s\Z', strtotime($post->post_date_gmt));
		$document->ds_changed = date('Y-m-d\TH:i:s\Z', strtotime($post->post_modified_gmt));
		
		
		$author_ids = array($post->post_author);
		$author_ids = apply_filters('solr_index_update_author_ids', $author_ids, $post->ID);
		foreach ($author_ids as $author_id){
			$author = get_user_by('id', $author_id);
			if($author instanceof \WP_User){
				$document->addField('sm_author', $author->display_name);
			}
		}

		$content = apply_filters('the_content', $post->post_content);

		$document->content = $update->getHelper()->filterControlCharacters($content);
		$document->url = $post->guid;
		
		// set categories
		$categories = get_the_category($post->ID);
		if(false != $categories){
			foreach ($categories as $category) {
				$document->addField('sm_category', $category->cat_name);
			}
		}
		
		
		// set tags
		$tags = get_the_tags($post->ID);
		if(false != $tags){
			foreach ($tags as $tag) {
				$document->addField('sm_tag', $tag->name);
			}
		}
		
		do_action('solr_add_post_fields_to_document', $document, $post);
		
		// set published field (string)
		$poststatus = get_post_status ( $post->ID );
		$document->ss_status = $poststatus;
		$document->ss_type = $post->post_type;
		
		return $document;
	}
}