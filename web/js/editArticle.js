$(document).ready(() => {

    $('form').submit(function (e) {
        e.preventDefault()

        swal({
            title: "Update",
            text: "Do you want to update?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {

                let formData = new FormData(this)

                let xhr = new XMLHttpRequest()
                xhr.open("POST", String(window.location.pathname))
                xhr.send(formData)
                xhr.onload = function () {
                    if ( xhr.status === 200 ) {
                        let data = JSON.parse(xhr.responseText)

                        if ( data['status'] === 'success' ) {
                            window.location.pathname = '/home'
                        }
                    }
                }

            } else {

            }
        })
    })
})