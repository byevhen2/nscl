/*
 * Functions:
 *     random
 *     random_fill
 *     random_in
 */

if (window.random == undefined) {
    /**
     * @param {Number} min Optional. 0 by default.
     * @param {Number} max Optional. 1 by default.
     * @return {Number} Integer nubmer in range [min; max].
     */
    function random(min, max)
    {
        "use strict";

        if (min == undefined) {
            min = 0;
        }

        if (max == undefined) {
            max = 1;
        }

        let range = max - min;
        let random = Math.random() * range + min;

        return Math.round(random);
    }
}

if (window.random_fill == undefined) {
    /**
     * Combination of array_fill() and random().
     *
     * @param {Number} count Optional. 0 by default.
     * @param {Number} min Optional. 0 by default.
     * @param {Number} max Optional. 1 by default.
     * @returns {Array} An array of random numbers.
     */
    function random_fill(count, min, max)
    {
        "use strict";

        if (count == undefined) {
            return [];
        } else {
            return (new Array(count)).map(() => random(min, max));
        }
    }
}

if (window.random_in == undefined) {
    function random_in(/*...*/)
    {
        "use strict";

        let selectedIndex = random(1, arguments.length);
        return arguments[selectedIndex];
    }
}
