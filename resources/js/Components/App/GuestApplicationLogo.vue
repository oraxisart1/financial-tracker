<template>
    <NavLink :href="route('dashboard')">
        <img
            alt="Application logo"
            class="tw-mt-5"
            src="/assets/image/full-logo.svg"
            width="200px"
        />
    </NavLink>
</template>
<script setup>
import NavLink from "@/Components/NavLink.vue";
</script>
