<?php
/*
Plugin Name: Amp Landing Pages
Plugin URI: https://www.ampwptools.com/amp-landing-pages/
Description: The AMP Landing Pages plugin allows you to easily create native AMP landing pages for lighting fast page loads and high conversions.
Version:  1.0.4
Author: TACG
Author URI: https://www.ampwptools.com/
License: GPL2
*/
?>
<?PHP defined('ABSPATH') || die(); ?>
<?php 
date_default_timezone_set("America/Los_Angeles");
class TA_wp_amplp{
	
	/**
	 * Basic construct method native to all PHP classes. I sets up all project and class variables then runs the extends_construct() method for the project if it is included.
	 * @requires : get_site_template_url, last_page_in_url, add_action, runit
	 */
	function __construct($prefix='',$dir=''){
		if($dir==''){$dir = dirname(__FILE__);}
		if(getenv('REMOTE_ADDR')=="127.0.0.1"){$splode = explode('\\',$dir);}else{$splode = explode('/',$dir);}
		$this->self['TITLE'] = 'Amp Landing Pages';
		$this->self['VERSION'] = '1.0.4';
		$this->self['DIR'] = $dir.'/';
		$dex = count($splode)-1;
		$this->self['FOLDER'] = $splode[$dex];
		if(strstr($this->self['DIR'],'wp-content/plugins/') or strstr($this->self['DIR'],'wp-content\\plugins\\')){
			$this->self['PATH'] = plugins_url($this->self['FOLDER']).'/';
		}else{
			$this->self['PATH'] = $this->get_site_template_url().'/';
		}
		if($prefix==''){$prefix = $this->last_page_in_url($this->self['DIR']);}
		$this->self['prefix'] = $prefix;	
		$this->self['BAD_CHARS'] = array('&#149;','<br />','<hr />','<','>','&','\\','"',"'",';');
		$this->self['NEW_CHARS'] = array('(_BL_)','(_BR_)','(_HR_)','(_LT_)','(_GT_)','(_AM_)','(_BS_)','(_DQ_)','(_SQ_)','(_SC_)');
		$this->self['BAD_CHARS_64'] = array('&#149;','<br />','<hr />','<','>','&','\\','"',"'",';','/','+');
		$this->self['NEW_CHARS_64'] = array('(_BL_)','(_BR_)','(_HR_)','(_LT_)','(_GT_)','(_AM_)','(_BS_)','(_DQ_)','(_SQ_)','(_SC_)','(_FS_)','(_PL_)');
		$this->self['TURNOFFS'] = array();
		$this->self['FONT_AWESOME_URL'] = 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
		$this->self['FONT_AWESOME_VERSION'] = '4.7.0';
		$datahook = 'wp';
		if(is_admin()){
			global $pagenow;
			if($pagenow=='post.php'){
				$datahook = 'add_meta_boxes';
			}
		}
		$this->add_action($datahook,'wp_document_data',1,1);
		$this->self['REGISTEREDCSS'] = '';
		$this->runit('extends_construct');
	}
	
	/**
	 * Add a Wordpress action from the local class by first checking for availability as a function/method.
	 * @requires : get_action
	 * @usage : __construct
	 */
	function add_action($act,$bct=''){
		if($bct!=''){
			if(is_array($bct)){
				$fct = $bct;
			}else{
				$fct = $this->get_action($bct);
			}
		}else{
			$fct = $this->get_action($act);
		}
		if($fct!=''){
			if($act=='save_post'){
				add_action($act,$fct,10,2);
			}else{
				add_action($act,$fct);
			}
		}
	}
	
	/**
	 * Take registered meta fields and add a Meta Box to the WP post editor.
	 * @requires : get_value, method_call, echo_post_editor_adv, echo_post_editor_side
	 * @usage : init
	 */
	function add_post_editor(){
		$how = '';
		$screen = get_current_screen();
		$type = $screen->post_type;
		if($type=='post' or $type=='page' or $type=='recipe'){$how = $type;}
		global $pagenow;
		if($pagenow=='post-new.php' and $this->get_value($_GET,'post_type')=='recipe'){$how = 'recipe';}		
		$mtitle = $this->get_value($this->self,'TITLE');
		$adv = false;
		$side = false;
		$arr = $this->self['meta_list'];
		if(count($arr)>0){
			foreach($arr as $name=>$data){
				$show = $this->get_value($data,'show');
				if($show!=''){
					$showit = $this->method_call($show);
					if($showit){}else{continue;}
				}
				$types = $this->get_value($data,'post_type');
				if(is_array($types)){
					$isit = false;
					foreach($types as $ptype){
						if($type==$ptype){$isit = true;}
					}
				}else{
					if($type==$types){$isit = true;}
				}
				if($isit){
					$mloc = $this->get_value($data,'location');
					if($mloc=='side'){
						$side = true;
					}else{
						$adv = true;
					}
				}
			}
		}
		if(!function_exists('gutenberg_init') and $side==true){$adv = true;$side = false;}
		if($adv){
			if($how!=''){add_meta_box($this->self['prefix'].'_layout_box',$mtitle,array(&$this,'echo_post_editor_adv'),$how,'advanced','core');}
		}
		if($side){
			if($how!=''){add_meta_box($this->self['prefix'].'_layout_box_side',$mtitle,array(&$this,'echo_post_editor_side'),$how,'side','core');}
		}
	}
	
	/**
	 * Allow outside CSS to be added to header through the AMP CSS tag.
	 * @requires : get_value, url_get_contents
	 * @usage : alp_head_css, amp_pages
	 */
	function add_registered_css($params=NULL){
		if($params==NULL){
			return $this->self['REGISTEREDCSS'];
		}else{
			$RAT = '';
			$com = $this->get_value($params,'command');
			$pass = $this->get_value($params,'pass');
			if($com=='register style'){
				$RAT = $pass;
			}else if($com=='register stylesheet'){
				$RAT = $this->url_get_contents($pass);
			}
			if($RAT!=''){
				$this->self['REGISTEREDCSS'] .= $RAT;
			}
		}
		return '';
	}
	
	/**
	 * Bridge for communication between JS and this PHP class. Only runs methods named/designated specifically for receiving these communications.
	 * @requires : get_value, runit
	 */
	function ajax_communication(){
		$command = $this->get_value($_POST,'command');
		if(substr($command,0,5)=='ajax_'){
			if(method_exists($this,$command)){
				$this->runit($command,$_POST);
			}
		}
	}
	
	/**
	 * Run the demo content import process for the current page. It assigns a placeholder featured image for the hero banner, demo content in Gutenberg blocks, and assigns the AMP Landing Page template (amplp.php).
	 * @requires : get_value, is_alp, import_demo_image, if_gutenberg
	 */
	function ajax_import_demo_content($params){
		$post_id = $this->get_value($params,'postid');
		if(!$this->is_alp($post_id)){die();}
		$calink = '';
		$startlink = '';
		$hero = $this->import_demo_image($post_id,$this->self['PATH'].'assets/images/AMPLP-hero-placeholder-image.jpg');
		$heroid = $this->get_value($hero,'id');
		if($heroid){set_post_thumbnail($post_id,$heroid);}
		$open = $this->import_demo_image($post_id,$this->self['PATH'].'assets/images/AMPLP-opening-placeholder-image.jpg');
		$opimgid = $this->get_value($open,'id');
		$opimg = $this->get_value($open,'src');
		$vid = $this->import_demo_image($post_id,$this->self['PATH'].'assets/images/AMPLP-video-placeholder-image.jpg');
		$vidimgid = $this->get_value($vid,'id');
		$vidimg = $this->get_value($vid,'src');
		$tb = $this->import_demo_image($post_id,$this->self['PATH'].'assets/images/AMPLP-testimonial-placeholder-image.jpg');
		$tbimgid = $this->get_value($tb,'id');
		$tbimg = $this->get_value($tb,'src');
		$soc = $this->import_demo_image($post_id,$this->self['PATH'].'assets/images/AMPLP-social-proof-logo.jpg');
		$soclogoid = $this->get_value($soc,'id');
		$soclogo = $this->get_value($soc,'src');
		$content = '
<!-- wp:paragraph {"align":"center"} -->
<p style="text-align:center">Add your key benefit(s) here. Tell your visitor why they should care about your product/service. Remember to think about the visitor\'s perspective - <em><strong>WIFM (What\'s in it for me)</strong></em></p>
<!-- /wp:paragraph -->
<!-- wp:button {"align":"center"} -->
<div class="wp-block-button aligncenter"><a class="wp-block-button__link" href="'.$calink.'">Type Your Call to Action </a></div>
<!-- /wp:button -->
<!-- wp:tacg/ms {"mediaID":'.$opimgid.'} -->
<div class="wp-block-tacg-ms">
	<div class="tbimg"><img src="'.$opimg.'" class="wp-image-'.$opimgid.'" /></div>
	<div class="tbcon">
		<h3 class="tbttl">Add some key selling points:</h3>
		<h4 class="tbsttl"></h4>
		<p class="tbcont"><strong>﻿Feature/Benefit 1</strong><br/><strong>Feature/Benefit 2</strong><br/><strong>Feature/Benefit 3</strong><br/>Tell them some more WIFM</p>
	</div>
	<div class="cf"></div>
</div>
<!-- /wp:tacg/ms -->
<!-- wp:tacg/ms {"mediaID":'.$vidimgid.',"alignment":"right"} -->
<div class="wp-block-tacg-ms right">
	<div class="tbimg"><img src="'.$vidimg.'" class="wp-image-'.$vidimgid.'" /></div>
	<div class="tbcon">
		<h3 class="tbttl">Close the deal</h3>
		<h4 class="tbsttl">Images and video draw in users</h4>
		<p class="tbcont">Give some proof showing why your product/service is so great. Reinforce it with a strong image or video that helps to make the benefits very clear. </p>
	</div>
	<div class="cf"></div>
</div>
<!-- /wp:tacg/ms -->
<!-- wp:tacg/tb {"mediaID":'.$tbimgid.'} -->
<div class="wp-block-tacg-tb" style="background-color:;color:">
	<div class="tbimg"><img src="'.$tbimg.'" class="wp-image-'.$tbimgid.'" /></div>
	<div class="tbcon">
		<h3 class="tbttl">Joe Schmoe</h3>
		<h4 class="tbsttl">Satisfied Customer</h4>
		<div class="qt fa fa-quote-left"></div>
		<hr class="qt hr" style="border-color:" />
		<div class="qt fa fa-quote-right"></div>
		<div class="cf"></div>
		<p class="tbcont">Add a quote from a satisfied customer. Testimonials work great.<br/>~ This landing page rocks!</p>
	</div>
	<div class="cf"></div>
</div>
<!-- /wp:tacg/tb -->
<!-- wp:columns {"columns":4,"className":"logo-row"} -->
<div class="wp-block-columns has-4-columns logo-row">
	<!-- wp:image {"id":'.$soclogoid.',"layout":"column-1"} -->
	<figure class="wp-block-image layout-column-1"><img src="'.$soclogo.'" alt="" class="wp-image-'.$soclogoid.'" /></figure>
	<!-- /wp:image -->
	<!-- wp:image {"id":'.$soclogoid.',"layout":"column-2"} -->
	<figure class="wp-block-image layout-column-2"><img src="'.$soclogo.'" alt="" class="wp-image-'.$soclogoid.'" /></figure>
	<!-- /wp:image -->
	<!-- wp:image {"id":'.$soclogoid.',"layout":"column-3"} -->
	<figure class="wp-block-image layout-column-3"><img src="'.$soclogo.'" alt="" class="wp-image-'.$soclogoid.'" /></figure>
	<!-- /wp:image -->
	<!-- wp:image {"id":'.$soclogoid.',"layout":"column-4"} -->
	<figure class="wp-block-image layout-column-4"><img src="'.$soclogo.'" alt="" class="wp-image-'.$soclogoid.'" /></figure>
	<!-- /wp:image -->
</div>
<!-- /wp:columns -->
<!-- wp:button {"align":"center"} -->
<div class="wp-block-button aligncenter"><a class="wp-block-button__link" href="'.$startlink.'">GET STARTED - IT\'S FREE</a></div>
<!-- /wp:button -->
<!-- wp:paragraph {"align":"center"} -->
<p style="text-align:center">Consider adding copyright and link to a privacy policy and/or legal disclaimers.</p>
<!-- /wp:paragraph -->
';
		if(!$this->if_gutenberg()){$content = preg_replace('/<!--(.|\s)*?-->/', '', $content);}
		$my_post = array('ID'=>$post_id,'post_content'=>$content);
		wp_update_post( $my_post );
		die();
	}
	
	/**
	 * @requires : is_alp, docdata, current_page_url, get_value, get_site_base_url, taxes
	 */
	function alp_admin_head_scripts(){
		if(!$this->is_alp()){return '';}
		$RAT = '';
		$RAT .= '<script type="text/javascript">';
		$data = $this->docdata();
		$url = $this->current_page_url();
		$pagetype = $this->get_value($data,'pagetype');
		$arctype = $this->get_value($data,'arctype');
		$pageid = $this->get_value($data,'pageid');
		if($arctype=='category'){$type = 'category';}else{$type = $pagetype;}
		$base = $this->get_site_base_url().'/';
		$slug = $this->get_value($data,'slug');
		$RAT .= 'var wppb_basepath = "'.$base.'";';
		$RAT .= 'var wppb_url = "'.$url.'";';
		$RAT .= 'var wppb_slug = "'.$slug.'";';
		$RAT .= 'var wppb_type = "'.$type.'";';
		$RAT .= 'var wppb_arctype = "'.$arctype.'";';
		if(is_front_page() or is_home()){
			if($type=='page'){
				$RAT .= 'var wppb_postid = "'.$pageid.'";';
			}else{
				$RAT .= 'var wppb_postid = "0";';
			}
		}else{
			$RAT .= 'var wppb_postid = "'.$pageid.'";';
		}
		$RAT .= 'var wppb_termid = "'.$this->taxes(array('com'=>'id')).'";';
		$RAT .= 'var ajaxurl = "'.admin_url('admin-ajax.php').'";';
		$RAT .= 'var frame_path = "'.$this->self['PATH'].'";';
		$RAT .= 'var h2c_enabled = true;';
		$RAT .= '</script>';
		echo $RAT;
	}
	
	/**
	 * Used by template to insert AMP-friendly schema markups ionto the head.
	 * @requires : generate_amp_markups
	 */
	function alp_amp_markups(){
		echo $this->generate_amp_markups();
	}
	
	/**
	 * Used by functions.php to register size choices into customizer settings.
	 * @requires : get_size_choices
	 */
	function alp_get_size_choices($how=''){
		return $this->get_size_choices($how);
	}
	
	/**
	 * Used by template to import, shortcode, minify and enhance the raw CSS inserted into the AMP-style tag... along with a meta tag reporting CSS content size in KB. AMP pages can only be styled using this tag, and must fall below 50KB to be compliant.
	 * @requires : url_get_contents, scrape_between, add_registered_css, process_stylesheet, generate_base_css, get_customizer_setting, get_attachment_data_by_src, get_value, cleanup_stylesheet
	 */
	function alp_head_css(){
		$file = plugins_url('style.css',__FILE__);
		$sheet = $this->url_get_contents($file);
		if(strstr($sheet,'START_STYLEHEET***********************/')){
			$sheet = $this->scrape_between($sheet.'[ENDD]','START_STYLEHEET***********************/','[ENDD]');
		}
		/* Insert Gutenberg stylesheet before plugin stylesheet, currently 20kb after minification. */
		$gutstyle = plugins_url('script/gutenberg_style.css',__FILE__);
			$guts = $this->url_get_contents($gutstyle);
			if($guts!=''){$sheet = $guts.$sheet;}
		/* Add CSS from outside sources before processing through shortcode system. */
		$sheet .= $this->add_registered_css();
		/* Process sheet for shortcodes. */
		$sheet = $this->process_stylesheet($sheet);
		$sheet .= $this->generate_base_css();
		$logoimg = $this->get_customizer_setting('amplp_topnavlogo');
		$wid = 0;
		$logowid = 0;
		if($logoimg!=''){
			$imgdata = $this->get_attachment_data_by_src($logoimg);
			$wid = $this->get_value($imgdata,'width');
			$hit = $this->get_value($imgdata,'height');
			$mhit = $this->get_customizer_setting('amplp_topnavheight');
			if($mhit!='auto'){
				$bhit = intval(str_replace('px','',$mhit));
				if($hit>$bhit){
					$nrat = $bhit/$hit;
					$hit = $bhit;
					$wid = floor($wid*$nrat);
				}
			}
			$logowid = $wid+20;
		}
		if($wid>0){$sheet .= '.topnav nav{width:calc(100% - '.$logowid.'px);}';}
		/* Cleanup spaces, line breaks and remove comments. */
		$sheet = $this->cleanup_stylesheet($sheet);
		echo '<style amp-custom>';
		echo $sheet;
		echo '</style>';
		$len = mb_strlen($sheet, '8bit')/1000;
		echo '<meta name="cssweight" content="'.$len.'KB">';
	}
	
	/**
	 * Used by template to include all necessary AMP scripts in the head.
	 * @requires : docdata, get_value, use_nav_menu, register_amp_module, amp_video_providers, amp_social_providers, get_customizer_setting, extract_wp_embeds, extract_gutenberg_embeds, registered_amp_modules, generate_amp_module_script
	 */
	function alp_head_scripts(){
		$doc = $this->docdata();
		$content = $this->get_value($doc,'content');
		$content = trim($content);
		if($this->use_nav_menu('amplp-top') or $this->use_nav_menu('amplp-mobile')){
			$this->register_amp_module('sidebar');
		}
		if(function_exists('amp_woo') && amp_woo('is woo')){
			$this->register_amp_module('form');
			$this->register_amp_module('mustache');
			if(function_exists('wc') && is_product()){
				$this->register_amp_module('selector');
				$this->register_amp_module('carousel');
				$this->register_amp_module('lightbox');
			}
		}
		$providers = $this->amp_video_providers();
		$provembed = $this->amp_social_providers();
		$fb = $this->get_customizer_setting('amplp_fbcode');
		$ga = $this->get_customizer_setting('amplp_gacode');
		$gt = $this->get_customizer_setting('amplp_gtcode');
		$gw = $this->get_customizer_setting('amplp_gwcode');
		if($fb!='' or $ga!='' or $gt!='' or $gw!=''){$this->register_amp_module('analytics');}
		$wpbeds = $this->extract_wp_embeds($content);
		$gutbeds = $this->extract_gutenberg_embeds($content);
		$embeds = array_merge($wpbeds,$gutbeds);
		if(count($embeds)>0){
			foreach($embeds as $data){
				$provider = $this->get_value($data,'provider');
				if(isset($providers[$provider]) or isset($provembed[$provider])){$this->register_amp_module($provider);}
			}
		}
		echo '<link rel="stylesheet" type="text/css" href="'.$this->self['FONT_AWESOME_URL'].'">';
		echo '<script async src="https://cdn.ampproject.org/v0.js"></script>';
		$mods = $this->registered_amp_modules();
		if(count($mods)>0){
			foreach($mods as $mod=>$vex){
				echo $this->generate_amp_module_script($mod);
			}
		}
	}
	
	/**
	 * Used by template to add a class that constrains the hero banner width to fit the content max width setting.
	 * @requires : get_customizer_checkbox
	 */
	function alp_hero_class(){
		if($this->get_customizer_checkbox('amplp_heromaxed')){echo 'rap';}
		return '';
	}
	
	/**
	 * Used by template to insert the hero title if one is assigned, then use the page title if not.
	 * @requires : docdata, get_value, get_meta
	 */
	function alp_hero_title(){
		$doc = $this->docdata();
		$pageid = $this->get_value($doc,'pageid');
		return $this->get_meta($pageid,'amplpHeroTitle');
	}
	
	/**
	 * Used by template to check for hero banner usage/availability
	 * @requires : docdata, get_value, get_thumb
	 */
	function alp_include_hero_banner(){
		$doc = $this->docdata();
		$pageid = $this->get_value($doc,'pageid');
		$img = $this->get_thumb($pageid);
		if($img==''){return false;}
		return true;
	}
	
	/**
	 * Used by template to insert AMP-friendly meta tags ionto the head.
	 * @requires : current_page_url, get_markup, delimitit
	 */
	function alp_meta_tags(){
		$url = $this->current_page_url();
		$meline = $this->get_markup('mainEntityOfPage','prop','inline');
		echo '<link'.$meline.' rel="canonical" href="'.$url.'" />';
		$metadesc = '';
		if(defined('WPSEO_FILE')){$metadesc = WPSEO_Frontend::get_instance()->metadesc(false);}
		$keywords = '';
		$list = get_the_tag_list();
		$tags = get_tags();
		if($tags!='' and count($tags)>0){
			foreach($tags as $tag){
				$tname = $tag->name;
				$keywords = $this->delimitit($keywords,', ',$tname);
			}
		}
		if($keywords!=''){echo '<meta name="keywords" content="'.$keywords.'" />';}
	}
	
	/**
	 * Used by template to insert a logo into the navbar.
	 * @requires : get_customizer_setting, generate_logo
	 */
	function alp_topnav_logo($pass=''){
		$RAT = '';
		$img = $this->get_customizer_setting('amplp_topnavlogo');
		if($img!=''){
			$homeurl = home_url();
			$RAT = $this->generate_logo($img,'useinline',$homeurl,true,'mnav');
		}
		if($pass=='echo'){
			echo $RAT;
		}else{
			return $RAT;
		}
	}
	
	/**
	 * To meet Google AMP compliance we must convert as many inline styles to custom classes as possible, then filter out invalid tags, attributes and remaining inline styles.
	 * @requires : amp_styles_to_classes, strip_tag_attribute, strip_tag, amp_filter_image
	 * @usage : wp_the_content, amp_filter_embeds
	 */
	function amp_filter($content,$pass=''){
		$content = $this->amp_styles_to_classes($content);// convert inline styles to classes where possible
		$content = $this->strip_tag_attribute($content,'style');// remove inline styling for AMP validation
		$content = $this->strip_tag($content,'iframe');// remove injected iframes from content
		$content = $this->amp_filter_image($content,$pass);// convert img tags into amp-img tags
		return $content;
	}
	
	/**
	 * Convert WP embeds and gutenberg embeds found in page/string content into AMP video and embed tags.
	 * @requires : amp_video_providers, amp_social_providers, extract_wp_embeds, get_value, generate_video_module, is_user_interface, generate_placeholder, if_gutenberg, extract_gutenberg_embeds, htmlout, extract_embed_data_from_url, amp_filter
	 * @usage : wp_the_content
	 */
	function amp_filter_embeds($content){
		$content = trim($content);
		$ampd = true;
		$providers = $this->amp_video_providers();
		$provembed = $this->amp_social_providers();
		$embeds = array();
		$embeds = $this->extract_wp_embeds($content);
		if(count($embeds)>0){
			foreach($embeds as $dex=>$data){
				$prov = $this->get_value($data,'provider');
				$url = $this->get_value($data,'url');
				$find = $this->get_value($data,'find');
				$type = $this->get_value($data,'type');
				if($prov!=''){
					if($type=='video'){
						$video = $this->get_value($data,'embedid');
						$rep = $this->generate_video_module(array('provider'=>$prov,'video'=>$video),$ampd);
					}else{
						$atts = $this->get_value($data,'atts');
						$wid = $this->get_value($data,'width');
						$hit = $this->get_value($data,'height');
						$layout = '';if($prov!='pinterest'){$layout = ' layout="responsive"';}
						if($this->is_user_interface()){
							$rep = $this->generate_placeholder('embed','Placeholder for `amp-'.$prov.'` module using: '.$atts.'');
						}else{
							$rep = '<amp-'.$prov.' class="ampfeed"'.$layout.' width="'.$wid.'" height="'.$hit.'"'.$atts.'><div fallback><p>There was an error with this AMP module. It may be a configuration problem, or your browser may not support this functionality.</p></div></amp-'.$prov.'>';
						}
					}
					$content = str_replace($find,$rep,$content);
				}
			}
		}
		if($this->if_gutenberg()){
			$RAT = '';
			$embeds = $this->extract_gutenberg_embeds($content);
			if(count($embeds)>0){
				foreach($embeds as $dex=>$dats){
					$block = $this->get_value($dats,'block');
					$html = $this->htmlout($this->get_value($dats,'html'));
					$raw = $this->htmlout($this->get_value($dats,'raw'));
					if($block=='embed' or $block=='core-embed'){
						$prov = $this->get_value($dats,'provider');
						$url = $this->get_value($dats,'url');
						$data = $this->extract_embed_data_from_url($url);
						if($prov==''){
							$prov = $this->get_value($data,'provider');
						}
						if($prov==''){continue;}
						if(!isset($providers[$prov]) and !isset($provembed[$prov])){continue;}
						if($prov=='soundcloud'){
							$RAT .= $this->generate_placeholder('soundcloud','The Soundcloud AMP module requires a Track ID, Wordpress only allows the embed of a URL. As a result, we cannot currently display Soundcloud embeds.','"soundcloud" URL embeds are not supported by AMP');
						}else if($prov!=''){
							$type = $this->get_value($data,'type');
							if($type=='video'){
								$video = $this->get_value($data,'embedid');
								$vid = $this->generate_video_module(array('provider'=>$prov,'video'=>$video),$ampd);
								$to = str_replace($url,$vid,$html);
								$RAT .= str_replace($html,$to,$raw);
							}else{
								$atts = $this->get_value($data,'atts');
								$wid = $this->get_value($data,'width');
								$hit = $this->get_value($data,'height');
								$layout = '';if($prov!='pinterest'){$layout = ' layout="responsive"';}
								if($this->is_user_interface()){
									$mod = $this->generate_placeholder('embed','Placeholder for `amp-'.$prov.'` module using: '.$atts.'');
								}else{
									$mod = '<amp-'.$prov.' class="ampfeed"'.$layout.' width="'.$wid.'" height="'.$hit.'"'.$atts.'><div fallback><p>There was an error with this AMP module. It may be a configuration problem, or your browser may not support this functionality.</p></div></amp-'.$prov.'>';
								}
								$to = str_replace($url,$mod,$html);
								$RAT .= str_replace($html,$to,$raw);
							}
						}
					}else{
						$RAT .= $raw;
					}
				}
			}
			$content = $RAT;
		}
		$content = $this->amp_filter($content,'content');
		$content = do_shortcode($content);
		return $content;
	}
	
	/**
	 * To meet Google AMP compliance we must replace img tags with amp-img tags, and populate them with AMP display parameters, image size and available srcset images (wp sized images, thumbs... etc).
	 * @requires : scrape_between, get_attachment_data_by_src, get_value, process_url_ssl, get_image_srcset, is_advanced, pinit_wrapper
	 * @usage : wp_post_thumbnail_html, amp_filter
	 */
	function amp_filter_image($content,$pass=''){
		$content = preg_replace("@(<img.*?\/>)@i", "$1</img>",$content);// close open image tags - step 1
		$content = str_replace('/></img>','></img>',$content);// close image tags - step 2
		$exp = explode('<img ',$content);
		$ct = 0;
		foreach($exp as $part){
			if($ct==0){$ct = 1;continue;}
			$rawtag = $this->scrape_between('[ST]'.$part,'[ST]','</img>');
			$tag = str_replace("'",'"',$rawtag);
			$imgw = $this->scrape_between($tag,'width="','"');
			$imgh = $this->scrape_between($tag,'height="','"');
			$imgc = $this->scrape_between($tag,'class="','"');
			$img = $this->scrape_between($tag,'src="','"');
			if($imgw=='' and $imgh==''){
				$imgw = $this->scrape_between($tag,'data-wid="','"');
				$imgh = $this->scrape_between($tag,'data-hit="','"');	
			}
			$tagadd = '';
			if($imgw=='' or $imgh==''){
				$dats = $this->get_attachment_data_by_src($img);
				$imgw = $this->get_value($dats,'width');
				$imgh = $this->get_value($dats,'height');
				if(!strstr($tag,'width="')){$tagadd .= ' width="'.$imgw.'"';}
				if(!strstr($tag,'height="')){$tagadd .= ' height="'.$imgh.'"';}
			}
			if($imgw!='' and $imgh!=''){
				$imgw = intval(str_replace('px','',$imgw));
				$imgh = intval(str_replace('px','',$imgh));
			}
			$find = '<img '.$rawtag.'</img>';
			$tag = $this->process_url_ssl($tag);
			$layout = 'responsive';
			if($pass=='fixed'){$layout = 'fixed';}
			$srcset = '';
			if(!strstr($tag,' srcset="')){$srcset = $this->get_image_srcset($img,'src');}
			$rep = '<amp-img layout="'.$layout.'"'.$srcset.' '.$tagadd.$tag.'</amp-img>';
			if($pass=='content'){
				$rwid = 6;
				if(is_numeric($imgw)){$rwid = floor($imgw/50);}
				if($rwid<1){$rwid = 1;}
				if($rwid>40){$rwid = 40;}
				$omc = ' W'.$rwid;
				if(strstr($imgc,'alignleft')){
					$omc .= ' alignleft';
				}else if(strstr($imgc,'alignright')){
					$omc .= ' alignright';
				}else if(strstr($imgc,'aligncenter')){
					$omc .= ' aligncenter';
				}
				if(method_exists($this,'is_advanced')){if($this->is_advanced()){$rep = $this->pinit_wrapper($rep,$tag);}}
				$rep = '<div class="omg'.$omc.'">'.$rep.'</div>';
			}
			$content = str_replace($find,$rep,$content);
		}
		return $content;
	}
	
	/**
	 * Central hub for handling function calls from templates and external PHP files.
	 * @requires : is_alp, get_value, use_nav_menu, add_registered_css, method_call
	 */
	function amp_pages($command='',$pass=''){
		if($command=='is alp'){return $this->is_alp($pass);}
		if($command=='is alp editor'){
			$pageid = $this->get_value($_GET,'post');
			if($pageid==''){return false;}
			global $pagenow;
			if($pagenow=='post.php' and $this->is_alp($pageid)){return true;}
			return false;
		}
		if($command=='use nav menu'){return $this->use_nav_menu($pass);}
		if($command=='register style' or $command=='register stylesheet'){
			if($pass!=NULL){$this->add_registered_css(array('command'=>$command,'pass'=>$pass));}
			return '';
		}
		$com = 'alp_'.str_replace(' ','_',$command);
		$fct = $this->method_call($com,$pass);
		return $fct;
	}
	
	/**
	 * Return an array of AMP supported social embed providers.
	 * @usage : alp_head_scripts, amp_filter_embeds, extract_embed_data_from_url
	 */
	function amp_social_providers(){
		return array('facebook'=>'Facebook','imgur'=>'Imgur','instagram'=>'Instagram','pinterest'=>'Pinterest','reddit'=>'Reddit','twitter'=>'Twitter');
	}
	
	/**
	 * Retrieve an array of replacements for converting inline styles to classes.
	 * @usage : amp_styles_css, amp_styles_to_classes
	 */
	function amp_style_replacements(){
		$replacements = array();
		$replacements['text-align'] = 'talign';
		$replacements['text-decoration'] = 'deco-';
		$replacements['font-size'] = 'fsize-';
		$replacements['font-weight'] = 'fwgt-';
		$replacements['font-family'] = 'ffam-';
		$replacements['color'] = 'clr-';
		$replacements['background-color'] = 'bgclr-';
		$replacements['border-color'] = 'bdclr-';
		$replacements['margin'] = 'marg-';
		$replacements['margin-top'] = 'margt-';
		$replacements['margin-right'] = 'margr-';
		$replacements['margin-bottom'] = 'margb-';
		$replacements['margin-left'] = 'margl-';
		$replacements['padding'] = 'padd-';
		$replacements['padding-top'] = 'padt-';
		$replacements['padding-right'] = 'padr-';
		$replacements['padding-bottom'] = 'padb-';
		$replacements['padding-left'] = 'padl-';
		$replacements['float'] = 'float-';
		$replacements['background-image'] = 'bgimg-';
		return $replacements;
	}
	
	/**
	 * Convert raw style data gathered from conversions, modules and filters into CSS for inclusion in the header.
	 * @requires : get_value, amp_style_replacements
	 * @usage : generate_base_css
	 */
	function amp_styles_css($k,$v){
		if($k=='background-image'){
			$vs = explode('[BGD]',$v);
			$dex = $this->get_value($vs,0);
			$vex = $this->get_value($vs,1);
			if($dex!='' and $vex!=''){
				return '.bgimg-'.$dex.'{background-image:'.$vex.';}';
			}else{
				return '';
			}
		}
		$v = trim($v);
		$v = str_replace('#','',$v);
		$vc = $v;
		$va = $v;
		if($k=='font-family'){
			$vc = str_replace(',','-',$vc);
			$va = str_replace('-',',',$va);
		}
		$vc = str_replace(' ','_',$vc);
		$va = str_replace('_',' ',$va);
		$val = '';
		$replacements = $this->amp_style_replacements();
		$rv = $this->get_value($replacements,$k);
		if($rv!=''){
			$ck = '';
			if($k=='color' or $k=='background-color' or $k=='border-color'){$ck = '#';}
			$val = '.'.$rv.$vc.'{'.$k.':'.$ck.$va.';}';
		}
		return $val;
	}
	
	/**
	 * Convert inline styles to classes. Some are covered by base stylesheet, the rest are generated as custom classes.
	 * @requires : amp_style_replacements, scrape_between, get_value, delimitit
	 * @usage : amp_filter, generate_base_css
	 */
	function amp_styles_to_classes($in,$ask=''){
		$replacements = $this->amp_style_replacements();
		$handler = array();
		$out = $in;
		$bgs = 0;
		$RAT = '';
		$arr = explode('<',$in);
		foreach($arr as $arp){
			$brr = explode('>',$arp);
			$tag = $brr[0];
			if(strstr($tag,'style="')){
				$styles = array();
				$style = $this->scrape_between($tag,'style="','"');
				$classes = '';
				$parts = explode(';',$style);
				if(count($parts)>0){
					foreach($parts as $part){
						$prt = trim($part);
						$prt = str_replace('http://','[HTTP]',$prt);
						$prt = str_replace('https://','[HTTPS]',$prt);
						$keys = explode(':',$prt);
						$key = $keys[0];
						$val = trim($this->get_value($keys,1));
						if(isset($replacements[$key])){
							if($key=='background-image'){
								$styles[$key] = $bgs;
								$val = str_replace('[HTTP]','http://',$val);
								$val = str_replace('[HTTPS]','https://',$val);
								$handler[] = array('background-image'=>$bgs.'[BGD]'.$val);
								$bgs += 1;
							}else{
								$styles[$key] = $val;
								$handler[] = $styles;
							}
						}
					}
					foreach($styles as $k=>$v){
						$vcon = $replacements[$k];
						$vc = trim($v);
						if($k=='font-family'){$vc = str_replace(',','-',$vc);}
						$vc = str_replace(' ','_',$vc);
						$vc = str_replace('#','',$vc);
						$vcon .= $vc;
						$classes = $this->delimitit($classes,' ',$vcon);
					}
				}
				if(strstr($tag,'class="')){
					$scrap = $this->scrape_between($tag,'class="','"');
					if($scrap!=''){$classes = $this->delimitit($classes,' ',$scrap);}
				}
				$newtag = preg_replace('!style="(.*?)"!s','',$tag);
				$newtag = preg_replace('!class="(.*?)"!s','',$newtag);
				if(substr($newtag,-1,1)=='/'){
					$bstr = substr($newtag,0,-1);
					$newtag = $bstr.' class="'.$classes.'" /';
				}else{
					$newtag .= ' class="'.$classes.'"';
				}
				$newtag = '<'.trim($newtag).'>';
				$oldtag = '<'.$tag.'>';
				$out = str_replace($oldtag,$newtag,$out);
			}
		}
		if($ask=='styles'){return $handler;}
		return $out;
	}
	
	/**
	 * Return an array of AMP supported video providers.
	 * @usage : alp_head_scripts, amp_filter_embeds, extract_embed_data_from_url, generate_video_module
	 */
	function amp_video_providers(){
		return array('dailymotion'=>'Dailymotion','facebook'=>'Facebook','vimeo'=>'Vimeo','youtube'=>'YouTube');
	}
	
	/**
	 * Attach an image file to the media library and return either the URL or attachment ID for the image.
	 * @requires : get_value, canonize
	 * @usage : media_library_import_image
	 */
	function attach_image($url,$post_id,$desc='',$how=''){
		if ( !function_exists('media_handle_upload') ) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}
		$tmp = download_url( $url );
		if( is_wp_error( $tmp ) ){
			return 'download failed: '.$url;
		}
		$file_array = array();
		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
		$filename = basename($this->get_value($matches,0));
		if($filename==''){return 'empty filename';}
		$splode = explode('.',$filename);
		$spcnt = count($splode);
		$fdex = $spcnt-1;
		$filetype = strtolower($splode[$fdex]);
		$filename = $this->canonize($desc).'.'.$filetype;
		$file_array['name'] = $filename;
		$file_array['tmp_name'] = $tmp;
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error($id) ) {
			return 'sideload failed:';
		}
		if($how=='src' or $how=='url'){
			$src = wp_get_attachment_url( $id );
			if ( is_wp_error( $src ) ){
				return 'attachment url from id ('.$id.') failed:';
			}
			return $src;
		}
		return $id;
	}
	
	/**
	 * Process a string to make it URL friendly.
	 * @usage : attach_image
	 */
	function canonize($txtin){
		$txtin = str_replace('.','',$txtin);// remove dots
		$txtin = preg_replace('/\s\s+/', ' ', $txtin);// remove excess whitespace
		$txtin = preg_replace('/[^a-zA-Z0-9 \-\/\_]/','',$txtin);// remove all punctuation except -_/
		$txtin = trim(strtolower($txtin));// trim removes beginning and ending whitespace
		$txtin = str_replace(" ","-",$txtin);// replace blanks with dashes
		return str_replace("-/","/",$txtin);// remove excess dashes that fall before slashes
	}
	
	/**
	 * Probe the server to see if SSL is in use.
	 * @usage : process_url_ssl
	 */
	function check_for_ssl(){
		$ssl = false;
		if(isset($_SERVER['HTTPS'])){	
			if(strtolower($_SERVER['HTTPS'])=='on'){$ssl = true;}
			if($_SERVER['HTTPS']=='1'){$ssl = true;}
		}
		if(isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT']=='443')){$ssl = true;}
		if(isset($_ENV['HTTPS']) && ($_ENV['HTTPS']=='on')){$ssl = true;}
		if(!empty($_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO']) && ($_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO']=='https')){$ssl = true;}
		if(!empty($_SERVER['HTTP_CF_VISITOR']) && ($_SERVER['HTTP_CF_VISITOR']=='https')){$ssl = true;}
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO']=='https')){$ssl = true;}
		if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL']=='on')){$ssl = true;}
		return $ssl;
	}
	
	/**
	 * Minify the CSS before including it in the header by removing everything that is not absolutely ncessary. This is an important step in AMP compliance due to the 50KB limit for the header amp-style tag.
	 * @usage : alp_head_css
	 */
	function cleanup_stylesheet($sheet){
		$sheet = preg_replace('/\s*(?!<\")\/\*[^\*]+\*\/(?!\")\s*/','',$sheet);// remove any remaining notes and unused css
		$sheet = str_replace(', ',',',$sheet);// remove space after commas in css lists
		$sheet = preg_replace("/\r\n|\r|\n/",'',$sheet);// remove line breaks
		$sheet = preg_replace("/\t/",'',$sheet);// remove tab indentations
		return $sheet;
	}
	
	/**
	 * Add settings and controls to the Customizer from registered data.
	 * @requires : get_value
	 * @usage : init_customizer_settings
	 */
	function create_customizer_setting($id,$params){
		global $wp_customize;
		$type = $this->get_value($params,'type');
		$sec = $this->get_value($params,'section');
		$title = $this->get_value($params,'title');
		$def = $this->get_value($params,'default');
		$prio = $this->get_value($params,'priority');
		$choices = $this->get_value($params,'choices');
		$desc = $this->get_value($params,'description');
		$live = $this->get_value($params,'live',false);
		$theme = $this->self['prefix'];
		if($id!='' and $type!='' and $sec!=''){
			$transport = 'refresh';
			if($live==true){$transport = 'postMessage';}
			if($type=='color'){
				$wp_customize->add_setting($id,array('default' => $def , 'transport' => $transport , 'sanitize_callback' => 'sanitize_hex_color'));
			}else{
				$wp_customize->add_setting($id,array('default' => $def , 'transport' => $transport , 'sanitize_callback' => 'sanitize_text_field'));
			}
			$pass = array('label'=>$title,$theme,'section'=>$sec,'settings'=>$id);
			if($prio!=''){$pass['priority'] = $prio;}
			if($choices!=''){$pass['choices'] = $choices;}
			if($desc!=''){$pass['description'] = $desc;}
			if($type=='color'){
				$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,$id,$pass));
			}else if($type=='image'){
				$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,$id,$pass));
			}else{
				$pass['type'] = $type;
				$wp_customize->add_control($id,$pass);
			}
		}
	}
	
	/**
	 * Get the page URL across a variety of server types.
	 * @usage : alp_admin_head_scripts, alp_meta_tags, generate_analytics_module, domain_from_url, last_page_in_url
	 */
	function current_page_url($comm=''){
		$pageURL = 'http';
		if( isset($_SERVER["HTTPS"]) ){
			if ($_SERVER["HTTPS"] == "on"){$pageURL .= "s";}
		}
		$pageURL .= "://";
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		if($comm=='base'){
			$exp = explode('?',$pageURL);
			return $exp[0];
		}
		return $pageURL;
	}
	
	/**
	 * Adds several interval options that only get filtered into WP if they are registered.
	 * @requires : has_cron
	 * @usage : init
	 */
	function custom_cron_schedules($schedules){
		$arr = array();
		if($this->has_cron()){
			foreach($this->self['cron_list'] as $fct=>$type){
				$arr[$type] = true;
			}
		}
		if(isset($arr['minutely'])){$schedules['minutely'] = array('interval' => 60,'display' => 'Every Minute');}
		if(isset($arr['weekly'])){$schedules['weekly'] = array('interval' => 60480,'display' => 'Every Week');}
		if(isset($arr['monthly'])){$schedules['monthly'] = array('interval' => 259200,'display' => 'Every 30 Days');}
		if(isset($arr['3hours'])){$schedules['3hours'] = array('interval' => 1080,'display' => 'Every 3 Hours');}
		if(isset($arr['6hours'])){$schedules['6hours'] = array('interval' => 2160,'display' => 'Every 6 Hours');}
		if(isset($arr['5minutes'])){$schedules['5minutes'] = array('interval' => 300,'display' => 'Every 5 Minutes');}
		return $schedules;
	}
	
	/**
	 * @usage : wp
	 */
	function debug_mode(){
		$this->self['DEBUG_MODE_STATE'] = true;
	}
	
	/**
	 * Add a string to a string with a delimiter if it is not blank.
	 * @usage : alp_meta_tags, amp_styles_to_classes, get_image_srcset, queryit, taxes
	 */
	function delimitit($str,$delim=',',$val=''){
		if($str!=''){$str .= $delim;}
		$str .= $val;
		return $str;
	}
	
	/**
	 * Return an array of data for the current page/post/category gathered during class initiation.
	 * @requires : get_value, wp_document_data
	 * @usage : alp_admin_head_scripts, alp_head_scripts, alp_hero_title, alp_include_hero_banner, generate_amp_markups, generate_analytics_module, is_alp, wp, wp_the_content, generate_base_css, process_stylesheet_if
	 */
	function docdata($var=null,$def=''){
		if(isset($this->self['DOCDATA'])){
			if($var==null){return $this->self['DOCDATA'];}
			return $this->get_value($this->self['DOCDATA'],$var,$def);
		}else{
			$this->wp_document_data();
			if(isset($this->self['DOCDATA'])){
				if($var==null){return $this->self['DOCDATA'];}
				return $this->get_value($this->self['DOCDATA'],$var,$def);
			}
		}
		return array();
	}
	
	/**
	 * @requires : current_page_url
	 * @usage : generate_analytics_module
	 */
	function domain_from_url($url=NULL){
		if($url===NULL){$url = $this->current_page_url();}
		$dats = parse_url($url);
		return $dats['host'];
	}
	
	/**
	 * Second step in adding a Meta Box to the WP post editor, this step fills that box.
	 * @requires : get_value, get_meta, generate_field, runit, using_admin_interface, generate_admin_interface
	 * @usage : echo_post_editor_adv, echo_post_editor_side
	 */
	function echo_post_editor($post,$pass,$eloc=''){
		wp_nonce_field( $this->self['prefix'].'_post_nonce', $this->self['prefix'].'_nonce_field' );
		$atts = array('media_buttons'=>false);
		$arr = $this->self['meta_list'];
		if(count($arr)>0){
			$functs = array();
			foreach($arr as $name=>$data){
				$type = $this->get_value($data,'type');
				$title = $this->get_value($data,'name');
				$desc = $this->get_value($data,'desc');
				$class = $this->get_value($data,'class');
				if($eloc=='adv' or $eloc=='side'){
					$mloc = $this->get_value($data,'location','adv');
					if(function_exists('gutenberg_init')){if($eloc!=$mloc){continue;}}
				}
				$classtxt = '';
				if($class!=''){$classtxt = ' class="'.$class.'"';}
				if($type=='function'){
					$fct = $this->get_value($data,'function');
					if($fct!=''){if(!in_array($fct,$functs)){$functs[] = $fct;}}
				}else{
					echo '<fieldset style="margin-top:10px;"'.$classtxt.'>';
					$val = $this->get_meta(get_the_ID(),$name);
					if($type=='html'){
						echo '<legend>'.$title.'</legend>';
						wp_editor($val,$name,$atts);
					}else{
						$data['value'] = $val;
						if($type=='checkbox'){
							echo '<span style="float:left;">'.$this->generate_field($data).'</span>';
							echo '<legend style="float:left;">'.$title.'</legend>';
							echo '<div style="clear:both;display:block;width:100%;"></div>';
						}else{
							echo $this->generate_field($data);
							echo '<legend>'.$title.'</legend>';
						}
					}
					if($desc!=''){echo '<div style="clear:both;box-shadow: inset 0 0 5px 0 #999;padding:5px 10px;margin:5px 0 0 0;">'.$desc.'</div>';}
					echo '</fieldset>';
				}
			}
			if(count($functs)>0){
				foreach($functs as $dex=>$fct){
					$this->runit($fct);
				}
			}
		}		
		if($this->using_admin_interface()){echo $this->generate_admin_interface();}
	}
	
	/**
	 * Middle step for handling meta boxes in side/inspector.
	 * @requires : echo_post_editor
	 * @usage : add_post_editor
	 */
	function echo_post_editor_adv($post,$pass){
		$this->echo_post_editor($post,$pass,'adv');
	}
	
	/**
	 * Middle step for handling meta boxes in side/inspector.
	 * @requires : echo_post_editor
	 * @usage : add_post_editor
	 */
	function echo_post_editor_side($post,$pass){
		$this->echo_post_editor($post,$pass,'side');
	}
	
	/**
	 * @requires : htmlin
	 * @usage : generate_facebook_remarketing_code
	 */
	function encode_text($input,$how='b64'){ 
		if($how=='b64'){
			return base64_encode($input);// this instance of base64 is used for DATA-URI in Google AMP modules, and storage of site settings as archives for backup/import
		}else{
			return $this->htmlin($input);
		}
	}
	
	/**
	 * Extension of base class init() method... unique to each project.
	 * @requires : register_notice, wp_the_content, wp_post_thumbnail_html, wp
	 */
	function extends_init(){
		if(class_exists('wfConfig')){
			$wfwall = wfConfig::get('ajaxWatcherDisabled_front');
			if(!$wfwall){
				$wferr = 'Wordfence Firewall is active on the "Front" end, the javascript file it loads will cause Google AMP Validation to fail.<br />You can turn this setting off in: <a href="'.admin_url().'admin.php?page=WordfenceWAF" target="_blank">wp-admin ~ Wordfence ~ Firewall</a> ~ uncheck "Front" under "Monitor Background Requests for False Positives:"<br />AMP does not need to validate while logged in, so you may leave "Admin Panel" checked.';
				$this->register_notice($wferr,'warning');
			}
		}
		/* Filter the content for AMP compliance. */
		add_filter('the_content',array(&$this,'wp_the_content'),10,1);
		/* Filter the post thumbnail to make it AMP compliant. */
		add_filter('post_thumbnail_html',array(&$this,'wp_post_thumbnail_html'),10,1);
		add_filter('wp',array(&$this,'wp'),10,1);
	}
	
	/**
	 * Filter through a URL for video/social provider information, returns provider, embed ID, embed type, URL and replacement text.
	 * @requires : amp_video_providers, amp_social_providers, get_value, last_page_in_url, scrape_between
	 * @usage : amp_filter_embeds, extract_wp_embeds
	 */
	function extract_embed_data_from_url($str=''){
		if($str==''){return array();}
		$url = $str;
		$prov = '';
		$type = '';
		if(strstr($str,'youtube.com') or strstr($str,'youtu.be')){$prov = 'youtube';}
		if(strstr($str,'vimeo.com')){$prov = 'vimeo';}
		if(strstr($str,'dailymotion.com')){$prov = 'dailymotion';}
		if(strstr($str,'hulu.com')){$prov = 'hulu';}
		if(strstr($str,'facebook.com')){$prov = 'facebook';}
		if(strstr($str,'twitter.com')){$prov = 'twitter';}
		if(strstr($str,'instagram.com')){$prov = 'instagram';}
		if(strstr($str,'imgur.com')){$prov = 'imgur';}
		if(strstr($str,'reddit.com')){$prov = 'reddit';}
		if(strstr($str,'pinterest.com')){$prov = 'pinterest';}
		if($prov!=''){
			$isvideo = false;
			$issocial = false;
			$providers = $this->amp_video_providers();
			$provembed = $this->amp_social_providers();
			if(isset($providers[$prov])){
				if($prov=='facebook'){
					if(strstr($str,'/videos/')){
						$isvideo = true;
					}else{
						$issocial = true;
					}
				}else{
					$isvideo = true;
				}
			}else if(isset($provembed[$prov])){
				$issocial = true;
			}
			if($isvideo){
				$type = 'video';
			}else if($issocial){
				$type = 'social';
			}
			if(substr($str,-1,1)=='/'){$str = substr($str,0,-1);}
			$vars = '';
			if(strstr($str,'?')){
				$exp = explode('?',$str);
				$str = $exp[0];
				$vars = $this->get_value($exp,1);
			}
			$exp = explode('/',$str);
			$exps = count($exp)-1;
			$code = $exp[$exps];
			if($code==''){$code = $exp[($exps-1)];}
			$eid = $code;
			if($prov=='dailymotion'){
				$exp = explode('_',$code);
				$eid = $exp[0];
			}
			if($prov=='youtube'){
				$eid = str_replace('watch?v=','',$code);
				$eid = str_replace('v=','',$eid);
				if($eid=='watch'){
					$eid = str_replace('v=','',$vars);
				}
			}
			if($prov=='hulu'){
				if(strstr($code,'eid=')){
					$p1 = explode('?',$code);
					foreach($p1 as $pt){
						$pts = explode('&',$pt);
						foreach($pts as $ptk){
							if(substr($ptk,0,4)=='eid='){
								$eid = str_replace('eid=','',$ptk);
							}
						}
					}
				}
			}
			if($prov=='facebook'){$eid = $this->last_page_in_url($str);}
			if($prov=='reddit'){$eid = $this->scrape_between($str,'comment/','/');}
			if($prov=='imgur'){$eid = $this->last_page_in_url($str);}
			if($prov=='instagram'){$eid = $this->last_page_in_url($str);}
			if($prov=='pinterest'){$eid = $this->last_page_in_url($str);}
			$wid = 800;
			$hit = 300;
			$atts = '';
			if($prov=='pinterest'){
				if(strstr($url,'/pin/')){
					$wid = 'auto';
					$atts = ' data-do="embedPin" data-url="'.$url.'"';
				}else{
					$wid = 100;
					$hit = 40;
					$atts = ' data-do="buttonFollow" data-href="'.$url.'" data-label="Follow"';
				}
			}else if($prov=='reddit'){
				$atts = ' data-embedtype="post"  data-src="'.$url.'"';
			}else if($prov=='imgur'){
				$atts = ' data-imgur-id="'.$eid.'"';
			}else if($prov=='instagram'){
				$atts = ' data-shortcode="'.$eid.'"';
			}else if($prov=='facebook'){
				$atts = ' data-href="'.$url.'"';
			}else if($prov=='twitter'){
				$atts = ' data-tweetid="'.$url.'"';
			}
			return array('provider'=>$prov,'type'=>$type,'embedid'=>$eid,'url'=>$url,'block'=>'embed','atts'=>$atts,'width'=>$wid,'height'=>$hit);
		}
		return array();
	}
	
	/**
	 * Break down the page/post content and return an array of Gutenberg blocks used in the content.
	 * @requires : scrape_between, get_value, htmlin
	 * @usage : alp_head_scripts, amp_filter_embeds
	 */
	function extract_gutenberg_embeds($str){
		$RET = array();
		$exp = explode('<!-- wp:',$str);
		if(count($exp)>1){
			foreach($exp as $part){
				$raw = '<!-- wp:'.$part;
				$node = $this->scrape_between('[ST]'.$part,'[ST]',' -->');
				$dats = '';
				$block = $node;
				if(strstr($node,'{')){
					$block = $this->scrape_between('[ST]'.$node,'[ST]',' {');
					$dats = $this->scrape_between($node,'{','}');
				}
				if($block!=''){
					$html = $this->scrape_between($part,' -->','<!-- /wp:'.$block.' -->');
					$data = json_decode('{'.$dats.'}');
					$prov = '';
					if(strstr($block,'/')){
						$exp = explode('/',$block);
						$block = $this->get_value($exp,0);
						$prov = $this->get_value($exp,1);
					}
					$data->block = $block;
					$data->provider = $prov;
					$data->html = $this->htmlin($html);
					$data->raw = $this->htmlin($raw);
					$RET[] = $data;
				}
			}
		}
		return $RET;
	}
	
	/**
	 * Filter through a string for video provider information, returns provider, video ID and URL.
	 * @requires : get_value
	 * @usage : generate_video_module
	 */
	function extract_video_data($video=''){
		if($video==''){return array();}
		$url = $video;
		$rep = '';
		$prov = '';
		if(strstr($video,'youtube.com') or strstr($video,'youtu.be')){$prov = 'youtube';}
		if(strstr($video,'vimeo.com')){$prov = 'vimeo';}
		if(strstr($video,'dailymotion.com')){$prov = 'dailymotion';}
		if(strstr($video,'facebook.com')){$prov = 'facebook';}
		if(strstr($video,'hulu.com')){$prov = 'hulu';}
		if($prov!=''){
			if(substr($video,-1,1)=='/'){$video = substr($video,0,-1);}
			$vars = '';
			if(strstr($video,'?')){
				$vids = explode('?',$video);
				$video = $vids[0];
				$vars = $this->get_value($vids,1);
			}
			$vids = explode('/',$video);
			$vidc = count($vids)-1;
			$vidcode = $vids[$vidc];
			if($vidcode==''){$vidcode = $vids[($vidc-1)];}
			$vid = $vidcode;
			if($prov=='dailymotion'){$exp = explode('_',$vidcode);$vid = $exp[0];}
			if($prov=='youtube'){
				$vid = str_replace('watch?v=','',$vidcode);
				$vid = str_replace('v=','',$vid);
				if($vid=='watch'){
					$vid = str_replace('v=','',$vars);
				}
			}
			if($prov=='hulu'){
				if(strstr($vidcode,'eid=')){
					$p1 = explode('?',$vidcode);
					foreach($p1 as $pt){
						$pts = explode('&',$pt);
						foreach($pts as $ptk){
							if(substr($ptk,0,4)=='eid='){
								$vid = str_replace('eid=','',$ptk);
							}
						}
					}
				}
			}
			if($prov=='facebook'){$vid = $video;}
			return array('provider'=>$prov,'video'=>$vid,'url'=>$url);
		}
		return array();
	}
	
	/**
	 * Parse through page/post content looking for WP Embed codes for videos that need to be converted to AMP modules.
	 * @requires : scrape_between, extract_embed_data_from_url
	 * @usage : alp_head_scripts, amp_filter_embeds
	 */
	function extract_wp_embeds($str){
		$RET = array();
		$exp = explode('[embed]',$str);
		if(count($exp)>1){
			foreach($exp as $dex=>$part){
				if($dex==0){continue;}
				$url = $this->scrape_between('[SS]'.$part,'[SS]','[/embed]');
				if($url!=''){
					$data = $this->extract_embed_data_from_url($url);
					$data['find'] = '[embed]'.$url.'[/embed]';
					$RET[] = $data;
				}
			}
		}
		if(strstr($str,'youtube.com') or strstr($str,'youtu.be') or strstr($str,'pinterest.com')){
			$start = '<a ';
			$exp = explode($start,$str);
			if(count($exp)>0){
				foreach($exp as $dex=>$part){
					if($dex==0){continue;}
					$url = $this->scrape_between($part,'href="','"');
					if($url!=''){
						$data = $this->extract_embed_data_from_url($url);
						$find = '<a '.$this->scrape_between('[SS]'.$part,'[SS]','</a>').'</a>';
						$data['find'] = $find;
						$RET[] = $data;
					}
				}
			}
		}
		return $RET;
	}
	
	/**
	 * Generate schema markups required by AMP for inclusion within the document head tag.
	 * @requires : docdata, get_value
	 * @usage : alp_amp_markups
	 */
	function generate_amp_markups(){
		$data = $this->docdata();
		$pageid = $this->get_value($data,'pageid');
		$pagetype = $this->get_value($data,'pagetype');
		$jsontype = $this->get_value($data,'jsontype');
		$url = $this->get_value($data,'url');
		$headline = $this->get_value($data,'headline');
		$desc = $this->get_value($data,'desc');
		$contype = $this->get_value($data,'contype');
		$title = $this->get_value($data,'title');
		$sitename = $this->get_value($data,'sitename');
		$published = $this->get_value($data,'published');
		$modified = $this->get_value($data,'modified');
		$author = $this->get_value($data,'author');
		$authorid = $this->get_value($data,'authorid');
		$publisher = $this->get_value($data,'publisher');
		$publishertype = $this->get_value($data,'publishertype');
		$logo = $this->get_value($data,'logo');
		$logow = $this->get_value($data,'logow');
		$logoh = $this->get_value($data,'logoh');
		$img = $this->get_value($data,'img');
		$imgw = $this->get_value($data,'imgw');
		$imgh = $this->get_value($data,'imgh');
		$images = $this->get_value($data,'',array());
		$category = $this->get_value($data,'category');
		$RAT = '';
		$useog = true;
		$usetwit = true;
		$yoast = false;
		$yoasted = false;
		if(defined('WPSEO_FILE')){
			$yoast = true;
			$options = WPSEO_Options::get_option('wpseo_social');
			if($options['opengraph']===true){$useog = false;}
			if($options['twitter']===true){$usetwit = false;}
		}
		if(is_home() or is_front_page()){if($yoast==true){$yoasted = true;}}
		if($useog==true){
			$RAT .= '<meta property="og:locale" content="en_US" />';
			$RAT .= '<meta property="og:type" content="'.$contype.'" />';
			$RAT .= '<meta property="og:title" content="'.$title.'" />';
			$RAT .= '<meta property="og:url" content="'.$url.'" />';
			$RAT .= '<meta property="og:site_name" content="'.$sitename.'" />';
			if($img!=''){$RAT .= '<meta property="og:image" content="'.$img.'" />';}
			if(count($images)>0){
				foreach($images as $image){
					$RAT .= '<meta property="og:image" content="'.$image.'" />';
				}
			}
		}
		if($usetwit==true){
			$RAT .= '<meta name="twitter:card" content="summary" />';
			$RAT .= '<meta name="twitter:title" content="'.$title.'" />';		
			if($img!=''){$RAT .= '<meta name="twitter:image" content="'.$img.'" />';}
		}
		if(is_single()){
			if($category!=''){$RAT .= '<meta property="article:section" content="'.$category.'" />';}
			if($published!=''){$RAT .= '<meta property="article:published_time" content="'.$published.'+00:00" />';}
			if($modified!=''){
				$RAT .= '<meta property="article:modified_time" content="'.$modified.'+00:00" />';
				$RAT .= '<meta property="og:updated_time" content="'.$modified.'+00:00" />';
			}
		}
		$RAT .= '<script type="application/ld+json">';		
		$RAT .= '{';
		$RAT .= '"@context": "http://schema.org"';
		$RAT .= ',"@type": "'.$jsontype.'"';
		$RAT .= ',"mainEntityOfPage": "'.$url.'"';
		$RAT .= ',"headline": "'.$headline.'"';
		if($published!=''){$RAT .= ',"datePublished": "'.$published.'"';}
		if($modified!=''){$RAT .= ',"dateModified": "'.$modified.'"';}
		$RAT .= ',"description": "'.$desc.'"';
		if($author!=''){$RAT .= ',"author": { "@type": "Person","name": "'.$author.'"}';}
		if($publisher!=''){
			$RAT .= ',"publisher": {"@type": "'.$publishertype.'","name": "'.$publisher.'"';
			if($logo!=''){$RAT .= ',"logo": {"@type": "ImageObject","url": "'.$logo.'","width": "'.$logow.'","height": "'.$logoh.'"}';}
			$RAT .= '}';
		}
		if($img!=''){$RAT .= ',"image": {"@type": "ImageObject","url": "'.$img.'","height": "'.$imgh.'","width": "'.$imgw.'"}';}
		$RAT .= '}';		
		$RAT .= '</script>';
		return $RAT;
	}
	
	/**
	 * Generate the script tag for a Google AMP module. Some modules have specific attributes they require, and some will require version updates as Google changes them.
	 * @usage : alp_head_scripts
	 */
	function generate_amp_module_script($mod){
		if($mod!=''){
			$vers = '0.1';$elm = 'element';
			if($mod=='sticky-ad'){$vers = '1.0';}
			if($mod=='mustache'){$elm = 'template';$vers = '0.2';}
			return '<script async custom-'.$elm.'="amp-'.$mod.'" src="https://cdn.ampproject.org/v0/amp-'.$mod.'-'.$vers.'.js"></script>';
		}
		return '';
	}
	
	/**
	 * Generate the AMP Analytics module/script for a suported analytics provider.
	 * @requires : generate_facebook_remarketing_code, domain_from_url, get_site_base_url, docdata, get_value, current_page_url, get_customizer_setting
	 * @usage : wp_the_content
	 */
	function generate_analytics_module($src,$aid=''){
		if($aid==''){return '';}
		if($src=='facebook'){return $this->generate_facebook_remarketing_code($aid);}
		$domain = $this->domain_from_url($this->get_site_base_url());			
		$pagedata = $this->docdata();
		$published = $this->get_value($pagedata,'published');
		if($src=='googletagmanager'){
			$srcurl = urlencode($this->current_page_url('base'));
			return '<amp-analytics config="https://www.googletagmanager.com/amp.json?id='.$aid.'&gtm.url='.$srcurl.'" data-credentials="include"></amp-analytics>';
		}
		$gcon = true;
		if($src=='googleadwords'){
			$gwtype = $this->get_customizer_setting('amplp_gwtype');
			if($gwtype=='remarketing'){$gcon = false;}
		}
		$adds = '';
		$RAT = '';
		$RAT .= '<amp-analytics type="'.$src.'"'.$adds.'>';
			$RAT .= '<script type="application/json">';
				$RAT .= '{';
				$RAT .= '"vars": {';
					if($src=='googleanalytics'){$RAT .= '"account":"'.$aid.'"';}
					if($src=='googleadwords'){
						$RAT .= '"googleConversionId": "'.$aid.'"';
						if($gcon){
							$RAT .= ',"googleConversionLanguage": "en"';
							$RAT .= ',"googleConversionFormat": "3"';
							$RAT .= ',"googleConversionLabel": "sampleLabel"';
							$RAT .= ',"googleRemarketingOnly": "false"';
						}else{
							$RAT .= ',"googleRemarketingOnly": "true"';
						}
					}
					$RAT .= '}';
					$RAT .= ',"triggers": {';
						if($src=='googleadwords'){
							$RAT .= '"onVisible": {';
								$RAT .= '"on": "visible",';
								if($gcon){
									$RAT .= '"request": "conversion"';
								}else{
									$RAT .= '"request": "remarketing"';
								}
							$RAT .= '}';
						}else{
							$RAT .= '"defaultPageview": {';
								$RAT .= '"on": "visible"';
								$RAT .= ',"request": "pageview"';
							$RAT .= '}';	
						}
					$RAT .= '}';
				$RAT .= '}';
			$RAT .= '</script>';
		$RAT .= '</amp-analytics>';
		return $RAT;
	}
	
	/**
	 * Convert inline styles to usable classes and add support for responsive image sizes through classes in 50px incerements.
	 * @requires : docdata, get_value, amp_styles_to_classes, amp_styles_css
	 * @usage : alp_head_css
	 */
	function generate_base_css(){
		$doc = $this->docdata();
		$pageid = $this->get_value($doc,'pageid');
		$pagetype = $this->get_value($doc,'pagetype');
		$arctype = $this->get_value($doc,'arctype');
		/* Start a stack of styles to be managed - preventing redundancy. */
		$stack = array();
		/* Gather styles onto the stack for Page/Post/Category Content conversions. */
		$content = $this->get_value($doc,'content');
		if($content!=''){
			$styles = $this->amp_styles_to_classes($content,'styles');
			if(count($styles)>0){
				foreach($styles as $style){
					foreach($style as $k=>$v){
						$val = $this->amp_styles_css($k,$v);
						if($val!=''){if(!isset($stack[$val])){$stack[$val] = $val;}}
					}
				}
			}
		}
		/* Add W1 through W40 as width in 50px increments supporting up to 2000px images */
		$fwx = '';
		for($i=1;$i<=40;$i++){
			$fwx .= '.W'.$i.'{width:'.($i*50).'px;}';
			$fwx .= '.FW'.$i.'{width:calc(100% - '.($i*50).'px);}';
		}
		$stack[] = '@media (min-width:900px){'.$fwx.'}';
		/* Process stack into CSS. */
		$RAT = '';
		if(count($stack)>0){foreach($stack as $css){$RAT .= $css;}}
		return $RAT;
	}
	
	/**
	 * Generate the script necessary to run a Facbook Pixel/Remarketing code in AMP.
	 * @requires : encode_text
	 * @usage : generate_analytics_module
	 */
	function generate_facebook_remarketing_code($id=''){
		if($id==''){return '';}
		$pixel = 'https://www.facebook.com/tr?id='.$id.'&ev=PageView&noscript=1';
		return '<amp-pixel src="'.$pixel.'"></amp-pixel>';
		$code = '';
		$code .= "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod? n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n; n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '".$id."'); fbq('track', 'PageView'); </script>";
		$code .= '<noscript><img height="1" width="1" src="https://www.facebook.com/tr?id='.$id.'&ev=PageView&noscript=1"/></noscript>';
		$enc = $this->encode_text($code,'b64');
		$uri = 'data:text/html;base64,'.$enc;
		return '<amp-iframe id="fbrcode" width="1" height="1" sandbox="allow-scripts" frameborder="0" src="'.$uri.'" scrolling="no"></amp-iframe>';
	}
	
	/**
	 * Simplified form field method, what more to say?
	 * @requires : get_value
	 * @usage : echo_post_editor
	 */
	function generate_field($data,$wrap=true){
		$RAT = '';
		$type = $this->get_value($data,'type');
		$name = $this->get_value($data,'name');
		$fid = $this->get_value($data,'id');
		$val = $this->get_value($data,'value');
		$filter = $this->get_value($data,'filter');
		$include = $this->get_value($data,'include');
		if(get_option($fid)!=''){$val = stripslashes(get_option($fid));}
		$filter = '';
		$filter .= $include;
		if($type=='hidden'){
			$RAT .= '<input name="'.$fid.'" id="'.$fid.'" type="'.$type.'" value="'.$val.'" />';
		}else if($type=='button'){
			$RAT .= '<input id="'.$fid.'" name="'.$fid.'" type="submit" value="'.$name.'" class="button" />';
		}else if($type=='text'){
			$RAT .= '<input name="'.$fid.'" id="'.$fid.'" type="'.$type.'" value="'.$val.'" '.$filter.' />';
		}else if($type=='number'){
			$RAT .= '<input name="'.$fid.'" id="'.$fid.'" type="'.$type.'" step="any" value="'.$val.'" '.$filter.' />';
		}else if($type=='textarea'){
			$RAT .= '<textarea name="'.$fid.'" id="'.$fid.'" type="'.$type.'" '.$filter.'>'.$val.'</textarea> ';
		}else if($type=='select'){
			$opts = $this->get_value($data,'options',array());
			if(count($opts)>0){
				$RAT .= '<select name="'.$fid.'" id="'.$fid.'"'.$include.'>';
				foreach($opts as $dex=>$option){
					$RAT .= '<option';
					if($dex==$val){
						$RAT .= ' selected="selected"';
					}
					$RAT .= ' value="'.$dex.'">'.$option.'</option>';
				}
				$RAT .= '</select>';
			}
		}else if($type=='checkbox'){
			if($val!=''){
				$checked = ' checked="checked"';
			}else{
				$checked = "";
			}
			$RAT .= '<input name="'.$fid.'" id="'.$fid.'"type="'.$type.'"  value="'.$val.'"'.$checked.''.$include.' />';
		}else if($type=='pass'){
			$RAT .= $this->get_value($data,'pass');
		}
		return $RAT;
	}
	
	/**
	 * Create the logos used in the banner and navigation bar based on site settings. They need to be handled for AMP compatibility and location specifics.
	 * @requires : inline_markup, get_attachment_data_by_src, get_value, get_customizer_setting, process_url_ssl
	 * @usage : alp_topnav_logo
	 */
	function generate_logo($img,$layout,$homeurl,$ampd,$how=''){
		$isinline = $this->inline_markup('yes');
		$iline = $this->inline_markup(' itemprop="logo" itemscope itemtype="http://schema.org/ImageObject"');
		if($layout=='useinline'){
			$isinline = 'yes';
			$iline = ' itemprop="logo" itemscope itemtype="http://schema.org/ImageObject"';
		}
		$sizes = '';
		$sized = '';
		if($img!=''){
			$imgdata = $this->get_attachment_data_by_src($img);
			$wid = $this->get_value($imgdata,'width','auto');
			$hit = $this->get_value($imgdata,'height',100);
			$issvg = false;
			if(strstr(strtolower($img),'.svg')){$issvg = true;}
			$resp = 'responsive';
			if($issvg){$sized = 'min-height:'.$hit.'px;';}
			if($how=='mnav'){
				if($hit>48){
					$nrat = 48/$hit;
					$hit = 48;
					$wid = floor($wid*$nrat);
					$sized = 'max-width:'.$wid.'px;max-height:'.$hit.'px;';
				}
			}else if($layout=='sidebar'){
				if($wid>200){
					$nrat = 200/$wid;
					$wid = 200;
					$hit = floor($hit*$nrat);
				}
			}else{
				$mhit = $this->get_customizer_setting('header_maxheight');
				if($mhit!='auto'){
					$bhit = intval(str_replace('px','',$mhit));
					if($hit>$bhit){
						$nrat = $bhit/$hit;
						$hit = $bhit;
						$wid = floor($wid*$nrat);
					}
				}
			}
			if(!$issvg){$sizes = ' sizes="(max-width: '.$wid.'px) calc(100vw - 60px), '.$wid.'px"';}
		}
		$id = 'logo';
		$clss = '';
		if($how=='mnav'){$clss .= 'nblogo';$id = 'navlogo';}
		if($img!=''){$clss .= ' haslogo';}
		$RAT = '';
		if($ampd){
			if($img!=''){
				$RAT .= '<div id="'.$id.'" class="logo '.$clss.'"'.$iline.'>';
					$img = $this->process_url_ssl($img);
					$RAT .= '<a href="'.$homeurl.'"><amp-img class="logoimg" src="'.$img.'" width="'.$wid.'" height="'.$hit.'" layout="'.$resp.'"'.$sizes.'></amp-img></a>';
					if($isinline=='yes'){
						$RAT .= '<meta itemprop="url" content="'.$img.'"></meta>';
						$RAT .= '<meta itemprop="width" content="'.$wid.'"></meta>';
						$RAT .= '<meta itemprop="height" content="'.$hit.'"></meta>';
					}
				$RAT .= '</div>';
			}
		}else{
			$RAT .= '<div id="'.$id.'" class="logo '.$clss.'"'.$iline.'>';
				$RAT .= '<a href="'.$homeurl.'"><img class="logoimg" src="'.$img.'" style="'.$sized.'" /></a>';
			$RAT .= '</div>';
		}
		return $RAT;
	}
	
	/**
	 * Create a placeholder box instead of an AMP module when required data is missing. This alerts users to the missing content without invalidating AMP compliance.
	 * @requires : get_module_title
	 * @usage : amp_filter_embeds, generate_video_module
	 */
	function generate_placeholder($mod,$pass='',$over=''){
		$RAT = '';
		if(method_exists($this,'get_module_title')){
			if($mod=='ad'){$mod = 'AD';}
			$title = 'AMP '.$this->get_module_title($mod);
		}else{
			$title = $mod;
		}
		if($over!=''){$title = $over;}
		$RAT .= '<div class="placeholderbox">';
			$RAT .= '<div class="ttl cf">'.$title.'</div>';
			if($pass!=''){$RAT .= '<div class="msg cf">'.$pass.'</div>';}
		$RAT .= '</div>';
		return $RAT;
	}
	
	/**
	 * Create an AMP video module from provided data, requires provider and video ID.
	 * @requires : get_value, amp_video_providers, extract_video_data, generate_warning, generate_placeholder
	 * @usage : amp_filter_embeds
	 */
	function generate_video_module($attrs,$ampd=false){
		$RAT = '';
		$provider = $this->get_value($attrs,'provider');
		$video = $this->get_value($attrs,'video');
		$aspect = $this->get_value($attrs,'aspectratio');
		$width = 16;
		$height = 9;
		$layout = 'responsive';
		if($aspect!=''){
			$vals = explode('x',$aspect);
			$xwid = $this->get_value($vals,0,$width);
			$xhit = $this->get_value($vals,1,$height);
			$width = $xwid;
			$height = $xhit;
		}
		$width = str_replace('"','',$width);
		$height = str_replace('"','',$height);
		$atts = '';
		$warn = '';
		$vidttl = '';
		if($provider==''){
			$warn = 'You must choose a Provider';
		}else{
			$providers = $this->amp_video_providers();
			$pass = '<span style="font-size:14px;">'.$this->get_value($providers,$provider).'</span>';
			$vidvar = '';
			$ttl = '';
			if($provider=='vimeo' or $provider=='dailymotion' or $provider=='youtube'){
				$vidvar = 'videoid';
				$ttl = 'Video ID';
			}else if($provider=='hulu'){
				$vidvar = 'eid';
			}else if($provider=='facebook'){
				$vidvar = 'href';
				$ttl = 'Video URL';
				$atts .= ' data-embed-as="video"';
			}else if($provider=='video'){
				if(strstr($video,'wp-content/uploads')){
					$exp = explode('wp-content/uploads',$video);
					$path = $this->get_value($exp,1);
					if($path!=''){
						$vidvar = 'video';
						$atts .= ' controls';
						$atts .= ' src="../wp-content/uploads'.$path.'"';
						$atts .= ' poster="'.$this->self['PATH'].'images/vidposter.jpg"';		
					}
				}
			}
			if($vidvar!=''){
				if($provider!='facebook'){
					if(strstr($video,'http') or strstr($video,'.com')){
						$vidata = $this->extract_video_data($video);
						$prov = $this->get_value($vidata,'provider');
						$videoid = $this->get_value($vidata,'video');
						if($videoid!='' and $prov==$provider){$video = $videoid;}
					}
				}
				$prov = $providers[$provider];
				if($video==''){
					$warn = 'You must enter a '.$prov.' "'.$ttl.'"';
				}else{
					$atts .= ' data-'.$vidvar.'="'.$video.'"';
					if($provider=='facebook' or $provider=='video'){
						$vidttl = $video;
					}else{
						$vidttl = $ttl.': '.$video;
					}
				}
			}
		}
		if($ampd and $warn==''){
			$RAT .= '<amp-'.$provider.' class="ampvideo" layout="'.$layout.'" width="'.$width.'" height="'.$height.'"'.$atts.'></amp-'.$provider.'>';
		}else{
			if($provider==''){
				$pass = $this->generate_warning($warn);
			}else{
				$pass = '<span>'.$providers[$provider].'</span>';
				if($warn==''){
					$pass .= '<br /><span>'.$vidttl.'</span>';
				}else{
					$pass .= $this->generate_warning($warn);
				}
			}
			$over = '';
			if($ampd){$over = 'amp';}
			$RAT .= $this->generate_placeholder('video',$pass,$over);
			if($provider=='youtube' or $provider=='dailymotion'){// ASPECT
				$hrat = ceil($height/$width*100);
				if($aspect=='239x100'){$spect = '2.39 : 1';}else{$spect = str_replace('x',' : ',$aspect);}
				$aspects = array('4x3'=>'Standard','16x9'=>'Widescreen','239x100'=>'Cinema','8x1'=>'Banner','1x1'=>'Square');
				$inspect = '';
				if(isset($aspects[$aspect])){$inspect = $aspects[$aspect].' ';$spect = $inspect.$spect;}else{$spect = 'Aspect '.$spect;}
				$RAT = '<div class="vidholder" style="width:100%;padding-top:'.$hrat.'%;">'.$RAT.'<div class="'.$inspect.'vidshower" style="width:100%;padding-top:'.$hrat.'%;"></div><div class="vidaspect">'.$spect.'</div></div>';
			}
		}
		return $RAT;
	}
	
	/**
	 * Wrap the passed sring with a warning box, used to alert about missing data where AMP modules would be displayed.
	 * @usage : generate_video_module
	 */
	function generate_warning($pass){
		return '<span class="warning">'.$pass.'</span>';
	}
	
	/**
	 * Check for existence of functions/methods registered by local class by first checking for a base level function, then for a local method.
	 * @usage : add_action
	 */
	function get_action($fct){
		if(function_exists($this->self['prefix'].'_'.$fct)){
			return $this->self['prefix'].'_'.$fct;
		}else if(method_exists($this,$fct)){
			return array(&$this,$fct);
		}else{
			return '';
		}
	}
	
	/**
	 * Get attachment id from image source and return image metadata.
	 * @requires : url_get_contents, get_value
	 * @usage : alp_head_css, import_demo_image, amp_filter_image, generate_logo, get_image_info
	 */
	function get_attachment_data_by_src($src,$stat=''){
		$attachment_id = attachment_url_to_postid($src);
		if($stat=='id'){return $attachment_id;}
		$issvg = false;
		if(strstr(strtolower($src),'.svg')){$issvg = true;}
		if($issvg){
			$xmlt = $this->url_get_contents($src);
			$xml = simplexml_load_string($xmlt);
			$json = json_encode($xml);
			$arr = json_decode($json,TRUE);
			$view = $this->get_value($arr,array('@attributes','viewBox'));
			$exp = explode(' ',$view);
			$wid = $this->get_value($exp,2);
			$hit = $this->get_value($exp,3);
			return array('width'=>$wid,'height'=>$hit);
		}else{
			return wp_get_attachment_metadata($attachment_id,true);
		}
	}
	
	/**
	 * Get the registered setting for the separator, then if it is a Fontawesome icon code generate it as such.
	 * @requires : htmlout, get_customizer_setting
	 * @usage : taxes
	 */
	function get_breadcrumb_separator($adds=''){
		$add = '';
		if($adds!=''){$add = ' '.$adds;}
		$sep = $this->htmlout($this->get_customizer_setting('seo_breadcrumbsep'));
		if(substr($sep,0,3)=='fa-'){return '<span class="crumbsep'.$add.'"><span class="fa '.$sep.'"></span></span>';}
		return '<span class="crumbsep'.$add.'">'.$sep.'</span>';
	}
	
	/**
	 * Return the boolean for a customizer setting that is a checkbox by filtering through all possible values.
	 * @requires : get_customizer_setting, is_checked
	 * @usage : alp_hero_class, process_stylesheet_code
	 */
	function get_customizer_checkbox($field){
		$val = $this->get_customizer_setting($field);
		if($val=='false' or $val=='0' or $val===''){return false;}
		if($this->is_checked($val)){return true;}
		return false;
	}
	
	/**
	 * Pull the Customizer setting, then if blank run through default values provided by the funtion call then by registration. Registering default values makes development easier.
	 * @requires : get_value, htmlout
	 * @usage : alp_head_css, alp_head_scripts, alp_topnav_logo, generate_analytics_module, wp_the_content, generate_logo, get_breadcrumb_separator, get_customizer_checkbox, get_customizer_setting_by_code, inline_markup, process_stylesheet_code, wp_document_data
	 */
	function get_customizer_setting($setting,$default=NULL){
		if(isset($this->self['SETTINGS_PREVIEW_DATA'])){
			$mod = $this->get_value($this->self['SETTINGS_PREVIEW_DATA'],$setting);
		}else{
			$mod = get_theme_mod($setting);
			if(isset($this->self['EDITOR_PREVIEW_DATA'])){
				if(isset($this->self['EDITOR_PREVIEW_DATA'][$setting])){
					$mod = $this->self['EDITOR_PREVIEW_DATA'][$setting];
				}
			}
		}
		if(isset($this->self['customizer_settings'][$setting])){
			$def = $this->get_value($this->self['customizer_settings'][$setting],'default');
			$type = $this->get_value($this->self['customizer_settings'][$setting],'type');
			if($type=='checkbox' and $mod==='false'){return '';}
			if($type=='checkbox' and $mod==='true'){return '1';}
			if($mod==''){
				if($default!=NULL){
					$mod = $default;
				}else{
					$mod = $def;
				}
			}
		}
		$mod = $this->htmlout($mod);
		$mod = str_replace('(_PL_)','+',$mod);
		return $mod;
	}
	
	/**
	 * Return the value for a customizer setting by using the internal/CSS shortcode assigned to it.
	 * @requires : get_value, get_customizer_setting
	 * @usage : process_stylesheet_if
	 */
	function get_customizer_setting_by_code($code){
		if(isset($this->self['customizer_settings'])){
			foreach($this->self['customizer_settings'] as $set=>$data){
				$incode = $this->get_value($data,'code');
				if($code==$incode){return $this->get_customizer_setting($set);}
			}
		}
		return '';
	}
	
	/**
	 * Return an array with registration data for a particular setting.
	 * @requires : has_customizer_settings, get_value
	 * @usage : process_stylesheet, process_stylesheet_code
	 */
	function get_customizer_settings($setting=NULL){
		if($setting==NULL){
			if($this->has_customizer_settings()){return $this->self['customizer_settings'];}
			return array();
		}else{
			return $this->get_value($this->self['customizer_settings'],$setting,array());
		}
	}
	
	/**
	 * Retrieve the featured thumbnail and return either the source URL or an array with width, height and url.
	 * @requires : get_page_setting, get_value
	 * @usage : wp_document_data
	 */
	function get_featured_image($pageid,$pagetype,$how=''){
		if($pagetype=='category'){
			if(method_exists($this,'get_page_setting')){
				return $this->get_page_setting($pageid,$pagetype,'featuredimage');
			}else{
				return '';
			}
		}else{
			if(has_post_thumbnail($pageid)){
				$imgid = get_post_thumbnail_id($pageid);
				$img = wp_get_attachment_url($imgid);
				if($how=='info'){
					$imgdata = wp_get_attachment_metadata($imgid,true);
					$iwid = $this->get_value($imgdata,'width');
					$ihit = $this->get_value($imgdata,'height');
					if($iwid!=''){$imgwid = $iwid;}
					if($ihit!=''){$imgwid = $ihit;}
					return array('url'=>$img,'width'=>$iwid,'height'=>$ihit);
				}
				return $img;
			}
		}
		return '';
	}
	
	/**
	 * Meta fields are registered as a variable name with an object prefix to keep it distinct from fields registerd by other WP products.
	 * @usage : get_meta, register_meta, update_meta, update_post
	 */
	function get_fieldname($field,$over=false){
		$pref = $this->self['prefix'];
		if($over==false){if(!strstr($field,$pref)){return '_'.$pref.'_'.$field;}}
		return $field;
	}
	
	/**
	 * Retrieve width, height and mime type from an image by source URL.
	 * @requires : get_attachment_data_by_src, get_value
	 * @usage : wp_document_data
	 */
	function get_image_info($url,$field){
		if($url==''){return '';}
		if(!isset($this->self['IMAGE_INFO'])){$this->self['IMAGE_INFO'] = array();}
		if(isset($this->self['IMAGE_INFO'][$url])){
			return $this->self['IMAGE_INFO'][$url][$field];
		}else{
			$imgdata = $this->get_attachment_data_by_src($url);
			$wid = $this->get_value($imgdata,'width');
			$hit = $this->get_value($imgdata,'height');
			$this->self['IMAGE_INFO'][$url] = array();
			$this->self['IMAGE_INFO'][$url]['width'] = $wid;
			$this->self['IMAGE_INFO'][$url]['height'] = $hit;
			if($field=='width'){return $wid;}
			if($field=='height'){return $hit;}
		}
		return '';
	}
	
	/**
	 * Create a srcset attribute for usage by AMP image tags to display the version of the image that most closely fits the available viewable area.
	 * @requires : get_value, delimitit
	 * @usage : amp_filter_image
	 */
	function get_image_srcset($imgid='',$how='id'){
		if($how=='src'){$imgid = attachment_url_to_postid($imgid);}
		if($imgid!=''){
			$set = '';
			$imgdata = wp_get_attachment_metadata($imgid,true);
			$imgsizes = $this->get_value($imgdata,'sizes',array());
			$used = array();
			if(count($imgsizes)>0){
				foreach($imgsizes as $size=>$dats){
					$wid = $this->get_value($dats,'width');
					if($wid!='' and $wid>0 and !isset($used['w'.$wid])){
						$att = wp_get_attachment_image_src($imgid,$size);
						$url = $this->get_value($att,0);
						if($url!=''){
							$used['w'.$wid] = true;
							$set = $this->delimitit($set,', ',$url.' '.$wid.'w');
						}
					}
				}
			}
			if($set!=''){return ' srcset="'.$set.'"';}
		}
		return '';
	}
	
	/**
	 * Used to add markups to objects in the DOM after checking if they are turned off by setting.
	 * @requires : inline_markup
	 * @usage : alp_meta_tags
	 */
	function get_markup($mark,$how,$what=''){
		if($this->inline_markup('yes')=='' and $what!='inline'){return '';}
		if($how=='type'){
			return ' itemscope itemtype="http://schema.org/'.$mark.'"';
		}else if($how=='prop'){
			return ' itemprop="'.$mark.'"';
		}else if($how=='rel'){
			return ' rel="v:'.$mark.'"';
		}
		return '';
	}
	
	/**
	 * Simplified meta value function that adds the object prefix and weeds out un-registered meta fields.
	 * @requires : get_fieldname
	 * @usage : alp_hero_title, echo_post_editor, update_post
	 */
	function get_meta($post_id,$var=''){
		if($var==''){return '';}
		if(!isset($this->self['meta_list'][$var])){return '';}
		$field = $this->get_fieldname($var);
		return get_post_meta($post_id,$field,true);
	}
	
	/**
	 * Return registered data as an array or return a value for a particular registered data point.
	 * @requires : get_value
	 * @usage : update_meta
	 */
	function get_meta_data($field,$var=''){
		if(isset($this->self['meta_list'][$field])){
			if($var!=''){return $this->get_value($this->self['meta_list'][$field],$var);}
			return $this->self['meta_list'][$field];
		}
		return array();
	}
	
	/**
	 * Return category data for a post in a number of ways, from full array to a particular variable/value.
	 * @requires : taxes
	 * @usage : wp_document_data
	 */
	function get_post_category_data($post_id=NULL,$how='',$what=''){
		if($post_id===NULL){$post_id = get_the_ID();}
		$cats = array();
		$cat = '';
		$cid = '';
		$dep = 0;
		$ctgy = get_the_category($post_id);
		if($how=='all'){
			$cats = $ctgy;
		}else if($how=='base'){
			$dep = 1000;
		}else{
			$dep = -1;
		}
		foreach($ctgy as $rex=>$vex){
			$catid = $vex->cat_ID;
			$ndep = $this->taxes(array('com'=>'depth','id'=>$catid));
			if($dep!=$ndep){
				if($how=='all'){
					if($cat!=''){$cat .= ',';}
					$cat .= $vex->cat_name;
					if($cid!=''){$cid .= ',';}
					$cid .= $catid;
				}else{
					$isdep = false;
					if($how=='base'){
						if($ndep<$dep){
							$isdep = true;
						}
					}else{
						if($ndep>$dep){
							$isdep = true;
						}
					}
					if($isdep==true){
						$dep = $ndep;
						$cat = $vex->cat_name;
						$cid = $catid;
						$cats = $vex;
					}
				}
			}
		}
		if($what=='array'){
			return $cats;
		}else if($what=='id'){
			return $cid;
		}else{
			return $cat;
		}
	}
	
	/**
	 * These two functions consolodate two WP steps into one.
	 * @usage : alp_admin_head_scripts, generate_analytics_module, media_library_import_image, taxes
	 */
	function get_site_base_url(){
		return esc_url(home_url());
	}
	
	/**
	 * Filter and return the theme's template directory URI.
	 * @usage : __construct
	 */
	function get_site_template_url(){
		return esc_url(get_template_directory_uri());
	}
	
	/**
	 * Return an array with standard size choices available to various objects.
	 * @usage : alp_get_size_choices
	 */
	function get_size_choices($how=''){
		$RET = array();
		$pre = substr($how,0,5);
		if($how=='auto' or $how=='thumbhead'){$RET ['auto'] = 'Auto';}
		if($how=='all'){
			$RET['10px'] = '10px';
			$RET['20px'] = '20px';
			$RET['30px'] = '30px';
			$RET['40px'] = '40px';
		}
		if($how!='hero'){
			if($how=='thumbhead' or $how=='all'){
				$RET['50px'] = '50px';
				$RET['60px'] = '60px';
				$RET['70px'] = '70px';
				$RET['80px'] = '80px';
				$RET['90px'] = '90px';
			}
			$RET['100px'] = '100px';
			if($pre=='thumb' or $how=='all'){
				$RET['110px'] = '110px';
				$RET['120px'] = '120px';
				$RET['130px'] = '130px';
				$RET['140px'] = '140px';
			}
			$RET['150px'] = '150px';
			if($pre=='thumb' or $how=='all'){
				$RET['160px'] = '160px';
				$RET['170px'] = '170px';
				$RET['180px'] = '180px';
				$RET['190px'] = '190px';
			}
			$RET['200px'] = '200px';
			if($pre=='fifty' or $how=='all'){$RET['250px'] = '250px';}
		}
		$RET['300px'] = '300px';
		if($pre!='thumb'){
			if($pre=='fifty' or $how=='all'){$RET['350px'] = '350px';}
			$RET['400px'] = '400px';
			if($pre=='fifty' or $how=='all'){$RET['450px'] = '450px';}
			$RET['500px'] = '500px';
			if($pre=='fifty' or $how=='all'){$RET['550px'] = '550px';}
			$RET['600px'] = '600px';
			if($pre=='fifty' or $how=='all'){$RET['650px'] = '650px';}
			$RET['700px'] = '700px';
			if($pre=='fifty' or $how=='all'){$RET['750px'] = '750px';}
			$RET['800px'] = '800px';
			if($pre='fifty' or $how=='all'){$RET['850px'] = '850px';}
			$RET['900px'] = '900px';
			if($pre=='fifty' or $how=='all'){$RET['950px'] = '950px';}
			$RET['1000px'] = '1000px';
		}
		return $RET;
	}
	
	/**
	 * @usage : queryit
	 */
	function get_table_name($table){
		global $wpdb;
		if($table=='post' or $table=='posts'){
			$table_name = $wpdb->prefix.'posts';
		}else if(isset($this->self['custom_data_tables'][$table])){
			$table_name = $wpdb->prefix.$this->self['prefix'].'_'.$table;
		}else{
			$table_name = $wpdb->prefix.$table;
		}
		return $table_name;
	}
	
	/**
	 * Check for and return the URL for the page/post's featured image by page/post id.
	 * @usage : alp_include_hero_banner
	 */
	function get_thumb($pageid){
		if(has_post_thumbnail($pageid)){
			$imgid = get_post_thumbnail_id($pageid);
			return wp_get_attachment_url($imgid);
		}
		return '';
	}
	
	/**
	 * Get a value from an array or object across nested levels of depth.
	 * @requires : object_to_array
	 * @usage : ajax_import_demo_content, alp_admin_head_scripts, alp_head_css, alp_head_scripts, alp_hero_title, alp_include_hero_banner, amp_pages, attach_image, generate_amp_markups, generate_analytics_module, is_alp, media_library_import_image, wp, wp_the_content, add_post_editor, add_registered_css, ajax_communication, amp_filter_embeds, amp_filter_image, amp_styles_css, amp_styles_to_classes, create_customizer_setting, docdata, echo_post_editor, extract_embed_data_from_url, extract_gutenberg_embeds, extract_video_data, generate_base_css, generate_field, generate_logo, generate_video_module, get_attachment_data_by_src, get_customizer_setting, get_customizer_setting_by_code, get_customizer_settings, get_featured_image, get_image_info, get_image_srcset, get_meta_data, init_customizer_settings, process_stylesheet, process_stylesheet_code, process_stylesheet_if, queryit, queryset, scrape_between, taxes, update_post, url_get_contents, using_meta, wp_document_data
	 */
	function get_value($incoming,$var,$def=''){
		if(is_object($incoming) and is_array($var)){$incoming = $this->object_to_array($incoming);}
		if (is_object($incoming)){
			if(isset($incoming->$var)){
				return $incoming->$var;
			}
		}else{
			if(is_array($var)){
				if(count($var)>0){
					$tar = $incoming;
					foreach($var as $far){
						if(isset($tar[$far])){
							$tar = $tar[$far];
						}else{
							return $def;
						}
					}
					return $tar;
				}
			}else{
				if(isset($incoming[$var])){
					return $incoming[$var];
				}
			}
		}
		return $def;
	}
	
	/**
	 * Check if the class has any cronjobs registered. Used before initiating WP setup script.
	 * @usage : custom_cron_schedules, init
	 */
	function has_cron(){
		if(isset($this->self['cron_list'])){if(count($this->self['cron_list'])>0){return true;}}return false;
	}
	
	/**
	 * Check if any settings have been registered, used to check if handling is necessary.
	 * @usage : get_customizer_settings, init, process_stylesheet, process_stylesheet_code
	 */
	function has_customizer_settings(){
		if(isset($this->self['customizer_settings'])){if(count($this->self['customizer_settings'])>0){return true;}}return false;
	}
	
	/**
	 * Simple mysql encoding of a string without using slashes. This is more reliable and works across PHP and JS.
	 * @requires : htmlin
	 * @usage : encode_text, extract_gutenberg_embeds, htmlin
	 */
	function htmlin($nstr){
		if(is_array($nstr)){
			if(count($nstr)>0){
				foreach($nstr as $dex=>$data){
					$nstr[$dex] = $this->htmlin($data);
				}
			}
		}else{
			for($cc=0;$cc<count($this->self['BAD_CHARS']);$cc++){
				$narr = explode($this->self['BAD_CHARS'][$cc],$nstr);
				$nstr = implode($this->self['NEW_CHARS'][$cc],$narr);
			}
		}
		return $nstr;
	}
	
	/**
	 * Decoding of a string that was encoded with htmlin().
	 * @requires : htmlout
	 * @usage : amp_filter_embeds, get_breadcrumb_separator, get_customizer_setting, htmlout
	 */
	function htmlout($nstr){
		if(is_array($nstr)){
			if(count($nstr)>0){
				foreach($nstr as $dex=>$data){
					$nstr[$dex] = $this->htmlout($data);
				}
			}
		}else{
			for($cc=0;$cc<count($this->self['NEW_CHARS']);$cc++){
				$narr = explode($this->self['NEW_CHARS'][$cc],$nstr);
				$nstr = implode($this->self['BAD_CHARS'][$cc],$narr);
			}
			$nstr = stripslashes($nstr);
		}
		return $nstr;
	}
	
	/**
	 * Check if Gutenberg is installed and activated.
	 * @usage : ajax_import_demo_content, amp_filter_embeds
	 */
	function if_gutenberg(){
		if(function_exists('gutenberg_init')){return true;}
		return false;
	}
	
	/**
	 * Add button to admin post editor for importing demo content into the page.
	 */
	function import_demo_content(){
		echo '<fieldset style="margin-top:10px;">';
			echo '<legend>Import Demo Content</legend>';
			echo '<div class="cf" style="margin:5px 0 0 0;">';
				echo '<a href="javascript:amplp_import_demo_content();" class="button">Import</a>';
			echo '</div>';
			echo '<div style="clear:both;box-shadow: inset 0 0 5px 0 #999;padding:5px 10px;margin:5px 0 0 0;">';
				echo 'Click the button to import demo content and assign a placeholder featured image. This procedure will override the current featured image and content (if any).';
			echo '</div>';
		echo '</fieldset>';
	}
	
	/**
	 * Import a placeholder image from the plugin assets folder into the media library for use in demo content for AMP Landing Pages.
	 * @requires : media_library_import_image, get_attachment_data_by_src
	 * @usage : ajax_import_demo_content
	 */
	function import_demo_image($post_id,$img=''){
		$url = $this->media_library_import_image($img);
		$imgid = $this->get_attachment_data_by_src($url,'id');
		if($imgid){return array('id'=>$imgid,'src'=>$url);}
		return array();
	}
	
	/**
	 * The main init() function, added as an action in the base level of the functions file.
	 * @requires : registration, is_child, import_theme_settings, has_customizer_settings, has_cron, using_meta, using_admin_interface, runit, init_customizer_settings, custom_cron_schedules, add_post_editor, update_post
	 */
	function init(){
		if(method_exists($this,'registration')){$this->registration();}
		if(method_exists($this,'is_child') and method_exists($this,'import_theme_settings')){
			if($this->is_child()){$this->import_theme_settings();}
		}
		if($this->has_customizer_settings()){
			add_action('customize_register',array(&$this,'init_customizer_settings'));
		}
		if($this->has_cron()){
			add_filter( 'cron_schedules', array(&$this,'custom_cron_schedules'));
			foreach($this->self['cron_list'] as $fct=>$interval){
				$action = $fct.'_cron';
				add_action($action, $fct);
				if(!wp_next_scheduled($action)){
					wp_schedule_event( time(), $interval, $action);
				}	
			}
		}
		global $pagenow;
		if(($this->using_meta() or $this->using_admin_interface()) and ($pagenow=='post.php' or $pagenow=='post-new.php')){
			add_action('edit_form_advanced',array(&$this,'add_post_editor'));
			add_action('edit_page_form',array(&$this,'add_post_editor'));
			add_action('edit_post',array(&$this,'update_post'),10,2);
		}
		$this->runit('extends_init');
	}
	
	/**
	 * Handle the WP Customizer setup of sections and settings using the registered data. This is added as a WordPress action by the theme's init() function.
	 * @requires : get_value, get_customizer_organization, create_customizer_setting
	 * @usage : init
	 */
	function init_customizer_settings($wp_customize){
		$secprio = 1;
		$defsect = '';
		foreach($this->self['customizer_sections'] as $section=>$data){
			if($defsect==''){$defsect = $section;}
			$title = $this->get_value($data,'title');
			$prio = $this->get_value($data,'priority');
			$desc = $this->get_value($data,'description');
			$call = $this->get_value($data,'active_callback');
			if($prio==''){$prio = $secprio;$secprio += 1;}
			$wp_customize->add_section($section,array('title'=>$title,'priority'=>$prio,'description'=>$desc,'active_callback'=>$call));
		}
		if(method_exists($this,'get_customizer_organization')){
			$org = $this->get_customizer_organization();
			if(count($org)>0){
				foreach($org as $sect=>$sets){
					$dex = 1;
					foreach($sets as $sett){
						$setting = $this->get_value($sett,0);
						$com = $this->get_value($sett,1);
						if($com=='label' or $com=='desc' or $com=='echo'){
						}else{
							$data = $this->get_value($this->self['customizer_settings'],$setting);
							if($data!=''){
								$data['section'] = $sect;
								$data['priority'] = $dex;
								$dex += 1;
								$this->create_customizer_setting($setting,$data);
							}
						}
					}
				}
			}
		}else{
			$bysect = array();
			foreach($this->self['customizer_settings'] as $setting=>$data){
				if(!isset($data['section'])){$data['section'] = $defsect;}
				$sect = $this->get_value($data,'section');
				if($sect!=''){
					if(!isset($bysect[$sect])){$bysect[$sect] = array();}
					$bysect[$sect][$setting] = $data;
				}
			}
			if(count($bysect)>0){
				foreach($bysect as $sect=>$sets){
					$dex = 1;
					foreach($sets as $setting=>$data){
						$data['priority'] = $dex;
						$dex += 1;
						$this->create_customizer_setting($setting,$data);
					}
				}
			}
		}
	}
	
	/**
	 * Check if linline markups are turned off, hooked by functions that utilize markups.
	 * @requires : get_customizer_setting
	 * @usage : generate_logo, get_markup
	 */
	function inline_markup($is,$isnt=''){
		$in = false;
		$isinline = $this->get_customizer_setting('seo_inlinemarkup');
		if($isinline!=''){$in = true;}
		if($in==true){return $is;}
		return $isnt;
	}
	
	/**
	 * Verify that the current page is using the AMP Landing Page template and thus being generated as an AMP compliant page.
	 * @requires : docdata, get_value
	 * @usage : ajax_import_demo_content, alp_admin_head_scripts, amp_pages, wp_post_thumbnail_html, wp_the_content
	 */
	function is_alp($pageid=''){
		if(function_exists('amp_woo')){
			return amp_woo('is woo');
		}
		if($pageid==''){
			$doc = $this->docdata();
			$pageid = $this->get_value($doc,'pageid');
		}
		if($pageid==''){
			$pageid = $this->get_value($_GET,'post');
		}
		if($pageid!=''){
			$file = plugin_dir_path( __FILE__ ).get_post_meta($pageid,'_wp_page_template',true);
			if($file==plugin_dir_path( __FILE__ ).'amplp.php'){return true;}
		}
		return false;
	}
	
	/**
	 * @usage : is_user_interface
	 */
	function is_amp_preview(){
		return true;
	}
	
	/**
	 * Check if data from a checkbox is true or false covering a number of possible values.
	 * @usage : get_customizer_checkbox, update_meta
	 */
	function is_checked($val){
		if($val=='1' or $val=='true' or $val===true){return true;}
		return false;
	}
	
	/**
	 * Verify that a string is an image URL.
	 * @usage : media_library_import_image
	 */
	function is_image($string){
		$info = pathinfo($string);
		if(isset($info['extension'])){
    		return in_array(strtolower($info['extension']),array("jpg", "jpeg", "gif", "png", "bmp"));
		}
		return false;
	}
	
	/**
	 * Check if the user is logged in and has Administrator level access.
	 * @usage : is_user_interface
	 */
	function is_user_admin(){
		$user = wp_get_current_user();
		if($user->exists() and current_user_can('manage_options')){return true;}
		return false;
	}
	
	/**
	 * @requires : is_user_admin, is_amp_preview
	 * @usage : amp_filter_embeds
	 */
	function is_user_interface(){
		if($this->is_user_admin() and !$this->is_amp_preview()){return true;}
		return false;
	}
	
	/**
	 * Break down the URL and return the page slug found after the last / slash.
	 * @requires : current_page_url
	 * @usage : __construct, extract_embed_data_from_url
	 */
	function last_page_in_url($url=NULL){
		if($url===NULL){$url = $this->current_page_url('base');}
		$drop = explode('?',$url);
		$splt = explode('/',$drop[0]);
		$cnt = count($splt)-1;
		$last = $splt[$cnt];
		if($last==''){
			$dex = ($cnt-1);
			if($dex<0){$dex = 0;}
			$last = $splt[$dex];
		}
		return $last;
	}
	
	/**
	 * Import an image into the media library, running through various checks for image validity and previous import. Returns the URL for the imported image.
	 * @requires : is_image, get_value, queryit, validsql, get_site_base_url, attach_image
	 * @usage : import_demo_image
	 */
	function media_library_import_image($incoming){
		$url = '';
		if($this->is_image($incoming)){
			$exp = explode('/',$incoming);
			$dex = count($exp)-1;
			$img = $this->get_value($exp,$dex);
			if($img!=''){
				$arr = $this->queryit("SELECT * FROM `wp_postmeta` WHERE `meta_key`='_wp_attached_file' AND `meta_value` LIKE '%$img'");
				if($this->validsql($arr)){
					foreach($arr as $data){
						if($url==''){
							$val = $this->get_value($data,'meta_value');
							$vexp = explode('/',$val);
							$vdex = count($vexp)-1;
							$vimg = $this->get_value($vexp,$vdex);
							$cimg = strtolower($img);
							if($vimg==$cimg){
								$verify = $this->get_site_base_url().'/wp-content/uploads/'.$val;
								if($this->is_image($verify)){
									$url = $verify;
								}
							}
						}
					}
				}
				if($url==''){
					$nexp = explode('.',$img);
					$imgname = $nexp[0];
					$url = $this->attach_image($incoming,0,$imgname,'url');
				}
			}
		}
		return $url;
	}
	
	/**
	 * Check for existence of a method in/by the local class, then run it and return it's value or a confirmation of non-existence.
	 * @usage : amp_pages, add_post_editor
	 */
	function method_call($fct,$pass=''){
		if(method_exists($this,$fct)){
			if($pass!=''){
				return call_user_func(array($this, $fct), $pass);
			}else{
				return call_user_func(array($this, $fct));
			}
		}
		return 'NA';
	}
	
	/**
	 * Convert a PHP object to an array.
	 * @usage : get_value, queryit, wp_document_data
	 */
	function object_to_array($d){
		if(is_object($d)){$d = get_object_vars($d);}if(is_array($d)){return array_map(array($this,'object_to_array'),$d);}else{return $d;}
	}
	
	/**
	 * Filter stylesheet content for shortcodes tied to registered customizer settings.
	 * @requires : has_customizer_settings, get_customizer_settings, get_value, process_stylesheet_code, process_stylesheet_if
	 * @usage : alp_head_css
	 */
	function process_stylesheet($sheet){
		if($this->has_customizer_settings()){
			$sets = $this->get_customizer_settings();
			$codes = array();
			foreach($sets as $set=>$data){
				$code = $this->get_value($data,'code');				
				if($code!=''){
					$codes[$code] = $set;
					$val = $this->process_stylesheet_code($set,$data);
					$sheet = str_replace('[['.$code.']]',$val,$sheet);
				}
			}
			$params['sets'] = $sets;
			$params['codes'] = $codes;
			$sheet = $this->process_stylesheet_if($sheet,'if',$params);
		}
		return $sheet;
	}
	
	/**
	 * Handle shortcodes used in the stylesheet.
	 * @requires : has_customizer_settings, get_customizer_settings, get_value, get_customizer_checkbox, get_customizer_setting
	 * @usage : process_stylesheet, process_stylesheet_if
	 */
	function process_stylesheet_code($set,$data=NULL){
		if($data==NULL){
			$data = array();
			if($this->has_customizer_settings()){
				$sets = $this->get_customizer_settings();
				$data = $this->get_value($sets,$set);
			}
		}
		$type = $this->get_value($data,'type');
		if($type=='checkbox'){
			$on = $this->get_customizer_checkbox($set);
			$pos = $this->get_value($data,'pos','block');
			$neg = $this->get_value($data,'neg','none');
			if($on==true){$val = $pos;}else{$val = $neg;}
		}else if($type=='color'){
			$val = $this->get_customizer_setting($set);
			if($val=='inherit' and isset($data['neg'])){
				$val = $data['neg'];
				if($val=='default'){$val = $this->get_value($data,'default');}
			}
		}else{
			$val = $this->get_customizer_setting($set);
		}
		return $val;
	}
	
	/**
	 * Filter stylesheet content for shortcodes that check for a condition and include the wrapped/tagged CSS only if the condition is met.
	 * @requires : docdata, get_value, scrape_between, get_customizer_setting_by_code, process_stylesheet_code
	 * @usage : process_stylesheet
	 */
	function process_stylesheet_if($sheet='',$pre='',$params=array()){
		if($pre==''){return $sheet;}
		$doc = $this->docdata();
		$arctype = $this->get_value($doc,'arctype');
		$sets = $this->get_value($params,'sets');
		$codes = $this->get_value($params,'codes');
		if(strstr($sheet,'[['.$pre.' ')){
			$sheet = str_replace('/* [[end '.$pre.']] */','[[end '.$pre.']]',$sheet);
			$sheet = str_replace('/* [['.$pre.' ','[['.$pre.' ',$sheet);
			$sheet = str_replace(']] */',']]]',$sheet);
			$exp = explode('[['.$pre.' ',$sheet);
			foreach($exp as $part){
				$sport = $this->scrape_between('[ST]'.$part,'[ST]','[[end '.$pre.']]');
				$find = '[['.$pre.' '.$sport.'[[end '.$pre.']]';
				$rep = '';
				$code = $this->scrape_between('[ST]'.$part,'[ST]',']]');
				$css = $this->scrape_between($part,']]]','[[end '.$pre.']]');
				if(substr($code,0,3)=='is_'){
					if($code=='is_post' and $arctype=='post'){$rep = $css;}
					if($code=='is_page' and $arctype=='page'){$rep = $css;}
					if($code=='is_home' and $arctype=='home'){$rep = $css;}
					if($code=='is_category' and $arctype=='category'){$rep = $css;}
					if($code=='is_author' and $arctype=='author'){$rep = $css;}
					if($code=='is_search' and $arctype=='search'){$rep = $css;}
				}else if(strstr($code,' = ')){
						$codes = explode(' = ',$code);
						$var = $this->get_value($codes,0);
						$val = $this->get_value($codes,1);
						$set = $this->get_customizer_setting_by_code($var);
						if($set==$val){$rep = $css;}
				}else{
					$not = false;
					if(substr($code,0,4)=='not '){$not = true;$code = str_replace('not ','',$code);}
					$set = $this->get_value($codes,$code);
					$data = $this->get_value($sets,$set,array());
					$val = $this->process_stylesheet_code($set,$data);
					if($not){
						if($val=='' or $val=='none'){$rep = $css;}
					}else{
						if($val!='' and $val!='none'){$rep = $css;}
					}
				}
				$sheet = str_replace($find,$rep,$sheet);
			}
		}
		return $sheet;
	}
	
	/**
	 * Check if using SSL then convert HTTP within the URL srting to HTTPS.
	 * @requires : check_for_ssl
	 * @usage : amp_filter_image, generate_logo
	 */
	function process_url_ssl($txt){
		if($this->check_for_ssl()){$txt = str_replace('http:','https:',$txt);}
		return $txt;
	}
	
	/**
	 * @requires : queryset, object_to_array, get_table_name, delimitit, get_value
	 * @usage : media_library_import_image, queryset
	 */
	function queryit($how,$table=NULL,$pass=NULL,$xtra=NULL){
		if($how=='set'){return $this->queryset($table,$pass,$xtra);}
		global $wpdb;
		if(is_string($how) and $table===NULL){return $this->object_to_array($wpdb->get_results($how));}
		$idt = 'ID';
		if($table=='post' or $table=='posts'){
			$table_name = $wpdb->prefix.'posts';
		}else if($table=='meta'){
			$idt = 'meta_id';
			$table_name = $wpdb->prefix.'postmeta';
		}else{
			$idt = 'id';
			$table_name = $this->get_table_name($table);
		}
		$fields = '';
		if(is_array($how)){
			if($table=='post' or $table=='posts'){
				$fields = 'ID';
			}else if($table=='meta'){
				$fields = 'meta_id';
			}else if(isset($this->self['custom_data_tables'][$table])){
				$fields = 'id,pid';
			}
			foreach($how as $field){
				$fields = $this->delimitit($fields,',',$field);
			}
			$how = 'select';
		}
		if($how=='get' or $how=='set' or $how=='put'){
			if($pass!=NULL and $xtra!=NULL){
				if(is_array($xtra)){
					$where = '';
					if(count($xtra)>0){
						foreach($xtra as $k=>$v){
							$where = $this->delimitit($where,' AND ',"`$k`='$v'");
						}
					}
					if($where!=''){
						$where = "WHERE ".$where;
					}
					$xtra = $where;
				}
				$qry = "SELECT * FROM $table_name $xtra";
				$isthere = $this->object_to_array($wpdb->get_results($qry));
				if($how=='set' or $how=='put'){
					$isid = $this->get_value($isthere,array(0,'id'));
					if($isid!=''){
						if($how=='put'){return false;}
						$how = 'update';
						$xtra = "WHERE `id`='$isid' LIMIT 1";
					}else{
						$how = 'insert';
					}
				}else if($how=='get'){
					return $this->get_value($isthere,array(0,$pass));
				}
			}
		}
		if($how=='select'){
			if($pass===NULL){
				$pass = '';
			}
			$end = '';
			if($xtra!=NULL and is_string($xtra)){
				$end = $xtra;
			}
			$gets = '*';
			if($fields!=''){$gets = $fields;}
			if(is_array($pass)){
				$where = '';
				if(count($pass)>0){
					foreach($pass as $k=>$v){
						$where = $this->delimitit($where,' AND ',"`$k`='$v'");
					}
				}
				if($where!=''){
					$where = "WHERE ".$where;
				}
				$qry = "SELECT $gets FROM $table_name $where $end";
			}else{
				$qry = "SELECT $gets FROM $table_name $pass $end";
			}
			return $this->object_to_array($wpdb->get_results($qry));
		}else{
			if(is_array($pass)){
				if($how=='update'){
					if($xtra===NULL or $xtra==''){$xtra = "WHERE `$idt`>0";}
					if(is_string($xtra)){
						$ups = '';
						if(count($pass)>0){
							foreach($pass as $k=>$v){
								$ups = $this->delimitit($ups,',',"`$k`='$v'");
							}
						}
						$qry = "UPDATE $table_name SET $ups $xtra";
						return $this->object_to_array($wpdb->get_results($qry));
					}else{
						return $wpdb->update($table_name,$pass,$xtra);
					}
				}else if($how=='replace'){
					return $wpdb->replace($table_name,$pass);
				}else if($how=='insert'){
					return $wpdb->insert($table_name,$pass);
				}else if($how=='delete'){
					return $wpdb->delete($table_name,$pass);
				}
			}
		}
		return NULL;
	}
	
	/**
	 * @requires : queryit, get_value
	 * @usage : queryit
	 */
	function queryset($table,$data,$where){
		$isit = $this->queryit('select',$table,$where);
		$id = $this->get_value($isit,array(0,'id'));
		if($id!=''){
			$this->queryit('update',$table,$data,array('id'=>$id));
		}else{
			$this->queryit('insert',$table,$data);
		}
	}
	
	/**
	 * Create a list of required AMP modules to be used when generating the html head.
	 * @usage : alp_head_scripts
	 */
	function register_amp_module($mod){
		if($mod!=''){
			if(!isset($this->self['AMP_REGISTERED_MODULES'])){$this->self['AMP_REGISTERED_MODULES'] = array();}
			if(!isset($this->self['AMP_REGISTERED_MODULES'][$mod])){$this->self['AMP_REGISTERED_MODULES'][$mod] = $mod;}
		}
	}
	
	/**
	 * Create a section in the customizer, each setting will be assigned to it's section in the order they are registered.
	 */
	function register_customizer_section($sect,$params=array()){
		if(!isset($this->self['customizer_sections'])){$this->self['customizer_sections'] = array();}
		$this->self['customizer_sections'][$sect] = $params;
	}
	
	/**
	 * Add a setting to the Customizer and store the data for use by the framework. Centralizing and storing the data in the class speeds up processing time.
	 */
	function register_customizer_setting($setting,$params=array()){
		if(!isset($this->self['customizer_settings'])){$this->self['customizer_settings'] = array();}
		$this->self['customizer_settings'][$setting] = $params;
	}
	
	/**
	 * Register a meta value by sending this method a variable name and an array of parameters, including acceptable post types.
	 * @requires : get_fieldname
	 */
	function register_meta($name,$data=array()){
		$field = $this->get_fieldname($name,true);
		$this->self['meta_list'][$field] = $data;
		$this->self['meta_list'][$field]['id'] = $this->get_fieldname($name);
	}
	
	/**
	 * Return the array of registered video modules.
	 * @usage : alp_head_scripts
	 */
	function registered_amp_modules(){
		if(isset($this->self['AMP_REGISTERED_MODULES'])){return $this->self['AMP_REGISTERED_MODULES'];}
		return array();
	}
	
	/**
	 * Run a function/method by name by first checking if it exists within the class, then if it exists in the base file.
	 * @usage : __construct, ajax_communication, echo_post_editor, init
	 */
	function runit($fct,$pass=''){
		if(method_exists($this,$fct)){
			if($pass!=''){
				call_user_func(array($this, $fct), $pass);
			}else{
				call_user_func(array($this, $fct));
			}
		}else if(function_exists($fct)){
			if($pass!=''){
				call_user_func($fct,$pass);
			}else{
				call_user_func($fct);
			}
		}
	}
	
	/**
	 * Find and return a portion of a string that falls between a starting string and an ending string. Used in various places for removing HTML tags and attributes that invalidate Google AMP compliance.
	 * @requires : get_value
	 * @usage : alp_head_css, amp_filter_image, amp_styles_to_classes, extract_embed_data_from_url, extract_gutenberg_embeds, extract_wp_embeds, process_stylesheet_if
	 */
	function scrape_between($txt,$start,$end){
		$splode = explode($start,$txt);
		$part = $this->get_value($splode,1);
		$parts = explode($end,$part);
		return $parts[0];
	}
	
	/**
	 * Remove a tag and its children from a string.
	 * @usage : amp_filter
	 */
	function strip_tag($content,$tag,$replace=''){
		return preg_replace('!<'.$tag.'(.*?)/'.$tag.'>!s',$replace,$content);
	}
	
	/**
	 * Remove an attribute from tags found in a string.
	 * @usage : wp_the_content, amp_filter
	 */
	function strip_tag_attribute($content,$attribute,$replace=''){
		return preg_replace('/'.$attribute.'=(["\'])[^\1]*?\1/i',$replace,$content,-1);
	}
	
	/**
	 * Return various pieces of category/taxonomy data. This centralized function makes development easier.
	 * @requires : get_value, taxes, get_site_base_url, delimitit, get_breadcrumb_separator
	 * @usage : alp_admin_head_scripts, get_post_category_data, taxes
	 */
	function taxes($params){
		$id = $this->get_value($params,'id');
		$com = $this->get_value($params,'com');
		$cat = $this->get_value($params,'category');
		if($id=='' or $id===NULL){
			if($cat==''){$cat = single_term_title("", false);}
			$id = get_cat_ID($cat);
		}
		if($com=='id'){
			return $id;
		}else if($com=='title'){
			return get_cat_name($id);	
		}else if($com=='description'){
			return category_description($id);		
		}else if($com=='depth'){
			$dep = 0;
			$category = get_category($id);
			for($i=1;$i<=9;$i++){
				if($category->category_parent){
					$category = get_category($category->category_parent);
					$dep = $i;
				}
			}
			return $dep+1;
		}else if($com=='data'){
			$category_data = get_term_by('term_id',$id,'category');
			$var = $this->get_value($params,'var');
			if($var==''){return $category_data;}
			if(is_object($category_data)){
				return $category_data->$var;
			}else{
				return $this->get_value($category_data,$var);
			}
		}else if($com=='link'){
			$catslug = $this->taxes(array('com'=>'path','id'=>$id));
			return $this->get_site_base_url().'/category/'.$catslug;
		}else if($com=='path'){
			$data = $this->taxes(array('com'=>'data','id'=>$id));			
			$pslug = $this->get_value($data,'slug');
			$parent = $this->get_value($data,'parent',0);
			$slug = $this->get_value($params,'slug');
			if($pslug!=''){
				$slug = $pslug.'/'.$slug;
				if($parent!=0){$slug = $this->taxes(array('com'=>'path','id'=>$parent,'slug'=>$slug));}
			}
			return $slug;
		}else if($com=='parents'){
			$data = $this->taxes(array('com'=>'data','id'=>$id));	
			$parent = $this->get_value($data,'parent',0);
			$ids = $this->get_value($params,'ids');
			$ids = $this->delimitit($ids,',',$parent);
			if($parent>0){$ids = $this->taxes(array('com'=>'parents','id'=>$parent,'ids'=>$ids));}
			return $ids;
		}else if($com=='crumbs'){
			$link = $this->taxes(array('com'=>'link','id'=>$id));
			$data = $this->taxes(array('com'=>'data','id'=>$id));
			$slug = $this->get_value($data,'slug');
			$parent = $this->get_value($data,'parent');
			$name = $this->get_value($data,'name');
			$count = $this->get_value($data,'count');
			$strg = $this->get_value($params,'text');
			$how = $this->get_value($params,'how');
			$markup = $this->get_value($params,'markup',true);
			if($markup==false){
				$href = '<span><a href="'.$link.'">'.$name.'</a></span>';
				$blank = '<span class="lastcrumb">'.$name.'</span>';
			}else{
				$href = '<span><a href="'.$link.'" rel="v:url">'.$name.'</a></span>';
				$href = '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.$link.'" rel="v:url" itemprop="item"><span itemprop="name">'.$name.'</span></a></span>';
				$blank = '<span class="lastcrumb" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="item"><span itemprop="name">'.$name.'</span></span></span>';
			}
			if($slug!=''){
				$lcsep = '';
				if($strg==''){$lcsep = 'lcsep';}
				if($strg=='' and $how==''){
					$strg = $blank;
				}else{
					$strg = $href.' '.$this->get_breadcrumb_separator($lcsep).' '.$strg;
				}
				if($parent!=0){$strg = $this->taxes(array('com'=>'crumbs','id'=>$parent,'text'=>$strg,'how'=>$how,'markup'=>$markup));}
			}
			return $strg;
		}
		return '';
	}
	
	/**
	 * Update post meta by adding/editing where appropriate, and deleting when empty. Updating the meta field this way minimizes database weight by not storing empty values and simplifies using meta values in this object.
	 * @requires : get_fieldname, get_meta_data, is_checked
	 * @usage : update_post
	 */
	function update_meta($post_id,$var,$newval,$pref=false){
		$field = $this->get_fieldname($var,$pref);
		$ftype = $this->get_meta_data($var,'type');
		if($ftype=='checkbox'){
			if($this->is_checked($newval)){
				$newval = '1';
			}else{
				$newval = '';
			}
		}
		if($newval===''){
			delete_post_meta($post_id,$field);
		}else{
			add_post_meta($post_id, $field, $newval, true) or update_post_meta($post_id, $field, $newval);
		}
	}
	
	/**
	 * Hook into the post editor update functionality to include our registered meta fields.
	 * @requires : get_value, get_fieldname, get_meta, update_meta, using_admin_interface, update_admin_interface
	 * @usage : init
	 */
	function update_post($post_id,$post){
		$runit = false;
		if(is_single($post_id)){
			if ( !empty($_POST) && check_admin_referer($this->self['prefix'].'_post_nonce',$this->self['prefix'].'_nonce_field') ){
				$runit = true;
			}
		}else{
			$runit = true;
		}
		if($runit==true){
			$arr = $this->self['meta_list'];
			if(count($arr)>0){
				foreach($arr as $name=>$data){
					$type = $this->get_value($data,'type');
					if($type=='pass'){continue;}
					$field = $this->get_fieldname($name);
					$oldval = $this->get_meta($post_id,$field);
					if($type=='checkbox'){
						$newval = '';if(isset($_POST[$field])){$newval = 1;}
					}else{
						$newval = $this->get_value($_POST,$field);
					}
					if($newval!=$oldval or $newval==''){$this->update_meta($post_id,$field,$newval);}
				}
			}
			if($this->using_admin_interface()){
				if(method_exists($this,'update_admin_interface')){$this->update_admin_interface();}
			}
		}
	}
	
	/**
	 * Retrieve text/HTML from a URL. This function consolodates the two steps necessary to gather the data and return only the text. This is used to check our API for updates to the Fontawesome version/list.
	 * @requires : get_value
	 * @usage : alp_head_css, add_registered_css, get_attachment_data_by_src
	 */
	function url_get_contents($url){
		$response = wp_remote_get($url);
		return $this->get_value($response,'body');
	}
	
	/**
	 * Check if a nav menu is available, assigned, and/or empty. These checks are useful in various situations.
	 * @usage : alp_head_scripts, amp_pages
	 */
	function use_nav_menu($location,$com=''){
		if(has_nav_menu($location)){
			if($com=='assigned'){return true;}
			if(wp_nav_menu(array('theme_location'=>$location,'echo'=>false))!==false){
				return true;
			}else{
				if($com=='empty'){return true;}
			}
		}
		return false;
	}
	
	/**
	 * Verify if a plugin is adding functionality to the post editor
	 * @usage : echo_post_editor, init, update_post
	 */
	function using_admin_interface(){
		if(method_exists($this,'generate_admin_interface')){return true;}
	}
	
	/**
	 * Verify that this object has meta field(s) reistered, used to check if a process is needed before running it.
	 * @requires : get_value
	 * @usage : init
	 */
	function using_meta(){
		if(isset($this->self['meta_list']) and count($this->self['meta_list'])>0){
			foreach($this->self['meta_list'] as $var=>$arr){
				if($this->get_value($arr,'type')!=''){return true;}
			}
		}
		return false;
	}
	
	/**
	 * @usage : media_library_import_image
	 */
	function validsql($input){
		if(isset($input[0]['id']) or isset($input[0]['ID']) or isset($input[0]['meta_id'])){
			return true;
		}
		return false;
	}
	
	/**
	 * @requires : get_value, debug_mode, docdata
	 * @usage : extends_init
	 */
	function wp(){
		if($this->get_value($_GET,'test')=='1'){
			$this->debug_mode();
			$doc = $this->docdata();
			echo '<textarea style="height:800px;width:100%;">';
				echo $this->get_value($doc,'content');
			echo '</textarea>';
			die();
		}
		return '';
	}
	
	/**
	 * Gather data for the current pages from various sources, initiated from the class construct method with an action added to the "wp" hook... the earliest available point where necessary data has been created.
	 * @requires : object_to_array, get_value, get_customizer_setting, get_image_info, get_featured_image, get_post_category_data
	 * @usage : docdata
	 */
	function wp_document_data(){
		$doc = get_queried_object();
		global $pagenow;
		if(is_customize_preview() or (is_admin() and $pagenow=='post.php')){
			global $post;
			$doc = $post;
		}
		if(function_exists('is_shop') && is_shop()){
			$wooid = wc_get_page_id('shop');
			if($wooid){
				$doc = get_post($wooid);
			}
		}
		$doc = $this->object_to_array($doc);
		$pageid = '';
		$pagetype = '';
		$arctype = '';
		$post_id = '';
		$term_id = '';
		if(isset($doc['post_type'])){
			$pageid = $this->get_value($doc,'ID');
			$pagetype = $doc['post_type'];
			$arctype = $pagetype;
			$term_id = $pageid;
		}else if(isset($doc['taxonomy'])){
			$pageid = $this->get_value($doc,'term_id');
			$pagetype = $doc['taxonomy'];
			$arctype = 'category';
			$post_id = $pageid;
		}
		if(is_home() and is_front_page()){$pageid = 0;$post_id = 0;}
		if($pageid===''){
				$this->self['DOCDATA'] = $doc;
			return '';
		}else{
			if($pageid===0){
				$url = get_bloginfo('url');
				$slug = '';
				$title = get_bloginfo('name');
				$content = get_bloginfo('description');
			}else if($arctype=='category'){
				$url = esc_url(get_term_link($pageid));
				$slug = $this->get_value($doc,'slug');
				$title = $this->get_value($doc,'name');
				$content = $this->get_value($doc,'description');
			}else{
				$url = esc_url(get_permalink($pageid));
				$slug = $this->get_value($doc,'post_name');
				$title = $this->get_value($doc,'post_title');
				$content = $this->get_value($doc,'post_content');
			}
		}
		$pos = strpos($url,'/page');
		if($pos!=''){
			$surl = substr($url,0,$pos);
			$url = $surl;
		}
		$desc = '';
		$jsontype = 'WebPage';
		$contype = 'website';
		$headline = $title;
		$sitename = get_bloginfo('title');
		if(is_home() or is_front_page()){
			$headline = $sitename;
			$jsontype = 'WebSite';
			$desc = get_bloginfo('description');	
			if(is_home()){
				$pageid = 0;
				$pagetype = 'post';
			}else{
				$pagetype = 'page';
			}
			$arctype = 'home';
		}else if(is_author()){
			$pageid = 0;
			$pagetype = 'author';
		}else if(is_search()){
			$pageid = 0;
			$pagetype = 'search';
		}else{
			if(is_category()){
				$jsontype = 'WebSite';
			}else{
				if(is_single()){$jsontype = 'NewsArticle';}
				$contype = 'article';
			}
		}
		$author = '';
		$published = '';
		$authorid = $this->get_value($doc,'post_author');
		$published = $this->get_value($doc,'post_date');
		$modified = $this->get_value($doc,'post_modified');
		if($authorid!=''){$author = get_the_author_meta('nicename',$authorid);}
		if($published==''){$published = date('Y-m-dTh:i:sZ',time());}
		$logow = '';
		$logoh = '';
		$imgw = '';
		$imgh = '';
		$images = array();
		$logo = $this->get_customizer_setting('header_logoimage');
		if($logo!=''){
			$logow = $this->get_image_info($logo,'width');
			$logoh = $this->get_image_info($logo,'height');
		}
		$ftype = $pagetype;if($arctype=='category'){$ftype = $arctype;}
		$img = $this->get_featured_image($pageid,$arctype);
		if($img!=''){
			$imgw = $this->get_image_info($img,'width');
			$imgh = $this->get_image_info($img,'height');
		}
		$category = '';
		$categories = '';
		if($arctype=='post'){
			$category = $this->get_post_category_data();
			$categories = $this->get_post_category_data($pageid,'all','array');
		}
		$doc['pagetype'] = $pagetype;
		$doc['arctype'] = $arctype;
		$doc['pageid'] = $pageid;
		$doc['postid'] = $post_id;
		$doc['termid'] = $term_id;
		$doc['jsontype'] = $jsontype;
		$doc['url'] = $url;
		$doc['slug'] = $slug;
		$doc['headline'] = $headline;
		$doc['desc'] = $desc;
		$doc['contype'] = $contype;
		$doc['content'] = $content;
		$doc['title'] = $title;
		$doc['sitename'] = $sitename;
		$doc['published'] = $published;
		$doc['modified'] = $modified;
		$doc['author'] = $author;
		$doc['authorid'] = $authorid;
		$doc['publisher'] = get_bloginfo('name');
		$doc['publishertype'] = 'Organization';
		$doc['logo'] = $logo;
		$doc['logow'] = $logow;
		$doc['logoh'] = $logoh;
		$doc['img'] = $img;
		$doc['imgw'] = $imgw;
		$doc['imgh'] = $imgh;
		$doc['images'] = $images;
		$doc['category'] = $category;
		$doc['categories'] = $categories;
		$this->self['DOCDATA'] = $doc;
	}
	
	/**
	 * Filter the post thumbnail to make it AMP compliant and handle it's display options in the post template.
	 * @requires : is_alp, amp_filter_image
	 * @usage : extends_init
	 */
	function wp_post_thumbnail_html($content){
		if($this->is_alp()){
			$content = $this->amp_filter_image($content,'hero');
			$content = str_replace('layout="fixed"','layout="responsive"',$content);
		}
		return $content;
	}
	
	/**
	 * Filter content/string for tag replacements and removal of non-AMP-compliant tag attributes.
	 * @requires : is_alp, docdata, get_value, amp_filter_embeds, amp_filter, strip_tag_attribute, generate_analytics_module, get_customizer_setting
	 * @usage : extends_init
	 */
	function wp_the_content($content){
		if($this->is_alp()){
			$doc = $this->docdata();
			$content = $this->get_value($doc,'content');
			$content = $this->amp_filter_embeds($content);
			$content = $this->amp_filter($content);
			$content = $this->strip_tag_attribute($content,'isadding');
			$content .= $this->generate_analytics_module('facebook',$this->get_customizer_setting('amplp_fbcode'));
			$content .= $this->generate_analytics_module('googleanalytics',$this->get_customizer_setting('amplp_gacode'));
			$content .= $this->generate_analytics_module('googletagmanager',$this->get_customizer_setting('amplp_gtcode'));
			$content .= $this->generate_analytics_module('googleadwords',$this->get_customizer_setting('amplp_gwcode'));
		}
		return $content;
	}
}
$amplp = new TA_wp_amplp("amplp",dirname(__FILE__));
$amplp->register_meta('amplpHeroTitle',array('type'=>'text','name'=>__('Custom Hero Banner Title','amp-landing-pages'),'post_type'=>array('page'),'location'=>'side','show'=>'is_alp','desc'=>__('You may enter a custom slogan or title to be used in the hero banner on this page. If left blank, the page title will be used instead. If no featured image is chosen for this page, no hero banner will be displayed and the title will be displayed as an H1 tag.','amp-landing-pages')));
$amplp->register_meta('amplpDemoContent',array('type'=>'function','function'=>'import_demo_content','name'=>__('Import Demo Content','amp-landing-pages'),'post_type'=>array('page'),'location'=>'side','show'=>'is_alp'));
$ptitle = 'AMP Landing Pages';
if(function_exists('amp_woo')){
	$ptitle = 'AMP for WooCommerce';
}
$amplp->register_customizer_section('amplp',array('title'=>__($ptitle,'amp-landing-pages'),'description'=>__('These settings only work on AMP Landing Pages, they will not be applied to any pages using the theme. Theme settings changed in the customizer while on an AMP Landing Page may cause effects in the preview window that will not be applied to the page.','amp-landing-pages')));
$thicksizes = array();
for($i=0;$i<=10;$i++){$thicksizes[$i.'px'] = $i.'px';}
$fontsizes = array();
for($i=10;$i<=20;$i++){$fontsizes[$i.'px'] = $i.'px';}
$menusizes = $fontsizes;
for($i=22;$i<=40;$i+=2){$fontsizes[$i.'px'] = $i.'px';}
$amplp->register_customizer_setting('amplp_maxwid',array('type'=>'select','title'=>__('Content Max Width','amp-publisher'),'code'=>'content-max-width','default'=>'100%','choices'=>array('100%'=>__('No Limit','amp-landing-pages'),'900px'=>'900px','1000px'=>'1000px','1100px'=>'1100px','1200px'=>'1200px','1300px'=>'1300px','1400px'=>'1400px','1500px'=>'1500px','1600px'=>'1600px'),'description'=>__('Set a maximum width for the page content, header/nav contents and Sections that you set as "wrapped" in the Composer.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_heromaxed',array('type'=>'checkbox','title'=>__('Hero Banner Fit Width','amp-publisher'),'code'=>'hero-fit-width','default'=>'','description'=>__('The hero banner (featured) image is displayed to the edges by default, you may have it constrain width to fit the content max width settings.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_basepad',array('type'=>'select','title'=>__('Standard Padding','amp-landing-pages'),'code'=>'base-padding','choices'=>array('20px'=>'20px','30px'=>'30px','40px'=>'40px','50px'=>'50px','60px'=>'60px','80px'=>'80px','100px'=>'100px'),'default'=>'30px','description'=>__('Choose a standard padding size for a number of elements that use it. (IE: margins, object separation, post separation)','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_bgcolor',array('type'=>'color','title'=>__('Background Color','amp-landing-pages'),'code'=>'background-color','default'=>'#ffffff'));
$amplp->register_customizer_setting('amplp_accentcolor',array('type'=>'color','title'=>__('Accent Line/Shadow Color','amp-landing-pages'),'code'=>'accent-color','default'=>'#cccccc'));
$amplp->register_customizer_setting('amplp_fontcolor',array('type'=>'color','title'=>__('Font Color','amp-landing-pages'),'code'=>'font-color','default'=>'#5C6B80'));
$amplp->register_customizer_setting('amplp_linkcolor',array('type'=>'color','title'=>__('Link Color','amp-landing-pages'),'code'=>'link-color','default'=>'#000000'));
$amplp->register_customizer_setting('amplp_linkhovercolor',array('type'=>'color','title'=>__('Link Hover Color','amp-landing-pages'),'code'=>'link-hover-color','default'=>'#8e8b85'));
$amplp->register_customizer_setting('amplp_fontsize',array('type'=>'select','title'=>__('Body Font Size','amp-landing-pages'),'code'=>'font-size','default'=>'16px','choices'=>$fontsizes));
$amplp->register_customizer_setting('amplp_h1fontsize',array('type'=>'select','title'=>'H1 '.__('Font Size','amp-landing-pages'),'code'=>'h1-font-size','default'=>'36px','choices'=>$fontsizes));
$amplp->register_customizer_setting('amplp_h2fontsize',array('type'=>'select','title'=>'H2 '.__('Font Size','amp-landing-pages'),'code'=>'h2-font-size','default'=>'24px','choices'=>$fontsizes));
$amplp->register_customizer_setting('amplp_h3fontsize',array('type'=>'select','title'=>'H3 '.__('Font Size','amp-landing-pages'),'code'=>'h3-font-size','default'=>'20px','choices'=>$fontsizes));
$amplp->register_customizer_setting('amplp_h4fontsize',array('type'=>'select','title'=>'H4 '.__('Font Size','amp-landing-pages'),'code'=>'h4-font-size','default'=>'18px','choices'=>$fontsizes));
$amplp->register_customizer_setting('amplp_lineheight',array('type'=>'select','title'=>__('Body Line Height Muliplier','amp-landing-pages'),'code'=>'line-height','default'=>'1.8','choices'=>array('1'=>'1','1.1'=>'1.1','1.2'=>'1.2','1.3'=>'1.3','1.4'=>'1.4','1.5'=>'1.5','1.6'=>'1.6','1.7'=>'1.7','1.8'=>'1.8','1.9'=>'1.9','2'=>'2','2.1'=>'2.1','2.2'=>'2.2','2.3'=>'2.3','2.4'=>'2.4','2.5'=>'2.5')));
$amplp->register_customizer_setting('amplp_topnavlogo',array('type'=>'image','title'=>__('Navbar Logo Image','amp-landing-pages'),'code'=>'logo','default'=>''));
$amplp->register_customizer_setting('amplp_topnavheight',array('type'=>'select','title'=>__('Navbar Base Height','amp-landing-pages'),'code'=>'topnav-height','choices'=>array('30px'=>'30px','40px'=>'40px','50px'=>'50px','60px'=>'60px','80px'=>'80px','100px'=>'100px','150px'=>'150px'),'default'=>'50px'));
$amplp->register_customizer_setting('amplp_topnavbgcolor',array('type'=>'color','title'=>__('Navbar Background Color','amp-landing-pages'),'code'=>'topnav-background-color','default'=>'#FFFFFF'));
$amplp->register_customizer_setting('amplp_topnavborder',array('type'=>'color','title'=>__('Navbar Border Color','amp-landing-pages'),'code'=>'topnav-border','default'=>'transparent'));
$amplp->register_customizer_setting('amplp_topnavtxtcolor',array('type'=>'color','title'=>__('Navbar Text Color','amp-landing-pages'),'code'=>'topnav-font-color','default'=>'#343434'));
$amplp->register_customizer_setting('amplp_topnavhovertext',array('type'=>'color','title'=>__('Navbar Hover Text Color','amp-landing-pages'),'code'=>'topnav-font-hover-color','default'=>'#000099'));
$amplp->register_customizer_setting('amplp_topnavfontsize',array('type'=>'select','title'=>__('NavbarText Size','amp-landing-pages'),'code'=>'topnav-font-size','default'=>'18px','choices'=>$menusizes));
$amplp->register_customizer_setting('amplp_topnavcase',array('type'=>'select','title'=>__('NavbarText Case','amp-landing-pages'),'code'=>'topnav-font-case','default'=>'none','choices'=>array('none'=>__('None','amp-landing-pages'),'capitalize'=>__('Capitalize','amp-landing-pages'),'uppercase'=>__('Upper Case','amp-landing-pages'),'lowercase'=>__('Lower Case','amp-landing-pages'))));
$amplp->register_customizer_setting('amplp_heroheight',array('type'=>'select','title'=>__('Hero Banner Height','amp-landing-pages'),'code'=>'hero-height','choices'=>$amplp->amp_pages('get size choices','hero'),'default'=>'500px'));
$amplp->register_customizer_setting('amplp_buttoncolor',array('type'=>'color','title'=>__('Button Font Color','amp-landing-pages'),'section'=>'amplp','code'=>'button-font-color','default'=>''));
$amplp->register_customizer_setting('amplp_buttonhcolor',array('type'=>'color','title'=>__('Button Hover Font Color','amp-landing-pages'),'section'=>'amplp','section'=>'amplp','code'=>'button-font-hover-color','default'=>''));
$amplp->register_customizer_setting('amplp_buttonbgcolor',array('type'=>'color','title'=>__('Button Color','amp-landing-pages'),'section'=>'amplp','code'=>'button-color','default'=>''));
$amplp->register_customizer_setting('amplp_buttonbghcolor',array('type'=>'color','title'=>__('Button Hover Color','amp-landing-pages'),'section'=>'amplp','code'=>'button-hover-color','default'=>''));
$amplp->register_customizer_setting('amplp_fbcode',array('type'=>'text','title'=>'Facebook Pixel','section'=>'amplp','default'=>'','description'=>__('Enter your Facebook Pixel/Remarketing code to add it to your AMP Landing Pages.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_gacode',array('type'=>'text','title'=>'Google Analytics','section'=>'amplp','default'=>'','description'=>__('Enter your Google Analytics Account ID to add it to your AMP Landing Pages.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_gtcode',array('type'=>'text','title'=>'Google Tag Manager','section'=>'amplp','default'=>'','description'=>__('Enter your Google Tag Manager ID to add it to your AMP Landing Pages.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_gwcode',array('type'=>'text','title'=>'Google Ad Words','section'=>'amplp','default'=>'','description'=>__('Enter your Google Ad Words Conversion ID to add it to your AMP Landing Pages. You must also choose either Conversion (default) or Remarketing from the field below.','amp-landing-pages')));
$amplp->register_customizer_setting('amplp_gwtype',array('type'=>'select','title'=>'Google Ad Words Type','section'=>'amplp','default'=>'conversion','choices'=>array('conversion'=>'Conversion','remarketing'=>'Remarketing')));
$amplp->init();
function amp_pages($command='',$pass=''){
	global $amplp;
	return $amplp->amp_pages($command,$pass);
}
function is_amplp($pageid=''){
	return amp_pages('is alp',$pageid);
}
function amplp_allowed_block_types($types,$post){
	$pageid = $post->ID;
	if(is_amplp($pageid)){
		$types = array();
		$types[] = 'core/block';// This is required to whitelist usage of shared blocks, but unallowed shared blocks can still be inserted... as well as converted blcoks and those listed in the hotkey insert menu.
		$types[] = 'core/image';
		$types[] = 'core/gallery';
		$types[] = 'core/heading';
		$types[] = 'core/quote';
		$types[] = 'core/list';
		$types[] = 'core/separator';
		$types[] = 'core/button';
		$types[] = 'core/pullquote';
		$types[] = 'core/table';
		$types[] = 'core/preformatted';
		$types[] = 'core/code';
		$types[] = 'core/cover-image';
		$types[] = 'core/text-columns';
		$types[] = 'core/verse';
		$types[] = 'core/paragraph';
		$types[] = 'core/columns';
		$types[] = 'tacg/tb';
		$types[] = 'tacg/ms';
		$types[] = 'core-embed/twitter';
		$types[] = 'core-embed/youtube';
		$types[] = 'core-embed/facebook';
		$types[] = 'core-embed/instagram';
		$types[] = 'core-embed/vimeo';
		$types[] = 'core-embed/dailymotion';
		$types[] = 'core-embed/imgur';
		$types[] = 'core-embed/reddit';
	}
	return $types;
}
add_action('wp','amplp_startup',10);
function amplp_startup(){
	if(is_amplp() and !is_admin()){
		remove_action('rest_api_init','wp_oembed_register_route');
		remove_filter('oembed_dataparse','wp_filter_oembed_result',10);
		remove_action('wp_head','wp_oembed_add_discovery_links',10);
		remove_action('wp_head','wp_oembed_add_host_js');
		remove_action('wp_head','wp_resource_hints',2);
		remove_action('wp_head','print_emoji_detection_script',7);
		remove_action('wp_head','rel_canonical');
		remove_action('wp_head','rest_output_link_wp_head',10);
		remove_action('wp_print_styles','print_emoji_styles');
		remove_filter('the_content',array($GLOBALS['wp_embed'],'autoembed'),8);
		/* Additional custom CSS is prevented from being inserted by WordPress to ensure AMP compliance. */
		remove_action('wp_head','wp_custom_css_cb',10);
	}
}
add_action('admin_enqueue_scripts','amplp_admin_enqueue_scripts');
function amplp_admin_enqueue_scripts($hook){
	if(amp_pages('is alp editor')){
		wp_enqueue_script('amplp-admin-script',plugins_url('script/amplp_admin.js',__FILE__));
		wp_enqueue_style('amplp-admin-style',plugins_url('script/amplp_admin.css',__FILE__));
	}
}
add_action('admin_init','amplp_admin_init');
function amplp_admin_init(){
	global $amplp;
	add_action('wp_ajax_nopriv_wppb_ajax_communication',array(&$amplp,'ajax_communication'));  
	add_action('wp_ajax_wppb_ajax_communication',array(&$amplp,'ajax_communication'));
	if(amp_pages('is alp editor')){
		remove_editor_styles();
		add_editor_style($amplp->self['FONT_AWESOME_URL']);
		add_editor_style(plugins_url('script/gutenberg_style.css',__FILE__));
		add_editor_style(plugins_url('script/tinymce_style.css',__FILE__));
	}
}
add_action('admin_head','amplp_admin_head');
function amplp_admin_head(){
	global $pagenow;
	if(amp_pages('is alp editor')){
		amp_pages('admin head scripts');
	}
}
function amplp_gutenberg_script(){
	if(amp_pages('is alp editor')){
		wp_enqueue_script('amplp_gutenberg_blocks-backend-script',plugins_url('script/amplp_guts.js',__FILE__),array('wp-blocks','wp-i18n','wp-element'),filemtime(plugin_dir_path(__FILE__).'script/amplp_guts.js'));
		global $amplp;
		$faurl = $amplp->self['FONT_AWESOME_URL'];
		wp_enqueue_style('amplp_fontawesome-backend-style',$faurl,array( 'wp-edit-blocks'));
		wp_enqueue_style('amplp_gutenberg_blocks-backend-style',plugins_url('script/amplp_guts.css',__FILE__),array( 'wp-edit-blocks'),filemtime(plugin_dir_path(__FILE__).'script/amplp_guts.css'));
	}
}
add_action( 'enqueue_block_editor_assets', 'amplp_gutenberg_script' );
register_nav_menus(array('amplp-top'=>__('AMP Landing Pages Top Menu','amp-landing-pages'),'amplp-mobile'=>__('AMPLP Mobile Menu Override','amp-landing-pages')));
/* The PageTemplater class was found at https://www.wpexplorer.com/wordpress-page-templates-plugin/ */
class PageTemplater {
	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;
	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;
	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 
		return self::$instance;
	} 
	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->templates = array();
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);
		} else {
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);
		}
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);
		$this->templates = array(
			'amplp.php' => 'AMP Landing Page',
		);
	} 
	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}
	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 
		wp_cache_delete( $cache_key , 'themes');
		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	} 
	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		if(is_home()){return $template;}
		global $post;
		if ( ! $post ) {
			return $template;
		}
		if ( ! isset( $this->templates[get_post_meta( 
			$post->ID, '_wp_page_template', true 
		)] ) ) {
			return $template;
		} 
		$file = plugin_dir_path( __FILE__ ). get_post_meta( 
			$post->ID, '_wp_page_template', true
		);
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}
		return $template;
	}
} 
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
?>