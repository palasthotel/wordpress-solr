<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.01.18
 * Time: 14:41
 */

namespace SolrPlugin;


class MetaBox {
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action("save_post", array($this, "save"));
		add_filter(Plugin::FILTER_SOLR_INDEX_IGNORE_POST, array($this, "ignore"),10 ,2);
	}

	function add_meta_boxes( $post_type, $post ) {

		/**
		 * add initiator
		 */
		add_meta_box(
			'solr_meta_box',
			$this->plugin->getName(),
			array( $this, 'render' ),
			$post_type,
			'side'
		);

	}


	function render(){
		wp_nonce_field( 'solr_metabox_nonce', 'solr_nonce' );
		$checked = $this->ignorePost(get_the_ID());
		?>
		<p>
			<label>
				<input type="checkbox" name="solr_post_ignore" value="ignore" <?php echo ($checked)? "checked":"" ?> />
				<?php _e("Do not add this content to solr search?", Plugin::DOMAIN) ?>
			</label>
		</p>
		<?php
	}

	function save($post_id){
		if(!isset($_POST["solr_nonce"]) || !wp_verify_nonce( $_POST["solr_nonce"], "solr_metabox_nonce")) return;
		if(!current_user_can("edit_post", $post_id)) return;

		if(isset($_POST["solr_post_ignore"]) && $_POST["solr_post_ignore"] == "ignore" ){
			update_post_meta($post_id, Plugin::POST_META_IGNORE, "ignore");
			$this->plugin->solr_index->deletePost(array(get_post($post_id)));
		} else {
			delete_post_meta($post_id,Plugin::POST_META_IGNORE);
		}

	}

	function ignorePost($post_id){
		return get_post_meta($post_id, Plugin::POST_META_IGNORE, true) == "ignore";
	}

	/**
	 * @param boolean $ignore
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	function ignore($ignore, $post){
		// it set to true elsewhere ignore flag on post
		if($ignore) return $ignore;
		// else check
		return $this->ignorePost($post->ID);

	}
}