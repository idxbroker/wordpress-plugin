<?php
/**
 * File to manpulate IDX widgets. Create widgets based upon IDX widget API. 
 * Based upon API response, iterate through array and create classes for widgets. 
 * 
 * @author IDX, Inc.
 * 
 */
	$data = get_transient('idx_widget_cache');
	if($data) {
		$idx_widgets = $data;
	} else {
		$idx_widgets = idx_platinum_get_widgets();
	}
	if($idx_widgets) {
		$code = '';		
		foreach($idx_widgets as $widget) {
			$idx_widget_title = $widget->name;
			$idx_widget_class = str_replace('-','_',$widget->uid);
			$idx_widget_link = $widget->url;
			$code .= '
			class widget_'.$idx_widget_class.' extends WP_Widget {
			
			function widget_'.$idx_widget_class.' () {
				$widget_ops = array( \'classname\' => \'widget_'.$idx_widget_class.'\', \'description\' => __( "IDX '.$idx_widget_title.'" ) );
				$this->WP_Widget(\'idx'.$idx_widget_class.'\', __(\'IDX '.$idx_widget_title.'\'), $widget_ops);
			}
			
			function widget($args, $instance) {
				extract($args);
				
				echo $before_widget;
				echo $before_title;
				
				if(!empty($instance[\'title\'])) {
					echo $instance[\'title\'];
				} else {
					echo "'.$idx_widget_title.'";
				}
			
				echo $after_title;
				echo \'<script src="'.$idx_widget_link.'"></script>\';
				echo $after_widget;
			}
			
			function update($new_instance, $old_instance) {
				return $new_instance;
			}
			
				function form($instance) {
	
					$title = (isset($instance[\'title\'])) ? $instance[\'title\'] : \'\';
					
					echo \'<div id="idx'.$idx_widget_class.'-admin-panel">\';
					
					echo \'<label for="\' . $this->get_field_id("title") .\'">Widget Title:</label>\';
					echo \'<input type="text" \';
					echo \'name="\' . $this->get_field_name("title") . \'" \';
					echo \'id="\' . $this->get_field_id("title") . \'" \';
					echo \'value="\' . $title . \'" /><br /><br />\';
					echo \'</div>\';
				}
			}';
			add_action('widgets_init', create_function('', 'return register_widget("widget_'.$idx_widget_class.'");'));
		}
		eval($code);
	} 