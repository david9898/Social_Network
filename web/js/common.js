let path      = window.location.pathname
let splitPath = path.split('/')

let webSocket
if ( splitPath.length === 3 ) {
    webSocket = new WebSocket('ws://192.168.0.100:9899/' + splitPath[1] + '/' + splitPath[2])
}else {
    webSocket = new WebSocket('ws://192.168.0.100:9899/' + splitPath[1])
}

$(document).ready(async () => {
    let myMessageTemplate          = await $.get('/TemplatesHbs/myMessage.hbs')
    let yourMessageTemplate        = await $.get('/TemplatesHbs/yourMessage.hbs')
    let seenTemplate               = await $.get('/TemplatesHbs/seenMessage.hbs')
    let deliveredTemplate          = await $.get('/TemplatesHbs/deliveredMessage.hbs')
    let savedTemplate              = await $.get('/TemplatesHbs/savedMessage.hbs')
    let friendTemplate             = await $.get('/TemplatesHbs/friendTemplate.hbs')
    let searchUserTemplate         = await $.get('/TemplatesHbs/searchFriend.hbs')
    let audio                      = new Audio('/sounds/tspt_game_button_04_040.mp3')
    let addMessageAudio            = new Audio('/sounds/musical_keyboard_key_flick_spring_up.mp3')

    webSocket.onopen = function () {
        console.log('Connection Established')
    }

    webSocket.onmessage = function (e) {
        let data = JSON.parse(e.data)
        console.log(data)

        switch ( data['command'] ) {
            case 'savedMsg':
                savedMsg(data, deliveredTemplate, savedTemplate)
                break

            case 'addMessage':
                addMessage(webSocket, data, yourMessageTemplate, addMessageAudio, friendTemplate, myMessageTemplate, yourMessageTemplate)
                break

            case 'seenMessage':
                seenMessage(data, seenTemplate)
                break

            case 'addSuggestion':
                addSuggestion(addMessageAudio)
                break

            case 'searchFriends':
                renderSearchFriends(data, searchUserTemplate)
                break

            case 'acceptSuggestion':
                acceptSuggestion()
                break

        }
    }

    webSocket.onerror = function (e) {
        console.log(e.data)
    }

    sendMessage(webSocket, myMessageTemplate, audio)
    searchFriends()
})

function sendMessage(webSocket, myMessageTemplate, audio) {
    $('.send_message_button').on('click', function () {
        let acceptUser = $('.current_user').attr('acceptuser')
        let content = $('.send_message').val()
        let randomId = Math.random().toString(36).substring(7)
        let scrollHeight = $('.message_container .message_text')[0].scrollHeight
        let obj = {
            'acceptUser': acceptUser,
            'content': content,
            'command': 'addMessage',
            'id': randomId
        }

        webSocket.send(JSON.stringify(obj))
        $('.send_message').val('')
        let template = Handlebars.compile(myMessageTemplate)
        let html = template(obj)
        $('.message_text .real_text_message_container').append(html)
        audio.play()
        $('.message_text').animate({scrollTop: scrollHeight})
        $('.message_notification_in_container').remove()
    })
}

function savedMsg(msg, deliveredTemplate, savedTemplate) {
    let msgId       = msg['id']
    let randomId    = msg['randomId']
    let toUser      = msg['acceptUser']
    let currentUser = $('.message_text').attr('acceptuser')
    $('.message_text .real_text_message_container #' + randomId).attr('id', msgId)

    if ( toUser == currentUser ) {
        if ( msg['messageStatus'] !== 'delivered' ) {
            let template = Handlebars.compile(savedTemplate)
            let html     = template()
            $('.real_text_message_container').append(html)
        }else {
            let template = Handlebars.compile(deliveredTemplate)
            let html     = template()
            $('.real_text_message_container').append(html)
        }
    }
}

function addMessage(webSocket, msg, yourMessageTemplate, audio, friendTemplate, myMessage, yourMessage) {
    let url = window.location.href
    let splitArr = url.split('/')

    if ( splitArr[3] === 'messages' ) {

        let acceptUser = msg['acceptUser']
        let currentUserOnMessanger = $('.message_text').attr('acceptuser')
        let scrollHeight = $('.message_container .message_text')[0].scrollHeight
        let profileImage = $('.current_user img').attr('src')
        let onlyImage = profileImage.split('/')
        let realImage = onlyImage[2]
        let msgId = msg['id']
        let sendUser = msg['sendUser']
        msg['profileImage'] = realImage
        audio.play()

        if (sendUser == currentUserOnMessanger) {
            $('.message_notification_in_container').remove()
            let template = Handlebars.compile(yourMessageTemplate)
            let html = template(msg)
            $('.real_text_message_container').append(html)
            $('.message_text').animate({scrollTop: scrollHeight})

        } else {
            let fullName = $('.friends_container #' + sendUser + ' .full_name').text()
            let img = $('.friends_container #' + sendUser + ' img').attr('src')
            let countMessages = $('.friends_container #' + sendUser + ' .current_user_count_message span').text()
            if (countMessages !== '') {
                countMessages++
            } else {
                countMessages = 1
            }
            let obj = {
                'id': sendUser,
                'image': img,
                'fullName': fullName,
                'countMsg': countMessages
            }
            $('.friends_container #' + sendUser).remove()
            let template = Handlebars.compile(friendTemplate)
            let html = template(obj)
            $('.friends_container').prepend(html)

            changeCurrentFriends($('#csrf_token').val(), myMessage, yourMessage)
        }
    }else {
        audio.play()
        toastr.info("You have new message!")
    }
}

function seenMessage(message, seenTemplate) {
    let sendUser               = message['sendUser']
    let currentUserOnMessanger = $('.message_text').attr('acceptuser')
    let acceptUser             = message['acceptUser']
    let messageId              = message['id']

    if ( acceptUser == currentUserOnMessanger ) {
        let messages    = $('.div_message_mine')
        let arrMessages = messages.toArray()
        let lastEl      = arrMessages[arrMessages.length - 1]
        let lastId      = $(lastEl).attr('id')

        if ( lastId == messageId ) {
            $('.message_notification_in_container').remove()
            let template = Handlebars.compile(seenTemplate)
            let html     = template()
            $('.real_text_message_container').append(html)
        }
    }
}


function addSuggestion(audio) {
    // audio.play()
    console.log('DAVOOOOO')
    return toastr.info('You have new Suggestion')
}

function acceptSuggestion() {
    return toastr.info('Somoone accept you suggestion')
}

function renderSearchFriends(message, searchUserTemplate) {

}

function searchFriends() {
    let csrfToken = $('#csrf_token').val()

    let options = {
        url: function(phrase) {
            return "api/searchFriends/" + csrfToken + "/" + phrase;
        },

        getValue: "fullName",

        listLocation: "users",

        requestDelay: 500,

        template: {
            type: "custom",
            method: function(value, item) {
                return `<a href="/profile/${item['id']}">
                            <span id="${item['id']}">
                                <img width="30" height="30" src="uploads/profileImages/${item['profileImage']}" />
                                ${item['fullName']}
                             </span>
                         </a>`;
            }
        },

        list: {
            onClickEvent: function () {
                console.log($(this))
            }
        }

    };

    $("#search_friends").easyAutocomplete(options);
}
