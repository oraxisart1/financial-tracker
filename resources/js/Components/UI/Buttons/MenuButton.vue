<script setup>
import { defineAsyncComponent, ref } from "vue";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    items: {
        type: Array,
        required: true,
    },
    target: {},
});

const isOpen = ref(false);
const menu = ref(null);
const container = ref(null);
const clickOutsideListener = ref(null);
const scrollHandler = ref(null);
const escHandler = ref(null);

const open = () => {
    isOpen.value = true;
};

const close = () => {
    isOpen.value = false;
};

const toggle = () => {
    isOpen.value ? close() : open();
};

const onMenuEnter = () => {
    alignMenu();
    addClickOutsideListener();
    addScrollHandler();
    addEscHandler();
};

const onMenuLeave = () => {
    removeClickOutsideListener();
    removeScrollHandler();
    removeEscHandler();
};

const alignMenu = () => {
    const containerRect = container.value.getBoundingClientRect();
    menu.value.style.top = `${containerRect.y - containerRect.height * 2}px`;
    menu.value.style.left = `${containerRect.x + containerRect.width}px`;
};

const addClickOutsideListener = () => {
    clickOutsideListener.value = (e) => {
        if (
            !container.value?.contains(e.target) &&
            !menu.value?.contains(e.target)
        ) {
            close();
        }
    };
    document.addEventListener("click", clickOutsideListener.value);
};

const removeClickOutsideListener = () => {
    if (clickOutsideListener.value) {
        document.removeEventListener("click", clickOutsideListener.value);
    }
};

const getIconComponent = (iconName) => {
    return defineAsyncComponent({
        loader: async () => {
            const icons = await import(`@heroicons/vue/24/outline`);
            return icons[iconName];
        },
    });
};

const handleItemClick = (item) => {
    item.onClick?.(props.target);
    close();
};

const addScrollHandler = () => {
    scrollHandler.value = (e) => {
        e.preventDefault();
    };
    document.addEventListener("wheel", scrollHandler.value, {
        passive: false,
    });
};

const removeScrollHandler = () => {
    if (scrollHandler.value) {
        document.removeEventListener("wheel", scrollHandler.value);
    }
};

const addEscHandler = () => {
    escHandler.value = (e) => {
        if (String(e.code).toLowerCase() === "escape") {
            close();
            e.stopPropagation();
        }
    };

    document.addEventListener("keydown", escHandler.value);
};

const removeEscHandler = () => {
    if (escHandler.value) {
        document.removeEventListener("keydown", escHandler.value);
    }
};
</script>

<template>
    <div ref="container" class="tw-relative">
        <span
            v-show="visible || isOpen"
            ref="button"
            class="tw-text-xl tw-cursor-pointer tw-select-none"
            @click="toggle"
            >&vellip;</span
        >

        <Teleport to="body">
            <Transition @enter="onMenuEnter" @leave="onMenuLeave">
                <div
                    v-show="isOpen"
                    ref="menu"
                    class="tw-absolute tw-flex tw-flex-col tw-bg-gray-100 tw-rounded-md tw-mt-4 tw-overflow-auto tw-max-h-[400px] tw-top-0 tw-min-w-[10rem] tw-select-none"
                >
                    <div
                        v-for="item in items"
                        class="tw-flex tw-gap-4 tw-p-4 tw-items-center tw-cursor-pointer hover:tw-bg-light-pastel"
                        @click="handleItemClick(item)"
                    >
                        <span v-if="item.icon">
                            <component
                                :is="getIconComponent(item.icon)"
                                class="tw-w-6 tw-h-6"
                            />
                        </span>

                        <span>{{ item.label }}</span>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped></style>