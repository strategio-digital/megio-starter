<script setup lang="ts">
defineProps<{ year: number }>()
</script>

<template>
    <span style="color: #fff; font-size: .8rem">&copy; 2022 - {{ year }}</span>
</template>