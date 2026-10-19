<?php defined('ABSPATH') or die('No Hackers!'); ?>
<ul class="pagination">
	<?php foreach ( $paginate as $page ) :?>
	<li><?=$page?></li>
	<?php endforeach; ?>
</ul>