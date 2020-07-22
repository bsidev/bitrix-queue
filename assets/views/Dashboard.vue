<template>
    <div class="c-dashboard">
        <div class="c-dashboard__panel">
            <app-time-picker
                v-model="timePreset"
                @change="handleTimePresetChange"
            />
            <app-refresh-picker
                v-model="refresh"
                :title="$t('tooltip.autoUpdate')"
            />
        </div>

        <el-row
            type="flex"
            :gutter="20"
        >
            <el-col :span="4">
                <dashboard-panel :title="$t('title.status')">
                    <stat-panel v-if="!init">
                        -
                    </stat-panel>
                    <stat-panel
                        v-else
                        :type="hasConsumers ? 'success' : 'danger'"
                        :title="!hasConsumers ? $t('tooltip.notFoundConsumers') : ''"
                    >
                        {{ hasConsumers ? $t('label.ok') : $t('label.problem') }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
            <el-col :span="4">
                <dashboard-panel :title="$t('title.consumers')">
                    <stat-panel>
                        {{ summary.consumers ? summary.consumers.toLocaleString() : 0 }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
            <el-col :span="4">
                <dashboard-panel :title="$t('enums.status.sent')">
                    <stat-panel type="info">
                        {{ summary.sent ? summary.sent.toLocaleString() : 0 }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
            <el-col :span="4">
                <dashboard-panel :title="$t('enums.status.received')">
                    <stat-panel type="warning">
                        {{ summary.received ? summary.received.toLocaleString() : 0 }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
            <el-col :span="4">
                <dashboard-panel :title="$t('enums.status.handled')">
                    <stat-panel type="success">
                        {{ summary.handled ? summary.handled.toLocaleString() : 0 }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
            <el-col :span="4">
                <dashboard-panel :title="$t('enums.status.failed')">
                    <stat-panel type="danger">
                        {{ summary.failed ? summary.failed.toLocaleString() : 0 }}
                    </stat-panel>
                </dashboard-panel>
            </el-col>
        </el-row>

        <el-row type="flex">
            <el-col :span="24">
                <dashboard-panel :title="$t('title.stats')">
                    <chart-panel
                        :series="chartSeries"
                        :options="chartOptions"
                        height="300px"
                    />
                </dashboard-panel>
            </el-col>
        </el-row>

        <el-row type="flex">
            <el-col :span="24">
                <dashboard-panel :title="$t('title.messages')">
                    <recent-messages-panel
                        :data="recentMessages.data"
                        :total="recentMessages.total"
                        :page-size="recentMessages.pageSize"
                        @message-select="handleMessageSelect"
                        @paginate="handleRecentMessagesPaginate"
                    />
                </dashboard-panel>
            </el-col>
        </el-row>

        <el-drawer
            v-if="drawerMessage"
            :title="drawerMessage.message"
            :visible.sync="drawer"
            destroy-on-close
            size="70%"
            custom-class="c-drawer"
            @closed="handleDrawerClosed"
        >
            <message-details
                :data="drawerMessage"
            />
        </el-drawer>
    </div>
</template>

<script>
    import ViewMixin from './ViewMixin';

    import AppTimePicker from '../components/AppTimePicker';
    import AppRefreshPicker from '../components/AppRefreshPicker';
    import DashboardPanel from '../components/DashboardPanel';
    import StatPanel from '../components/StatPanel';
    import ChartPanel from '../components/ChartPanel';
    import RecentMessagesPanel from '../components/RecentMessagesPanel';
    import MessageDetails from '../components/MessageDetails';

    import { fetchFromModule } from '../utils/request';

    export default {
        components: {
            AppTimePicker,
            AppRefreshPicker,
            DashboardPanel,
            StatPanel,
            ChartPanel,
            RecentMessagesPanel,
            MessageDetails
        },

        mixins: [ViewMixin],

        data() {
            return {
                timePreset: '-1 hours',
                refresh: false,
                refreshTimer: null,
                refreshDelay: 10000,
                init: false,
                summary: {},
                chartSeries: [],
                chartOptions: {
                    colors: ['#909399', '#E6A23C', '#67C23A', '#F56C6C']
                },
                recentMessages: {
                    data: [],
                    total: 0,
                    pageSize: 10,
                    page: 1
                },
                drawer: false,
                drawerMessage: null
            };
        },

        computed: {
            hasConsumers() {
                return this.summary.consumers && Number(this.summary.consumers) > 0;
            }
        },

        watch: {
            refresh(value) {
                localStorage['bsi.queue.dashboard.refresh'] = value;
                this.initAutoUpdate();
            }
        },

        created() {
            let storedTimePreset = localStorage.getItem('bsi.queue.dashboard.timePreset');
            if (storedTimePreset) {
                this.timePreset = storedTimePreset;
            }

            let storedRefreshState = localStorage.getItem('bsi.queue.dashboard.refresh');
            if (storedRefreshState === 'true') {
                this.refresh = true;
            }

            this.fetchData().then(() => {
                this.init = true;
            });
        },

        beforeDestroy() {
            clearInterval(this.refreshTimer);
        },

        methods: {
            fetchData() {
                return Promise.all([
                    this.fetchSummaryData(),
                    this.fetchChartData(),
                    this.fetchRecentMessagesData()
                ]);
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

                this.chartSeries = data.map(item => {
                    return {
                        name: this.$t(`enums.status.${item.status}`),
                        data: item.values.map(value => {
                            value[0] *= 1000;
                            return value;
                        })
                    };
                });
            },

            async fetchRecentMessagesData() {
                const data = await fetchFromModule('bsi:queue.api.dashboard.recentMessages', {
                    from: this.timePreset,
                    to: 'now',
                    pageSize: this.recentMessages.pageSize,
                    page: this.recentMessages.page
                });

                this.recentMessages.data = data.data;
                this.recentMessages.total = data.total;
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
            },

            handleTimePresetChange(value) {
                localStorage['bsi.queue.dashboard.timePreset'] = value;
                this.fetchData();
            },

            handleMessageSelect(message) {
                this.drawerMessage = message;
                this.drawer = true;
            },

            handleRecentMessagesPaginate(value) {
                this.recentMessages.page = value;
                this.fetchRecentMessagesData();
            },

            handleDrawerClosed() {
                this.drawerMessage = null;
            }
        }
    };
</script>