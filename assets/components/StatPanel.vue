<template>
  <div
    class="c-stat-panel"
    :class="type ? `c-stat-panel--${type}` : ''"
  >
    <div class="c-stat-panel__value">
      <slot />
    </div>
  </div>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: ''
            }
        }
    };
</script>