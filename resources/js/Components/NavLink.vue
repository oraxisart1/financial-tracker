<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});
</script>

<template>
    <Link
        :class="`tw-text-navigation tw-font-medium tw-px-1.5 ' + ${
            active
                ? 'tw-border-l-2 tw-border-r-2 tw-text-2xl tw-border-navigation'
                : 'tw-text-shadow-underline'
        }`"
        :href="href"
    >
        <slot />
    </Link>
</template>
