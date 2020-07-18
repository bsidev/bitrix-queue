<template>
  <div class="c-message-details c-dashboard">
    <el-row
      type="flex"
      :gutter="20"
    >
      <el-col :span="12">
        <dashboard-panel :title="$t('title.summary')">
          <div class="c-message-details__summary">
            <el-row
              v-for="(prop, index) in summaryProps"
              :key="index"
              type="flex"
              :gutter="20"
              class=""
            >
              <el-col :span="8">
                {{ prop.name }}
              </el-col>
              <el-col :span="16">
                {{ prop.value }}
              </el-col>
            </el-row>
          </div>
        </dashboard-panel>
      </el-col>
      <el-col :span="12">
        <dashboard-panel :title="$t('title.data')">
          <div class="c-message-details__data">
            <vue-json-pretty
              :data="prettyData"
            />
          </div>
        </dashboard-panel>
      </el-col>
    </el-row>

    <el-row
      v-if="data.error"
      type="flex"
    >
      <el-col :span="24">
        <dashboard-panel :title="$t('title.errors')">
          <table class="c-table c-table--striped">
            <tbody>
              <tr
                v-for="(row, index) in errorRows"
                :key="index"
              >
                <td>
                  {{ row }}
                </td>
              </tr>
            </tbody>
          </table>
        </dashboard-panel>
      </el-col>
    </el-row>
  </div>
</template>

<script>
    import VueJsonPretty from 'vue-json-pretty';
    import DashboardPanel from './DashboardPanel';

    import phpUnserialize from 'phpunserialize';

    export default {
        components: {
            VueJsonPretty,
            DashboardPanel
        },

        props: {
            data: {
                type: Object,
                required: true
            }
        },

        computed: {
            summaryProps() {
                let values = [
                    {
                        name: this.$t('label.uuid'),
                        value: this.data.uuid
                    },
                    {
                        name: this.$t('label.transport_name'),
                        value: this.data.transport_name
                    },
                    {
                        name: this.$t('label.buses'),
                        value: Array.isArray(this.data.buses) ? this.data.buses.join(', ') : null
                    },
                    {
                        name: this.$t('label.sent_at'),
                        value: this.formatDate(this.data.sent_at)
                    },
                    {
                        name: this.$t('label.received_at'),
                        value: this.data.received_at ? this.formatDate(this.data.received_at) : null
                    },
                    {
                        name: this.$t('label.handled_at'),
                        value: this.data.handled_at ? this.formatDate(this.data.handled_at) : null
                    },
                    {
                        name: this.$t('label.failed_at'),
                        value: this.data.failed_at ? this.formatDate(this.data.failed_at) : null
                    }
                ];

                return values.filter(item => item.value !== null && item.value !== undefined && item.value !== '');
            },

            prettyData() {
                return phpUnserialize(this.data.data);
            },

            errorRows() {
                return this.data.error.split(/\r?\n/);
            }
        },

        methods: {
            formatDate(date) {
                return (new Date(date)).toLocaleString();
            }
        }
    };
</script>