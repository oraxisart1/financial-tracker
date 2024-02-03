export default {
    mounted(el, { value }) {
        el.keyPressHandler = (e) => {
            const code = String(e.code).toLowerCase();
            for (const keyCode of Object.keys(value)) {
                if (String(keyCode).toLowerCase() === code) {
                    value[keyCode]();
                }
            }
        };

        document.addEventListener("keyup", el.keyPressHandler);
    },
    beforeUnmount(el) {
        document.removeEventListener("keyup", el.keyPressHandler);
    },
};
