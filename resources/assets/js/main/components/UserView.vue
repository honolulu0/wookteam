<template>
    <div class="user-view-inline">
        <Tooltip :disabled="nickname === null" :delay="delay" :transfer="transfer" :placement="placement" @on-popper-show="popperShow">
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
        },
        data() {
            return {
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
                    this.$emit("on-result", data);
                }, cacheTime);
            }
        }
    }
</script>
