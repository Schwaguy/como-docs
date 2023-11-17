<?php defined('ABSPATH') or die('No Hackers!'); ?>
<?php get_header(); ?>
<?php
	$doc['id'] = get_the_ID();
	$doc['post-date'] = get_the_date();
	$doc['image'] = get_the_post_thumbnail($doc['id'],'full',array('class'=>'team-photo'));
	$doc['title'] = get_the_title();
	$doc['author'] = get_post_meta($doc['id'],'comodoc-author',true);
	$doc['publication'] = get_post_meta($doc['id'],'comodoc-publication',true);
	$doc['event'] = get_post_meta($doc['id'],'comodoc-event',true);
	$doc['abstract'] = get_post_meta($doc['id'],'comodoc-abstract',true);
	$doc['volume'] = get_post_meta($doc['id'],'comodoc-volume',true);
	$doc['number'] = get_post_meta($doc['id'],'comodoc-number',true);
	$doc['page-start'] = get_post_meta($doc['id'],'comodoc-page-start',true);
	$doc['page-end'] = get_post_meta($doc['id'],'comodoc-page-end',true);
	$doc['date'] = get_post_meta($doc['id'],'comodoc-date',true);
	$doc['doi'] = get_post_meta($doc['id'],'comodoc-doi',true);
	$doc['file'] = get_post_meta($doc['id'],'comodoc-file',true);
	$doc['file-id'] = get_post_meta($doc['id'],'comodoc-file-id',true);
	$doc['file-2'] = get_post_meta($doc['id'],'comodoc-file-2',true);
	$doc['file-id-2'] = get_post_meta($doc['id'],'comodoc-file-id-2',true);
	$doc['link'] = get_post_meta($doc['id'],'comodoc-link',true);
	
	$publication = ''; 
	$publication .= '<p itemscope itemtype="http://schema.org/ScholarlyArticle" class="news-item show-on-scroll fadeInUp-on-scroll no-repeat"><p>';
	
	$publication .= (($doc['author']) ? '<span class="author" itemprop="author">'. $doc['author'] .'</span>. ' : '');
	$publication .= (($doc['title']) ? '<span class="title" itemprop="name">'. $doc['title'] .'</span>. ' : '');
	$publication .= (($doc['event']) ? '<span class="event" itemprop="event">'. $doc['event'] . (($doc['date']) ? ' '. $doc['date'] : '') .'</span>. ' : '');
	$publication .= (($doc['publication']) ? '<span itemscope itemtype="http://schema.org/Periodical"><span class="pubName" itemprop="name">'. $doc['publication'] .'</span></span> '. (($doc['date']) ? ' '. $doc['date'] : '') .'' : '');
	$publication .= (($doc['abstract']) ? '<span class="abstract" itemprop="abstract">'. $doc['abstract'] .'</span>. ' : '');
	if ($doc['volume']) {
		$publication .= '<span itemprop="isPartOf" itemscope itemtype="http://schema.org/PublicationVolume">'; 
		$publication .= (($doc['volume']) ? '<span class="volume" itemprop="volumeNumber">'. $doc['volume'] .'</span>' : '');
		if ($doc['page-start']) {
			$publication .= (($doc['page-start']) ? '<span class="pages" itemprop="pageStart">'. $doc['page-start'] .'</span>' : '');
			$publication .= (($doc['page-end']) ? '-<span class="pages" itemprop="pageEnd">'. $doc['page-end'] .'</span>' : '');
			$publication .= '. ';
		}
		$publication .= '</span>';	
	}
	$publication .= (($doc['doi']) ? 'DOI:<a itemprop="sameAs" href="http://dx.doi.org/'. $doc['doi'] .'" target="_blank" title="'. $doc['title'] .'">'. $doc['doi'] .'</a>.' : '');	
	$publication .= '</p>';
	$publication .= (($doc['link']) ? '<a class="read-more" href="'. $doc['link'] .'" target="_blank" itemprop="url">Read More &gt;</a> ' : '');
	$publication .= (($doc['file-id']) ? '<a class="read-more" href="'. wp_get_attachment_url($doc['file-id']) .'" target="_blank" itemprop="contentUrl">'. get_the_title($doc['file-id']) .'</a> ' : '');
	$publication .= '</p>';
?>
<div id="content-wrap">
	<section class="page-section">
		<div class="container main content">
			<div class="row justify-content-center page-content">
				<div class="col-12 col-md-12 col-lg-11 col-xl-10 content-wrap">
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
						<!--<div class="show-on-scroll fadeInDown-on-scroll no-repeat"><?=getTitles($post->ID,'page')?></div>-->
						<div class="content show-on-scroll fadeIn-on-scroll no-repeat">
							<?=apply_filters('the_content',$publication)?>
						</div>
					<?php
						endwhile;
					else:
						include (locate_template('/template-parts/content-none.php',true));         
					endif; ?>
					<div class="post-edit"><?php edit_post_link( __( 'Edit', 'como-strap' ) ); ?></div>
				</div>
			</div>
		 </div><!-- /.main -->
	</section>
</div><!-- /#content-wrap -->
<?php get_footer(); ?>