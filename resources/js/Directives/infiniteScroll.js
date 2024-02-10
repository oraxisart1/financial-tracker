export default {
    mounted(el, { value }) {
        const trigger = document.createElement("span");

        const observer = new IntersectionObserver((entries) =>
            entries.forEach((entry) => entry.isIntersecting && value(), {
                rootMargin: "-150px 0px 0px 0px",
            }),
        );

        observer.observe(trigger);
        el.appendChild(trigger);
    },
};
