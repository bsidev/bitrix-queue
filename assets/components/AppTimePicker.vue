<template>
  <div class="c-time-picker">
    <el-dropdown @command="handleCommand">
      <el-button type="default">
        <i class="el-icon-time el-icon--left" />{{ currentPreset.name }}<i class="el-icon-arrow-down el-icon--right" />
      </el-button>

      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item
          v-for="preset in presets"
          :key="preset.id"
          :command="preset.id"
        >
          {{ preset.name }}
          <i
            v-if="preset.id === value"
            class="el-icon-check el-icon--right"
          />
        </el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>
  </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: String,
                required: true
            },
            presets: {
                type: Array,
                default: () => {
                    return [
                        { id: '-5 minutes', name: 'Последние 5 минут' },
                        { id: '-15 minutes', name: 'Последние 15 минут' },
                        { id: '-30 minutes', name: 'Последние 30 минут' },
                        { id: '-1 hours', name: 'Последний 1 час' },
                        { id: '-3 hours', name: 'Последние 3 часа' },
                        { id: '-6 hours', name: 'Последние 6 часа' },
                        { id: '-12 hours', name: 'Последние 12 часа' },
                        { id: '-24 hours', name: 'Последние 24 часа' },
                        { id: '-2 days', name: 'Последние 2 дня' },
                        { id: '-7 days', name: 'Последние 7 дней' },
                        { id: '-30 days', name: 'Последние 30 дней' },
                        { id: '-60 days', name: 'Последние 60 дней' },
                        { id: '-90 days', name: 'Последние 90 дней' },
                        { id: '-6 months', name: 'Последние 6 месяцев' },
                        { id: '-1 year', name: 'Последний 1 год' },
                    ];
                }
            }
        },

        computed: {
            currentPreset() {
                return this.presets.find(preset => preset.id === this.value);
            }
        },

        methods: {
            handleCommand(command) {
                this.$emit('input', command);
            }
        }
    };
</script>