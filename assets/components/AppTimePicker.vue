<template>
    <div class="c-time-picker">
        <el-dropdown @command="handleCommand">
            <el-button type="default">
                <i class="el-icon-time el-icon--left" />{{ currentPreset.name }}<i class="el-icon-arrow-down el-icon--right" />
            </el-button>

            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item
                    v-for="preset in enabledPresets"
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
                    return [];
                }
            }
        },

        data() {
            return {
                allPresets: [
                    { id: '-5 minutes', name: this.$t('enums.datePreset.last5m') },
                    { id: '-15 minutes', name: this.$t('enums.datePreset.last15m') },
                    { id: '-30 minutes', name: this.$t('enums.datePreset.last30m') },
                    { id: '-1 hours', name: this.$t('enums.datePreset.last1h') },
                    { id: '-3 hours', name: this.$t('enums.datePreset.last3h') },
                    { id: '-6 hours', name: this.$t('enums.datePreset.last6h') },
                    { id: '-12 hours', name: this.$t('enums.datePreset.last12h') },
                    { id: '-24 hours', name: this.$t('enums.datePreset.last24h') },
                    { id: '-2 days', name: this.$t('enums.datePreset.last2d') },
                    { id: '-7 days', name: this.$t('enums.datePreset.last7d') },
                    { id: '-30 days', name: this.$t('enums.datePreset.last30d') },
                    { id: '-60 days', name: this.$t('enums.datePreset.last60d') },
                    { id: '-90 days', name: this.$t('enums.datePreset.last90d') },
                    { id: '-6 months', name: this.$t('enums.datePreset.last6M') },
                    { id: '-1 year', name: this.$t('enums.datePreset.last1y') }
                ]
            };
        },

        computed: {
            currentPreset() {
                return this.allPresets.find(preset => preset.id === this.value);
            },

            enabledPresets() {
                if (this.presets.length === 0) {
                    return this.allPresets;
                }

                return this.presets.map(presetId => this.allPresets.find(preset => preset.id === presetId));
            }
        },

        methods: {
            handleCommand(command) {
                this.$emit('input', command);
                this.$emit('change', command);
            }
        }
    };
</script>