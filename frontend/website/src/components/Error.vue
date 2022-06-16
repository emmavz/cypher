<script>
export default {
}
</script>

<template>
    <div v-if="isError == 1" class="error-container">
        <h2>Error</h2>
        <p>
            This request couldn't be processed right now. Please try again later!
        </p>
    </div>
</template>