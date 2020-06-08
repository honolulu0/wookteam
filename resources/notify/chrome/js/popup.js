chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
    if (request.act === "instances") {
        showLists(request.instances);
    }
});

chrome.runtime.sendMessage({
    act: 'getInstances'
}, function (response) {
    showLists(response);
});

function onDelete(index) {
    chrome.runtime.sendMessage({
        act: 'delInstances',
        index: index,
    });
}

function showLists(lists) {
    var html = '';
    console.log(lists);
    for (var index in lists) {
        if (!lists.hasOwnProperty(index)) {
            continue;
        }
        const item = lists[index];
        html+= '<li class="message_box" data-index="' + index + '" data-token="' + item.token + '">';
        html+= '<div class="message_username">' + item.username + '</div>';
        html+= '<div class="message_host">' + index + '</div>';
        html+= '<div class="message_unread">未读: ' + item.unread + '</div>';
        html+= '<div class="message_delete">删除</div>';
        html+= '</li>';
    }
    if (!html) {
        html+= '<li class="message_box">';
        html+= '<div class="message_loading">没有相关的记录！</div>';
        html+= '</li>';
    }
    $("#message_div").html('<ul>' + html + '</ul>');
    $("div.message_delete").click(function(){
        if (confirm("确定要删除此记录吗？")) {
            onDelete($(this).parents("li").attr("data-index"));
        }
    });
    $("div.message_unread,div.message_host").click(function(){
        const index = $(this).parents("li").attr("data-index");
        const token = encodeURIComponent($(this).parents("li").attr("data-token"));
        chrome.tabs.query({}, function (tabs) {
            var has = false;
            tabs.some(function (item) {
                if ($A.getHostname(item.url) == index) {
                    chrome.windows.update(item.windowId, {focused: true});
                    chrome.tabs.highlight({tabs: item.index, windowId: item.windowId});
                    chrome.tabs.update({url: $A.urlAddParams($A.removeURLParameter(item.url, ['open', 'rand']), {open: 'chat', rand: Math.round(new Date().getTime())})});
                    return has = true;
                }
            });
            if (!has) {
                chrome.tabs.create({ url: 'http://' + index + '/#/todo?token=' + token + '&open=chat' })
            }
        });
    })
}

