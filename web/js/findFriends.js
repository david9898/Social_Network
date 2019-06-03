$(document).ready(
    async function () {
        sessionStorage.setItem('currentList', 1)

        searchFriends()

    }
)

function findFriends(searchUserTemplate) {
    $('#show_more').on('click', function () {
        $('.find-friends-user #show_more').remove()
        let num = Number(sessionStorage.getItem('currentList'));
        let biggerNum = num + 1;
        let csrfToken = $('.find-friends-user').attr('csrf_token')
        let name = $('.friends_search_input').val()

        $.ajax({
            url: 'api/findMoreFriends/' + biggerNum + '/' + csrfToken + '/' + name,
            type: 'GET'
        }).then((res) => {
            let responce = JSON.parse(res)
            for (let obj of responce['data']) {
                if ( obj['id'] == responce['currentId'] ) {
                    continue
                }else {
                    let template = Handlebars.compile(searchUserTemplate)
                    let html = template(obj)
                    $('.search_friends_results').append(html)
                }
            }

            if ( responce['last'] === 'falce' ) {
                $('.find-friends-user').append('<button id="show_more">show more</button>')
            }

            findFriends(searchUserTemplate)
            seeUserInDetails()

            sessionStorage.setItem('currentList', biggerNum)
        })
    })
}

function searchFriends() {
    $('.friends_search_input').on('input', function () {
        let name = $('.friends_search_input').val()

        let obj = {
            'command': 'searchFriends',
            'name': name
        }

        webSocket.send(JSON.stringify(obj))
    })
}

function addFriend() {
    $('.add-friend-button').on('click', function () {

    })
}

function seeUserInDetails() {
    $('.find-friends-user .search_friends_results div').on('click', function () {
        let id = $(this).attr('id')

        window.location.pathname = '/profile/' + id
    })
}