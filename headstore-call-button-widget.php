<?php
/**
 * Plugin Name: Headstore Call Button Widget
 * Description: A widget that displays the call Button.
 * Version: 0.1
 * Author: Kelian Maissen
**/


add_action( 'widgets_init', 'headstore_widget' );


function headstore_widget() {
	register_widget( 'Main_Widget' );
}

class Main_Widget extends WP_Widget {

	function Main_Widget() {
		$widget_ops = array( 'classname' => 'headstore', 'description' => __('Add a Headstore Call Button.', 'headstore') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'headstore-widget' );
		
		$this->WP_Widget( 'headstore-widget', __('Headstore Call Button Widget', 'headstore'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		
		wp_enqueue_script( 'headstore_script', $instance['embed_script']);
		
		echo $before_widget;

		// Display the widget title 
		if ( $title )
		echo $before_title . $title . $after_title;
			
		//Display the code 	
		
	
		
		//echo '<div class="callme-button" data-group="'.$instance['group'].'" data-design="'.$instance['design'].'"></div>';
		echo '<div class="callme-button" data-group="'.$instance['group'].'" data-design="'.$instance['design'].'"></div>';
	
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['group'] = strip_tags( $new_instance['group'] );
		$instance['design'] = strip_tags( $new_instance['design'] );
		$instance['group_obj'] = strip_tags( $new_instance['group_obj'] );	
		
		$instance['embed_params_array'] = $new_instance['embed_params_array'];	
		
		$instance['embed_code'] = $new_instance['embed_code'];
		$instance['embed_script'] = $new_instance['embed_script'];
		
		
		foreach( $instance['embed_params_array'] as $key => $value) { 
		
			if(array_key_exists($key.'2',$instance['embed_params_array'])) 
			{
				if($instance['embed_params_array'][$key.'2'] != "")
				{
					$instance['embed_params_array'][$key] = $instance['embed_params_array'][$key.'2'];
					$instance['embed_params_array'][$key.'2'] = "";
				}
			}	
		}
		
		$embed_script_url = '//app.headstore.com/callme/callme.js?id='.$instance['group'];
		//$embed_script_url = '//app.headstore.com/callme/callme.js';
			 
		foreach( $instance['embed_params_array'] as $key2 => $value2) {
			if($value2 != "")
			{
			$embed_script_url .= '&'.$key2.'='.$value2;
			}
		} 
		
		$instance['embed_script'] = $embed_script_url;
		

		return $instance;
	}

	
	function form( $instance ) {

		
		//Set up some default widget settings.
		$defaults = array( 
		'title' => __('Call Me', 'example'), 
		'username' => __('', 'example'),
		'embed_script' => __('', 'embed_script'),
		'embed_code' => __('', 'embed_code'));
	
		
		
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget Title:', 'example'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<?php
		
		
		
		if($instance['username'] == "")
		{
			?>
            
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Username:'); ?></label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>" style="width:100%;" />
			</p>
            
            // Please enter the Username and click Save to proceed.<br /><br /><br /><br />

            <?php
		}
		else
		{

			?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Username:'); ?></label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>" style="width:100%;" />
			</p>
            <?php
			
			$json = file_get_contents('https://app.headstore.com/api/callme/wp/'.$instance['username'].'/');
			$jsonarray = json_decode($json, true);
	
	
			if(!array_key_exists('groups',$jsonarray))
			{ 
				exit(); 
			}
			
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e('Group:'); ?></label>
                <select id="<?php echo $this->get_field_id( 'group' ); ?>" name="<?php echo $this->get_field_name( 'group' ); ?>"  style="width:100%;">
                <?php foreach( $jsonarray['groups'] as $group) { ?>
                <option value="<?php echo $group['webToken']; ?>" <?php if($instance['group'] == $group['webToken']){echo "selected";}?>>
				<?php echo $group['shortDescription']; ?></option>
                <?php } ?>
                </select>         
            </p>   		

			<p>
				<label for="<?php echo $this->get_field_id( 'design' ); ?>"><?php _e('Design:'); ?></label>
                <select id="<?php echo $this->get_field_id( 'design' ); ?>" name="<?php echo $this->get_field_name( 'design' ); ?>"  style="width:100%;">

                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                </select>         
            </p>   		

            <?php
			
			foreach( $jsonarray['params'] as $params) { 
			
				//echo $params['name'];
				
				if($params['type'] == "SELECT_FROM_GROUP_PROPERTY")
				{
					?>
					<label for="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>"><?php _e($params['label']); ?>:</label>
                    
                    <select id="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>" 
                    name="<?php echo $this->get_field_name('embed_params_array').'['.$params['name'].']'; ?>" style="width:100%;">
                    <?php 
					foreach( $jsonarray['groups'] as $group2) 
					{  
						if($instance['group'] == $group2['webToken'])
						{ 
						
						if(!in_array($instance['embed_params_array'][$params['name']],$group2[$params['groupPropertyName']]))
						{
							$select_option2 = $instance['embed_params_array'][$params['name']];
							?>
							<option value="<?php echo $select_option2; ?>" <?php if($instance['embed_params_array'][$params['name']] == $select_option2)
							{echo "selected";}?>><?php echo $select_option2; ?></option>
							<?php
						}
						
						foreach( $group2[$params['groupPropertyName']] as $select_option) 
						{ 
							?>
							<option value="<?php echo $select_option; ?>" <?php if($instance['embed_params_array'][$params['name']] == $select_option)
							{echo "selected";}?>><?php echo $select_option; ?></option>
							<?php
						}
						}
					} 
					?>
                    </select> 
                    <input id="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'].'2' ); ?>" 
                    name="<?php echo $this->get_field_name('embed_params_array').'['.$params['name'].'2]'; ?>"
                    value="<?php echo ""; ?>" style="width:100%;" />
                	<?php	
				}
				elseif($params['type'] == "SELECT")
				{
					?>
                    
                    <label for="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>"><?php _e($params['label']); ?>:</label>
                    
                    <select id="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>" 
                    name="<?php echo $this->get_field_name('embed_params_array').'['.$params['name'].']'; ?>" style="width:100%;">
                    <?php 
					foreach( $params['options'] as $select_option) 
					{ 
						$select_option_pack = explode(":", $select_option);
						?>
						<option value="<?php echo $select_option_pack[0]; ?>" <?php if($instance['embed_params_array'][$params['name']] == $select_option_pack[0])
						{echo "selected";}?>><?php if(isset($select_option_pack[1])){ echo $select_option_pack[1]; }else{ echo $select_option_pack[0]; } ?></option>
						<?php
					} 
					?>
                    </select> 
                    <?php
					
				}
				elseif($params['type'] == "INPUT")
				{
					?>
					<label for="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>"><?php _e($params['label']); ?>:</label>
                    <input id="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>" 
                    name="<?php echo $this->get_field_name('embed_params_array').'['.$params['name'].']'; ?>"
                    value="<?php echo $instance['embed_params_array'][$params['name']]; ?>" style="width:100%;" />
                    <?php
				}
				elseif($params['type'] == "CHECKBOX")
				{
					?>
                    <label for="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>"><?php _e($params['label']); ?>:</label>
					<br />
                    <input class="checkbox" type="checkbox" <?php if($instance['embed_params_array'][$params['name']] == true){echo"checked";} ?> 
                    id="<?php echo $this->get_field_id( 'embed_params_array_'.$params['name'] ); ?>" 
                    name="<?php echo $this->get_field_name('embed_params_array').'['.$params['name'].']'; ?>" /> 
					<?php
				}
				
				?><br /><br /><?php
				
			}
			
			//echo $instance['embed_script'];
		}
		
	}
}


/*

*/
?>