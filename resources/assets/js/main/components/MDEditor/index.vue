<template>
    <div>
        <div class="mdeditor-box">
            <MarkdownPro ref="md1" v-model="content" :height="height" :toolbars="toolbars" :is-custom-fullscreen="transfer" @on-custom="customClick"></MarkdownPro>
            <img-upload ref="myUpload" class="teditor-upload" type="callback" @on-callback="editorImage" num="50" style="display:none;"></img-upload>
        </div>
        <Modal v-model="transfer" class="mdeditor-transfer" footer-hide fullscreen transfer :closable="false">
            <div class="mdeditor-transfer-body">
                <MarkdownPro ref="md2" v-if="transfer" v-model="content" :toolbars="toolbars" :is-custom-fullscreen="transfer" height="100%" @on-custom="customClick"></MarkdownPro>
            </div>
        </Modal>
        <Modal v-model="html2md" title="html转markdown" okText="转换成markdown" width="680" class-name="simple-modal" @on-ok="htmlOk" transfer>
            <Input type="textarea" v-model="htmlValue" :rows="14" placeholder="请输入html代码..." />
        </Modal>
    </div>
</template>

<style lang="scss">
    .mdeditor-transfer {
        background-color: #ffffff;
        .ivu-modal-header {
            display: none;
        }
        .ivu-modal-close {
            top: 7px;
        }
        .mdeditor-transfer-body {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
        }
    }
</style>
<style lang="scss" scoped>
    .mdeditor-box {
        position: relative;
    }
</style>
<script>
    import MarkdownPro from './pro';
    import ImgUpload from "../ImgUpload";

    export default {
        name: 'MDEditor',
        components: {ImgUpload, MarkdownPro},
        props: {
            value: {
                default: ''
            },
            height: {
                default: 380,
            },
            toolbars: {
                type: Object,
                default: () => {
                    return {
                        strong: true,
                        italic: true,
                        overline: true,
                        h1: true,
                        h2: true,
                        h3: true,
                        h4: false,
                        h5: false,
                        h6: false,
                        hr: true,
                        quote: true,
                        ul: true,
                        ol: true,
                        code: true,
                        link: true,
                        image: false,
                        uploadImage: false,
                        table: true,
                        checked: true,
                        notChecked: true,
                        split: true,
                        preview: true,
                        fullscreen: false,
                        theme: false,
                        exportmd: false,
                        importmd: false,
                        save: false,
                        clear: false,

                        custom_image: true,
                        custom_uploadImage: true,
                        custom_fullscreen: true,
                    };
                }
            }
        },
        data() {
            return {
                content: '',
                transfer: false,
                html2md: false,
                htmlValue: '',
            };
        },
        mounted() {
            this.content = this.value;
        },
        activated() {
            this.content = this.value;
        },
        watch: {
            value(newValue) {
                if (newValue == null) {
                    newValue = "";
                }
                this.content = newValue;
            },
            content(val) {
                this.$emit('input', val);
            },
        },
        methods: {
            editorImage(lists) {
                for (let i = 0; i < lists.length; i++) {
                    let item = lists[i];
                    if (typeof item === 'object' && typeof item.url === "string") {
                        if (this.transfer) {
                            this.$refs.md2.insertContent('\n![image](' + item.url + ')');
                        } else {
                            this.$refs.md1.insertContent('\n![image](' + item.url + ')');
                        }
                    }
                }
            },
            customClick(act) {
                switch (act) {
                    case "image-browse": {
                        this.$refs.myUpload.browsePicture();
                        break;
                    }
                    case "image-upload": {
                        this.$refs.myUpload.handleClick();
                        break;
                    }
                    case "fullscreen": {
                        this.transfer = !this.transfer;
                        break;
                    }
                    case "html2md": {
                        this.html2md = true;
                        break;
                    }
                }
            },
            htmlOk() {
                this.loadScript(window.location.origin + '/js/html2md.js', () => {
                    if (typeof toMarkdown !== 'function') {
                        alert("组件加载失败！");
                        return;
                    }
                    if (this.transfer) {
                        this.$refs.md2.insertContent('\n' + toMarkdown(this.htmlValue, { gfm: true }));
                    } else {
                        this.$refs.md1.insertContent('\n' + toMarkdown(this.htmlValue, { gfm: true }));
                    }
                    this.htmlValue = "";
                });
            },
            loadScript(url, callback) {
                let script = document.createElement("script");
                script.type = "text/javascript";
                if (script.readyState) {
                    script.onreadystatechange = () => {
                        if (script.readyState === "loaded" || script.readyState === "complete") {
                            script.onreadystatechange = null;
                            callback();
                        }
                    };
                } else {
                    script.onload = () => {
                        callback();
                    };
                }
                script.src = url;
                document.body.appendChild(script);
            }
        }
    }
</script>
