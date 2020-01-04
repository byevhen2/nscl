/**
 * @param {Number} month Month number starting from 1 (January).
 * @param {Number} year
 * @returns {Number}
 */
function days_in_month(month, year)
{
    // Here is a trick. The days are 1-based. So when we pass 0 as a day number
    // then JS Date object goes a day before (the last day of the previous month).
    // The months in function is 1-based also, but months in Date class in 0-based
    // (%function_month% = %Date_month% + 1).
    // 
    // So that's how it works: we select the 0-day of the next month and then go
    // to the last day of the previous (required) month.
    let date = new Date(year, month, 0);

    return date.getDate();
}
