<?php

namespace SolrPlugin;


class SolrCommentFields {
	
	/**
	 * SolrCommentFields constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct(Plugin $plugin ) {
		add_filter(Plugin::FILTER_SOLR_INDEX_FIELDS_COMMENT, array($this, 'add_comment_fields'),10,3);
	}
	
	/**
	 * Sets the fields from a WP_Comment object to a Solarium Document, which will be
	 * uploaded to Solr.
	 *
	 * @param \Solarium\QueryType\Update\Query\Document\DocumentInterface $document
	 * @param \WP_Comment $comment
	 * @param \Solarium\QueryType\Update\Query\Query $update
	 * @return \Solarium\QueryType\Update\Query\Document\DocumentInterface
	 */
	public function add_comment_fields( \Solarium\QueryType\Update\Query\Document\DocumentInterface $document,  \WP_Comment $comment, \Solarium\QueryType\Update\Query\Query $update) {
		
		$document->content = $update->getHelper()->filterControlCharacters($comment['comment_content']);
		$document->ts_author = $comment['comment_author'];
		$document->ds_published = date('Y-m-d\TH:i:s\Z', strtotime($comment['comment_date_gmt']));
		$document->ss_type = 'comment';
		
		return $document;
	}
}