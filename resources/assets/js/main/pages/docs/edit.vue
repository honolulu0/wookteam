<template>
    <div class="w-main docs-edit">

        <v-title>{{$L('文档编辑')}}-{{$L('轻量级的团队在线协作')}}</v-title>

        <div class="edit-box">
            <div class="edit-header">
                <div class="header-menu active" @click="handleClick('back')"><Icon type="md-arrow-back" /></div>
                <div class="header-menu" @click="handleClick('menu')"><Icon type="md-menu" /></div>
                <!--<div class="header-menu" @click="handleClick('share')"><Icon type="md-share" /></div>
                <div class="header-menu" @click="handleClick('view')"><Icon type="md-eye" /></div>-->
                <div class="header-menu" @click="handleClick('history')"><Icon type="md-time" /></div>
                <Poptip class="header-menu synch">
                    <div class="synch-container">
                        <Icon type="md-contacts" :title="$L('正在协作会员')"/><em v-if="synchUsers.length > 0">{{synchUsers.length}}</em>
                    </div>
                    <ul class="synch-lists" slot="content">
                        <li class="title">{{$L('正在协作会员')}}:</li>
                        <li v-for="(item, key) in synchUsersS" :key="key" @click="handleSynch(item.username)">
                            <img class="synch-userimg" :src="item.userimg"/>
                            <user-view class="synch-username" placement="right" :username="item.username"/>
                            <span v-if="item.username==userInfo.username" class="synch-self">{{$L('自己')}}</span>
                        </li>
                    </ul>
                </Poptip>
                <div class="header-title">{{docDetail.title}}</div>
                <div v-if="docDetail.type=='document'" class="header-hint">
                    <ButtonGroup size="small" shape="circle">
                        <Button :type="`${docContent.type!='md'?'primary':'default'}`" @click="$set(docContent, 'type', 'text')">{{$L('文本编辑器')}}</Button>
                        <Button :type="`${docContent.type=='md'?'primary':'default'}`" @click="$set(docContent, 'type', 'md')">{{$L('MD编辑器')}}</Button>
                    </ButtonGroup>
                </div>
                <div v-else-if="docDetail.type=='mind'" class="header-hint">{{$L('选中节点，按enter键添加子节点，tab键添加同级节点')}}</div>
                <Button :disabled="(disabledBtn || loadIng > 0) && hid == 0" class="header-button" size="small" type="primary" @click="handleClick('save')">{{$L('保存')}}</Button>
            </div>
            <div class="docs-body">
                <template v-if="docDetail.type=='document'">
                    <MDEditor v-if="docContent.type=='md'" class="body-text" v-model="docContent.content" height="100%"></MDEditor>
                    <TEditor v-else class="body-text" v-model="docContent.content" height="100%"></TEditor>
                </template>
                <minder v-else-if="docDetail.type=='mind'" class="body-mind" v-model="docContent"></minder>
                <sheet v-else-if="docDetail.type=='sheet'" class="body-sheet" v-model="docContent.content"></sheet>
                <flow v-else-if="docDetail.type=='flow'" class="body-flow" v-model="docContent.content"></flow>
            </div>
        </div>

        <WDrawer v-model="docDrawerShow" maxWidth="450">
            <Tabs v-if="docDrawerShow" v-model="docDrawerTab">
                <TabPane :label="$L('知识库目录')" name="menu">
                    <nested-draggable :lists="sectionLists" :readonly="true" :activeid="sid" @change="handleSection"></nested-draggable>
                    <div v-if="sectionLists.length == 0" style="color:#888;padding:32px;text-align:center">{{sectionNoDataText}}</div>
                </TabPane>
                <TabPane :label="$L('文档历史版本')" name="history">
                    <Table class="tableFill" :columns="historyColumns" :data="historyLists" :no-data-text="historyNoDataText" size="small" stripe></Table>
                </TabPane>
            </Tabs>
        </WDrawer>

    </div>
</template>


<style lang="scss">
    .docs-edit {
        .body-text {
            .mdeditor-box {
                position: relative;
                width: 100%;
                .markdown {
                    position: absolute;
                    top: 0;
                    left: 0;
                    bottom: 0;
                    right: 0;
                    overflow: auto;
                    transform: translateZ(0);
                    &.border {
                        border: 0 !important;
                    }
                }
            }
            .teditor-loadedstyle {
                .tox-tinymce {
                    border: 0;
                    border-radius: 0;
                }
                .tox-mbtn {
                    height: 28px;
                }
                .tox-menubar,
                .tox-toolbar-overlord {
                    padding: 0 12%;
                    background: #f9f9f9;
                }
                .tox-toolbar__overflow,
                .tox-toolbar__primary {
                    background: none !important;
                    border-top: 1px solid #eaeaea !important;
                }
                .tox-toolbar-overlord {
                    border-bottom: 1px solid #E9E9E9 !important;
                }
                .tox-toolbar__group:not(:last-of-type) {
                    border-right: 1px solid #eaeaea !important;
                }
                .tox-sidebar-wrap {
                    margin: 22px 12%;
                    border: 1px solid #e8e8e8;
                    border-radius: 2px;
                    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.08);
                    .tox-edit-area {
                        border-top: 0;
                    }
                }
                .tox-statusbar {
                    border-top: 1px solid #E9E9E9;
                    .tox-statusbar__resize-handle {
                        display: none;
                    }
                }
            }
        }
        .body-sheet {
            box-sizing: content-box;
            * {
                box-sizing: content-box;
            }
        }
    }
</style>
<style lang="scss" scoped>
    .docs-edit {
        .edit-box {
            display: flex;
            flex-direction: column;
            position: absolute;
            width: 100%;
            height: 100%;
            .edit-header {
                display: flex;
                flex-direction: row;
                align-items: center;
                width: 100%;
                height: 38px;
                background-color: #ffffff;
                box-shadow: 0 1px 5px 0 rgba(0, 0, 0, 0.1);
                position: relative;
                z-index: 9;
                .header-menu {
                    width: 50px;
                    height: 100%;
                    text-align: center;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 3px;
                    cursor: pointer;
                    color: #777777;
                    position: relative;
                    .ivu-icon {
                        font-size: 16px;
                    }
                    &.synch {
                        .synch-container {
                            width: 50px;
                            height: 38px;
                            line-height: 38px;
                            em {
                                padding-left: 2px;
                            }
                        }
                    }
                    &:hover,
                    &.active {
                        color: #fff;
                        background: #059DFD;
                    }
                    .synch-lists {
                        max-height: 500px;
                        overflow: auto;
                        li {
                            display: flex;
                            align-items: center;
                            padding: 6px 0;
                            border-bottom: 1px dashed #eeeeee;
                            &.title {
                                font-size: 14px;
                                font-weight: 600;
                                color: #333333;
                            }
                            .synch-userimg {
                                width: 24px;
                                height: 24px;
                                border-radius: 50%;
                            }
                            .synch-self {
                                padding: 1px 3px;
                                margin-left: 5px;
                                height: 18px;
                                line-height: 16px;
                                background-color: #FF5722;
                                color: #ffffff;
                                font-size: 12px;
                                border-radius: 3px;
                                transform: scale(0.95);
                            }
                            .synch-username {
                                padding-left: 8px;
                                font-size: 14px;
                                color: #555555;
                            }
                        }
                    }
                }
                .header-title {
                    flex: 1;
                    color: #333333;
                    border-left: 1px solid #ddd;
                    margin-left: 5px;
                    padding-left: 24px;
                    padding-right: 24px;
                    font-size: 16px;
                    white-space: nowrap;
                }
                .header-hint {
                    padding-right: 22px;
                    font-size: 12px;
                    color: #666;
                    white-space: nowrap;
                    .ivu-btn {
                        font-size: 12px;
                        padding: 0 10px;
                    }
                }
                .header-button {
                    font-size: 12px;
                    margin-right: 12px;
                }
            }
            .docs-body {
                flex: 1;
                width: 100%;
                position: relative;
                .body-text {
                    display: flex;
                    width: 100%;
                    height: 100%;
                    .teditor-loadedstyle {
                        height: 100%;
                    }
                }
            }
        }
    }
</style>
<script>
    import Vue from 'vue'
    import minder from '../../components/docs/minder'
    Vue.use(minder)

    const MDEditor = resolve => require(['../../components/MDEditor/index'], resolve);
    const TEditor = resolve => require(['../../components/TEditor'], resolve);
    const Sheet = resolve => require(['../../components/docs/sheet/index'], resolve);
    const Flow = resolve => require(['../../components/docs/flow/index'], resolve);
    const NestedDraggable = resolve => require(['../../components/docs/NestedDraggable'], resolve);
    const WDrawer = resolve => require(['../../components/iview/WDrawer'], resolve);

    export default {
        components: {WDrawer, Flow, Sheet, MDEditor, TEditor, NestedDraggable},
        data () {
            return {
                loadIng: 0,

                sid: 0,
                hid: 0,

                docDetail: { },
                docContent: { },
                bakContent: null,

                docDrawerShow: false,
                docDrawerTab: '',

                sectionLists: [],
                sectionNoDataText: "",

                historyColumns: [],
                historyLists: [],
                historyNoDataText: "",

                userInfo: {},

                routeName: '',
                synergyNum: 0,
                synchUsers: [],
            }
        },
        created() {
            this.historyColumns = [{
                "title": this.$L("存档日期"),
                "minWidth": 160,
                "maxWidth": 200,
                render: (h, params) => {
                    return h('span', $A.formatDate("Y-m-d H:i:s", params.row.indate));
                }
            }, {
                "title": this.$L("操作员"),
                "key": 'username',
                "minWidth": 80,
                "maxWidth": 130,
                render: (h, params) => {
                    return h('UserView', {
                        props: {
                            username: params.row.username
                        }
                    });
                }
            }, {
                "title": " ",
                "key": 'action',
                "width": 80,
                "align": 'center',
                render: (h, params) => {
                    if (this.hid == params.row.id || (this.hid == 0 && params.index == 0)) {
                        return h('Icon', {
                            props: { type: 'md-checkmark' },
                            style: { marginRight: '6px', fontSize: '16px', color: '#FF5722' },
                        });
                    }
                    return h('Button', {
                        props: {
                            type: 'text',
                            size: 'small'
                        },
                        style: {
                            fontSize: '12px'
                        },
                        on: {
                            click: () => {
                                let data = {sid: this.getSid() + "-" + params.row.id, other: this.$route.params.other}
                                if (params.index == 0) {
                                    data.sid = this.getSid();
                                }
                                this.goForward({name: 'docs-edit', params: data }, true);
                                this.refreshSid();
                                this.docDrawerShow = false;
                            }
                        }
                    }, this.$L('还原'));
                }
            }];
        },
        mounted() {
            this.routeName = this.$route.name;
            this.userInfo = $A.getUserInfo((res, isLogin) => {
                if (this.userInfo.id != res.id) {
                    this.userInfo = res;
                }
            }, false);
            //
            $A.WSOB.setOnMsgListener("chat/index", ['docs'], (msgDetail) => {
                if (this.routeName !== this.$route.name) {
                    return;
                }
                let body = msgDetail.body;
                if (body.sid != this.sid) {
                    return;
                }
                if (body.type === 'users') {
                    this.synchUsers = body.lists;
                    this.synchUsers.splice(this.synchUsers.length);
                } else if (body.type === 'update') {
                    this.$Modal.confirm({
                        title: this.$L("更新提示"),
                        content: this.$L('团队成员（%）更新了内容，<br/>更新时间：%。<br/><br/>点击【确定】加载最新内容。', body.nickname, $A.formatDate("Y-m-d H:i:s", body.time)),
                        onOk: () => {
                            this.refreshDetail();
                        }
                    });
                }
            });
        },
        activated() {
            this.refreshSid();
            this.synergy(true);
        },
        deactivated() {
            this.synergy(false);
            this.docDrawerShow = false;
            if ($A.getToken() === false) {
                this.sid = 0;
            }
        },
        watch: {
            sid(val) {
                if (!val) {
                    this.goBack();
                    return;
                }
                this.hid = $A.runNum($A.strExists(val, '-') ? $A.getMiddle(val, "-", null) : 0);
                this.refreshDetail();
            },

            docDrawerTab(act) {
                switch (act) {
                    case "menu":
                        if (!this.sectionNoDataText) {
                            this.sectionNoDataText = this.$L("数据加载中.....");
                            let bookid = this.docDetail.bookid;
                            $A.aAjax({
                                url: 'docs/section/lists',
                                data: {
                                    bookid: bookid
                                },
                                error: () => {
                                    if (bookid != this.docDetail.bookid) {
                                        return;
                                    }
                                    this.sectionNoDataText = this.$L("数据加载失败！");
                                },
                                success: (res) => {
                                    if (bookid != this.docDetail.bookid) {
                                        return;
                                    }
                                    if (res.ret === 1) {
                                        this.sectionLists = res.data;
                                        this.sectionNoDataText = this.$L("没有相关的数据");
                                    }else{
                                        this.sectionLists = [];
                                        this.sectionNoDataText = res.msg;
                                    }
                                }
                            });
                        }
                        break;

                    case "history":
                        if (!this.historyNoDataText) {
                            this.historyNoDataText = this.$L("数据加载中.....");
                            let sid = this.getSid();
                            $A.aAjax({
                                url: 'docs/section/history',
                                data: {
                                    id: sid,
                                    pagesize: 50
                                },
                                error: () => {
                                    if (sid != this.getSid()) {
                                        return;
                                    }
                                    this.historyNoDataText = this.$L("数据加载失败！");
                                },
                                success: (res) => {
                                    if (sid != this.getSid()) {
                                        return;
                                    }
                                    if (res.ret === 1) {
                                        this.historyLists = res.data;
                                        this.historyNoDataText = this.$L("没有相关的数据");
                                    }else{
                                        this.historyLists = [];
                                        this.historyNoDataText = res.msg;
                                    }
                                }
                            });
                        }
                        break;
                }
            }
        },
        computed: {
            disabledBtn() {
                return this.bakContent == $A.jsonStringify(this.docContent);
            },
            synchUsersS() {
                let temp = Math.round(new Date().getTime() / 1000);
                return this.synchUsers.filter(item => {
                    return item.indate + 10 > temp;
                });
            }
        },
        methods: {
            synergy(enter) {
                if (enter === false) {
                    $A.WSOB.sendTo('docs', {
                        type: 'quit',
                        sid: this.sid,
                        username: this.userInfo.username,
                    });
                } else {
                    if (this.routeName !== this.$route.name) {
                        let tmpNum = this.synergyNum;
                        setTimeout(() => {
                            if (tmpNum === this.synergyNum) {
                                this.synergyNum++;
                                this.synergy();
                            }
                        }, 10000);
                    } else {
                        $A.WSOB.sendTo('docs', null, {
                            type: enter === true ? 'enter' : 'refresh',
                            sid: this.sid,
                            username: this.userInfo.username,
                            userimg: this.userInfo.userimg,
                            indate: Math.round(new Date().getTime() / 1000),
                        }, (res) => {
                            this.synchUsers = res.status === 1 ? res.message : [];
                            let tmpNum = this.synergyNum;
                            setTimeout(() => {
                                if (tmpNum === this.synergyNum) {
                                    this.synergyNum++;
                                    this.synergy();
                                }
                            }, 10000);
                        });
                    }
                }
            },

            refreshSid() {
                this.sid = this.$route.params.sid;
                if (typeof this.$route.params.other === "object") {
                    this.$set(this.docDetail, 'title', $A.getObject(this.$route.params.other, 'title'))
                }
            },

            getSid() {
                return $A.runNum($A.getMiddle(this.sid, null, '-'));
            },

            refreshDetail() {
                this.docDetail = { };
                this.docContent = { };
                this.bakContent = null;
                this.getDetail();
            },

            getDetail() {
                this.loadIng++;
                $A.aAjax({
                    url: 'docs/section/content',
                    data: {
                        id: this.sid,
                    },
                    complete: () => {
                        this.loadIng--;
                    },
                    error: () => {
                        this.goBack();
                        alert(this.$L('网络繁忙，请稍后再试！'));
                    },
                    success: (res) => {
                        if (res.ret === 1) {
                            this.docDetail = res.data;
                            this.docContent = $A.jsonParse(res.data.content);
                            this.bakContent = $A.jsonStringify(this.docContent);
                        } else {
                            this.$Modal.error({title: this.$L('温馨提示'), content: res.msg});
                            this.goBack();
                        }
                    }
                });
            },

            handleSection(act, detail) {
                if (act === 'open') {
                    this.goForward({name: 'docs-edit', params: {sid: detail.id, other: detail || {}}}, true);
                    this.refreshSid();
                    this.docDrawerShow = false;
                }
            },

            handleSynch(username) {
                if (username == this.userInfo.username) {
                    return;
                }
                if (typeof window.onChatOpenUserName === "function") {
                    window.onChatOpenUserName(username);
                }
            },

            handleClick(act) {
                switch (act) {
                    case "back":
                        if (this.bakContent == $A.jsonStringify(this.docContent) && this.hid == 0) {
                            this.goBack();
                            return;
                        }
                        this.$Modal.confirm({
                            title: this.$L('温馨提示'),
                            content: this.$L('是否放弃修改的内容返回？'),
                            loading: true,
                            cancelText: this.$L('放弃保存'),
                            onCancel: () => {
                                this.goBack();
                            },
                            okText: this.$L('保存并返回'),
                            onOk: () => {
                                this.bakContent = $A.jsonStringify(this.docContent);
                                $A.aAjax({
                                    url: 'docs/section/save?id=' + this.getSid(),
                                    method: 'post',
                                    data: {
                                        D: Object.assign(this.docDetail, {content: this.bakContent})
                                    },
                                    error: () => {
                                        this.$Modal.remove();
                                        alert(this.$L('网络繁忙，请稍后再试！'));
                                    },
                                    success: (res) => {
                                        this.$Modal.remove();
                                        this.goBack();
                                        setTimeout(() => {
                                            if (res.ret === 1) {
                                                this.$Message.success(res.msg);
                                                this.historyNoDataText = '';
                                            } else {
                                                this.$Modal.error({title: this.$L('温馨提示'), content: res.msg});
                                            }
                                        }, 350);
                                    }
                                });
                            }
                        });
                        break;

                    case "save":
                        this.bakContent = $A.jsonStringify(this.docContent);
                        $A.aAjax({
                            url: 'docs/section/save?id=' + this.getSid(),
                            method: 'post',
                            data: {
                                D: Object.assign(this.docDetail, {content: this.bakContent})
                            },
                            error: () => {
                                alert(this.$L('网络繁忙，保存失败！'));
                            },
                            success: (res) => {
                                if (res.ret === 1) {
                                    this.$Message.success(res.msg);
                                    this.historyNoDataText = '';
                                } else {
                                    this.$Modal.error({title: this.$L('温馨提示'), content: res.msg});
                                }
                            }
                        });
                        break;

                    case "menu":
                    case "history":
                        this.docDrawerTab = act;
                        this.docDrawerShow = true
                        break;

                    case "share":
                    case "view":
                        this.$Message.info(this.$L("敬请期待！"));
                        break;

                }
            }
        },
    }
</script>
