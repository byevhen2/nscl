function array_fill(count, value)
{
    value = value || 0;
    return (new Array(count)).fill(value);
}
