<template>
    <NavLink :href="route('dashboard')">
        <img
            alt="Application logo"
            class="tw-w-14 tw-h-14"
            src="/assets/image/short-logo.svg"
        />
    </NavLink>
</template>
<script setup>
import NavLink from "@/Components/NavLink.vue";
</script>
