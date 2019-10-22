(function ($) {
    $.fn.justTabs = function (args) {
        var options = this.extend({
            activeClass: 'active',
            hideClass: '' // The class used to hide elements
        }, args);

        var navs = this.filter('a');
        var sections = {}; // {#href: jQuery section element}

        var currentNav = $('');     // Empty collection but all functions will work
        var currentSection = $(''); // on it. No need to check "if x != null"

        // Collect all available sections in the current set
        navs.each(function (i, nav) {
            var selector = nav.getAttribute('href'); // nav.href will return full URL
            var section = $(selector);

            sections[selector] = section; // Collection length is >= 0

            // Hide all section on start except of current one
            if (!nav.classList.contains(options.activeClass)) {
                hideSection(section);
            }
        });

        // Listen for clicks on navs
        navs.on('click', function (event) {
            event.preventDefault();

            var newNav = $(this);

            if (!newNav.hasClass(options.activeClass)) {
                var newSection = sections[newNav.attr('href')];

                selectNav(newNav);
                selectSection(newSection);
            }

            // Remove selection border
            newNav.blur();
        });

        // Show one section
        var selectedNav = navs.filter('.' + options.activeClass).first();

        if (selectedNav.length > 0) {
            // Show section of the current tab
            selectNav(selectedNav);
            selectSection( sections[selectedNav.attr('href')] );

        } else if (navs.length > 0) {
            // Show first tab
            var newNav = navs.first();
            var newSection = sections[newNav.attr('href')];

            selectNav(newNav);
            selectSection(newSection);
        }

        function selectNav(newNav)
        {
            currentNav.removeClass(options.activeClass);
            newNav.addClass(options.activeClass);

            currentNav = newNav;
        }

        function selectSection(newSection)
        {
            hideSection(currentSection);
            showSection(newSection)

            currentSection = newSection;
        }

        function hideSection(section)
        {
            if (options.hideClass != '') {
                section.addClass(options.hideClass);
            } else {
                section.hide();
            }
        }

        function showSection(section)
        {
            if (options.hideClass != '') {
                section.removeClass(options.hideClass);
            } else {
                section.show();
            }
        }

        return this;
    };
})(jQuery);
