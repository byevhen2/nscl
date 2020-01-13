function array_fill(count, value)
{
    value = value || 0;
    return (new Array(count)).fill(value);
}

/**
 * @param {Array} array
 * @param {Callable} callback
 * @returns {Array}
 */
function array_filter(array, callback = value => !!value)
{
    return array.filter(callback);
}
