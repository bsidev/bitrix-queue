<template>
    <div class="c-dashboard-panel">
        <div class="c-dashboard-panel__header">
            <div class="c-dashboard-panel__title">
                {{ title }}
            </div>
        </div>
        <div class="c-dashboard-panel__content">
            <slot>
                <div class="c-dashboard-panel__stub">
                    <i class="el-icon-s-data" />
                    <div>Нет данных</div>
                </div>
            </slot>
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
        }
    };
</script>