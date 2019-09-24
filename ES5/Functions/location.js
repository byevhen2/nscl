/**
 * @param {String} url
 * @param {Boolean} replaceCurrent Optional. Remove the URL of the current
 *     document from the document history. No by default.
 */
function redirect_to(url, replaceCurrent)
{
    if (!replaceCurrent) {
        window.location.href = url;
    } else {
        window.location.replace(url);
    }
}

/**
 * @param {Boolean} loadFromServer Force the reloaded page to come from the
 *     server (instead of cache).
 */
function reload_page(loadFromServer)
{
    loadFromServer = loadFromServer || true;
    document.location.reload(loadFromServer);
}
