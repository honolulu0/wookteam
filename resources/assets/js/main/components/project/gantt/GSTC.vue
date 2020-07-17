<template>
    <div class="gstc" ref="gstc"></div>
</template>

<style lang="scss">
    .gantt-schedule-timeline-calendar__list-column-row {
        .gantt-schedule-timeline-calendar__list-column-row-content {
            display: flex;
            cursor: pointer;
            &:hover {
                .gantt-schedule-timeline-calendar-goto {
                    display: flex;
                }
            }
            .gantt-schedule-timeline-calendar-overdue {
                display: flex;
                align-items: center;
                margin-right: 2px;
                em {
                    font-size: 12px;
                    height: 22px;
                    line-height: 22px;
                    color: #ffffff;
                    padding: 0 4px;
                    border-radius: 3px;
                    background: #ff0000;
                    transform: scale(0.9);
                    transform-origin: 0 center;
                }
            }
            .gantt-schedule-timeline-calendar-title {
                flex: 1;
                padding-right: 6px;
            }
            .gantt-schedule-timeline-calendar-goto {
                font-family: Ionicons;
                font-weight: 400;
                text-align: center;
                display: none;
                justify-content: center;
                align-items: center;
                width: 32px;
                margin-right: 8px;
                font-size: 16px;
                color: #888888;
                &:before {
                    content: "\F216";
                }
                &:hover {
                    color: #333333;
                }
            }
        }
    }
</style>
<script>
    import GSTC from "gantt-schedule-timeline-calendar";
    import "gantt-schedule-timeline-calendar/dist/style.css";

    export default {
        name: "GSTC",
        props: ["config"],
        data() {
            return {
                state: null,
                gstc: null,
            }
        },
        mounted() {
            this.state = GSTC.api.stateFromConfig(this.config);
            this.$emit("state", this.state);
            this.$refs.gstc.addEventListener('gstc-loaded', () => {
                this.gstc.api.scrollToTime(new Date().getTime());
            });
            this.gstc = GSTC({
                element: this.$refs.gstc,
                state: this.state
            });
            if (this.isMac()) {
                this.$refs.gstc.addEventListener('mousewheel', this.mouseHandler, { passive: false });
            }
            //
            this.$watch(
                "config",
                (config) => {
                    this.state.update("config", current => {
                        if (current !== config) {
                            return GSTC.api.mergeDeep({}, current, config);
                        } else {
                            return current;
                        }
                    });
                },
                {deep: true}
            );
        },
        destroyed() {
            this.gstc.app.destroy();
        },
        methods: {
            isMac() {
                return /macintosh|mac os x/i.test(navigator.userAgent);
            },
            mouseHandler(e) {
                e.preventDefault();
            },
            getGstc() {
                return this.gstc;
            },
            getState() {
                return this.state;
            },
            setPeriod(val) {
                GSTC.api.setPeriod(val);
            },
            updateTime(id, time) {
                this.state.update('config.chart.items', items => {
                    for (let itemId in items) {
                        if (items.hasOwnProperty(itemId) && itemId == id) {
                            items[itemId].time = $A.cloneData(time);
                        }
                    }
                    return items;
                });
            }
        }
    };
</script>
