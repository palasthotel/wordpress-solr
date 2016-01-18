<?php
/**
 * This is an example configuration file.
 * Change it according to your needs and
 * rename it to config.php.
 */

/**
 * Configuration for this module.
 */
$phsolr_config = array(

  // 'AND' or 'OR'
  'default_query_operator' => 'AND',

  // list of fields to search and their corresponding weight
  'search_fields' => array(
    'title' => 4.0,
    'date' => 0.5,
    'author' => 0.5,
    'content' => 1.0
  ),

  // list of additional boost functions
  'boost_functions' => array(
    'recip(abs(ms(NOW,date)),3.16e-11,1,1)^5'
  ),

  // list of fields to return
  'result_fields' => array(
    'title',
    'date',
    'author'
  ),

  // list of fields that are intended to be highlighted
  'highlight_fields' => array(
    'title',
    'content'
  ),

  // facet definitions
  'facets' => array(
    'type' => array(
      'field' => 'type',
      'title' => 'Type'
    ),
    'date' => array(
      'field' => 'date',
      'title' => 'Date'
    )
  )
);

// TODO: move to wordpress filter function
function phsolr_post_filter(WP_Post $post) {
  return true;
}
// TODO: move to wordpress filter function
function phsolr_comment_filter(WP_Comment $comment) {
  return true;
}

/**
 * Sets the fields from a WP_Post object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * Don't set the ID explicitly. It will be set for you to.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Post $post
 */
// TODO: move to wordpress action
function phsolr_set_post_fields( Solarium\QueryType\Update\Query\Document\DocumentInterface $document, WP_Post $post) {
	// get the authors name
	$author_name = get_user_by('id', $post->post_author)->display_name;

	$document->title = $post->post_title;
	$document->date = date('Y-m-d\TH:i:s\Z', strtotime($post->post_date_gmt));
	$document->modified = date('Y-m-d\TH:i:s\Z',
	  strtotime($post->post_modified_gmt));
	$document->author = $author_name;
	$document->content = strip_tags($post->post_content);
	$document->url = $post->guid;

	// set categories
	$categories = get_the_category($post->ID);
	foreach ($categories as $category) {
	$document->addField('category', $category->cat_name);
	}

	// set published field (boolean value)
	$document->published = $post->post_status === 'publish';

	$document->type = $post->type;
}



/**
 * Sets the fields from a WP_Comment object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Comment $comment
 */
// TODO: move to wordpress action
function phsolr_set_comment_fields( Solarium\QueryType\Update\Query\Document\DocumentInterface $document,  WP_Comment $comment) {
	$document->content = $comment['comment_content'];
	$document->author = $comment['comment_author'];
	$document->date = date('Y-m-d\TH:i:s\Z',
	  strtotime($comment['comment_date_gmt']));

	$document->type = 'comment';
}
