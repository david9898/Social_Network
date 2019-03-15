$(document).ready(() => {
    // getUnseenSuggestions()
})

function getUnseenSuggestions() {
    setInterval(function () {
        $.ajax({
            url: 'api/getUnseenSuggestion',
            type: 'GET'
        }).then((res) => {
            let parseRes = JSON.parse(res)

            if ( parseRes['status'] === 'success' ) {
                if ( parseRes['suggestion'] === 'true' ) {
                    toastr.info('You have new suggestion!!!')
                }
            }
        }).catch((err) => console.log(err))
    }, 5000)
}
