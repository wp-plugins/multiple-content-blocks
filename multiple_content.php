<?php
/*
Plugin Name: Multiple content blocks
Plugin URI: http://plugins.trendwerk.nl/documentation/multiple-content-blocks/
Description: Lets you use more than one content "block" on a template. You only have to insert one tag inside the template, so it's easy to use.
Version: 1.2
Author: Ontwerpstudio Trendwerk
Author URI: http://plugins.trendwerk.nl/
*/


function init_multiplecontent() {
	add_meta_box('multi_content',__('Multiple content blocks','trendwerk'),'add_multiplecontent_box','page','normal','high');
	add_meta_box('multi_content',__('Multiple content blocks','trendwerk'),'add_multiplecontent_box','post','normal','high');
}

add_action('admin_init','init_multiplecontent');


function multiplecontent_css() {
	echo '
	<style type="text/css">
		.js .theEditor, #editorcontainer #content {
			color: #000 !important;
		}
	</style>
	';
}

add_action('admin_head','multiplecontent_css');


function add_multiplecontent_box() {
	//check which template is used
	global $post;
	$fileToRead = get_template_directory_uri().'/'.$post->page_template;
	
	//read the template
	$fileToRead = strstr($fileToRead,'/themes/');
	if(substr(strrchr($fileToRead,'/'),1) == 'default') {
		$fileToRead = substr($fileToRead, 0 ,-7) . 'page.php';
	}
	if(!substr(strrchr($fileToRead,'/'),1) && $post->post_type == 'post') {
		$fileToRead .= 'single.php';
	}
	$fileToRead = validate_file_to_edit($fileToRead, $allowed_files);
	$fileToRead = get_real_file_to_edit($fileToRead);


	$f = fopen($fileToRead, 'r');
	$contents = fread($f, filesize($fileToRead));
	$contents = htmlspecialchars( $contents );
	
	//read the templates header, sidebar and footer, added in v1.1
		$headercontents = read_tag('header',$contents);
		$footercontents = read_tag('footer',$contents);
		
		//multiple sidebars, v1.2
		$amount_sidebars = substr_count($contents,'get_sidebar(');
		$nextContent = $contents;
		for($i=0;$i<$amount_sidebars;$i++) {
			$sidebarcontents .= read_tag('sidebar',$nextContent);
			$nextContent = substr(strstr($contents,'get_sidebar('),13);
		}
		
		$contents = $headercontents.$contents.$sidebarcontents.$footercontents;
		
	//check how many content field there have to be
	$editors = substr_count($contents," the_block(");
	
	$nextString = $contents;	
	for($i=0;$i<$editors;$i++) {
		//get the name from it
		$stringFirst = strstr($nextString,' the_block(');
		$stringFirst = substr($stringFirst,1);
		$stringLast = strstr($stringFirst,');');
		//remove single and double quotes
		$editorName = str_replace('\'','', str_replace('&quot;','',str_replace('the_block(','',str_replace($stringLast,'',$stringFirst))));
		$nextString = $stringLast;
		
		//add editor
		echo '<p><strong>'.ucfirst($editorName).'</strong></p>';
		echo '<input type="hidden" name="multiplecontent_box-'.$i.'" value="'.$editorName.'" />';
		
		global $current_user;
		get_currentuserinfo();
		
		if(get_usermeta($current_user->ID,'rich_editing') == 'true') {
			//leave this away when wysigwyg is disabled
			echo '<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go(\'multiplecontent_box-'.$editorName.'\', \'html\');">HTML</a><a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go(\'multiplecontent_box-'.$editorName.'\', \'tinymce\');">Wysiwyg</a>';
		}
		
		echo '<input type="hidden" name="multiplecontent_box-'.$editorName.'-nonce" id="multiplecontent_box-'.$editorName.'-nonce" value="'.wp_create_nonce("multiplecontent_box-".$editorName."-nonce").'" />'."\n";  //nonce
		echo '<textarea id="multiplecontent_box-'.$editorName.'" tabindex="2" name="multiplecontent_box-'.$editorName.'" cols="158" class="theEditor" rows="15">';
			$content = get_post_meta($post->ID, '_ot_multiplecontent_box-'.$editorName , true);
			echo apply_filters('the_editor_content', $content);
		echo '</textarea>';
		echo '<p>&nbsp;</p>';
	}
	
	if($editors == 0) {
		_e('There are no content blocks in this template.','trendwerk');
	}
}

function read_tag($tag,$contents) {
	$theTag = strstr($contents,'get_'.$tag.'(');
	//when the tag doesnt exist, return nothing, or it will take the standard file
	if(!$theTag) {
		return '';
	}
	
	$theTag = str_replace('get_'.$tag.'(','',$theTag);
	if(strpos($theTag,');') != 0) {
		$theTag = substr($theTag,1, strpos($theTag,');')-2);
	} else {
		$theTag = '';
	}
	
	$fileToRead = get_template_directory_uri().'/'; 
	$fileToRead .= $tag;
	if($theTag) {
		$fileToRead .= '-'.$theTag;
	}
	$fileToRead .= '.php';
	$fileToRead = strstr($fileToRead,'/themes/');
	$fileToRead = validate_file_to_edit($fileToRead, $allowed_files);
	$fileToRead = get_real_file_to_edit($fileToRead);


	$f = fopen($fileToRead, 'r');
	$tagContents = fread($f, filesize($fileToRead));
	$tagContents = htmlspecialchars( $tagContents );
	
	return $tagContents;
}

function save_multiplecontent_box($id) {
	for($i=0;$i>-1;$i++) {
		if (!wp_verify_nonce($_POST['multiplecontent_box-'.$_POST['multiplecontent_box-'.$i].'-nonce'],"multiplecontent_box-".$_POST['multiplecontent_box-'.$i]."-nonce")) return $id; //nonce
		
		if(isset($_POST['multiplecontent_box-'.$_POST['multiplecontent_box-'.$i]])) {
			
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
			return apply_filters('the_content', $content);
		} else {
			echo apply_filters('the_content', $content);
		}
	}
}
?>