<template>
    <div class="project-gstc-gantt">
        <GSTC v-if="loadFinish" ref="gstc" :config="config" @state="emitState"/>
        <Dropdown class="project-gstc-dropdown" @on-click="tapView">
            <Icon class="project-gstc-dropdown-icon" type="md-funnel" />
            <DropdownMenu slot="list">
                <DropdownItem name="now">{{$L('现在')}}</DropdownItem>
                <DropdownItem name="day" :class="{'project-gstc-dropdown-period':period=='day'}">{{$L('天视图')}}</DropdownItem>
                <DropdownItem name="week" :class="{'project-gstc-dropdown-period':period=='week'}">{{$L('周视图')}}</DropdownItem>
                <DropdownItem name="month" :class="{'project-gstc-dropdown-period':period=='month'}">{{$L('月视图')}}</DropdownItem>
            </DropdownMenu>
        </Dropdown>
        <div class="project-gstc-close" @click="$emit('on-close')"><Icon type="md-close" /></div>
    </div>
</template>

<style lang="scss">
    .project-gstc-gantt {
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        bottom: 15px;
        z-index: 1;
        transform: translateZ(0);
        background-color: #fdfdfd;
        border-radius: 3px;
        .gantt-schedule-timeline-calendar {
            background-color: transparent;
            padding: 12px;
        }
        .gantt-schedule-timeline-calendar__list-column-header-resizer-container {
            line-height: 92px;
        }
        .project-gstc-dropdown {
            position: absolute;
            top: 44px;
            left: 276px;
            .project-gstc-dropdown-icon {
                cursor: pointer;
                color: #999;
                font-size: 20px;
            }
            .project-gstc-dropdown-period {
                color: #058ce4;
            }
        }
        .project-gstc-close {
            position: absolute;
            top: 8px;
            left: 12px;
            cursor: pointer;
            &:hover {
                i {
                    transform: scale(1) rotate(45deg);
                }
            }
            i {
                color: #666666;
                font-size: 28px;
                transform: scale(0.92);
                transition: all .2s;
            }
        }
    }
</style>
<script>
    import GSTC from "./GSTC";
    import ItemMovement from "gantt-schedule-timeline-calendar/dist/ItemMovement.plugin.js"
    import CalendarScroll from "gantt-schedule-timeline-calendar/dist/CalendarScroll.plugin.js"
    import WeekendHighlight from "gantt-schedule-timeline-calendar/dist/WeekendHighlight.plugin.js"

    /**
     * 甘特图
     */
    export default {
        name: 'ProjectGantt',
        components: { GSTC },
        props: {
            projectLabel: {
                default: []
            },
        },

        data () {
            return {
                loadFinish: false,

                period: 'day',

                rows: {},
                items: {},

                config: {},
            }
        },

        mounted() {
            this.initData();
            this.loadFinish = true;
            //
            this.$watch(
                "projectLabel",
                (projectLabel) => {
                    this.initData(this.loadFinish == true);
                },
                {deep: true}
            );
        },

        methods: {
            initData(isUpdate) {
                this.rows = {};
                this.items = {};
                this.projectLabel.forEach((item) => {
                    item.taskLists.forEach((taskData) => {
                        let start = taskData.startdate || taskData.indate;
                        let end = taskData.enddate || (taskData.indate + 86400);
                        if (end == start) {
                            end = Math.round(new Date($A.formatDate('Y-m-d 23:59:59', end)).getTime()/1000);
                        }
                        end = Math.max(end, start + 60);
                        start*= 1000;
                        end*= 1000;
                        //
                        let color = '#058ce4';
                        if (taskData.complete) {
                            color = '#c1c1c1';
                        } else {
                            if (taskData.level === 1) {
                                color = '#ff0000';
                            }else if (taskData.level === 2) {
                                color = '#BB9F35';
                            }else if (taskData.level === 3) {
                                color = '#449EDD';
                            }else if (taskData.level === 4) {
                                color = '#84A83B';
                            }
                        }
                        //
                        let label = `<div class="gantt-schedule-timeline-calendar-title">${taskData['title']}</div>`;
                        if (taskData.overdue) {
                            label = `<div class="gantt-schedule-timeline-calendar-overdue"><em>${this.$L('已超期')}</em></div>${label}`;
                        }
                        label = `${label}<div class="gantt-schedule-timeline-calendar-goto" data-id="${taskData['id']}"></div>`;
                        this.rows[taskData['id']] = {
                            id: taskData['id'],
                            label: label,
                            complete: taskData.complete,
                            overdue: taskData.overdue,
                        };
                        this.items[taskData['id']] = {
                            id: taskData['id'],
                            rowId: taskData['id'],
                            label: taskData['title'],
                            time: { start, end },
                            style: { background: color },
                        };
                    });
                });
                //
                if (Object.keys(this.rows).length == 0) {
                    this.$Modal.warning({
                        title: this.$L("温馨提示"),
                        content: this.$L('任务列表为空，请先添加任务。'),
                        onOk: () => {
                            this.$emit('on-close');
                        },
                    });
                }
                //
                this.config = Object.assign({
                    plugins: [ItemMovement({
                        moveable: 'x',
                        resizeable: true,
                        collisionDetection: true
                    }), CalendarScroll(), WeekendHighlight()],
                    height: this.$el.clientHeight - 24,
                    list: {
                        toggle: {
                            display: false
                        },
                        rows: this.rows,
                        columns: {
                            percent: 100,
                            resizer: {
                                inRealTime: true,
                                dots: 0
                            },
                            data: {
                                label: {
                                    id: "label",
                                    data: "label",
                                    width: 300,
                                    isHTML: true,
                                    header: {
                                        content: this.$L("任务名称")
                                    }
                                }
                            }
                        }
                    },
                    chart: {
                        time: {
                            period: 'day',
                            additionalSpaces: {
                                hour: { before: 24, after: 24, period: 'hour' },
                                day: { before: 1, after: 1, period: 'month' },
                                week: { before: 1, after: 1, period: 'year' },
                                month: { before: 6, after: 6, period: 'year' },
                                year: { before: 12, after: 12, period: 'year' }
                            }
                        },
                        items: this.items
                    },

                }, isUpdate ? {} : {
                    actions: {
                        "list-column-row": [this.actionRow],
                        'chart-timeline-items-row-item': [this.actionResizing]
                    }
                }, this.getLanguage() == 'en' ? {} : {
                    locale: {
                        name: "zh-cn",
                        weekdays: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"],
                        weekdaysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
                        weekdaysMin: ["日", "一", "二", "三", "四", "五", "六"],
                        months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                        ordinal: function(t, e) {
                            switch (e) {
                                case "W":
                                    return "".concat(t, "周");
                                default:
                                    return "".concat(t, "日")
                            }
                        },
                        weekStart: 1,
                        yearStart: 4,
                        formats: {
                            LT: "HH:mm",
                            LTS: "HH:mm:ss",
                            L: "YYYY/MM/DD",
                            LL: "YYYY年M月D日",
                            LLL: "YYYY年M月D日Ah点mm分",
                            LLLL: "YYYY年M月D日ddddAh点mm分",
                            l: "YYYY/M/D",
                            ll: "YYYY年M月D日",
                            lll: "YYYY年M月D日 HH:mm",
                            llll: "YYYY年M月D日dddd HH:mm"
                        },
                        relativeTime: {
                            future: "%s内",
                            past: "%s前",
                            s: "几秒",
                            m: "1 分钟",
                            mm: "%d 分钟",
                            h: "1 小时",
                            hh: "%d 小时",
                            d: "1 天",
                            dd: "%d 天",
                            M: "1 个月",
                            MM: "%d 个月",
                            y: "1 年",
                            yy: "%d 年"
                        },
                    },
                });
            },

            actionRow(element, data) {
                let onClick = (event) => {
                    //打开任务
                    if (event.target.className == 'gantt-schedule-timeline-calendar-goto') {
                        let rowId = event.target.getAttribute("data-id");
                        rowId && this.$refs.gstc.getGstc().api.scrollToTime(this.items[rowId].time.start)
                    } else {
                        this.taskDetail(data.rowId);
                    }
                }
                element.addEventListener('click', onClick);
                return {
                    update(element, newData) {
                        data = newData;
                    },
                    destroy(element, data) {
                        element.removeEventListener('click', onClick);
                    }
                };
            },

            actionResizing(element, data) {
                let thas = this;
                return {
                    update(element, newData) {
                        data = newData;
                        if (data.item.isResizing) {
                            if (Math.abs(thas.items[data.item['id']].time.end - data.item.time.end) > 60000
                                || Math.abs(thas.items[data.item['id']].time.start - data.item.time.start) > 60000) {
                                //修改时间（变化超过1分钟)
                                let backTime = $A.cloneData(thas.items[data.item['id']].time);
                                let newTime = $A.cloneData(data.item.time);
                                let timeStart = $A.formatDate('Y-m-d H:i', Math.round(newTime.start / 1000));
                                let timeEnd = $A.formatDate('Y-m-d H:i', Math.round(newTime.end / 1000));
                                thas.items[data.item['id']].time = newTime;
                                thas.$refs.gstc.updateTime(data.item['id'], newTime);
                                thas.$Modal.confirm({
                                    title: thas.$L("计划时间"),
                                    content: thas.$L('确定要修改任务【%】的计划时间吗？<br/>开始时间：%<br/>结束时间：%', data.item['label'], timeStart, timeEnd),
                                    onCancel: () => {
                                        thas.items[data.item['id']].time = backTime;
                                        thas.$refs.gstc.updateTime(data.item['id'], backTime);
                                    },
                                    onOk: () => {
                                        let ajaxData = {
                                            act: 'plannedtime',
                                            taskid: data.item['id'],
                                            content: timeStart + "," + timeEnd,
                                        };
                                        $A.apiAjax({
                                            url: 'project/task/edit',
                                            data: ajaxData,
                                            error: () => {
                                                alert(thas.$L('网络繁忙，请稍后再试！'));
                                                thas.items[data.item['id']].time = backTime;
                                                thas.$refs.gstc.updateTime(data.item['id'], backTime);
                                            },
                                            success: (res) => {
                                                if (res.ret === 1) {
                                                    thas.$Message.success(res.msg);
                                                    $A.triggerTaskInfoListener(ajaxData.act, res.data);
                                                    $A.triggerTaskInfoChange(ajaxData.taskid);
                                                } else {
                                                    setTimeout(() => {
                                                        thas.$Modal.error({title: thas.$L('温馨提示'), content: res.msg});
                                                    }, 350)
                                                    thas.items[data.item['id']].time = backTime;
                                                    thas.$refs.gstc.updateTime(data.item['id'], backTime);
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                };
            },

            emitState(GSTCState) {
                GSTCState.subscribe('config.plugin.ItemMovement.item', data => {
                    if (!data) return;
                    GSTCState.update(`config.chart.items.${data.id}.isResizing`, !(data.waiting || data.moving || data.resizing));
                });
            },

            tapView(e) {
                if ("now" === e) {
                    var i = this.$refs.gstc.getGstc()
                    return i.api.scrollToTime(i.api.time.date().valueOf())
                }
                this.period = e;
                this.$refs.gstc.setPeriod(e);
            }
        }
    }
</script>
