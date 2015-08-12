(function ($) {

  var jsTheme = {
    init: function () {
      jsTheme.lib.init();
      jsTheme.sticky.init();
      jsTheme.forms.init();
      jsTheme.searchThemes.init();
      jsTheme.equalColumns.init();
      jsTheme.progressAnimator.init();
      jsTheme.accordion.init();
      jsTheme.toggleFieldset.init();
      jsTheme.addMobileBreadcrumb.init();
      jsTheme.addMobileSearchIcon.init();
      jsTheme.stickyNav.init();
    }
  };

  jsTheme.lib = {
    init: function () {
      if (typeof $.fn.matchHeight !== 'undefined') {
        $('.js-height').matchHeight();
        $('.js-equal').matchHeight(false);
      }

      if (typeof $.fn.masonry !== 'undefined') {

        var $container = $('.multi-column-items').masonry({
          itemSelector: 'article',
          columnWidth: '.island',
          isAnimated: true,
          gutter: '.gutter'
        });
        $container.imagesLoaded(function () {
          $container.masonry();
        });

      }
    }
  };

  jsTheme.sticky = {
    init: function () {
      var selector = 'body > .sticky, body > #admin-menu';

      var callback = function () {
        var elements = $(selector);
        var wrapper = $('#sticky-wrapper');
        var spacer = $('#sticky-spacer');
        var height = 0;

        if (elements.length) {
          // Add the wrapper and spacer if missing.
          if (!wrapper.length) {
            wrapper = $('<div>')
              .attr('id', 'sticky-wrapper')
              .css({
                width: '100%',
                position: 'fixed',
                top: '0px',
                left: '0px',
                'z-index': 999
              });

            $('body')
              .prepend(wrapper = $('<div>').attr('id', 'sticky-wrapper').css({
                width: '100%',
                position: 'fixed',
                top: '0px',
                left: '0px',
                'z-index': 999
              }))
              .prepend(spacer = $('<div>').attr('id', 'sticky-spacer'));
          }

          // Move the new elements.
          wrapper.append(
            elements
              .clone()
              .css('position', 'static')
          );
          elements.remove();

          // Resort the content.
          wrapper.append(
            wrapper.children().sort(function (a, b) {
              a = a.className.match(/sticky-(\d+)/) || [0];
              a = parseInt(a[a.length - 1], 10);

              b = b.className.match(/sticky-(\d+)/) || [0];
              b = parseInt(b[b.length - 1], 10);

              return a - b;
            })
          );
        }

        if (!wrapper.children().length) {
          // No wrapper children left, remove our divs.
          wrapper.remove();
          spacer.remove();
        }
        else {
          // Calculate and set the height.
          wrapper.children().each(function () {
            height += $(this).outerHeight();
          });

          spacer.css('height', height + 'px');
        }
      };

      $(selector + ', #sticky-wrapper > *').livequery(callback, callback);
      callback();
    }
  };

  jsTheme.forms = {
    init: function () {
      $('.alert-box').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.alert-box').fadeOut(300);
      });
    }
  };

  jsTheme.searchThemes = {
    init: function () {
      // Open list of themes
      $('.search-filter-theme-title a').click(function (e) {
        e.preventDefault();
        $('.facetapi-facet-theme > ul').fadeToggle('fast');
        $(this).children('span.icon').toggleClass('icon-arrow-down icon-arrow-up');
      });

      // On Mobile open list of types and themes
      $('.search-filter-toggle-btn').click(function (e) {
        e.preventDefault();
        $('.search-filter > div').slideToggle();
      });
    }
  };

  jsTheme.equalColumns = {
    init: function () {
      // Get all columns and set them to equal height
      var tallest = 0;
      if ($(window).width() > 480) {
        $('.equal-columns .col-equal').each(function () {
          var thisHeight = $(this).height();
          if (thisHeight > tallest) {
            tallest = thisHeight;
          }
        });
        $('.equal-columns .col-equal').height(tallest);
      }
    }
  };

  jsTheme.progressAnimator = {
    init: function () {
      // Animate the width of progress indicators
      $('.progress-indicator').each(function () {
        var targetWidth = $(this).data('percentage');
        var duration = targetWidth * 30;
        $(this).animate({
          width: targetWidth + '%'
        }, duration, 'linear');
      });
    }
  };

  jsTheme.accordion = {
    init: function () {
      // Code for simple accordion animations
      $('.accordion').each(function () {
        // Close all contents except the first
        $('.accordion-item-content', this).hide();
        var activeItem = $('.accordion-item:first', this);
        activeItem.find('.accordion-item-content').show();
        activeItem.addClass('active').find('.accordion-item-title span').toggleClass('icon-arrow-down icon-arrow-up');

        // Now toggle items on click
        $('.accordion-item-title a', this).click(function (e) {
          e.preventDefault();
          var currentItem = $(this).parents('.accordion-item');

          if (currentItem.hasClass('active')) {
            currentItem.removeClass('active');
            currentItem.find('.accordion-item-content').slideUp('slow');
            currentItem.find('.accordion-item-title span').toggleClass('icon-arrow-up icon-arrow-down');
          }
          else {
            currentItem.addClass('active');
            currentItem.find('.accordion-item-content').slideDown('slow');
            currentItem.find('.accordion-item-title span').toggleClass('icon-arrow-down icon-arrow-up');
          }
        });
      });
    }
  };

  jsTheme.toggleFieldset = {
    init: function () {
      $('fieldset.collapsible').on('collapsed', function (e) {
        var $fieldset = $(this);

        // Remove previous icons.
        $('.icon-collapsed, .icon-collapsible', $fieldset).remove();

        // Add new icon based on collapsed state.
        if (e.value) {
          $('.fieldset-legend', $fieldset).append('<span class="icon-collapsed"></span>');
        }
        else {
          $('.fieldset-legend', $fieldset).append('<span class="icon-collapsible"></span>');
        }
      });
    }
  };

  /**
   * Creates a mobile breadcrumb as a <select> with <option>s via javascript, based on the printed breadcrumb.
   *   - Dashes will be added per depth level.
   *   - The last option will have current url.
   *   - Each crumb which have no link will be added as disabled option.
   */
  jsTheme.addMobileBreadcrumb = {
    init: function () {

      var breadcrumb = $('ul.nav--breadcrumb');
      breadcrumb.once('mobile-breadcrumb', function () {
        var mobile_breadcrumb = $('<select class="nav nav--mobile-breadcrumb" onchange="window.location=this.value;" />');

        var items = $('li', this);
        $.each(items, function (index, value) {
          var link = $('a', value).attr('href');
          var prefix = new Array(index + 1).join('-');
          var text = prefix ? (prefix + ' ' + $(value).text()) : $(value).text();

          var last_item = (index + 1 === items.length);
          if (typeof link === 'undefined' && last_item) {
            link = window.location.href.replace(/^(?:\/\/|[^\/]+)*\//, '/');
            mobile_breadcrumb.append('<option value="' + link + '">' + text + '</option>');
          }
          else if (typeof link === 'undefined' && !last_item) {
            mobile_breadcrumb.append('<option disabled="disabled">' + text + '</option>');
          }
          else {
            mobile_breadcrumb.append('<option value="' + link + '">' + text + '</option>');
          }
        });

        mobile_breadcrumb.find('option:last').attr('selected', 'selected');
        breadcrumb.after(mobile_breadcrumb);
      });
    }
  };

  /**
   * Adds mobile search icon.
   */
  jsTheme.addMobileSearchIcon = {
    init: function () {
      $('<div class="search-icon-block"><i class="icon icon-search"></i></div>').insertBefore('.top-section > nav.holder > .l-row > .l-primary--offset');
    }
  };

  /**
   * Makes all elements with "sticky-nav" class sticky so they will be attached to the viewport top when scrolling down.
   */
  jsTheme.stickyNav = {
    init: function () {
      if ($('.sticky-nav').length > 0) {
        $('.sticky-nav').once('sticky-nav', function () {
          $(this).sticky({topSpacing: 20});
        });
      }
    }
  };

  /**
   * Splits legend in 2 columns that can be read left to right.
   */
  jsTheme.mapColumnizeLegend = {
    init: function () {

      var external_panel = $('.openlayers-container .external-panel-container');
      if (external_panel.length > 0) {
        $('.layers--default', external_panel).once('columnize', function () {
          var layers = $(this);

           // Prevent checkbox - label splitting.
          $('.layer-wrapper', layers).addClass('dontsplit');

          layers.columnize({
            columns: 2,
            doneFunc: function() {
              // Because columnizer cannot handle display:none elements. We need to columnize the layers first.
              // Then afterwards we should see if we need to collapsed the legend.

              // IMPORTANT
              // This will probably not work when more than 1 openlayers map (with legend) is rendered inside 1 page.
              var toggle_button = $('.external-panel__toggle', external_panel)
              var is_collapsed = toggle_button.hasClass('collapsed');
              if (is_collapsed) {
                $('.external-panel', external_panel).hide();
              }
            }
          });
        });
      }
    }
  };

  // Register, for backwards compatibilty with Drupal's default jQuery version,
  // $.on as alias of $.live.
  if (typeof $.fn.on === 'undefined') {
    jQuery.fn.extend({
      on: jQuery.fn.live
    });
  }

  function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window)) {
      a = 'client';
      e = document.documentElement || document.body;
    }
    return { width : e[a + 'Width'], height : e[a + 'Height'] };
  }

  var windowWidth = viewport().width; // This should match your media query

  function paddingLeftProgressbar() {
    $('.webform-component-progressbar-wrapper').each(function () {
      var number = $('.webform-component-progressbar .webform-component-progressbar-page').length;
      var progressbarLeftPadding = 50 / number;
      $('.webform-component-progressbar').css('padding-left', progressbarLeftPadding + '%');
    });
  }


  function stikyWidth() {
    if ($(window).width() >= 960) {
      var oldWidth = $('#block-system-main .view-mode-full .l-secondary').width();
      $('.field-group-accordion').width(oldWidth);
    } else {
      $('.field-group-accordion').width('auto');
    }
  }

  function positionCategorieDropdown() {
    var catWidth = $('#page-title .current .selector > ul').width();
    var leftmargin =  catWidth / 2;
    var titlespanWidth = $('#page-title .title-span').width();
    var pageWidth = $('#page-title').width();
    var freeWidth = pageWidth - titlespanWidth;
    var extraMargin = leftmargin - freeWidth;
    var dropdownPosition = leftmargin + extraMargin;
    if (freeWidth >= leftmargin) {
      $('#page-title .current .selector > ul').css('margin-left', '-' + leftmargin + 'px');
    } else {
      $('#page-title .current .selector > ul').css('margin-left', '-' + dropdownPosition + 'px');
    }
  }



  function categorieAction() {
    if (windowWidth >= 640) {
      $('#page-title .current .selector').addClass('initial');
      $('#page-title .current').unbind().click(function () {
        // show or hide dropdown
        $('#page-title .current .selector > ul').toggle();
        if ($('#page-title .current .selector').hasClass('close')) {
          $('#page-title .current .selector').removeClass('close');
          $('#page-title .current .selector').addClass('open');
        } else {
          $('#page-title .current .selector').removeClass('open');
          $('#page-title .current .selector').addClass('close');
        }
        $('#page-title .current .selector').removeClass('initial');

      });
    } else {
      //remove click event
      $('#page-title .current').unbind();

      $('#page-title .current .selector').removeClass('close');
      $('#page-title .current .selector').removeClass('open');
    }

    if (windowWidth < 640) {
      $('#page-title .current .selector > ul').hide();
    }
    if (($('#page-title .current .selector').hasClass('close')) && (windowWidth >= 640)) {
      $('#page-title .current .selector > ul').show();
    }

  }

  // Initialize the theme.
  $(jsTheme.init);

  /**
   * Document READY event.
   */
  $(document).ready(function () {
    windowWidth = viewport().width;

    //Show and hide search form
    $('.search-icon-block').click(function () {
      $('.not-front .search-widget > div').toggle([9000]);
    });

    paddingLeftProgressbar();
    stikyWidth();
    categorieAction();
    positionCategorieDropdown();

    jsTheme.mapColumnizeLegend.init();
  });

  /**
   * Document RESIZE event.
   */
  $(document).resize(function () {
    windowWidth = viewport().width;
    stikyWidth();
    paddingLeftProgressbar();
    categorieAction();
    positionCategorieDropdown();
  });

})(jQuery);
