<template>
  <div class="c-dashboard">
    <div class="c-dashboard__panel">
      <app-time-picker v-model="timePreset" />
      <app-refresh-picker
        v-model="refresh"
        title="Автоматическое обновление"
      />
    </div>

    <el-row :gutter="30">
      <el-col :span="6">
        <app-stat-card title="Отправлено">
          {{ summary.sent ? summary.sent.toLocaleString() : 0 }}
        </app-stat-card>
      </el-col>
      <el-col :span="6">
        <app-stat-card title="Обрабатывается">
          {{ summary.received ? summary.received.toLocaleString() : 0 }}
        </app-stat-card>
      </el-col>
      <el-col :span="6">
        <app-stat-card title="Завершено">
          {{ summary.handled ? summary.handled.toLocaleString() : 0 }}
        </app-stat-card>
      </el-col>
      <el-col :span="6">
        <app-stat-card title="Ошибки">
          {{ summary.failed ? summary.failed.toLocaleString() : 0 }}
        </app-stat-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
    import ViewMixin from './ViewMixin';
    import { fetchFromModule } from '../utils/request';
    import AppTimePicker from '../components/AppTimePicker';
    import AppStatCard from '../components/AppStatCard';
    import AppRefreshPicker from '../components/AppRefreshPicker';

    export default {
        components: {
            AppTimePicker,
            AppRefreshPicker,
            AppStatCard
        },
        mixins: [ViewMixin],

        props: {},

        data() {
            return {
                timePreset: '-24 hours',
                refresh: false,
                summary: {}
            };
        },

        watch: {
            timePreset(newValue, oldValue) {
                if (newValue !== oldValue) {
                    this.fetchSummaryData();
                }
            }
        },

        created() {
            this.fetchSummaryData();
        },

        methods: {
            async fetchSummaryData() {
                this.summary = await fetchFromModule('bsi:queue.api.dashboard.summary', {
                    from: this.timePreset,
                    to: 'now'
                });
            }
        }
    };
</script>