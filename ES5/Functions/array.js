function array_fill(count, value)
{
    "use strict";

    value = value || 0;
    return (new Array(count)).fill(value);
}
