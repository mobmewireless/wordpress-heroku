<?php

/**
* This class contains some utility methods for:
* - the settings page,
* - the video setup assistant page,
* - the shortcode generation and transformation,
* - other utility tools.
*/
class SublimeVideoUtils {

  // Settings stuff
  static function authorize_form($authorize_url) {
    global $sublimevideo;

    return $sublimevideo->t('authorize_text').sprintf($sublimevideo->t('authorize_form'), $authorize_url);
  }

  static function video_default_width() {
    return (int) ($GLOBALS['content_width'] ? $GLOBALS['content_width'] : (get_option('embed_size_w') ? get_option('embed_size_w') : 500));
  }

  static function site_with_wildcard_and_path($site) {
    global $sublimevideo;

    if ($site->main_domain != '') {
      $text = "";
      if ($site->wildcard) $text .= "(*)";
      $text .= $site->main_domain;
      if ($site->path != '') $text .= "/".$site->path;
    }
    else $text = "domain not specified";

    return $text;
  }

  // Video setup assistant stuff
  static function images() {
    $args = array(
      'post_type'      => 'attachment',
      'post_status'    => 'inherit',
      'post_mime_type' => 'image',
      'numberposts'    => 50
    );
    $posts = get_posts($args);

    $images = array();
    if ($posts) {
      foreach ($posts as $image) {
        $metadata = wp_get_attachment_metadata($image->ID);

        // Ensure that the image has metadatas (especially including a thumbnail)
        if (!empty($metadata) && isset($metadata['file']) && isset($metadata['sizes']) && isset($metadata['sizes']['thumbnail']) && isset($metadata['sizes']['thumbnail']['file'])) {
          $images[] = array_merge(array('id' => $image->ID), $metadata);
        }
      }
    }

    return json_encode($images);
  }

  static function guess_type_from_url($url) {
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    if (preg_match("/mp4|m4v|mov/i", $ext)) return 'mp4';
    elseif (preg_match("/webm|og[gv]/i", $ext) ) return 'webm_ogg';
    return null;
  }

  static function videos_urls() {
    $args = array(
      'post_type'      => 'attachment',
      'numberposts'    => 100,
      'post_status'    => null,
      'post_mime_type' => 'video',
      'post_parent'    => null
    );
    $posts = get_posts( $args );
    $videos_urls = array('mp4' => array(), 'webm_ogg' => array());
    if ($posts) {
      foreach ( $posts as $video ) {
        $video_url = wp_get_attachment_url($video->ID);
        $type = self::guess_type_from_url($video_url);
        if ($type) $videos_urls[$type][] = $video_url;
      }
    }

    return $videos_urls;
  }

  static function options_from_videos_urls($videos_urls, $type) {
    $html  = "";
    $html .= "<option value=''>Choose a video source</option>";
    foreach ($videos_urls[$type] as $video_url) {
      $html .= "<option value='".$video_url."'>".str_replace(get_option( 'siteurl' )."/wp-content/uploads/", '', $video_url )."</option>";
    }

    return $html;
  }

  // Misc stuff
  static function non_blank($value) {
    return '' != $value;
  }

  static function strtolower_quoted($value) {
    return strtolower("'$value'");
  }

}

?>
