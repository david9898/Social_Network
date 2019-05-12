	$(document).ready(async () => {
    let csrfToken = $('#csrf_token').val()
    let myMessageTemplate = await $.get('TemplatesHbs/myMessage.hbs')
    let yourMessageTemplate = await $.get('TemplatesHbs/yourMessage.hbs')
    let friendTemplate = await $.get('TemplatesHbs/friendTemplate.hbs')

    sendMessage(csrfToken, myMessageTemplate)
    changeCurrentFriends(csrfToken, myMessageTemplate, yourMessageTemplate)
    getMessages(csrfToken, myMessageTemplate, yourMessageTemplate, friendTemplate)
    searchInFriends(friendTemplate, csrfToken, myMessageTemplate, yourMessageTemplate)

})

function sendMessage(csrfToken, myMessage) {
    $('.send_message_button').on('click', function () {
        let acceptUser = $('.message_text').attr('acceptUser')
        let content = $('.send_message').val()

        if ( content !== '' ) {
            let data = {
                'csrfToken': csrfToken,
                'acceptUser': acceptUser,
                'content': content
            }

            $.ajax({
                url: 'api/sendMessage',
                type: 'POST',
                data: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                $('.be_first_to_message').empty()

                let obj = {
                    'content': content
                }
                let template = Handlebars.compile(myMessage)
                let html = template(obj)
                $('.message_text .real_text_message_container').append(html)

                let scrollHeight = $('.message_container .message_text')[0].scrollHeight
                $('.message_text').animate({scrollTop: scrollHeight})
                $('.send_message').val('')

            }).catch((err) => {

            })
        }
    })
}

function changeCurrentFriends(csrfToken, myMessage, yourMessage) {
    let xhr = new XMLHttpRequest()

    $('.friend_id').on('click', function () {
        $('.spinner').css('display', 'none')
        sessionStorage.setItem('haveMore', 'false')
        let currentUser = $('.current_user').attr('acceptUser')
        let id = $(this).attr('id')
        let data = null

        if (currentUser === id) {
            return
        }

        $('.message_container .message_text .real_text_message_container').empty()
        let img = $(this).children('img').attr('src')
        let names = $(this).children('.full_name').text()

        $('.message_container .current_user img').attr('src', img)
        $('.message_container .current_user h3').text(names)
        $('.message_container .current_user').attr('acceptUser', id)
        $('.message_container .message_text').attr('acceptUser', id)
        $('.message_container .message_text .sk-circle').css('display', 'block')

        $('aside #' + id + ' .current_user_count_message span').text('0')
        $('aside #' + id + ' .current_user_count_message').css('display', 'none')
        $('aside #' + id + ' .search_friends_container .current_user_count_message').css('display', 'none')

        xhr.open('GET', 'api/getMessagesBetweenUsers/' + id + '/' + csrfToken)

        xhr.send()

        xhr.onload = function () {
            if ( xhr.status === 200 ) {
                data = JSON.parse(xhr.responseText)
                renderMessagesWithUser(data, csrfToken, myMessage, yourMessage)
            }
        }


    })
}

function renderMessagesWithUser(res, csrfToken, myMessage, yourMessage) {
    let realData = JSON.parse(res)
    let myId = realData['userId']
    let data = realData['responce']

    if (data === 'none') {
        $('.message_container .message_text .real_text_message_container').empty()
        $('.message_container .message_text .sk-circle').css('display', 'none')
        $('.message_container .message_text .real_text_message_container').append("<p class='be_first_to_message'>Be first to write something!!!</p>")
        $('.message_container .div_send_message').css('display', 'block')
        return
    }

    for (let i = 0; i < data.length; i++) {
        $('.message_container .message_text .sk-circle').css('display', 'none')
        $('.message_container .div_send_message').css('display', 'block')

        if (i < 20) {
            if (data[i]['sendUserId'] === myId) {
                let template = Handlebars.compile(myMessage)
                let html = template(data[i])
                $('.message_container .message_text .real_text_message_container').prepend(html)
            } else {
                let template = Handlebars.compile(yourMessage)
                let html = template(data[i])
                $('.message_container .message_text .real_text_message_container').prepend(html)
            }
        } else {
            onScrollTop(csrfToken, myMessage, yourMessage)
        }
    }

    let scrollHeight = $('.message_container .message_text')[0].scrollHeight
    $('.message_text').animate({scrollTop: scrollHeight})
}

function onScrollTop(csrfToken, myMessage, yourMessage) {
    sessionStorage.setItem('list', '1')
    sessionStorage.setItem('haveMore', 'true')
    let scrollHeight = $('.message_container .message_text')[0].scrollHeight
    let xhr = new XMLHttpRequest()

    if (scrollHeight > 430) {

        $('.message_container .message_text').scroll(function () {
            if ( sessionStorage.getItem('haveMore') === 'true' ) {
                let currentUser = $('.current_user').attr('acceptUser')
                let scrollTop = $(this).scrollTop()
                if (scrollTop === 0) {
                    sessionStorage.setItem('haveMore', 'false')
                    $('.spinner').css('display', 'block')

                    xhr.open('GET', 'api/getMoreMessages/' + csrfToken + '/' + currentUser + '/' + Number(sessionStorage.getItem('list')))

                    xhr.send()

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            showMoreOnScroll(JSON.parse(xhr.responseText), myMessage, yourMessage)
                        }
                    }
                }
            }
        })
    }
}

function showMoreOnScroll(res, myMessage, yourMessage) {
    let realData = JSON.parse(res)

    if ( $('.current_user').attr('acceptUser') === realData['otherUser'] ) {
        let data = realData['messages']
        let currentId = realData['currentId']

        sessionStorage.setItem('list', Number(sessionStorage.getItem('list')) + 1)

        $('.spinner').css('display', 'none')

        sessionStorage.setItem('haveMore', 'false')

        for (let i = 0; i < data.length; i++) {

            if (i < 20) {
                if (data[i]['sendUserId'] === currentId) {
                    let template = Handlebars.compile(myMessage)
                    let html = template(data[i])
                    $('.message_container .message_text .real_text_message_container').prepend(html)
                } else {
                    let template = Handlebars.compile(yourMessage)
                    let html = template(data[i])
                    $('.message_container .message_text .real_text_message_container').prepend(html)
                }

            } else {
                sessionStorage.setItem('haveMore', 'true')
            }

        }
    }
}

function getMessages(csrfToken, myMessage, yourMessage, friendTemplate) {
    let audio = new Audio('sounds/musical_keyboard_key_flick_spring_up.mp3')
    setInterval(function () {
        let currentUser = $('.current_user').attr('acceptUser')

        if ( currentUser === undefined ) {
            currentUser = 'undefined'
        }

        $.ajax({
            url: 'api/getMessageData/' + csrfToken + '/' + currentUser,
            type: 'GET',
        }).then((res) => {
            let realData = JSON.parse(res)
            let currentUser = $('.current_user').attr('acceptUser')
            let data = realData['data']

            if ( data.length > 0 ) {
                audio.play()
                for (let msg of data) {
                    if ( msg['sendUserId'] == currentUser ) {
                        let template = Handlebars.compile(yourMessage)
                        let html = template(msg)
                        $('.message_container .message_text .real_text_message_container').append(html)
                        let scrollHeight = $('.message_container .message_text')[0].scrollHeight
                        $('.message_text').animate({scrollTop: scrollHeight})
                    }else {
                        let id = msg['sendUserId']
                        $('aside #' + id + ' .current_user_count_message').css('display', 'block')
                        let currentMsg = $('aside .friends_container #' + id + ' .current_user_count_message span').text()
                        if ( currentMsg !== '' ) {
                            let obj = {
                                'id': id,
                                'image': $('aside .friends_container #' + id + ' img').attr('src'),
                                'fullName': $('aside .friends_container #' + id + ' .full_name').text(),
                                'countMsg': Number(currentMsg) + 1
                            }
                            $('aside #' + id).remove()

                            let template = Handlebars.compile(friendTemplate)
                            let html = template(obj)
                            $('aside .friends_container').prepend(html)
                        }else {
                            let obj = {
                                'id': id,
                                'image': $('aside .friends_container #' + id + ' img').attr('src'),
                                'fullName': $('aside .friends_container #' + id + ' .full_name').text(),
                                'countMsg': 1
                            }
                            $('aside #' + id).remove()

                            let template = Handlebars.compile(friendTemplate)
                            let html = template(obj)
                            $('aside .friends_container').prepend(html)
                        }
                    }
                }
            }
            changeCurrentFriends(csrfToken, myMessage, yourMessage)
        })
    }, 5000)
}

function searchInFriends(friendTemplate, csrfToken, myMessage, yourMessage) {

    $('aside .search_friends .search_friends_input').on('input', function () {
        let arr = []
        let val = $(this).val()

        for (let text of $('.friends_container .friend_id').toArray()) {
            let obj = {}
            let id = $(text).attr('id')
            let image = $(text).children('img').attr('src')
            let fullName = $(text).children('.full_name').text()
            let countMsg = null

            if ( $(text).children('.current_user_count_message').text() !== '' ) {
                countMsg = $(text).children('.current_user_count_message').text()
            }else {
                countMsg = ''
            }

            obj['id'] = id
            obj['fullName'] = fullName
            obj['image'] = image
            obj['countMsg'] = countMsg

            arr.push(obj)
        }

        if ( val !== '' ) {
            $('.search_friends_container').css('display', 'block')
            $('.friends_container').css('display', 'none')
            $('.search_friends_container').empty()
            for ( let obj of arr ) {
                if ( obj['fullName'].includes(val) ) {
                    let template = Handlebars.compile(friendTemplate)
                    let html = template(obj)
                    $('.search_friends_container').append(html)
                }
            }
        }else {
            $('.friends_container').css('display', 'block')
            $('.search_friends_container').css('display', 'none')
        }

        changeCurrentFriends(csrfToken, myMessage, yourMessage)
    })
}
