<script setup>
import GuestApplicationLogo from "@/Components/App/GuestApplicationLogo.vue";
import GuestContainer from "@/Components/App/GuestContainer.vue";
</script>

<template>
    <GuestContainer>
        <GuestApplicationLogo />

        <div
            class="tw-w-full tw-bg-white tw-overflow-hidden tw-flex tw-justify-center tw-items-center"
        >
            <slot />
        </div>
    </GuestContainer>
</template>
