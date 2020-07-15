<template>
  <div class="c-dashboard">
    <div class="c-dashboard__panel">
      <app-time-picker v-model="timePreset" />
      <app-refresh-picker
        v-model="refresh"
        title="Автоматическое обновление"
      />
    </div>

    <el-row
      type="flex"
      :gutter="20"
    >
      <el-col :span="4">
        <dashboard-panel title="Статус">
          <stat-panel>
            TODO
          </stat-panel>
        </dashboard-panel>
      </el-col>
      <el-col :span="4">
        <dashboard-panel title="Подписчиков">
          <stat-panel>
            TODO
          </stat-panel>
        </dashboard-panel>
      </el-col>
      <el-col :span="4">
        <dashboard-panel title="Отправлено">
          <stat-panel>
            {{ summary.sent ? summary.sent.toLocaleString() : 0 }}
          </stat-panel>
        </dashboard-panel>
      </el-col>
      <el-col :span="4">
        <dashboard-panel title="В работе">
          <stat-panel>
            {{ summary.received ? summary.received.toLocaleString() : 0 }}
          </stat-panel>
        </dashboard-panel>
      </el-col>
      <el-col :span="4">
        <dashboard-panel title="Завершено">
          <stat-panel>
            {{ summary.handled ? summary.handled.toLocaleString() : 0 }}
          </stat-panel>
        </dashboard-panel>
      </el-col>
      <el-col :span="4">
        <dashboard-panel title="Ошибки">
          <stat-panel>
            {{ summary.failed ? summary.failed.toLocaleString() : 0 }}
          </stat-panel>
        </dashboard-panel>
      </el-col>
    </el-row>

    <el-row>
      <el-col :span="24">
        <dashboard-panel title="Активность">
          <chart-panel
            :series="series"
            height="300px"
          />
        </dashboard-panel>
      </el-col>
    </el-row>
  </div>
</template>

<script>
    import ViewMixin from './ViewMixin';

    import AppTimePicker from '../components/AppTimePicker';
    import AppRefreshPicker from '../components/AppRefreshPicker';
    import DashboardPanel from '../components/DashboardPanel';
    import ChartPanel from '../components/ChartPanel';
    import StatPanel from '../components/StatPanel';

    import { fetchFromModule } from '../utils/request';

    export default {
        components: {
            AppTimePicker,
            AppRefreshPicker,
            DashboardPanel,
            StatPanel,
            ChartPanel
        },

        mixins: [ViewMixin],

        data() {
            return {
                timePreset: '-1 hours',
                refresh: false,
                refreshTimer: null,
                refreshDelay: 10000,
                summary: {},
                series: []
            };
        },

        watch: {
            timePreset(value) {
                localStorage['bsi.queue.dashboard.timePreset'] = value;
                this.fetchData();
            },

            refresh(value) {
                localStorage['bsi.queue.dashboard.refresh'] = value;
                this.initAutoUpdate();
            }
        },

        created() {
            this.fetchData();

            let storedTimePreset = localStorage.getItem('bsi.queue.dashboard.timePreset');
            if (storedTimePreset) {
                this.timePreset = storedTimePreset;
            }

            let storedRefreshState = localStorage.getItem('bsi.queue.dashboard.refresh');
            if (storedRefreshState === 'true') {
                this.refresh = true;
            }
        },

        beforeDestroy() {
            clearInterval(this.refreshTimer);
        },

        methods: {
            fetchData() {
                this.fetchSummaryData();
                this.fetchChartData();
            },

            async fetchSummaryData() {
                this.summary = await fetchFromModule('bsi:queue.api.dashboard.summary', {
                    from: this.timePreset,
                    to: 'now'
                });
            },

            async fetchChartData() {
                const data = await fetchFromModule('bsi:queue.api.dashboard.queryRange', {
                    from: this.timePreset,
                    to: 'now'
                });

                this.series = data.map(item => {
                    return {
                        name: item.metric.name,
                        data: item.values.map(value => {
                            value[0] *= 1000;
                            return value;
                        })
                    };
                });
            },

            initAutoUpdate() {
                if (this.refresh === true) {
                    this.refreshTimer = setInterval(this.autoUpdate, this.refreshDelay);
                } else {
                    clearInterval(this.refreshTimer);
                }
            },

            autoUpdate() {
                this.fetchData();
            }
        }
    };
</script>