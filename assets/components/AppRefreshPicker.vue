<template>
  <div class="c-refresh-picker">
    <el-button
      :type="value ? 'primary' : 'default'"
      @click="handleClick"
    >
      <i class="el-icon-refresh" />
    </el-button>
  </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: Boolean,
                required: true
            }
        },

        methods: {
            handleClick() {
                this.$emit('input', !this.value);
            }
        }
    };
</script>