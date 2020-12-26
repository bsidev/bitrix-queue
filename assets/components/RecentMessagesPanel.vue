<template>
    <div class="c-table-panel">
        <div class="c-table-panel__search">
            <el-input
                v-model="search"
                :placeholder="$t('label.findMessages')"
            />
        </div>
        <table class="c-table">
            <thead>
                <tr>
                    <th style="width: 140px">
                        {{ $t('label.sent_at') }}
                    </th>
                    <th>
                        {{ $t('label.message') }}
                    </th>
                    <th style="width: 140px">
                        {{ $t('label.status') }}
                    </th>
                    <th style="width: 200px">
                        {{ $t('label.transport_name') }}
                    </th>
                    <th>
                        {{ $t('label.buses') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in data"
                    :key="index"
                >
                    <td>
                        {{ formatDate(row.sent_at) }}
                    </td>
                    <td>
                        <a
                            class="c-link"
                            @click.prevent="handleMessageSelect(row)"
                        >
                            {{ row.message }}
                        </a>
                    </td>
                    <td>
                        <el-tag
                            :type="getStatusType(row.status)"
                            disable-transitions
                        >
                            {{ $t(`enums.status.${row.status}`) }}
                        </el-tag>
                    </td>
                    <td>
                        {{ row.transport_name }}
                    </td>
                    <td>
                        {{ Array.isArray(row.buses) ? row.buses.join(', ') : '' }}
                    </td>
                </tr>

                <tr v-if="data.length === 0">
                    <td
                        colspan="5"
                        style="text-align: center"
                    >
                        Нет данных
                    </td>
                </tr>
            </tbody>
        </table>

        <el-pagination
            background
            layout="prev, pager, next, sizes"
            :total="total"
            :page-size.sync="currentPageSize"
            :page-sizes="pageSizes"
            @current-change="handlePageChange"
            @size-change="handlePageSizeChange"
        />
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Array,
            required: true
        },
        total: {
            type: Number,
            required: true
        },
        pageSize: {
            type: Number,
            default: 10
        },
        pageSizes: {
            type: Array,
            default: () => [10, 20, 50]
        },
        searchDelay: {
            type: Number,
            default: 300
        }
    },

    data() {
        return {
            search: '',
            searchTimeout: null
        };
    },

    computed: {
        currentPageSize: {
            get() {
                return this.pageSize;
            },
            set(value) {
                this.$emit('update:pageSize', value);
            }
        }
    },

    watch: {
        search(value) {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            this.searchTimeout = setTimeout(() => {
                this.$emit('search', value);
            }, this.searchDelay);
        }
    },

    methods: {
        getStatusType(status) {
            if (status === 'failed') {
                return 'danger';
            }
            if (status === 'received') {
                return 'warning';
            }
            if (status === 'handled') {
                return 'success';
            }
            return 'info';
        },

        formatDate(date) {
            return (new Date(date)).toLocaleString();
        },

        handleMessageSelect(row) {
            this.$emit('message-select', row);
        },

        handlePageChange(value) {
            this.$emit('paginate', value);
        },

        handlePageSizeChange(value) {
            this.$emit('page-size-change', value);
        }
    }
};
</script>
