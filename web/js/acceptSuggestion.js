$(document).ready(() => {
    onClickAccept()
    denySuggestion()
})

function onClickAccept() {
    $('.accept_suggestion').on('click', function () {
        let suggestionId = $(this).attr('id')
        let csrfToken = $('.find-friends-user').attr('csrf_token')

        let data = {
            'userId': suggestionId.trim(),
            'csrf_token': csrfToken
        }

        $.ajax({
            url: 'api/acceptSuggestion',
            type: 'POST',
            data: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            let parceRes = JSON.parse(res)
            
            if ( parceRes['status'] === 'success' ) {
                $(this).parent().fadeOut()
                return toastr.success('Success added!!!')
            }else {
                return toastr.error(parceRes['description'])
            }
        })
    })
}

function denySuggestion() {

    $('.deny_suggestion').on('click', function () {
        let csrfToken = $('.find-friends-user').attr('csrf_token')
        let otherUser = $(this).attr('id')

        $.ajax({
            url: 'api/disableSuggestion/' + otherUser + '/' + csrfToken,
            type: 'GET'
        }).then((res) => {
            let parceData = JSON.parse(res)

            if ( parceData['status'] === 'success' ) {
                $(this).parent().parent().fadeOut
                return toastr.success('You denied suggestion')
            }else {
                return toastr.error(parceData['description'])
            }
        })
    })
}
