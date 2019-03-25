$(document).ready(
    function () {
        sessionStorage.setItem('currentList', 1)

        addSuggestion()

        $('#show_more').on('click', function () {
            let num = Number(sessionStorage.getItem('currentList'));

            let biggerNum = num + 1;

            sessionStorage.setItem('currentList', biggerNum)

            findFriends(biggerNum)
        })
    }
)

function findFriends(list) {
    $.ajax({
        url: 'api/findMoreFriends/' + list,
        method: 'GET'
    }).then(function (e) {
        let arr = JSON.parse(e);

        let users = arr['users'];

        $.get('TemplatesHbs/userTemplate.hbs').then((result) => {
            for (let obj of users) {
                let template = Handlebars.compile(result)
                let html = template(obj)
                $('.find-friends-user').append(html)
            }
            if ( arr['last'] != null ) {
                $('#show_more').remove();
            }else {
                addSuggestion()
            }
        }).catch(err => console.log(err))
    }).catch(err => console.log(err))
}

function addSuggestion() {
    $('.add_suggestion').on('click', function () {
        let id = $(this).attr('id')
        let csrfToken = $('.find-friends-user').attr('csrf_token');

        let data = {
            'csrf_token': csrfToken.trim(),
            'target_user': id
        }

        $.ajax({
            url: 'api/addSuggestion',
            type: 'POST',
            data: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            let respoce = JSON.parse(res);

            if ( respoce['status'] == 'success' ) {
                $(this).css('display', 'none')
                return toastr.success('Add was success')
            }else {
                return toastr.error('This user can`t accept friends for now!!!');
            }
        }).catch(err => console.log(err))

    })

}