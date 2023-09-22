<script setup lang="ts">
import { ref } from 'vue'
import HelloWorld from '@/assets/vue/app/components/HelloWorld.vue'

const year = ref(new Date().getFullYear())

</script>

<template>
    <HelloWorld :year="year" />
</template>