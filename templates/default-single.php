<?php
// Default Document Display Template
// $comodoc_array = (id,image,title,author,publication,event,volume,number,page-start,page-end,date,doi,file,link);
// [comodocs template=TEMPLATE NAME document-cat=DOCUMENT_CATEGORY orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC]
$comodocDisplay = '<div class="row justify-content-center"><div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';
$comodocDisplay .= '<ul class="publication-list">';
foreach ($comodoc_array as $doc) {
	$comodocDisplay .= '<li itemscope itemtype="http://schema.org/ScholarlyArticle" class="publication row">';
	$comodocDisplay .='<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';
	$comodocDisplay .= (($doc['date']) ? '<span class="date" itemprop="datePublished">'. $doc['date'] .'</span>' : '') .'<br>';
	$comodocDisplay .='</div>';
	$comodocDisplay .='<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9">';
	$comodocDisplay .= (($doc['author']) ? '<span class="author" itemprop="author">'. $doc['author'] .'</span>. ' : '');
	$comodocDisplay .= (($doc['title']) ? '<span class="title" itemprop="name">'. $doc['title'] .'</span>. ' : '');
	$comodocDisplay .= (($doc['publication']) ? '<span itemscope itemtype="http://schema.org/Periodical"><span class="pubName" itemprop="name">'. $doc['publication'] .'</span></span>. ' : '');
	$comodocDisplay .= (($doc['event']) ? '<span class="event" itemprop="name">'. $doc['event'] .'</span>. ' : '');
	if ($doc['volume']) {
		$comodocDisplay .= '<span itemprop="isPartOf" itemscope itemtype="http://schema.org/PublicationVolume">'; 
		$comodocDisplay .= (($doc['volume']) ? 'vol. <span class="volume" itemprop="volumeNumber">'. $doc['volume'] .'</span>' : '');
		$comodocDisplay .= (($doc['number']) ? ' no. <span class="number" itemprop="issueNumber">'. $doc['number'] .'</span>' : '');
		$comodocDisplay .= ((($doc['volume']) || ($doc['number'])) ? ', ' : '');
		if ($doc['page-start']) {
			$comodocDisplay .= (($doc['page-start']) ? 'pp. <span class="pages" itemprop="pageStart">'. $doc['page-start'] .'</span>-' : '');
			$comodocDisplay .= '<span class="pages" itemprop="pageEnd">'. (($doc['page-end']) ? $doc['page-end'] : $doc['page-start']) .'</span>, ';
		}
		$comodocDisplay .= '</span>';	
	}
	//$comodocDisplay .= (($doc['date']) ? '<span class="date" itemprop="datePublished">'. $doc['date'] .'</span>.' : '') .'<br>';
	$comodocDisplay .= (($doc['doi']) ? 'doi: <a itemprop="sameAs" href="http://dx.doi.org/'. $doc['doi'] .'" target="_blank" title="'. $doc['title'] .'">'. $doc['doi'] .'</a><br>' : '');	
	if ($doc['file']) {
		$comodocDisplay .= '<br><a class="more-link" href="'. $doc['file'] .'" target="_blank">'. insertStringText('View','publications') .'</a>';
	} elseif ($doc['link']) {
		$comodocDisplay .= '<br><a class="more-link" href="'. $doc['link'] .'" target="_blank">'. insertStringText('View','publications') .'</a>';
	}
	$comodocDisplay .='</div>';
	$comodocDisplay .= '</li>';
}
$comodocDisplay .= '</ul><!-- /doclist -->'; 	
$comodocDisplay .= '</div></div>'; 	
?>