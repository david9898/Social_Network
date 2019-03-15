$(document).ready(() => {
    sendMessage()
    changeCurrentFriends()
})

function sendMessage() {
    $('.send_message_button').on('click', function () {
        let csrfToken = $('#csrf_token').val()
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
                $.ajax({
                    url: 'TemplatesHbs/myMessage.hbs',
                    type: 'GET'
                }).then((res) => {

                    let obj = {
                        'content': content
                    }
                    let template = Handlebars.compile(res)
                    let html = template(obj)
                    $('.message_text .real_text_message_container').append(html)

                    let scrollHeight = $('.message_container .message_text')[0].scrollHeight
                    $('.message_text').animate({scrollTop: scrollHeight})
                    $('.send_message').val('')
                }).catch((err) => {
                    console.log(err)
                })
            }).catch((err) => {

            })
        }
    })
}

function changeCurrentFriends() {
    $('.friend_id').on('click', function () {
        let currentUser = $('.current_user').attr('acceptUser')
        let id = $(this).attr('id')

        if ( currentUser === id ) {
            return
        }
        
        $('.message_container .message_text .real_text_message_container').empty()
        let img = $(this).children('img').attr('src')
        let names = $(this).children('p').text()
        let csrfToken = $('#csrf_token').val()

        $('.message_container .current_user img').attr('src', img)
        $('.message_container .current_user h3').text(names)
        $('.message_container .current_user').attr('acceptUser', id)
        $('.message_container .message_text').attr('acceptUser', id)
        $('.message_container .message_text .sk-circle').css('display', 'block')

        $.ajax({
            url: 'api/getMessagesBetweenUsers/' + id + '/' + csrfToken,
            type: 'GET'
        }).then(async (res) => {
            let realData = JSON.parse(res)
            let myId = realData['userId']
            let data = realData['responce']

            if ( data === 'none' ) {
                $('.message_container .message_text .real_text_message_container').empty()
                $('.message_container .message_text .sk-circle').css('display', 'none')
                $('.message_container .message_text .real_text_message_container').append("<p class='be_first_to_message'>Be first to write something!!!</p>")
                $('.message_container .div_send_message').css('display', 'block')
                return
            }

            let myMessageTemplate = null
            let yourMessageTemplate = null
            await $.get('TemplatesHbs/yourMessage.hbs').then((res) => {
                yourMessageTemplate = res
            })
            await $.get('TemplatesHbs/myMessage.hbs').then((res) => {
                myMessageTemplate = res
            })

            for (let i = 0; i < data.length; i++) {
                $('.message_container .message_text .sk-circle').css('display', 'none')
                $('.message_container .div_send_message').css('display', 'block')

                if ( i < 20 ) {
                    if (data[i]['send_user']['id'] === myId) {
                        let template = Handlebars.compile(myMessageTemplate)
                        let html = template(data[i])
                        $('.message_container .message_text .real_text_message_container').prepend(html)
                    } else {
                        let template = Handlebars.compile(yourMessageTemplate)
                        let html = template(data[i])
                        $('.message_container .message_text .real_text_message_container').prepend(html)
                    }
                }else {

                }
            }

            let scrollHeight = $('.message_container .message_text')[0].scrollHeight
            $('.message_text').animate({scrollTop: scrollHeight})
            onScrollTop()
        })
    })
}

function onScrollTop() {
    let isAble = true
    let list = 1
    let scrollHeight = $('.message_container .message_text')[0].scrollHeight

    if ( scrollHeight > 430 ) {
        let csrfToken = $('#csrf_token').val()
        let currentUser = $('.current_user').attr('acceptUser')

        $('.message_container .message_text').scroll(function () {
            if (isAble === true) {
                let scrollTop = $(this).scrollTop()
                if (scrollTop === 0) {
                    $('.spinner').css('display', 'block')
                    isAble = false
                    $.ajax({
                        url: 'api/getMoreMessages/' + csrfToken + '/' + currentUser + '/' + list,
                        type: 'GET'
                    }).then(async (res) => {
                        let realData = JSON.parse(res)
                        let data = realData['messages']
                        let currentId = realData['currentId']

                        let myMessageTemplate = null
                        let yourMessageTemplate = null
                        await $.get('TemplatesHbs/yourMessage.hbs').then((res) => {
                            yourMessageTemplate = res
                        })
                        await $.get('TemplatesHbs/myMessage.hbs').then((res) => {
                            myMessageTemplate = res
                        })

                        $('.spinner').css('display', 'none')
                        for (let i = 0; i < data.length; i++) {

                            if ( i < 20 ) {
                                if ( data[i]['send_user']['id'] === currentId ) {
                                    let template = Handlebars.compile(myMessageTemplate)
                                    let html = template(data[i])
                                    $('.message_container .message_text .real_text_message_container').prepend(html)
                                }else {
                                    let template = Handlebars.compile(yourMessageTemplate)
                                    let html = template(data[i])
                                    $('.message_container .message_text .real_text_message_container').prepend(html)
                                }
                            }else {
                                isAble = true
                            }

                        }

                    })
                }
            }
        })
    }
}