<?php
/*
Plugin Name: Multiple content blocks
Plugin URI: http://plugins.trendwerk.nl/
Description: Lets you use more than one content "block" on a template. You only have to insert one tag inside the template, so it's easy to use.
Version: 1.0
Author: Ontwerpstudio Trendwerk
Author URI: http://www.trendwerk.nl/
*/


function init_multiplecontent() {
	add_meta_box('multi_content',__('Multiple content blocks','trendwerk'),'add_multiplecontent_box','page','normal','high');
}

add_action('admin_init','init_multiplecontent');


function add_multiplecontent_box() {
	//check which template is used
	global $post;
	$fileToRead = get_template_directory_uri().'/'.$post->page_template;
	
	//read the template
	$fileToRead = strstr($fileToRead,'/themes/');
	if(substr(strrchr($fileToRead,'/'),1) == 'default') {
		$fileToRead = str_replace('default','page.php',$fileToRead);
	}
	$fileToRead = validate_file_to_edit($fileToRead, $allowed_files);
	$fileToRead = get_real_file_to_edit($fileToRead);
	$f = fopen($fileToRead, 'r');
	$contents = fread($f, filesize($fileToRead));
	$contents = htmlspecialchars( $contents );
	
	
	//check how many content field there have to be
	$editors = substr_count($contents," the_block(");
	
	$nextString = $contents;	
	for($i=0;$i<$editors;$i++) {
		//get the name from it
		$stringFirst = strstr($nextString,' the_block(');
		$stringFirst = substr($stringFirst,1);
		$stringLast = strstr($stringFirst,');');
		$editorName = str_replace('\'','',str_replace('the_block(','',str_replace($stringLast,'',$stringFirst)));
		$nextString = $stringLast;
		
		//add editor
		echo '<p><strong>'.ucfirst($editorName).'</strong></p>';
		echo '<input type="hidden" name="multiplecontent_box-'.$i.'" value="'.$editorName.'" />';
		echo '<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go(\'multiplecontent_box-'.$editorName.'\', \'html\');">HTML</a><a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go(\'multiplecontent_box-'.$editorName.'\', \'tinymce\');">Wysiwyg</a>';
		echo '<input type="hidden" name="multiplecontent_box-'.$editorName.'-nonce" id="multiplecontent_box-'.$editorName.'-nonce" value="'.wp_create_nonce("multiplecontent_box-".$editorName."-nonce").'" />'."\n";  //nonce
		echo '<textarea id="multiplecontent_box-'.$editorName.'" tabindex="2" name="multiplecontent_box-'.$editorName.'" cols="158" class="theEditor" rows="15">';
			$content = get_post_meta($post->ID, '_ot_multiplecontent_box-'.$editorName , true);
			echo $content;
		echo '</textarea>';
		echo '<p>&nbsp;</p>';
	}
	
	if($editors == 0) {
		_e('There are no content blocks in this template.','trendwerk');
	}
}

function save_multiplecontent_box($id) {
	for($i=0;$i>-1;$i++) {
		if (!wp_verify_nonce($_POST['multiplecontent_box-'.$_POST['multiplecontent_box-'.$i].'-nonce'],"multiplecontent_box-".$_POST['multiplecontent_box-'.$i]."-nonce")) return $id; //nonce
		
		if($_POST['multiplecontent_box-'.$_POST['multiplecontent_box-'.$i]]) {
			
			$contents = '';
			$contents = apply_filters('content_save_pre',$_POST['multiplecontent_box-'.$_POST['multiplecontent_box-'.$i]]);
			
			if($contents) update_post_meta($id, "_ot_multiplecontent_box-".$_POST['multiplecontent_box-'.$i] , $contents);
			else delete_post_meta($id,"_ot_multiplecontent_box-".$_POST['multiplecontent_box-'.$i]);
			
		} else {
			break;
		}
	}

}

add_action('save_post', 'save_multiplecontent_box');


//front end

function the_block($blockName,$return=true) {
	if($blockName) {
		global $post;
		$content =  get_post_meta($post->ID, '_ot_multiplecontent_box-'.$blockName , true);
		if(!$return) {
			return $content;
		} else {
			echo $content;
		}
	}
}
?>