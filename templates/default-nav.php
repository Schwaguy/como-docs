<div id="container-comodocs-async" data-paged="<?=$a['per_page']?>" data-loading="<?=$a['text-loading']?>" data-results="<?=$a['text-results']?>" data-list-template="<?=$a['list-template']?>" data-pagination-template="<?=$a['pagination-template']?>" class="sc-ajax-filter">
	<ul class="nav-filter">
		<li data-loading="<?=$a['text-loading']?> All" data-results="<?=$a['text-results']?>" class="active"><a href="#" data-filter="all-terms" data-term="all-terms" data-page="1" class="active" aria-selected="true">All</a></li>
		<?php foreach ($terms as $term) : ?>
			<li<?=(($term->term_id == $a['active']) ? ' class="active"' : '')?> data-loading="<?=$a['text-loading']?> <?=$term->name?>" data-results="<?=$a['text-results']?> <?=$term->name?>"><a href="<?=get_term_link($term, $term->taxonomy)?>" data-filter="<?=$term->taxonomy?>" data-term="<?=$term->slug?>" data-page="1" data-title="<?=$term->name?>" aria-selected="false"><?=$term->name?></a></li>
		<?php endforeach; ?>
	</ul>
	<p class="status"></p>
	<div class="content"></div>
</div>