<template>
  <div class="c-chart-panel">
    <apexchart
      type="area"
      :options="mergedOptions"
      :series="series"
      width="98%"
      :height="height"
    />
  </div>
</template>

<script>
    import VueApexCharts from 'vue-apexcharts';
    import lang from 'apexcharts/dist/locales/ru.json';

    export default {
        components: {
            apexchart: VueApexCharts
        },

        props: {
            series: {
                type: Array,
                required: true
            },

            options: {
                type: Object,
                required: false,
                default: () => ({})
            },

            height: {
                type: String,
                default: 'auto'
            }
        },

        data() {
            return {
                defaultOptions: {
                    chart: {
                        locales: [lang],
                        defaultLocale: 'ru',
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        shared: true,
                        x: {
                            format: 'dd.MM.yyyy HH:mm:ss'
                        }
                    },
                    stroke: {
                        curve: 'straight',
                        width: 2
                    },
                    fill: {
                        type: 'solid',
                        opacity: 0.4
                    },
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false
                        }
                    },
                    yaxis: {
                        decimalsInFloat: false
                    }
                }
            };
        },

        computed: {
            mergedOptions() {
                return this.extend(this.defaultOptions, this.options);
            }
        },

        methods: {
            isObject(item) {
                return (item && typeof item === 'object' && !Array.isArray(item));
            },

            extend(target, source) {
                let output = Object.assign({}, target);
                if (this.isObject(target) && this.isObject(source)) {
                    Object.keys(source).forEach(key => {
                        if (this.isObject(source[key])) {
                            if (!(key in target)) {
                                Object.assign(output, {
                                    [key]: source[key]
                                });
                            } else {
                                output[key] = this.extend(target[key], source[key]);
                            }
                        } else {
                            Object.assign(output, {
                                [key]: source[key]
                            });
                        }
                    });
                }
                return output;
            }
        }
    };
</script>