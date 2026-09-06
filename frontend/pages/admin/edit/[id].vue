<template>
  <DashShell active="overview">
    <VehicleForm :vehicle-id="id" />
  </DashShell>
</template>

<script setup>
const route = useRoute()
const id = route.params.id
</script>
