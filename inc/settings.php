<?php

namespace SolrPlugin;

class Settings{
	/**
	* construct settings
	* @param $solr_plugin Plugin
	*/
	function __construct( $solr_plugin ){
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
		/**
		 * add styles and scripts
		 */
		wp_enqueue_style('solr-settings-css',$this->plugin->url."css/settings.css",array(),"1",'all');
		wp_enqueue_script('solr-settings-js', $this->plugin->url."js/settings.js",array('jquery'),"1",true);
		/**
		 * render contents
		 */
		$current = (isset($_GET["tab"]))? $_GET["tab"]:"core";
		?>
		<h2>Solr Settings</h2>
		<?php
		$tabs = array( 'core' => 'Core' , 'automatics' => 'Automatics', 'advanced'=> 'Advanced', 'index' => 'Index',);
		echo '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=solr&tab=$tab'>$name</a>";
		}
		echo '</h2>';

		/**
		 * save changes if form war committed
		 */
		$this->try_save();

		/**
		 * all settings by template here
		 */
		$file = $this->plugin->dir."/parts/settings--$current.php";
		if(file_exists($file)){
			require $file;
			return;
		}
		/**
		 * else try settings dynamically
		 */
		?>
		<div class="wrap">
			<form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>?page=solr&amp;tab=<?php echo $current; ?>">
				<?php

				if($this->plugin->config->has_file_options()){
					_e("All locked settings are defined in config.json file.", Plugin::DOMAIN);
				}

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
		switch($page){
			case 'core':
				return array(
				  	array(
					  'key'=>Plugin::OPTION_ENABLED,
					  'label' => 'Enable Solr Search',
					  "type" => "checkbox",
					  "description" => "default theme/search.php template will be ignored ".
						"and theme/solr/search.php or this_plugin/template/search.php template will be used",
					),
				  	array('key'=>Plugin::OPTION_HOST, 'label' => 'Host'),
					array('key'=>Plugin::OPTION_PORT, 'label' => 'Port'),
					array('key'=>Plugin::OPTION_PATH, 'label' => 'Path'),
				  	array('key'=>Plugin::OPTION_CORE, 'label' => 'Core'),
				  	array('key'=>Plugin::OPTION_USERNAME, 'label' => 'Username'),
				  	array('key'=>Plugin::OPTION_PW, 'label' => 'Password', 'type' => 'password'),
				);
			case 'advanced':
				return array(
				  array(
					'key'=>Plugin::OPTION_DOCUMENTS_PER_CALL,
					'label' => 'Documents per index update',
					'type' => 'number',
					'description' => 'How many items should be send to solr per update call?',
				  ),
				  array(
					'key'=>Plugin::OPTION_TIMEOUT,
					'label' => 'Timeout',
					'type' => 'number',
					'description' => 'Timeout in seconds for solariums http connection to solr server',
				  ),
				);
		}
		return array();
	}

	private function render_setting($setting){
		$config = $this->plugin->config;
		$label = $setting['label'];
		$value = $config->get_option($setting['key']);
		$key = $setting['key'];
		$editable = !$this->plugin->config->is_file_option($setting["key"]);

		?>
		<tr>
			<th scope="row"><label for="<?php echo $key; ?>"><?php echo $label; ?></label></th>
			<td><?php
				$type = "text";
				if( isset($setting['type']) && !empty($setting['type']) ){
					$type = $setting['type'];
				}

				$readonly = ($editable)? "": " readonly='readonly' disabled='disabled' ";

				if( $type == 'checkbox' ){
					/**
					 * checkbox render
					 */
					$checked = '';
					if($value){
						$checked = 'checked';
					}
					?><input <?php echo $readonly; ?> type="checkbox" id="<?php echo $key; ?>"
							 name="<?php echo $key; ?>" <?php echo $checked; ?>
							 class="checkbox">
					<input type="hidden" name="<?php echo "checkbox_".$key; ?>" value="<?php echo $key; ?>" /><?php

				} else {
					/**
					 * text input
					 */
					if($type != "password" || $type != "number"){
						$type = "text";
					}
					?><input <?php echo $readonly; ?>
							type="<?php echo $type; ?>" id="<?php echo $key; ?>"
							 name="<?php echo $key; ?>" value="<?php echo $value; ?>"
							 class="regular-text"><?php
				}

				if(!empty($setting["description"])){
					echo "<p class='description'>".$setting["description"]."</p>";
				}


				?></td>
		</tr>
		<?php
	}

	private function try_save() {
		$config = $this->plugin->config;
		if( isset($_POST) && empty($_POST) ) return;
		foreach($_POST as $key => $value){
			/**
			 * save every post value with plugin prefix
			 */
			if( strpos($key, "solr_") === 0 ){
				/**
				 * prevent double prefix
				 */
				$config->set_option($key,$value);
			} else if( strpos($key,"checkbox_solr") === 0){
				/**
				 * save checkbox value
				 */
				$_key = str_replace("checkbox_",'',$key);
				if(!isset($_POST[$_key]) || empty($_POST[$_key])){
					$config->set_option($_key,false);
				}
			}

		}
	}


}
