<template>
    <div class="flow-content">
        <iframe ref="myFlow" class="flow-iframe" :src="url"></iframe>
        <div v-if="loadIng" class="flow-loading"><w-loading></w-loading></div>
    </div>
</template>

<style lang="scss" scoped>
    .flow-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        .flow-iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 0 0;
            border: 0;
            float: none;
            margin: -1px 0 0;
            max-width: none;
            outline: 0;
            padding: 0;
        }
        .flow-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    }
</style>
<script>
    import JSPDF from "jspdf";

    export default {
        name: "Flow",
        props: {
            value: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            readOnly: {
                type: Boolean,
                default: false
            },
        },
        data() {
            return {
                loadIng: true,

                flow: null,
                url: window.location.origin + '/js/grapheditor/' + (this.readOnly ? 'viewer' : 'index') + '.html',
            }
        },
        mounted() {
            window.addEventListener('message', this.handleMessage)
            this.flow = this.$refs.myFlow.contentWindow;
        },
        activated() {
            window.addEventListener('message', this.handleMessage)
            this.flow = this.$refs.myFlow.contentWindow;
        },
        watch: {
            value: {
                handler() {
                    this.updateContent();
                },
                deep: true
            }
        },
        methods: {
            updateContent() {
                this.flow.postMessage({
                    act: 'setXml',
                    params: Object.assign(this.value, typeof this.value.xml === "undefined" ? {
                        xml: this.value.content
                    } : {})
                }, '*')
            },
            handleMessage (event) {
                const data = event.data;
                switch (data.act) {
                    case 'ready':
                        this.loadIng = false;
                        this.updateContent();
                        break

                    case 'change':
                        this.$emit('input', data.params);
                        break

                    case 'save':
                        this.$emit('saveData');
                        break

                    case 'imageContent':
                        let pdf = new JSPDF({
                            format: [data.params.width, data.params.height]
                        });
                        pdf.addImage(data.params.content, 'PNG', 0, 0, 0, 0);
                        pdf.save(`${data.params.name}.pdf`);
                        break
                }
            },

            exportPNG(name, scale = 10) {
                this.flow.postMessage({
                    act: 'exportPNG',
                    params: {
                        name: name || this.$L('无标题'),
                        scale: scale,
                        type: 'png',
                    }
                }, '*')
            },

            exportPDF(name, scale = 10) {
                this.flow.postMessage({
                    act: 'exportPNG',
                    params: {
                        name: name || this.$L('无标题'),
                        scale: scale,
                        type: 'imageContent',
                    }
                }, '*')
            }
        },
    }
</script>
