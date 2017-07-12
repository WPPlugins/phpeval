<?php

/*
Plugin Name: PHP Eval
Plugin URI: http://code.google.com/p/phpeval/
Description: The PHPEval Plugin is a Wordpress plugin which allows users to write php code inside of their pages.
Version: 1.0.2
Author: Carsten Jonstrup
Author URI: http://www.lenius.dk
*/


//languages
if (function_exists('load_plugin_textdomain')) {
  if ( !defined('WP_PLUGIN_DIR') ) {
    load_plugin_textdomain('phpeval', str_replace( ABSPATH, '', dirname(__FILE__)));
  } else {
	load_plugin_textdomain('phpeval', false, dirname(plugin_basename(__FILE__)));
  }
}



// Hook for adding admin menus
add_action('admin_menu', 'phpeval_instructions_page_insert');

// action function for above hook
function phpeval_instructions_page_insert() {
    //Add instructions tab to top-level menu
    add_menu_page('PHPEval Instructions', 'PHP Eval', 1, __FILE__, 'phpeval_instructions_page');
    //add_submenu_page(__FILE__, 'Templates', 'Howto', 'administrator', 'PHPEval-howto', 'mt_template_settings');
    //add_submenu_page(__FILE__, 'Templates', 'Examples', 'administrator', 'PHPEval-examples', 'mt_template_settings');
}


//displays the page content for the custom Test Toplevel menu
function phpeval_instructions_page() {
?>

 <div class="wrap">

 <h2>PHP Eval</h2>
by <strong>Carsten Jonstrup</strong> of <strong>Lenius.dk</strong>
<p>
&nbsp;<a target="_blank" title="PHP Eval Release History"
href="http://code.google.com/p/phpeval/wiki/Changelog">Changelog</a>
| <a target="_blank" title="FAQ"
href="http://code.google.com/p/phpeval/wiki/FAQ">FAQ</a>
| <a target="_blank" title="PHP Eval Support Forum"
href="http://code.google.com/p/phpeval/w/list">Support</a>

<br />
</p>


<div >
<div style="float:left;background-color:white;padding: 10px 10px 10px 10px;margin-right:15px;border: 1px solid #ddd;">
<div style="width:350px;height:130px;">
	<h3>Donate</h3>
<em>If you like this plugin and find it useful, help keep this plugin free and actively developed by clicking the <strong>donate</strong> button or send me a gift from my <a href="http://amzn.com/w/3N3BXXUE6GCY" target="_blank"><strong>Amazon wishlist</strong></a>.  Also, don't forget to follow me on <a href="http://twitter.com/cjonstrup/" target="_blank"><strong>Twitter</strong></a>.</em>
</div>

<div style="float:left">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="Z9WCWVCAQLJCW">
<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="http://www.paypal.com/da_DK/i/scr/pixel.gif" width="1" height="1">
</form>
     </div>

	<a target="_blank" title="Amazon Wish List" href="http://amzn.com/w/3N3BXXUE6GCYF">
	<img src="<?php echo WP_PLUGIN_URL; ?>/phpeval/images/amazon.jpg" alt="My Amazon Wish List" /> </a>

	<a target="_blank" title="Follow us on Twitter" href="http://twitter.com/cjonstrup/">
	<img src="<?php echo WP_PLUGIN_URL; ?>/phpeval/images/twitter.jpg" alt="Follow Us on Twitter" />	</a>


</div>
<!--
<div style="float:left;background-color:white;padding: 10px 10px 10px 10px;border: 1px solid #ddd;">

<div style="width:423px;height:130px;">
	<h3>Partners</h3>
	We would also like to recommend <a href="http://www.folia.dk" target="_blank">FoliaMaptool</a> for Professional GoogleMap solutions.  They are attractive, affordable, performance optimized maps that integrate perfectly with with your website.
</div>
	<a target="_blank" title="iBlogPro" href="http://www.pagelines.com/wpthemes/">
	<img src="<?php echo WP_PLUGIN_URL; ?>/phpeval/images/iblogpro.jpg" alt="iBlogPro theme" />	</a>

	<a target="_blank" title="PageLines Themes" href="http://www.pagelines.com/wpthemes/">
	<img src="<?php echo WP_PLUGIN_URL; ?>/phpeval/images/pagelines.jpg" alt="Pagelines Themes" /> </a>

	<a target="_blank" title="WhiteHouse" href="http://www.pagelines.com/wpthemes/">
	<img src="<?php echo WP_PLUGIN_URL; ?>/phpeval/images/whitehouse.jpg" alt="WhiteHouse theme" />	</a>

</div>

</div>
-->

</div>


<?php
}

/*
Filter the content

*/
function phpeval_filter($content) {

  Global $post;
  Global $wpdb;


    if(strpos($content,'{phpeval=') === false)
      return $content;

    preg_match_all("/{phpeval=(.*)}/Uis",$content,$match,PREG_PATTERN_ORDER);

    $table = $wpdb->prefix."phpeval";
    $cg_eval = $wpdb->get_results("SELECT * FROM ".$table." where pageId =".$post->ID);
    if(is_array($cg_eval)){
      $replace = array_unique($match[0]);
      $eval    = array_unique($match[1]);
      foreach($eval as $index => $value){
       $_code[$value] = $replace[$index];
      }
      foreach($cg_eval as $key => $obj){
        $_tmp[$obj->evalName] = $obj->pageEval;
        if($_code[$obj->evalName]){
          ob_start();
          eval(stripslashes($obj->pageEval));
          $eval = ob_get_contents();
          ob_end_clean();
          $content = str_replace($_code[$obj->evalName],$eval,$content);
        }
      }
    }

    return $content;

}
add_filter('the_content','phpeval_filter');


/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', 'phpeval_add_custom_box');

/* Adds a custom section to the "advanced" Post and Page edit screens */
function phpeval_add_custom_box() {

  if( function_exists( 'add_meta_box' )) {

    add_meta_box( 'phpeval_sectionid', __( 'PHP Eval', 'phpeval' ),'phpeval_inner_custom_box', 'page', 'advanced' );

  }
}

/* Prints the inner fields for the custom post/page section */
function phpeval_inner_custom_box() {
  // Use nonce for verification

  echo '<input type="hidden" name="phpeval_noncename" id="phpeval_noncename" value="' .
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

  Global $wpdb;
  Global $post;

  $table = $wpdb->prefix."phpeval";
  $cg_eval = $wpdb->get_results("SELECT * FROM ".$table." where pageId =".$post->ID);




  echo '<div id="postcustomstuff">';

  if(count($cg_eval)>0){
  ?>
  <table id="list-table">
  <thead>
  <tr>
  <th><?php _e('Name', 'phpeval') ?> ({phpeval=<span id="test">?</span>})</th>
  <th><?php _e('Value', 'phpeval') ?></th>
  </tr>
  </thead>
  <?php
  foreach($cg_eval as $key =>$item){
    echo '<tr onmouseover="jQuery(\'#test\').html(\''.$item->evalName.'\')">';
    echo '<td class="left"><input type="text" name="phpeval['.$item->id.'][name]" value="'.$item->evalName.'" size="20" />';
    echo '<div class="submit"><input class="deletemeta" type="submit" name="phpeval['.$item->id.'][del]" value="Del"/><input class="add" type="submit" name="phpeval['.$item->id.'][update]" value="'.__('Update', 'phpeval').'"/></div></td>';
    echo '<td valign="top"><textarea id="phpeval_'.$item->id.'" onclick="" name="phpeval['.$item->id.'][code]" rows="4" cols="30">'.stripslashes($item->pageEval).'</textarea></td>';
    echo "</tr>";
  }
  echo "</table>";

  }
  ?>
  <p><strong><?php _e('Add new', 'phpeval') ?></strong></p>

  <table id=\"newmeta\">
  <thead>
  <tr>
  <th><?php _e('Name', 'phpeval') ?></th>
  <th><?php _e('Value', 'phpeval') ?></th>
  </tr>
  </thead>
  <tr>
  <td class="left" valign="top"><input type="text" name="evalName" value="" size="20" /></td>
  <td valign="top"><textarea name="pageEval" rows="4" cols="30"></textarea></td>
  </tr>
  <tr>
  <td class="submit" style="float:left" colspan="2"><input name="addeval" type="submit" value="<?php _e('Add', 'phpeval') ?>"/></td>
  </tr>
  </table>
  </div>
  <?php
}

function phpeval_activate() {
    global $wpdb;

    $table = $wpdb->prefix."phpeval";

    $structure = "CREATE TABLE ".$table." (
        id INT(9) NOT NULL AUTO_INCREMENT,
        pageId INT(9) NOT NULL,
        pageEval TEXT NOT NULL,
        evalName VARCHAR(20) NOT NULL,
	    UNIQUE KEY id (id),
        UNIQUE KEY pageId (pageId,evalName)
    );";
    $wpdb->query($structure);

    //$upgrade = "ALTER TABLE ".$table." ADD UNIQUE (pageId,evalName);";
    //$wpdb->query($upgrade);

    return "Installeret";
}

register_activation_hook( __FILE__, 'phpeval_activate' );



/* Use the save_post action to do something with the data entered */
add_action('save_post', 'phpeval_save_postdata');



/* When the post is saved, saves our custom data */
function phpeval_save_postdata( $post_id ) {

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  Global $wpdb;
  global $phpeval_flag;

  if ( !wp_verify_nonce( $_POST['phpeval_noncename'], plugin_basename(__FILE__) )) {
    return $post_id;
  }

  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    return $post_id;


  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  }

  if ($phpeval_flag == 0) {
    // OK, we're authenticated: we need to find and save the data

    $post_ID = $_POST['post_ID'];

    $table = $wpdb->prefix."phpeval";

    $evalpost = $wpdb->get_results("SELECT * FROM ".$table." WHERE pageId = ".(int)$post_ID);

    //update del actions
    if(is_array($_POST['phpeval'])){
      foreach($_POST['phpeval'] as $id =>$item){
        if(isset($item['update'])){
         $sql = "update ".$table." set evalName = '".$item[name]."' , pageEval = '".addslashes($item[code])."' where id = ".(int)$id;
         $wpdb->query($sql);
        }
        if(isset($item['del'])){
          $sql = "delete from ".$table." where id = ".(int)$id;
          $wpdb->query($sql);
        }
      }
    }

    /*Add new script block*/
    if($_POST['addeval']){
      $sql = "insert into ".$table." (pageId,evalName,pageEval) values (".$post_ID.",'".$_POST[evalName]."','".addslashes($_POST[pageEval])."')";
      $wpdb->query($sql);
    }


   }
   $phpeval_flag = 1;

   // Do something with $mydata
   return $mydata;
}
?>