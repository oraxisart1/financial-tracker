<script setup>
import Header from "@/Components/App/Header.vue";
import Main from "@/Components/App/Main.vue";
import Container from "@/Components/App/Container.vue";
</script>

<template>
    <Container>
        <Header></Header>
        <Main>
            <slot/>
        </Main>
    </Container>
</template>
