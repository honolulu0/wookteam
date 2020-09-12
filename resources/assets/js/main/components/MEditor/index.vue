<template>
    <div>
        <div class="mdeditor-box">
            <mavon-editor
                ref="markdown"
                class="markdown"
                :style="markdownStyle"
                v-model="content"
                :boxShadow="false"
                :toolbars="toolbars"
                @imgAdd="imgAdd">
                <template slot="left-toolbar-after">
                    <span class="op-icon-divider"></span>
                    <Dropdown @on-click="customClick" transfer>
                        <button type="button" class="op-icon fa fa-mavon-picture-o" aria-hidden="true"></button>
                        <DropdownMenu slot="list">
                            <DropdownItem name="image-browse">{{$L('浏览图片')}}</DropdownItem>
                            <DropdownItem name="image-upload">{{$L('上传图片')}}</DropdownItem>
                        </DropdownMenu>
                    </Dropdown>
                    <div class="left-toolbar-item" :title="$L('上传文件')" @click="customClick('file-upload')">
                        <svg t="1599285632421" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="45640" width="16" height="16"><path d="M127.519 892.879v-763.34h655.448V514.69h63.612V81.831c0-8.783-7.12-15.903-15.903-15.903H79.81c-8.783 0-15.903 7.12-15.903 15.903v858.757c0 8.783 7.12 15.903 15.903 15.903h493.993v-63.612H127.519z" fill="#999999" p-id="45641"></path><path d="M231.608 228.388h447.269V292H231.608zM231.608 384.409h447.269v63.612H231.608zM231.608 540.43h245.141v63.612H231.608zM231.608 696.451h245.141v63.612H231.608zM923.269 762.938L745.315 584.984c-3.545-3.545-8.34-5.074-12.966-4.596a15.931 15.931 0 0 0-9.848 4.616L544.586 762.918c-6.248 6.248-6.248 16.379 0 22.627l22.353 22.353c6.248 6.248 16.379 6.248 22.627 0l112.555-112.555v245.148c0 8.837 7.163 16 16 16h31.612c8.837 0 16-7.163 16-16V695.363l112.555 112.555c6.248 6.248 16.379 6.248 22.627 0l22.353-22.353c6.249-6.248 6.249-16.378 0.001-22.627z" fill="#999999" p-id="45642"></path></svg>
                    </div>
                    <span class="op-icon-divider"></span>
                    <div class="left-toolbar-item text" :title="$L('html转markdown')" @click="customClick('html2md')">HTML2MD</div>
                </template>
            </mavon-editor>
            <ImgUpload
                ref="myUpload"
                class="upload-control"
                type="callback"
                :uploadIng.sync="uploadIng"
                @on-callback="editorImage"
                num="50"/>
            <Upload
                name="files"
                ref="fileUpload"
                class="upload-control"
                :action="actionUrl"
                :data="params"
                multiple
                :format="uploadFormat"
                :show-upload-list="false"
                :max-size="maxSize"
                :on-progress="handleProgress"
                :on-success="handleSuccess"
                :on-error="handleError"
                :on-format-error="handleFormatError"
                :on-exceeded-size="handleMaxSize"
                :before-upload="handleBeforeUpload"/>
        </div>
        <Spin fix v-if="uploadIng > 0">
            <Icon type="ios-loading" class="upload-control-spin-icon-load"></Icon>
            <div>{{$L('正在上传文件...')}}</div>
        </Spin>
        <Modal v-model="html2md" title="html转markdown" okText="转换成markdown" width="680" class-name="simple-modal" @on-ok="htmlOk" transfer>
            <Input type="textarea" v-model="htmlValue" :rows="14" placeholder="请输入html代码..." />
        </Modal>
    </div>
</template>

<style lang="scss" scoped>
.mdeditor-box {
    position: relative;
}
.upload-control {
    display: none;
    width: 0;
    height: 0;
    overflow: hidden;
}
.left-toolbar-item {
    box-sizing: border-box;
    display: inline-block;
    cursor: pointer;
    height: 28px;
    width: 28px;
    margin: 6px 0 5px 0;
    font-size: 14px;
    padding: 4.5px 6px 5px 3.5px;
    color: #757575;
    border-radius: 5px;
    text-align: center;
    background: none;
    border: none;
    outline: none;
    line-height: 1;
    vertical-align: middle;
    transition: all 0.2s linear 0s;
    &:hover {
        color: rgba(0,0,0,0.8);
        background: #e9e9eb;
    }
    &.text {
        width: auto;
        line-height: 28px;
        padding: 0 3px;
    }
}
</style>

<script>
import Vue from 'vue'
import mavonEditor from 'mavon-editor'
import 'mavon-editor/dist/css/index.css'
import ImgUpload from "../ImgUpload";
import * as axios from "axios";
Vue.use(mavonEditor)

export default {
    name: 'MEditor',
    components: {ImgUpload},
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
                    bold: true, // 粗体
                    italic: true, // 斜体
                    header: true, // 标题
                    underline: true, // 下划线
                    strikethrough: true, // 中划线
                    mark: true, // 标记
                    superscript: true, // 上角标
                    subscript: true, // 下角标
                    quote: true, // 引用
                    ol: true, // 有序列表
                    ul: true, // 无序列表
                    link: true, // 链接
                    imagelink: false, // 图片链接
                    code: true, // code
                    table: true, // 表格
                    fullscreen: true, // 全屏编辑
                    readmodel: true, // 沉浸式阅读
                    htmlcode: true, // 展示html源码
                    help: false, // 帮助
                    undo: true, // 上一步
                    redo: true, // 下一步
                    trash: true, // 清空
                    save: false, // 保存（触发events中的save事件）
                    navigation: true, // 导航目录
                    alignleft: true, // 左对齐
                    aligncenter: true, // 居中
                    alignright: true, // 右对齐
                    subfield: true, // 单双栏模式
                    preview: false, // 预览
                };
            }
        }
    },
    data() {
        return {
            content: '',
            html2md: false,
            htmlValue: '',

            uploadIng: 0,
            uploadFormat: ['jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'esp', 'pdf', 'rar', 'zip', 'gz'],
            actionUrl: $A.apiUrl('system/fileupload'),
            params: { token: $A.getToken() },
            maxSize: 10240
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
    computed: {
        markdownStyle() {
            const {height} = this;
            const style = {};
            if (height) {
                style.height = /^\d+$/.test(height) ? (height + 'px') : height
            }
            return style;
        }
    },
    methods: {
        imgAdd(pos, file) {
            //删除原本的地址
            let reg = eval("/(!\\[\[^\\[\]*?\\]\(?=\\(\)\)\\(\\s*\(" + pos + "\)\\s*\\)/g");
            let val = this.$refs.markdown.d_value;
            let obj = this.$refs.markdown.getTextareaDom();
            let match = val.match(reg);
            if (match) {
                obj.focus();
                let startPos = -1;
                if (typeof obj.selectionStart === 'number') {
                    startPos = obj.selectionStart - match[0].length;
                }
                this.$refs.markdown.d_value = val.replace(reg, "");
                this.$refs.markdown.$refs.toolbar_left.img_file = [];
                this.$nextTick(() => {
                    if (startPos > -1) {
                        obj.selectionStart = startPos
                        obj.selectionEnd = startPos;
                        obj.focus();
                    }
                });
            }
            //上传文件
            this.$refs.myUpload.handleManual(file);
        },

        editorImage(lists) {
            for (let i = 0; i < lists.length; i++) {
                let item = lists[i];
                if (typeof item === 'object' && typeof item.url === "string") {
                    this.$refs.markdown.insertText(this.$refs.markdown.getTextareaDom(), {
                        prefix: (i > 0 ? '\n' : '') + '![image](' + item.url + ')',
                        subfix: '',
                        str: ''
                    });
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
                case "file-upload": {
                    this.$refs.fileUpload.handleClick();
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
                this.$refs.markdown.insertText(this.$refs.markdown.getTextareaDom(), {
                    prefix: '\n' + toMarkdown(this.htmlValue, { gfm: true }),
                    subfix: '',
                    str: ''
                });
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
        },

        /********************文件上传部分************************/

        handleProgress() {
            //开始上传
            this.uploadIng++;
        },

        handleSuccess(res, file) {
            //上传完成
            this.uploadIng--;
            if (res.ret === 1) {
                this.$refs.markdown.insertText(this.$refs.markdown.getTextareaDom(), {
                    prefix: `[${res.data.name} (${$A.bytesToSize(res.data.size * 1024)})](${res.data.url})`,
                    subfix: '',
                    str: ''
                });
            } else {
                this.$Modal.warning({
                    title: this.$L('上传失败'),
                    content: this.$L('文件 % 上传失败，%', file.name, res.msg)
                });
            }
        },

        handleError() {
            //上传错误
            this.uploadIng--;
        },

        handleFormatError(file) {
            //上传类型错误
            this.$Modal.warning({
                title: this.$L('文件格式不正确'),
                content: this.$L('文件 % 格式不正确，仅支持上传：%', file.name, this.uploadFormat.join(','))
            });
        },

        handleMaxSize(file) {
            //上传大小错误
            this.$Modal.warning({
                title: this.$L('超出文件大小限制'),
                content: this.$L('文件 % 太大，不能超过%。', file.name, $A.bytesToSize(this.maxSize * 1024))
            });
        },

        handleBeforeUpload() {
            //上传前判断
            this.params = {
                token: $A.getToken(),
            };
            return true;
        },
    }
}
</script>
