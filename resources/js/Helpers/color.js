export function guessFontColorByBackgroundColor(
    backgroundColor,
    darkColor = "#000000",
    lightColor = "#FFFFFF",
) {
    backgroundColor = String(backgroundColor).replace("#", "");

    const red = parseInt(backgroundColor.substring(0, 2), 16);
    const green = parseInt(backgroundColor.substring(2, 4), 16);
    const blue = parseInt(backgroundColor.substring(4, 6), 16);

    return red * 0.299 + green * 0.587 + blue * 0.114 > 140
        ? darkColor
        : lightColor;
}
