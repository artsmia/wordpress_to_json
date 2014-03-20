<?php
header("Access-Control-Allow-Origin: *");

global $wpdb;
global $post;
$objects = array();
$stories = array();
$notes = array();
$tms_hash = array();

$posts = $wpdb->get_results(
  "SELECT * FROM $wpdb->posts WHERE (post_type = 'object' || post_type = 'story') && post_status = 'publish'"
);

// Populate hash of object post IDs and TMS IDs
foreach($posts as $post){
  if($post->post_type == 'object'){
    $tms_hash[$post->ID] = get_field('tms_id', $post->ID);
  }
}
reset($posts);

foreach($posts as $post){
  setup_postdata($post);
  if($post->post_type == 'object'){
    // VIEWS
    $tms_id = get_field('tms_id');
    $rows = get_field('views');
    $views = array();
    for($n=0;$n<count($rows);$n++){
      //$views[$n]['primary'] = $rows[$n]['primary'];
      $image = $views[$n]['image'] = $rows[$n]['img_link'];
      $views[$n]['credit'] = $rows[$n]['credit'];
      $views[$n]['annotations'] = array();
      //ANNOTATIONS
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://afrx.firebaseio.com/".$image."/notes2.json");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $annotation_json = curl_exec($ch);
      $f_annotations = json_decode($annotation_json);
      curl_close($ch);

      $image_notes = array();
      $annos = $rows[$n]['annotations'];
      for($i=0;$i<count($annos);$i++){
        $views[$n]['annotations'][$i] = $image_notes[$i] = array(
          'title' => mb_convert_encoding($annos[$i]['title'], "UTF-8", "HTML-ENTITIES"),
          'description' => $annos[$i]['description'],
          'geoJSON' => $f_annotations[$i]
        );
        $attachments = $annos[$i]['attachments'];
        foreach($attachments as $attachment){
          $id = $attachment['attachment_img_link'];
          $views[$n]['annotations'][$i]['attachments'][] = array( 'image_id' => $id );
        }
      }

      $notes[$image] = $image_notes;
    }
    // CONNECTIONS
    $rels = array();
    $connected = get_posts(
      array(
        'connected_type' => 'objects_to_stories',
        'connected_items' => $post->ID,
        'nopaging' => true,
        'suppress_filters' => false,
      )
    );
    if($connected){
      foreach($connected as $connection){
        $rels[] = $connection->ID;
      }
    }
    // BASIC INFO
    $objects[$tms_id] = array(
      'id' => $tms_id,
      'title' => get_the_title(),
      'description' => get_field('description'),
      'views' => $views,
      'relatedStories' => $rels,
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
        'suppress_filters' => false,
      )
    );
    if($connected){
      foreach($connected as $connection){
        $rels[] = $tms_hash[$connection->ID];
      }
    }
    // BASIC INFO
    $stories[get_the_ID()] = array(
      'title' => get_the_title(),
      'pages' => $pages,
    );
  }
  wp_reset_postdata();
}

$json = array(
  'objects' => $objects,
  'stories' => $stories,
);

echo json_encode($json);

?>
