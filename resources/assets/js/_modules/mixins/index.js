export default {
    install(Vue) {
        Vue.mixin({
            data() {
                return {
                    //用户信息
                    usrLogin: false,
                    usrName: '',
                    usrInfo: {},
                    //浏览器宽度≤768返回true
                    windowMax768: window.innerWidth <= 768,
                }
            },

            mounted() {
                this.usrInfo = $A.getUserInfo((data, isLogin) => {
                    this.usrLogin = isLogin;
                    this.usrInfo = data;
                    this.usrName = this.usrInfo.username || '';
                }, false);
                this.usrLogin = $A.getToken() !== false;
                this.usrName = this.usrInfo.username || '';
                //
                window.addEventListener('resize', this.windowMax768Listener);
            },

            beforeDestroy() {
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
