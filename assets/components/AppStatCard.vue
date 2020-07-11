<template>
  <div class="c-stat-card">
    <div class="c-stat-card__title">
      {{ title }}
    </div>
    <div class="c-stat-card__value">
      <slot />
    </div>
    <div
      v-if="hasDescriptionSlot"
      class="c-stat-card__description"
    >
      <slot name="description" />
    </div>
  </div>
</template>

<script>
    export default {
        props: {
            title: {
                type: String,
                default: ''
            }
        },

        computed: {
            hasDescriptionSlot() {
                return !!this.$slots.description;
            }
        }
    };
</script>