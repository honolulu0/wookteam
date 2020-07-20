<template>
    <div class="project-gstc-gantt">
        <GSTC v-if="loadFinish" ref="gstc" :config="config" @state="emitState"/>
        <Dropdown class="project-gstc-dropdown-eye" @on-click="tapView">
            <Icon class="project-gstc-dropdown-icon" type="md-eye" />
            <DropdownMenu slot="list">
                <DropdownItem name="now">{{$L('现在')}}</DropdownItem>
                <DropdownItem name="day" :class="{'project-gstc-dropdown-period':period=='day'}">{{$L('天视图')}}</DropdownItem>
                <DropdownItem name="week" :class="{'project-gstc-dropdown-period':period=='week'}">{{$L('周视图')}}</DropdownItem>
                <DropdownItem name="month" :class="{'project-gstc-dropdown-period':period=='month'}">{{$L('月视图')}}</DropdownItem>
            </DropdownMenu>
        </Dropdown>
        <Dropdown class="project-gstc-dropdown-filtr" @on-click="tapProject">
            <Icon class="project-gstc-dropdown-icon" :class="{filtr:filtrProjectId>0}" type="md-funnel" />
            <DropdownMenu slot="list">
                <DropdownItem :name="0" :class="{'project-gstc-dropdown-period':filtrProjectId==0}">{{$L('全部')}}</DropdownItem>
                <DropdownItem v-for="(item, index) in projectLabel" :key="index" :name="item.id" :class="{'project-gstc-dropdown-period':filtrProjectId==item.id}">{{item.title}} ({{item.taskLists.length}})</DropdownItem>
            </DropdownMenu>
        </Dropdown>
        <div class="project-gstc-close" @click="$emit('on-close')"><Icon type="md-close" /></div>
        <div class="project-gstc-edit" :class="{info:editShowInfo, visible:editData.length > 0}">
            <div class="project-gstc-edit-info">
                <Table class="tableFill" size="small" max-height="600" :columns="editColumns" :data="editData"></Table>
                <div class="project-gstc-edit-btns">
                    <Button :loading="editLoad > 0" size="small" type="text" @click="editSubmit(false)">{{$L('取消')}}</Button>
                    <Button :loading="editLoad > 0" size="small" type="primary" @click="editSubmit(true)">{{$L('保存')}}</Button>
                    <Icon type="md-arrow-dropright" class="zoom" @click="editShowInfo=false"/>
                </div>
            </div>
            <div class="project-gstc-edit-small">
                <div class="project-gstc-edit-text" @click="editShowInfo=true">{{$L('未保存计划时间')}}: {{editData.length}}</div>
                <Button :loading="editLoad > 0" size="small" type="text" @click="editSubmit(false)">{{$L('取消')}}</Button>
                <Button :loading="editLoad > 0" size="small" type="primary" @click="editSubmit(true)">{{$L('保存')}}</Button>
            </div>
        </div>
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
            line-height: 90px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .project-gstc-dropdown-eye,
        .project-gstc-dropdown-filtr {
            position: absolute;
            top: 46px;
            left: 276px;
            .project-gstc-dropdown-icon {
                cursor: pointer;
                color: #999;
                font-size: 20px;
                &.filtr {
                    color: #058ce4;
                }
            }
            .project-gstc-dropdown-period {
                color: #058ce4;
            }
        }
        .project-gstc-dropdown-eye {
            left: 238px;
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
        .project-gstc-edit {
            position: absolute;
            bottom: 6px;
            right: 6px;
            background: #ffffff;
            border-radius: 4px;
            opacity: 0;
            transform: translate(120%, 0);
            transition: all 0.2s;
            &.visible {
                opacity: 1;
                transform: translate(0, 0);
            }
            &.info {
                .project-gstc-edit-info {
                    display: block;
                }
                .project-gstc-edit-small {
                    display: none;
                }
            }
            .project-gstc-edit-info {
                display: none;
                border: 1px solid #e4e4e4;
                background: #ffffff;
                padding: 6px;
                border-radius: 4px;
                width: 500px;
                .project-gstc-edit-btns {
                    margin: 12px 6px 4px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    .ivu-btn {
                        margin-right: 8px;
                        font-size: 13px;
                    }
                    .zoom {
                        font-size: 20px;
                        color: #444444;
                        cursor: pointer;
                        &:hover {
                            color: #57a3f3;
                        }
                    }
                }
            }
            .project-gstc-edit-small {
                border: 1px solid #e4e4e4;
                background: #ffffff;
                padding: 6px 12px;
                display: flex;
                align-items: center;
                .project-gstc-edit-text {
                    cursor: pointer;
                    text-decoration: underline;
                    color: #444444;
                    margin-right: 8px;
                    &:hover {
                        color: #57a3f3;
                    }
                }
                .ivu-btn {
                    margin-left: 4px;
                    font-size: 13px;
                }
            }
        }
    }
    .gantt-notice-box {
        .ivu-notice-desc-btn {
            margin-top: 6px;
            text-align: right;
            .ivu-btn {
                margin-left: 4px;
                font-size: 13px;
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

                editColumns: [],
                editData: [],
                editShowInfo: false,
                editLoad: 0,

                filtrProjectId: 0,
            }
        },

        mounted() {
            this.editColumns = [
                {
                    title: this.$L('任务名称'),
                    key: 'label',
                    minWidth: 150,
                    ellipsis: true,
                }, {
                    title: this.$L('原计划时间'),
                    minWidth: 135,
                    align: 'center',
                    render: (h, params) => {
                        if (params.row.notime === true) {
                            return h('span', '-');
                        }
                        return h('div', {
                            style: {},
                        }, [
                            h('div', $A.formatDate('Y-m-d H:i', Math.round(params.row.backTime.start / 1000))),
                            h('div', $A.formatDate('Y-m-d H:i', Math.round(params.row.backTime.end / 1000)))
                        ]);
                    }
                }, {
                    title: this.$L('新计划时间'),
                    minWidth: 135,
                    align: 'center',
                    render: (h, params) => {
                        return h('div', {
                            style: {},
                        }, [
                            h('div', $A.formatDate('Y-m-d H:i', Math.round(params.row.newTime.start / 1000))),
                            h('div', $A.formatDate('Y-m-d H:i', Math.round(params.row.newTime.end / 1000)))
                        ]);
                    }
                }
            ];
            //
            this.initData();
            this.loadFinish = true;
            //
            this.$watch("projectLabel",
                (projectLabel) => {
                    this.initData();
                },
                {deep: true}
            );
        },

        methods: {
            initData() {
                let isUpdate = this.loadFinish == true;
                this.rows = {};
                this.items = {};
                let index = 0;
                this.projectLabel.forEach((item) => {
                    if (this.filtrProjectId > 0) {
                        if (item.id != this.filtrProjectId) {
                            return;
                        }
                    }
                    item.taskLists.forEach((taskData) => {
                        index++;
                        let notime = taskData.startdate == 0 || taskData.enddate == 0;
                        let times = this.getTimeObj(taskData);
                        let start = times.start;
                        let end = times.end;
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
                        let label = `<div class="gantt-schedule-timeline-calendar-title${taskData.complete?' complete-title':''}">${taskData['title']}</div>`;
                        if (taskData.overdue) {
                            label = `<div class="gantt-schedule-timeline-calendar-overdue"><em>${this.$L('已超期')}</em></div>${label}`;
                        }
                        label = `${label}<div class="gantt-schedule-timeline-calendar-goto"></div>`;
                        this.rows[index] = {
                            id: index,
                            label: label,
                            taskId: taskData['id'],
                            complete: taskData.complete,
                            overdue: taskData.overdue,
                        };
                        let tempTime = { start, end };
                        let findData = this.editData.find((t) => { return t.id == taskData['id'] });
                        if (findData) {
                            findData.backTime = $A.cloneData(tempTime)
                            tempTime = $A.cloneData(findData.newTime);
                        }
                        this.items[taskData['id']] = {
                            rowId: index,
                            id: taskData['id'],
                            label: taskData['title'],
                            time: tempTime,
                            notime: notime,
                            style: { background: color },
                        };
                    });
                });
                //
                if (Object.keys(this.rows).length == 0 && this.filtrProjectId == 0) {
                    this.$Modal.warning({
                        title: this.$L("温馨提示"),
                        content: this.$L('任务列表为空，请先添加任务。'),
                        onOk: () => {
                            this.$emit('on-close');
                        },
                    });
                }
                //
                if (isUpdate) {
                    this.$refs.gstc.getState().update('config.list.rows', this.rows);
                    this.$refs.gstc.getState().update('config.chart.items', this.items);
                    return;
                }
                //
                this.config = {
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
                    actions: {
                        "list-column-row": [this.actionRow],
                        'chart-timeline-items-row-item': [this.actionResizing]
                    },
                    locale: this.getLanguage() == 'en' ? {} : {
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
                };
            },

            actionRow(element, data) {
                let onClick = (event) => {
                    //打开任务
                    let id = this.rows[data.rowId].taskId;
                    if (event.target.className == 'gantt-schedule-timeline-calendar-goto') {
                        id && this.$refs.gstc.getGstc().api.scrollToTime(this.items[id].time.start)
                    } else {
                        id && this.taskDetail(id);
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
                            let original = thas.getRawTime(data.item.id);
                            if (Math.abs(original.end - data.item.time.end) > 1000 || Math.abs(original.start - data.item.time.start) > 1000) {
                                //修改时间（变化超过1秒钟)
                                let backTime = $A.cloneData(original);
                                let newTime = $A.cloneData(data.item.time);
                                let findData = thas.editData.find((item) => { return item.id == data.item.id });
                                if (findData) {
                                    findData.newTime = newTime;
                                } else {
                                    thas.editData.push({
                                        id: data.item.id,
                                        label: data.item.label,
                                        notime: data.item.notime,
                                        backTime,
                                        newTime,
                                    })
                                }
                            }
                        }
                    }
                };
            },

            getRawTime(taskId) {
                let times = null;
                this.projectLabel.some((item) => {
                    item.taskLists.some((taskData) => {
                        if (taskData.id == taskId) {
                            times = this.getTimeObj(taskData);
                            return true;
                        }
                    });
                    if (times) {
                        return true;
                    }
                });
                return times;
            },

            getTimeObj(taskData) {
                let start = taskData.startdate || taskData.indate;
                let end = taskData.enddate || (taskData.indate + 86400);
                if (end == start) {
                    end = Math.round(new Date($A.formatDate('Y-m-d 23:59:59', end)).getTime()/1000);
                }
                end = Math.max(end, start + 60);
                start*= 1000;
                end*= 1000;
                return {start, end};
            },

            editSubmit(save) {
                let triggerTask = [];
                this.editData.forEach((item) => {
                    if (!this.items[item.id]) {
                        return;
                    }
                    if (save) {
                        this.editLoad++;
                        let timeStart = $A.formatDate('Y-m-d H:i', Math.round(item.newTime.start / 1000));
                        let timeEnd = $A.formatDate('Y-m-d H:i', Math.round(item.newTime.end / 1000));
                        let ajaxData = {
                            act: 'plannedtime',
                            taskid: item.id,
                            content: timeStart + "," + timeEnd,
                        };
                        $A.apiAjax({
                            url: 'project/task/edit',
                            data: ajaxData,
                            error: () => {
                                this.items[item.id].time = item.backTime;
                                this.$refs.gstc.updateTime(item.id, item.backTime);
                            },
                            success: (res) => {
                                if (res.ret === 1) {
                                    triggerTask.push({
                                        status: 'await',
                                        act: ajaxData.act,
                                        taskid: ajaxData.taskid,
                                        data: res.data,
                                    })
                                } else {
                                    this.items[item.id].time = item.backTime;
                                    this.$refs.gstc.updateTime(item.id, item.backTime);
                                }
                            },
                            afterComplete: () => {
                                this.editLoad--;
                                if (this.editLoad <= 0) {
                                    triggerTask.forEach((info) => {
                                        if (info.status == 'await') {
                                            info.status = 'trigger';
                                            $A.triggerTaskInfoListener(info.act, info.data);
                                            $A.triggerTaskInfoChange(info.taskid);
                                        }
                                    });
                                }
                            },
                        });
                    } else {
                        this.items[item.id].time = item.backTime;
                        this.$refs.gstc.updateTime(item.id, item.backTime);
                    }
                });
                this.editData = [];
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
            },

            tapProject(e) {
                this.filtrProjectId = $A.runNum(e);
                this.initData();
            }
        }
    }
</script>
