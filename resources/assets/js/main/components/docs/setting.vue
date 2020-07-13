<template>
    <drawer-tabs-container>
        <div class="book-setting">
            <Form ref="formSystem" :model="formSystem" :label-width="80">
                <FormItem :label="$L('修改权限')">
                    <div>
                        <RadioGroup v-model="formSystem.role_edit">
                            <Radio label="private">{{$L('私有文库')}}</Radio>
                            <Radio label="member">{{$L('成员开放')}}</Radio>
                            <Radio label="reg">{{$L('注册会员')}}</Radio>
                        </RadioGroup>
                    </div>
                    <div class="form-placeholder">{{$L('管理文库的会员。')}}</div>
                </FormItem>
                <FormItem :label="$L('阅读权限')">
                    <div>
                        <RadioGroup v-model="formSystem.role_view">
                            <Radio label="private">{{$L('私有文库')}}</Radio>
                            <Radio label="member">{{$L('成员开放')}}</Radio>
                            <Radio label="reg">{{$L('注册会员')}}</Radio>
                            <Radio label="all">{{$L('完全开放')}}</Radio>
                        </RadioGroup>
                    </div>
                    <div class="form-placeholder">{{$L('可以打开阅读分享地址的会员。')}}</div>
                </FormItem>
                <FormItem>
                    <Button :loading="loadIng > 0" type="primary" @click="handleSubmit('formSystem')">{{$L('提交')}}</Button>
                    <Button :loading="loadIng > 0" @click="handleReset('formSystem')" style="margin-left: 8px">{{$L('重置')}}</Button>
                </FormItem>
            </Form>
        </div>
    </drawer-tabs-container>
</template>

<style lang="scss" scoped>
    .book-setting {
        padding: 0 12px;
        .form-placeholder {
            color: #999;
        }
    }
</style>
<script>
    import DrawerTabsContainer from "../DrawerTabsContainer";
    export default {
        name: 'BookSetting',
        components: {DrawerTabsContainer},
        props: {
            id: {
                default: 0
            },
            canload: {
                type: Boolean,
                default: true
            },
        },
        data () {
            return {
                loadYet: false,

                loadIng: 0,

                formSystem: {},
            }
        },
        mounted() {
            if (this.canload) {
                this.loadYet = true;
                this.getSetting();
            }
        },

        watch: {
            id() {
                if (this.loadYet) {
                    this.getSetting();
                }
            },
            canload(val) {
                if (val && !this.loadYet) {
                    this.loadYet = true;
                    this.getSetting();
                }
            }
        },

        methods: {
            getSetting(save) {
                this.loadIng++;
                $A.aAjax({
                    url: 'docs/book/setting?type=' + (save ? 'save' : 'get'),
                    data: Object.assign(this.formSystem, {
                        id: this.id
                    }),
                    complete: () => {
                        this.loadIng--;
                    },
                    success: (res) => {
                        if (res.ret === 1) {
                            this.formSystem = res.data;
                            this.formSystem.role_edit = this.formSystem.role_edit || 'reg';
                            this.formSystem.role_view = this.formSystem.role_view || 'all';
                            if (save) {
                                this.$Message.success(this.$L('修改成功'));
                            }
                        } else {
                            if (save) {
                                this.$Modal.error({title: this.$L('温馨提示'), content: res.msg });
                            }
                        }
                    }
                });
            },
            handleSubmit(name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        switch (name) {
                            case "formSystem": {
                                this.getSetting(true);
                                break;
                            }
                        }
                    }
                })
            },
            handleReset(name) {
                this.$refs[name].resetFields();
            },
        }
    }
</script>
