/**
 * EDS Blog Posts Widget — Frontend JavaScript
 * eDesign Space · https://edesignspace.com/
 */
(function ($) {
  'use strict';

  $(function () {
    $('.eds-posts[data-pagination]').each(function () {
      var $wrap = $(this);
      var type  = $wrap.attr('data-pagination');
      if      ('load_more'      === type) { initLoadMore($wrap); }
      else if ('infinite_scroll' === type) { initInfiniteScroll($wrap); }
    });
  });

  // ── AJAX fetch ──────────────────────────────────────────────────────────────

  function fetchPage($wrap, page, onSuccess, onError) {
    $.ajax({
      url:    $wrap.attr('data-ajaxurl'),
      method: 'POST',
      data: {
        action:    'eds_blog_posts_load_more',
        nonce:     $wrap.attr('data-nonce'),
        widget_id: $wrap.attr('data-widget-id'),
        page:      page,
        per_page:  $wrap.attr('data-per-page'),
      },
      success: function (res) {
        if (res && res.success) { onSuccess(res.data); }
        else { onError(res); }
      },
      error: function (xhr, status, err) { onError({ message: err }); }
    });
  }

  // ── Load More ────────────────────────────────────────────────────────────────

  function initLoadMore($wrap) {
    var $btn     = $wrap.find('.eds-posts__load-more');
    var $spinner = $wrap.find('.eds-posts__spinner');
    var $noMore  = $wrap.find('.eds-posts__no-more');
    var $grid    = $wrap.find('.eds-posts__grid');
    var curPage  = 1;
    var loading  = false;

    $btn.on('click', function () {
      if (loading) return;
      loading = true;
      var label = $btn.attr('data-label') || $btn.text();
      $btn.addClass('is-loading').prop('disabled', true).text('Loading\u2026');
      $spinner.show();

      fetchPage($wrap, curPage + 1,
        function (data) {
          loading = false;
          $spinner.hide();
          $btn.removeClass('is-loading').prop('disabled', false).text(label);
          if (data.html) { $grid.append(data.html); curPage++; }
          if (!data.has_more) { $btn.hide(); $noMore.fadeIn(200); }
        },
        function () {
          loading = false;
          $spinner.hide();
          $btn.removeClass('is-loading').prop('disabled', false).text(label);
        }
      );
    });
  }

  // ── Infinite Scroll ──────────────────────────────────────────────────────────

  function initInfiniteScroll($wrap) {
    if (typeof IntersectionObserver === 'undefined') {
      scrollFallback($wrap); return;
    }
    var $sentinel = $wrap.find('.eds-posts__infinite-sentinel');
    var $spinner  = $wrap.find('.eds-posts__spinner');
    var $noMore   = $wrap.find('.eds-posts__no-more');
    var $grid     = $wrap.find('.eds-posts__grid');
    var curPage   = 1; var loading = false; var done = false;

    var obs = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting || loading || done) return;
      loading = true; $spinner.show();
      fetchPage($wrap, curPage + 1,
        function (data) {
          loading = false; $spinner.hide();
          if (data.html) { $grid.append(data.html); curPage++; }
          if (!data.has_more) { done = true; obs.disconnect(); $noMore.fadeIn(200); }
        },
        function () { loading = false; $spinner.hide(); }
      );
    }, { rootMargin: '200px 0px', threshold: 0 });

    if ($sentinel.length) { obs.observe($sentinel[0]); }
  }

  function scrollFallback($wrap) {
    var $noMore  = $wrap.find('.eds-posts__no-more');
    var $spinner = $wrap.find('.eds-posts__spinner');
    var $grid    = $wrap.find('.eds-posts__grid');
    var curPage  = 1; var loading = false; var done = false;

    $(window).on('scroll.eds-inf', function () {
      if (loading || done) return;
      if ($(window).scrollTop() + $(window).height() < $wrap.offset().top + $wrap.outerHeight() - 300) return;
      loading = true; $spinner.show();
      fetchPage($wrap, curPage + 1,
        function (data) {
          loading = false; $spinner.hide();
          if (data.html) { $grid.append(data.html); curPage++; }
          if (!data.has_more) { done = true; $(window).off('scroll.eds-inf'); $noMore.fadeIn(200); }
        },
        function () { loading = false; $spinner.hide(); }
      );
    });
  }

}(jQuery));
