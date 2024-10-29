<!DOCTYPE html itemscope itemtype="http://schema.org/WebPage">
<?php
/**
 * The template for displaying AMP Landing Pages
 *
 * This template consolodates all templates into one because
 * Google AMP compliance requires very specific and strict
 * usages of javascript, stylesheets and HTML.
 *
 * @package AMP Landing Pages
 * @since 1.0
 * @version 1.0
 */

defined('ABSPATH') || die();
echo '<html ';
echo '⚡ ';
language_attributes();
echo '>';
?>
<head>
	<meta charset="utf-8">
	<title><?php wp_title(''); ?></title>
	<meta name='robots' content='noindex,follow' />
	<?php amp_pages('meta tags'); ?>
	<?php amp_pages('amp markups'); ?>
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
	<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	<?php amp_pages('head scripts'); ?>	
	<?php amp_pages('head css'); ?>
</head>
<body <?php body_class(); ?>>
<?php if(amp_pages('use nav menu','amplp-mobile') || amp_pages('use nav menu','amplp-top')){ ?>
	<amp-sidebar id="mobnav" class="" layout="nodisplay">
		<div class="mobnavwrap">
			<div id="mobile-navmenu-sub">
				<?php
				if(amp_pages('use nav menu','amplp-mobile')){
					wp_nav_menu(array('container'=>false,'theme_location'=>'amplp-mobile'));
				}else{
					wp_nav_menu(array('container'=>false,'theme_location'=>'amplp-top'));
				}
				?>
			</div>
		</div>
	</amp-sidebar>
<?php } ?>
<div class="main">
	<header id="masthead" class="site-header" role="banner">
		<?php 
		$runnavbar = false;
		$logo = amp_pages('topnav logo');
		if('' !== $logo){$runnavbar = true;}
		if(amp_pages('use nav menu','amplp-top')){$runnavbar = true;}
		if(amp_pages('use nav menu','amplp-mobile')){$runnavbar = true;}
		if(true === $runnavbar){
		?>
			<div class="topnav" itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
					<div class="rap">
						<?php amp_pages('topnav logo','echo'); ?>
						<?php if(amp_pages('use nav menu','amplp-mobile') || amp_pages('use nav menu','amplp-top')){ 
							$nbclass = '';
							if(!amp_pages('use nav menu','amplp-top')){$nbclass = ' nbon';}
						?>
							<a class="navburger<?php echo esc_attr($nbclass); ?>" on="tap:mobnav.open" role="button" tabindex="0"><span class="fa fa-navicon"></span></a>
						<?php } ?>
						<?php if(amp_pages('use nav menu','amplp-top')){ ?>
							<nav itemscope itemtype="http://schema.org/SiteNavigationElement">
								<?php
								wp_nav_menu(array(
									 'container' => false,
									 'menu' => __('AMPLP Top Menu','amp-landing-pages'),
									 'menu_class' => 'topmenu',
									 'theme_location' => 'amplp-top',
									 'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
								));
								?>
							</nav>
						<?php 
						}
						if(function_exists('wc') && function_exists('amp_woo') && (is_shop() || is_product_category())){
							$catbar = amp_woo('category menu');
							if($catbar!=''){
								echo '<div id="CategoryNavBar" class="catbar navigatorbar">';
									echo '<div class="wrap">';
										echo $catbar;
										echo '<div class="cf"></div>';
									echo '</div>';
								echo '</div>';
							}
						}				
						?>
					</div>
				<div class="cf"></div>
			</div>
		<?php } ?>
	</header>
	<?php
	if(function_exists('amp_woo') && true === amp_woo('is woo')){
		amp_woo('woo content');
	}else{
	?>
	<?php if(amp_pages('include hero banner')){ ?>
		<div class="hero <?php amp_pages('hero class'); ?>">
			<div class="pshot hcom">
				<?php the_post_thumbnail('full'); ?>
			</div>
			<div class="pover hcom">
				<div class="hov">
					<h1 class="post-title">
						<?php
						$ptitle = amp_pages('hero title');
						if('' === $ptitle){
							the_title();
						}else{
							echo esc_attr($ptitle);
						}
						?>
					</h1>
				</div>
			</div>
		</div>
	<?php }else{ ?>
		<h1 class="post-title rap">
			<?php
			$ptitle = amp_pages('hero title');
			if('' === $ptitle){
				the_title();
			}else{
				echo esc_attr($ptitle);
			}
			?>
		</h1>
	<?php } ?>
	<div class="cont rap">
		<?php
		if (have_posts()){
			$ran = false;
			while (have_posts()) : the_post();
				if(true === $ran){continue;}$ran = true;
				the_content();
			endwhile;
		}
		?>
	</div>
	<?php } ?>
	<footer class="rap">
		<a href="http://ampwptools.com/" title=“AMPWPTools” rel="nofollow">Powered by AMPWPTools</a>
	</footer>
</div>
</body>
</html>