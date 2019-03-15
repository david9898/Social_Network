$(document).ready(() => {
    onClickAccept()
})

function onClickAccept() {
    $('.accept_suggestion').on('click', function () {
        let suggestionId = $(this).attr('id')
        let csrfToken = $('.find-friends-user').attr('csrf_token')

        let data = {
            'suggestionId': suggestionId.trim(),
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
                return toastr.error('Something Wrong!!!')
            }
        })
    })
}