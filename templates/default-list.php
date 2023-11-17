<article itemtype="http://schema.org/ScholarlyArticle" class="loop-item news-item animated fadeIn">
	<header>
		<h2 class="entry-title"><a href="<?=the_permalink()?>"><span class="title" itemprop="name"><?=the_title()?></span></a></h2>
	</header>
	<div class="entry-summary">
		<p>
		<?=(($doc['comodoc-author'][0]) ? '<span class="author" itemprop="author">'. $doc['comodoc-author'][0] .'</span>. ' : '')?>
		<?=(($doc['comodoc-event'][0]) ? '<span class="event" itemprop="event">'. $doc['comodoc-event'][0] . (($doc['comodoc-date']) ? ' '. $doc['comodoc-date'][0] : '') .'</span>. ' : '')?>
		<?=(($doc['comodoc-publication'][0]) ? '<span itemscope itemtype="http://schema.org/Periodical"><span class="pubName" itemprop="name">'. $doc['comodoc-publication'][0] .'</span></span> '. (($doc['comodoc-date'][0]) ? ' '. $doc['comodoc-date'][0] : '') .'' : '')?>
		<?=(($doc['comodoc-abstract'][0]) ? '<span class="abstract" itemprop="abstract">'. $doc['comodoc-abstract'][0] .'</span>. ' : '')?>
		<?php if ($doc['comodoc-volume'][0]) : ?>
			<span itemprop="isPartOf" itemscope itemtype="http://schema.org/PublicationVolume">
				<?=(($doc['comodoc-volume'][0]) ? '<span class="volume" itemprop="volumeNumber">'. $doc['comodoc-volume'][0] .'</span>' : '')?>
				<?php if ($doc['comodoc-page-start'][0]) : ?>
					<?=(($doc['comodoc-page-start'][0]) ? '<span class="pages" itemprop="pageStart">'. $doc['comodoc-page-start'][0] .'</span>' : '')?><?=(($doc['comodoc-page-end'][0]) ? '-<span class="pages" itemprop="pageEnd">'. $doc['comodoc-page-end'][0] .'</span>' : '')?>. 
				<?php endif; ?>
			</span>	
		<?php endif; ?>
		<?=(($doc['comodoc-doi'][0]) ? 'DOI:<a itemprop="sameAs" href="http://dx.doi.org/'. $doc['comodoc-doi'][0] .'" target="_blank" title="'. the_title() .'">'. $doc['doi'][0] .'</a>.' : '')?>
		</p>
		<p>
		<?=(($doc['comodoc-link'][0]) ? '<a class="read-more" href="'. $doc['comodoc-link'][0] .'" target="_blank" itemprop="url">Read More &gt;</a> ' : '')?>
		<?=(($doc['comodoc-file-id'][0]) ? '<a class="read-more" href="'. wp_get_attachment_url($doc['comodoc-file-id'][0]) .'" target="_blank" itemprop="contentUrl">Read More &gt;</a> ' : '')?>
		</p>	
	</div>
</article>