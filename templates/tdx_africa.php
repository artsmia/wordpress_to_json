<?php

global $wpdb;
global $post;
$objects = array();
$stories = array();

$posts = $wpdb->get_results(
	"SELECT * FROM $wpdb->posts WHERE (post_type = 'object' || post_type = 'story') && post_status = 'publish'"
);

foreach($posts as $post){
	setup_postdata($post);
	if($post->post_type == 'object'){
		// VIEWS
		$rows = get_field('views');
		$views = array();
		for($n=0;$n<count($rows);$n++){
			//$views[$n]['primary'] = $rows[$n]['primary'];
			$views[$n]['image'] = $rows[$n]['img_link'];
			$views[$n]['credit'] = $rows[$n]['credit'];
			$views[$n]['annotations'] = array();
			//ANNOTATIONS
			$annos = $rows[$n]['annotations'];
			for($i=0;$i<count($annos);$i++){
				$views[$n]['annotations'][$i]['x'] = $annos[$i]['x'];
				$views[$n]['annotations'][$i]['y'] = $annos[$i]['y'];
				$views[$n]['annotations'][$i]['description'] = $annos[$i]['description'];
			}
		}
		// CONNECTIONS
		$rels = array();
		$connected = get_posts(
			array(
				'connected_type' => 'objects_to_stories',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'suppress_filters' => false
			)
		);
		if($connected){
			foreach($connected as $connection){
				$rels[] = $connection->ID;
			}
		}
		// BASIC INFO 
		$objects[] = array(
			'id' => get_the_ID(),
			'title' => get_the_title(),
			'description' => get_field('description'),
			'tombstone' => get_field('tombstone'),
			'views' => $views,
			'relatedStories' => $rels
		);
	}
	if($post->post_type == 'story'){
		// PAGES
		$rows = get_field('pages');
		$pages = array();
		for($n=0;$n<count($rows);$n++){
			$pages[$n]['type'] = $rows[$n]['acf_fc_layout'];
			if($rows[$n]['text']){
				$pages[$n]['text'] = $rows[$n]['text'];
			}
			if($rows[$n]['map']){
				$pages[$n]['map'] = $rows[$n]['map'];
			}
			if($rows[$n]['vid_link']){
				$pages[$n]['video'] = $rows[$n]['vid_link'];
			}
			if($rows[$n]['credit']){
				$pages[$n]['credit'] = $rows[$n]['credit'];
			}
			if($rows[$n]['img_link']){
				$pages[$n]['image'] = $rows[$n]['img_link'];
			}
			if($rows[$n]['img_link_b']){
				$pages[$n]['imageB'] = $rows[$n]['img_link_b'];
			}
		}
		// CONNECTIONS
		$rels = array();
		$connected = get_posts(
			array(
				'connected_type' => 'objects_to_stories',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'suppress_filters' => false
			)
		);
		if($connected){
			foreach($connected as $connection){
				$rels[] = $connection->ID;
			}
		}		
		// BASIC INFO
		$stories[] = array(
			'id' => get_the_ID(),
			'title' => get_the_title(),
			'pages' => $pages,
			'relatedObjects' => $rels
		);
	}
	wp_reset_postdata();
}

$json = array(
	'objects' => $objects,
	'stories' => $stories
);

echo json_encode($json);

?>
