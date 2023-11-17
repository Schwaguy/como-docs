// Ajax Document Filter
(function($) {
	$doc = $(document);
	$doc.ready( function() {
		// Retrieve posts
		
		//console.log($params);
		
		function get_posts($params) {
			$container = $('#container-comodocs-async');
			$content   = $container.find('.content');
			$status    = $container.find('.status');
			//$status.text('Loading...');
			//$status.text($('#container-comodocs-async').data('loading'));
			
			var loadingText = (($('#container-comodocs-async').find('.active').data('loading')) ? $('#container-comodocs-async').find('.active').data('loading') : $('#container-comodocs-async').data('loading')); 
			$status.text(loadingText);
			
			$.ajax({
				url: comoDocs.ajax_url,
				data: {
					action: 'do_filter_posts',
					nonce: comoDocs.nonce,
					params: $params
				},
				type: 'post',
				dataType: 'json',
				success: function(data, textStatus, XMLHttpRequest) {
					if (data.status === 200) {
						$content.html(data.content);
					} else if (data.status === 201) {
						$content.html(data.message);
					} else {
						$status.html(data.message);
					}
				},
				error: function(MLHttpRequest, textStatus, errorThrown) {
					$status.html(textStatus);
					/*console.log(MLHttpRequest);
					console.log(textStatus);
					console.log(errorThrown);*/
				},
				complete: function(data, textStatus) {
					msg = textStatus;
					if (textStatus === 'success') {
						msg = data.responseJSON.found;
					}
					//$status.text('Posts found: ' + msg);
					
					var resultText = (($('#container-comodocs-async').find('.active').data('results')) ? $('#container-comodocs-async').find('.active').data('results') : $('#container-comodocs-async').data('results'));
					
					$status.text(resultText +': ' + msg);
	            	/*console.log(data);
	            	console.log(textStatus);*/
				}
			});
		}
		// Bind get_posts to tag cloud and navigation
		$('#container-comodocs-async').on('click', 'a[data-filter], .pagination a', function(event) {
			if(event.preventDefault) { event.preventDefault(); }
			$this = $(this);
			
			var scrollToTop = (($('#container-comodocs-async').data('scroll-top')) ? $('#container-comodocs-async').data('scroll-top') : false); 
			var scrolloffset = (($('#container-comodocs-async').data('scroll-offset')) ? $('#container-comodocs-async').data('scroll-offset') : 0);  
			var scrollDelay = (($('#container-comodocs-async').data('scroll-delay')) ? $('#container-comodocs-async').data('scroll-delay') : 0);  
			
			$('#container-comodocs-async .content .loop-item').fadeOut('medium');
			// Set filter active
			if ($this.data('filter')) {
				$this.closest('ul').find('.active').removeClass('active').attr('aria-selected', false);
				$this.addClass('active').attr('aria-selected', true).parent('li').addClass('active');
				$page = $this.data('page');
			} else {
				// Pagination
				$page = parseInt($this.attr('href').replace(/\D/g,''));
				$this = $('.nav-filter .active a');
			}
			$params    = {
				'page' : $page,
				'tax'  : $this.data('filter'),
				'term' : $this.data('term'),
				'qty'  : $this.closest('#container-comodocs-async').data('paged'),
				'list-template' : $this.closest('#container-comodocs-async').data('list-template'),
				'pagination-template' : $this.closest('#container-comodocs-async').data('pagination-template')
			};
			// Run query
			get_posts($params);
			
			if (scrollToTop) {
				$('html,body').stop().animate({
					scrollTop: (jQuery('#container-comodocs-async').offset().top - scrolloffset)
				}, scrollDelay, 'easeInOutExpo');
			}
			//$('#container-comodocs-async .content .loop-item').fadeIn('medium');
			
		});
		$('a[data-term="all-terms"]').trigger('click');
	});
})(jQuery);