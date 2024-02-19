<script setup>
import ApplicationLogo from "@/Components/App/ApplicationLogo.vue";
import Container from "@/Components/App/Container.vue";
</script>

<template>
    <Container>
        <div>
            <ApplicationLogo
                class="tw-w-20 tw-h-20 tw-fill-current tw-text-gray-500"
            />
        </div>

        <div
            class="tw-w-full tw-bg-white tw-overflow-hidden tw-flex tw-justify-center tw-items-center"
        >
            <slot />
        </div>
    </Container>
</template>
