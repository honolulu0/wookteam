export default {
    install(Vue) {
        Vue.mixin({
            data() {
                return {
                    mixinId: 0,
                    //用户信息
                    usrLogin: false,
                    usrInfo: {},
                    usrName: '',
                    //浏览器宽度≤768返回true
                    windowMax768: window.innerWidth <= 768,
                }
            },

            mounted() {
                if (typeof window.__mixinId != "number") window.__mixinId = 0;
                this.mixinId = window.__mixinId++;
                //
                this.usrLogin = $A.getToken() !== false;
                this.usrInfo = $A.getUserInfo();
                this.usrName = this.usrInfo.username || '';
                $A.setOnUserInfoListener('mixins_' + this.mixinId, (data, isLogin) => {
                    this.usrLogin = isLogin;
                    this.usrInfo = data;
                    this.usrName = this.usrInfo.username || '';
                });
                //
                window.addEventListener('resize', this.windowMax768Listener);
            },

            beforeDestroy() {
                $A.removeUserInfoListener('mixins_' + this.mixinId);
                window.removeEventListener('resize', this.windowMax768Listener);
            },

            methods: {
                isArray(obj) {
                    return typeof (obj) == "object" && Object.prototype.toString.call(obj).toLowerCase() == '[object array]' && typeof obj.length == "number";
                },

                isJson(obj) {
                    return typeof (obj) == "object" && Object.prototype.toString.call(obj).toLowerCase() == "[object object]" && typeof obj.length == "undefined";
                },

                windowMax768Listener() {
                    this.windowMax768 = window.innerWidth <= 768
                }
            }
        });
    }
}
