<?php

namespace SolrPlugin;

class Settings{
	/**
	* construct settings
	* @param $solr_plugin \SolrPlugin
	*/
	function __construct( \SolrPlugin $solr_plugin){
		$this->plugin = $solr_plugin;
		add_action( 'admin_menu', array($this, 'menu_pages') );
	}
	/**
	 * register menu pages
	 */
	public function menu_pages(){
		add_submenu_page( 'options-general.php', 'Solr', 'Solr', 'manage_options', "solr", array($this, "render_solr_settings"));
	}
	/**
	*  renders settings page
	*/
	public function render_solr_settings(){
		$current = (isset($_GET["tab"]))? $_GET["tab"]:"core";
		?>
		<h2>Solr Settings</h2>
		<?php
		$tabs = array( 'core' => 'Core', 'index' => 'Index' , 'advanced' => 'Advanced');
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=solr&tab=$tab'>$name</a>";
		}
		echo '</h2>';

		/**
		 * all settings by template here
		 */
		if($current == 'index'){
			require $this->plugin->dir."/parts/settings--$current.php";
			return;
		}
		/**
		 * else try settings by programming
		 */
		?>
		<div class="wrap">
			<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=solr&amp;tab=<?php echo $current; ?>">
				<?php
				$settings = $this->get_settings_for_page($current);
				?>
				<table class="form-table">
					<?php
					foreach($settings as $setting){
						$this->render_setting($setting);
					}
					?>
				</table>
				<?php submit_button('Save changes'); ?>
			</form>
		</div>
		<?php
	}

	private function get_settings_for_page($page){
		// to include config class
		$config = $this->plugin->get_config();
		switch($page){
			case 'core':
				return array(
					array('key'=>$config::$HOST, 'label' => 'Host', 'default' => '127.0.0.1'),
					array('key'=>$config::$PORT, 'label' => 'Port', 'default' => '8983'),
					array('key'=>$config::$PATH, 'label' => 'Path', 'default' => '/solr/'),
				);
			case 'advanced':
				return array(
				  array('key'=>$config::$SPELL_CHECK, 'label' => 'Spellcheck enabled', 'type' => 'checkbox'),
				  array('key'=>$config::$QUERY_LIMIT, 'label' => 'Maximum number of serach results'),
				);
		}
		return array();
	}

	private function render_setting($setting){
		$key = $setting['key'];
		$label = $setting['label'];
		$value = get_option('solr_'.$key, $this->plugin->get_config()->get_default($key));
		?>
		<tr>
			<th scope="row"><label for="<?php echo $key; ?>"><?php echo $label; ?></label></th>
			<td><?php
				if(!empty($setting['type']) || $setting['type'] == 'checkbox'){
					/**
					 * checkbox render
					 */
					$checked = '';
					if($value){
						$checked = 'checked';
					}
					?><input type="checkbox" id="<?php echo $key; ?>"
							 name="<?php echo $key; ?>" <?php echo $checked; ?>
							 class="checkbox"><?php

				} else {
					/**
					 * text input
					 */
					?><input type="text" id="<?php echo 'solr_'.$key; ?>"
							 name="<?php echo 'solr_'.$key; ?>" value="<?php echo $value; ?>"
							 class="regular-text"><?php
				}


				?></td>
		</tr>
		<?php
	}


}
