 <?php
// Default Document Display Template
// $comodoc_array = (id,image,title,author,publication,event,volume,number,page-start,page-end,date,doi,file,link);
// [comodocs template=TEMPLATE NAME document-cat=DOCUMENT_CATEGORY orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC]
$comodocDisplay = '<div class="row justify-content-center"><div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-10">';
$comodocDisplay .= '<ul class="publication-list">';
foreach ($comodoc_array as $doc) {
	
	$term_obj_list = get_the_terms($doc['id'], 'document-cat');
	$categories = (($term_obj_list) ? join(' ', wp_list_pluck($term_obj_list, 'slug')) : ''); 
	
	if ($doc['file']) {
		$doclink = $doc['file'];
	} elseif ($doc['link']) {
		$doclink = $doc['link'];
	}
	
	$comodocDisplay .= '<li itemscope itemtype="http://schema.org/ScholarlyArticle" class="publication row '. $categories .'">';
	
	// Date Column
	$comodocDisplay .='<div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3">';
	$comodocDisplay .= '<span class="date" itemprop="datePublished">'. date('F, Y', strtotime($doc['pub-date'])) .'</span>';
	$comodocDisplay .='</div>';
	
	// Article Column
	$comodocDisplay .='<div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9"><p>';
	$comodocDisplay .= (($doc['author']) ? '<span class="author" itemprop="author">'. $doc['author'] .'</span>. ' : '');
	$comodocDisplay .= (($doc['title']) ? '<a href="'. $doclink .'" target="_blank"><span class="title" itemprop="name">'. $doc['title'] .'</span></a>. ' : '');
	$comodocDisplay .= (($doc['event']) ? '<span class="event" itemprop="name">'. $doc['event'] .'</span>. ' : '');
	$comodocDisplay .= (($doc['publication']) ? '<span itemscope itemtype="http://schema.org/Periodical"><span class="pubName" itemprop="name">'. $doc['publication'] .'</span></span> '. (($doc['date']) ? ' '. $doc['date'] : '') .'' : '') .'; ';
	if ($doc['volume']) {
		$comodocDisplay .= '<span itemprop="isPartOf" itemscope itemtype="http://schema.org/PublicationVolume">'; 
		$comodocDisplay .= (($doc['volume']) ? '<span class="volume" itemprop="volumeNumber">'. $doc['volume'] .'</span>' : '');
		$comodocDisplay .= (($doc['number']) ? ' <span class="number" itemprop="issueNumber">'. $doc['number'] .'</span>' : '');
		$comodocDisplay .= ((($doc['volume']) || ($doc['number'])) ? ': ' : '');
		if ($doc['page-start']) {
			$comodocDisplay .= (($doc['page-start']) ? '<span class="pages" itemprop="pageStart">'. $doc['page-start'] .'</span>' : '');
			$comodocDisplay .= (($doc['page-end']) ? '-<span class="pages" itemprop="pageEnd">'. $doc['page-end'] .'</span>' : '');
			//$comodocDisplay .= '<span class="pages" itemprop="pageEnd">'. (($doc['page-end']) ? $doc['page-end'] : $doc['page-start']) .'</span>, ';
			$comodocDisplay .= '. ';
		}
		$comodocDisplay .= '</span>';	
	}
	//$comodocDisplay .= (($doc['date']) ? '<span class="date" itemprop="datePublished">'. $doc['date'] .'</span>.' : '') .'<br>';
	$comodocDisplay .= (($doc['doi']) ? 'DOI:<a itemprop="sameAs" href="http://dx.doi.org/'. $doc['doi'] .'" target="_blank" title="'. $doc['title'] .'">'. $doc['doi'] .'</a>.' : '');	
	$comodocDisplay .= '</p><a class="more-link" href="'. $doclink .'" target="_blank">' . insertStringText('View','publications') .'</a>';
	
	/*if ($doc['file']) {
		$comodocDisplay .= '<br><a class="more-link" href="'. $doc['file'] .'" target="_blank">'. insertStringText('View','publications') .'</a>';
	} elseif ($doc['link']) {
		$comodocDisplay .= '<br><a class="more-link" href="'. $doc['link'] .'" target="_blank">'. insertStringText('View','publications') .'</a>';
	}*/
	$comodocDisplay .='</div>';
	$comodocDisplay .= '</li>';
}
$comodocDisplay .= '<li class="publication row"><div class="col-12 col-xs-12 col-sm-4 col-md-3 col-lg-3"></div><div class="col-12 col-xs-12 col-sm-8 col-md-9 col-lg-9"><em>'. insertStringText('Note: This is a select list of our scientific publications.','publications') .'</em></div></li>';
$comodocDisplay .= '</ul><!-- /doclist -->'; 	
$comodocDisplay .= '</div></div>'; 	
// Add Isotope Scripts to Footer
function add_script_to_footer($currentFilter) {
	$currentFilter = ((get_query_var('pganchor')) ? get_query_var('pganchor') : 'all');
	$showCat = ((!empty($currentFilter)) ? 'filter: \'.'. $currentFilter .'\',' : ''); 
    
	echo '<script type="text/javascript">
		jQuery(document).ready(function($) {
			
			//console.log(\'filterValue: '. $currentFilter .'\');
			
			
			updatePubs(\''. $currentFilter .'\');
			$(\'.publication-nav\').on( \'click\', \'a\', function() {
				var filterValue = $(this).attr(\'href\').split(\'/\')[3];
				
				console.log(\'filterValue: \'+ filterValue);
				
				if (filterValue === null || filterValue == \'scientific-publications\') { filterValue = \'all\' }
				updatePubs(filterValue);
				if (filterValue == \'all\') {
					window.history.pushState(null,null,\'/science/scientific-publications/\');
				} else if (filterValue != \'\') {
					var newURL = \'/science/scientific-publications/\' + filterValue + \'/\';
					window.history.pushState(null,null,newURL);
				}
				return false;
			});
			function updatePubs(current) {
				if (current === \'all\') {
					$(\'.publication-nav a\').removeClass(\'active\');
					$(\'.publication\').addClass(\'active\');
				} else {
					$(\'.publication-nav .menu-item\').not(\'.\' + current).removeClass(\'active\');
					$(\'.publication-nav .menu-item.\' + current).addClass(\'active\');
					$(\'.publication\').not(\'.\' + current).removeClass(\'active\');
					$(\'.publication.\' + current).addClass(\'active\');
				}
			}
		});
	</script>';
}
add_action('wp_footer', 'add_script_to_footer', 100);
?>