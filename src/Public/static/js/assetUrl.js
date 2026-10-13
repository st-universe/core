function assetUrl(value) {
    if (typeof value !== "string" || !/^\/?assets\//.test(value)) {
        return value;
    }
    const fragmentIndex = value.indexOf("#");
    const fragment = fragmentIndex < 0 ? "" : value.slice(fragmentIndex);
    const withoutFragment = fragmentIndex < 0 ? value : value.slice(0, fragmentIndex);
    const queryIndex = withoutFragment.indexOf("?");
    const path = queryIndex < 0 ? withoutFragment : withoutFragment.slice(0, queryIndex);
    let key;
    try {
        key = decodeURIComponent(path.replace(/^\/?assets\//, ""));
    } catch {
        return value;
    }
    const hash = window.STU_ASSET_VERSIONS?.[key];
    if (!hash) {
        return value;
    }
    const parameters = (queryIndex < 0 ? [] : withoutFragment.slice(queryIndex + 1).split("&"))
        .filter(parameter => parameter && parameter.split("=", 1)[0] !== "v");
    parameters.push("v=" + hash);
    return path + "?" + parameters.join("&") + fragment;
}
