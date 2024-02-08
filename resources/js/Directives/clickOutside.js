export default {
    mounted(el, { value: [handler, options] }) {
        el.clickOutsideHandler = (e) => {
            if (options.targets?.length) {
                for (const target of options.targets) {
                    if (target.value.contains(e.target)) {
                        return;
                    }
                }

                handler();
            } else if (!el.contains(e.target)) {
                handler();
            }
        };

        document.addEventListener("click", el.clickOutsideHandler);
    },
    beforeUnmount(el) {
        document.removeEventListener("click", el.clickOutsideHandler);
    },
};
