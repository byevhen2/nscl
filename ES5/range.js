/*
 * Functions:
 *     in_range
 *     limit
 */

if (window.in_range == undefined) {
    function in_range(value, min, max)
    {
        "use strict";
        return value >= min && value <= max;
    }
}

if (window.limit == undefined) {
    function limit(value, min, max)
    {
        "use strict";

        if (Array.isArray(value)) {
            return value.map(value => limit(value, min, max));
        }

        if (min == undefined) min = -Infinity;
        if (max == undefined) max = Infinity;

        return Math.max(min, Math.min(value, max));
    }
}
