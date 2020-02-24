<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 27.06.17
 * Time: 08:53
 */

namespace SolrPlugin\Flags;

define('SOLR_FLAG_MODIFIED', 'modified');
define('SOLR_FLAG_INDEXED', 'indexed');
define('SOLR_FLAG_ERRORED', 'errored');
define('SOLR_FLAG_IGNORED', 'ignored');

/**
 * set post modified
 *
 * @param $item_id
 *
 * @param $type
 *
 * @return false|int
 */
function set_modified($item_id, $type ){
	return _update($item_id, $type, SOLR_FLAG_MODIFIED);
}

function is_modified($item_id, $type ){
	return _isFlagged($item_id, $type, SOLR_FLAG_MODIFIED);
}

/**
 * set post indexed
 *
 * @param $item_id
 *
 * @param $type
 *
 * @return false|int
 */
function set_indexed($item_id, $type ){
	return _update($item_id, $type, SOLR_FLAG_INDEXED);
}

function is_indexed($item_id, $type ){
	return _isFlagged($item_id, $type, SOLR_FLAG_INDEXED);
}

/**
 * set post error
 *
 * @param $item_id
 *
 * @param $type
 *
 * @return false|int
 */
function set_error($item_id, $type){
	return _update($item_id, $type, SOLR_FLAG_ERRORED);
}
function is_errored($item_id, $type ){
	return _isFlagged($item_id, $type, SOLR_FLAG_ERRORED);
}

/**
 * set post ignored
 *
 * @param $item_id
 *
 * @param $type
 *
 * @return false|int
 */
function set_ignored($item_id, $type){
	return _update($item_id, $type, SOLR_FLAG_IGNORED);
}
function is_ignored($item_id, $type ){
	return _isFlagged($item_id, $type, SOLR_FLAG_IGNORED);
}

/**
 * count falgged items
 * @param $flag
 *
 * @return null|string
 */
function count($flag){
	global $wpdb;
	return $wpdb->get_var(
		$wpdb->prepare(
			"
SELECT count(id) FROM ".tablename()." WHERE flag = %s
",
			$flag
		)
	);
}

/**
 * delete flag of post
 * @param $item_id
 *
 * @return false|int
 */
function delete($args, $format = null){
	global $wpdb;
	return $wpdb->delete(
		tablename(),
		$args,
		$format
	);
}

/**
 * @param $itemId
 * @param $type
 * @param $flag
 *
 * @return bool
 */
function _isFlagged($itemId, $type, $flag){
	global $wpdb;
	$table = tablename();
	return intval($wpdb->get_var($wpdb->prepare(
		"SELECT count(id) FROM $table WHERE item_id = ? AND type = ? AND flag = ?",
		$itemId,
		$type,
		$flag
	))) > 0;
}

/**
 *
 * update flag of post
 * @param $item_id
 * @param $type
 * @param $flag
 *
 * @return false|int
 */
function _update($item_id, $type, $flag){
	global $wpdb;
	return $wpdb->replace(
		tablename(),
		array(
			'item_id' => $item_id,
			'type' => $type,
			'flag' => $flag,
		),
		array(
			'%d',
			'%s',
			'%s',
		)
	);
}

/**
 * table name of
 * @return string
 */
function tablename(){
	global $wpdb;
	return $wpdb->prefix."solr_flags";
}

/**
 * install flags table
 */
function install(){
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta("CREATE TABLE IF NOT EXISTS ".tablename()." 
		(
		 id bigint(20) unsigned not null auto_increment ,
		 item_id bigint(20) unsigned not null ,
		 type varchar(50) not null,
		 flag varchar(15) not null,
		 primary key (id),
		 unique key element (item_id, type),
		 key (flag)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}