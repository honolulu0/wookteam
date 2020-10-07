const languageListenerObjects = [];

export default {
    install(Vue) {
        Vue.mixin({
            data() {
                return {
                    languageInit: false,
                    languageData: {},
                    languageType: window.localStorage['__language:type__'] || 'zh',
                }
            },

            watch: {
                languageType: {
                    handler(type) {
                        if (type && typeof this.initLanguage === "function") {
                            this.initLanguage();
                        }
                    },
                    immediate: true
                },
            },

            methods: {
                /**
                 * 初始化语言数据
                 * @private
                 */
                __initLanguageData() {
                    if (this.languageInit === false) {
                        this.languageInit = true;
                        //
                        this.addLanguageData({
                            en: require("../../../../lang/en/general.js").default,
                            zh: require("../../../../lang/zh/general.js").default
                        });
                        //
                        languageListenerObjects.push((lang) => {
                            this.languageType = lang;
                        });
                    }
                },

                /**
                 * 监听语言变化
                 * @param callback
                 */
                setLanguageListener(callback) {
                    if (typeof callback === 'function') {
                        languageListenerObjects.push((lang) => {
                            callback(lang);
                        });
                    }
                },

                /**
                 * 语言包数据
                 * @param language
                 * @param data
                 */
                addLanguageData(language, data) {
                    if (typeof language === 'object') {
                        Object.keys(language).forEach((key) => {
                            this.addLanguageData(key, language[key]);
                        });
                        return;
                    }
                    if (!language || typeof data !== "object") {
                        return;
                    }
                    this.__initLanguageData();
                    if (typeof this.languageData[language] === "undefined") {
                        this.languageData[language] = {};
                    }
                    Object.assign(this.languageData[language], data);
                    //
                    if (language === 'en') {
                        if (typeof this.languageData['zh'] === "undefined") {
                            this.languageData['zh'] = {};
                        }
                        let cnData = {};
                        for(let key in data) {
                            if (data.hasOwnProperty(key) && typeof this.languageData['zh'][data[key]] === 'undefined') {
                                cnData[data[key]] = key;
                            }
                        }
                        Object.assign(this.languageData['zh'], cnData);
                    }else if (language === 'zh') {
                        if (typeof this.languageData['en'] === "undefined") {
                            this.languageData['en'] = {};
                        }
                        let enData = {};
                        for(let key in data) {
                            if (data.hasOwnProperty(key) && typeof this.languageData['en'][data[key]] === 'undefined') {
                                enData[data[key]] = key;
                            }
                        }
                        Object.assign(this.languageData['en'], enData);
                    }
                },

                /**
                 * 变化语言
                 * @param language
                 */
                setLanguage(language) {
                    this.__initLanguageData();
                    window.localStorage['__language:type__'] = language;
                    languageListenerObjects.forEach((call) => {
                        if (typeof call === 'function') {
                            call(language);
                        }
                    });
                },

                /**
                 * 获取语言
                 * @returns {*}
                 */
                getLanguage() {
                    this.__initLanguageData();
                    return this.languageType;
                },

                /**
                 * 替换%遍历
                 * @param text
                 * @param objects
                 */
                replaceArgumentsLanguage(text, objects) {
                    let j = 1;
                    while (text.indexOf("%") !== -1) {
                        if (typeof objects[j] === "object") {
                            text = text.replace("%", "");
                        } else {
                            text = text.replace("%", objects[j]);
                        }
                        j++;
                    }
                    return text;
                },

                /**
                 * 显示语言
                 * @return {string}
                 */
                $L(text) {
                    if (text) {
                        this.__initLanguageData();
                        //
                        if (typeof this.languageData[this.languageType] === "object") {
                            let temp = this.languageData[this.languageType][text];
                            if (temp === null) {
                                return this.replaceArgumentsLanguage(text, arguments);
                            }
                            if (typeof temp !== 'undefined') {
                                return this.replaceArgumentsLanguage(temp, arguments);
                            }
                        }
                        //
                        try {
                            let key = '__language:Undefined__';
                            let tmpData = JSON.parse(window.localStorage[key] || '{}');
                            if (typeof tmpData[this.languageType] !== "object") {
                                tmpData[this.languageType] = {};
                            }
                            tmpData[this.languageType][text] = "";
                            window.localStorage[key] = JSON.stringify(tmpData);
                        }catch (e) {
                            //
                        }
                    }
                    return this.replaceArgumentsLanguage(text, arguments);
                }
            }
        });
    }
}
