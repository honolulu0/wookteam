<template>
    <div class="user-view-inline">
        <Tooltip :disabled="loadIng" :delay="delay" :transfer="transfer" :placement="placement" @on-popper-show="popperShow">
            {{nickname || username}}
            <div slot="content">
                <div>{{$L('用户名')}}: {{username}}</div>
                <div>{{$L('职位/职称')}}: {{profession || '-'}}</div>
            </div>
        </Tooltip>
    </div>
</template>

<style lang="scss" scoped>
    .user-view-inline {
        display: inline-block;
    }
</style>

<script>
    export default {
        name: 'UserView',
        props: {
            username: {
                default: ''
            },
            delay: {
                type: Number,
                default: 600
            },
            transfer: {
                type: Boolean,
                default: true
            },
            placement: {
                default: 'bottom'
            },
            info: {
                default: null
            },
        },
        data() {
            return {
                loadIng: true,

                nickname: null,
                profession: ''
            }
        },
        mounted() {
            this.getUserData(300);
        },
        watch: {
            username() {
                this.getUserData(300);
            },
        },
        methods: {
            isJson(obj) {
                return typeof (obj) == "object" && Object.prototype.toString.call(obj).toLowerCase() == "[object object]" && typeof obj.length == "undefined";
            },

            popperShow() {
                this.getUserData(30)
            },

            getUserData(cacheTime) {
                $A.getUserBasic(this.username, (data, success) => {
                    if (success) {
                        this.nickname = data.nickname;
                        this.profession = data.profession;
                    } else {
                        this.nickname = '';
                        this.profession = '';
                    }
                    this.loadIng = false;
                    this.$emit("on-result", data);
                    //
                    if (this.isJson(this.info)) {
                        this.$set(this.info, 'nickname', this.nickname);
                    }
                }, cacheTime);
            }
        }
    }
</script>
