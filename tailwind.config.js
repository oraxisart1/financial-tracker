import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import("tailwindcss").Config} */
export default {
    prefix: "tw-",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Roboto", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navigation: "#445B5E",
                "navigation-inactive": "rgba(68, 91, 94, 0.7)",
                teal: "#4C8286",
                "light-pastel": "rgba(181, 210, 209, 0.7)",
                pastel: "#B5D2D1",
                light: "#DEE9E6",
            },
            fontSize: {
                md: "16px",
                xl: "20px",
                "2xl": "24px",
                "3xl": "32px",
            },
            padding: {
                4.5: "1.125rem",
            },
            gap: {
                7.5: "1.875rem",
            },
        },
    },

    plugins: [forms],
};
