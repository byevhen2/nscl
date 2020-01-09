/**
 * Reset the number of available options in the select element.
 *
 * @param {Object} $select jQuery object of the select element.
 * @param {Number} limit
 * @param {Function} textCallback Optional. Callback to generate text for each
 *     option. By default uses the numbers as text content.
 */
function $reset_options_number($select, limit, textCallback = number => number)
{
    let optionsCount = $select.children('[value!=""]').length;

    if (optionsCount < limit) {
        // Add more options
        for (let i = optionsCount + 1; i <= limit; i++) {
            let $option = jQuery('<option value="' + i + '">' + textCallback(i) + '</option>');
            $select.append($option);
        }

    } else if (optionsCount > limit) {
        // Remove extra options
        $select.children().each((i, option) => {
            if (option.value !== '' && parseInt(option.value) > limit) {
                option.remove();
            }
        });
    }
}

/**
 * Reset the number of available options in the select element.
 *
 * @param {Object} select Instance of the select element.
 * @param {Number} limit
 * @param {Function} textCallback Optional. Callback to generate text for each
 *     option. By default uses the numbers as text content.
 */
function reset_options_number(select, limit, textCallback = number => number)
{
    let optionsCount = 0;
    let extraOptions = []; // Extra options to remove

    // Count options and also search all the extra options that we'll remove later
    select.childNodes.forEach(option => {
        if (option.value === '') {
            return;
        }

        if (parseInt(option.value) > limit) {
            extraOptions.push(option);
        } else {
            optionsCount++;
        }
    });

    // Add or remove the options
    if (optionsCount < limit) {
        // Add more options
        for (let i = optionsCount + 1; i <= limit; i++) {
            let option = '<option value="' + i + '">' + textCallback(i) + '</option>';

            select.insertAdjacentHTML('beforeend', option);
        }

    } else if (extraOptions.length > 0) {
        // Remove extra options
        extraOptions.forEach(option => option.remove());
    }
}
